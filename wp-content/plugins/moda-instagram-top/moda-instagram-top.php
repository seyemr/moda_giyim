<?php
/**
 * Plugin Name: Moda Instagram Vitrini
 * Description: Instagram hesabındaki en çok izlenen veya en çok etkileşim alan paylaşımı ana sayfada otomatik gösterir.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Text Domain: moda-instagram-top
 *
 * @package Moda_Instagram_Top
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MODA_IG_TOP_VERSION', '1.0.0' );
define( 'MODA_IG_TOP_URL', plugin_dir_url( __FILE__ ) );
define( 'MODA_IG_TOP_API', 'https://graph.instagram.com/v21.0/' );

/**
 * Read the stored top post data.
 *
 * @return array
 */
function moda_ig_top_get_state() {
	$state = get_option( 'moda_ig_top_state', array() );
	return is_array( $state ) ? $state : array();
}

/**
 * Sanitize the access token; an empty submission keeps the saved token.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function moda_ig_top_sanitize_token( $value ) {
	$value = preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $value );
	return '' === $value ? (string) get_option( 'moda_ig_top_token', '' ) : $value;
}

/**
 * Sanitize ranking metric.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function moda_ig_top_sanitize_metric( $value ) {
	return 'engagement' === $value ? 'engagement' : 'views';
}

/**
 * Sanitize the day window.
 *
 * @param mixed $value Submitted value.
 * @return int
 */
function moda_ig_top_sanitize_days( $value ) {
	return max( 0, min( 365, absint( $value ) ) );
}

/**
 * Sanitize the enabled flag.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function moda_ig_top_sanitize_enabled( $value ) {
	return '1' === $value ? '1' : '0';
}

/**
 * Register settings.
 *
 * @return void
 */
function moda_ig_top_register_settings() {
	register_setting( 'moda_ig_top', 'moda_ig_top_token', array( 'type' => 'string', 'sanitize_callback' => 'moda_ig_top_sanitize_token', 'autoload' => false ) );
	register_setting( 'moda_ig_top', 'moda_ig_top_metric', array( 'type' => 'string', 'sanitize_callback' => 'moda_ig_top_sanitize_metric', 'default' => 'views' ) );
	register_setting( 'moda_ig_top', 'moda_ig_top_days', array( 'type' => 'integer', 'sanitize_callback' => 'moda_ig_top_sanitize_days', 'default' => 30 ) );
	register_setting( 'moda_ig_top', 'moda_ig_top_enabled', array( 'type' => 'string', 'sanitize_callback' => 'moda_ig_top_sanitize_enabled', 'default' => '1' ) );
}
add_action( 'admin_init', 'moda_ig_top_register_settings' );

/**
 * Add the settings page.
 *
 * @return void
 */
function moda_ig_top_admin_menu() {
	add_submenu_page( 'woocommerce', __( 'Instagram Vitrini', 'moda-instagram-top' ), __( 'Instagram Vitrini', 'moda-instagram-top' ), 'manage_woocommerce', 'moda-instagram-top', 'moda_ig_top_settings_page' );
}
add_action( 'admin_menu', 'moda_ig_top_admin_menu' );

/**
 * Call the Instagram API.
 *
 * @param string $path  Endpoint path.
 * @param array  $query Query arguments.
 * @param string $token Access token.
 * @return array|WP_Error
 */
function moda_ig_top_request( $path, $query, $token ) {
	$query['access_token'] = $token;
	$response              = wp_remote_get( add_query_arg( $query, MODA_IG_TOP_API . ltrim( $path, '/' ) ), array( 'timeout' => 15 ) );
	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'moda_ig_http', __( 'Instagram sunucusuna bağlanılamadı.', 'moda-instagram-top' ) );
	}
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) || ! is_array( $body ) ) {
		$message = is_array( $body ) && ! empty( $body['error']['message'] ) ? sanitize_text_field( $body['error']['message'] ) : __( 'Instagram beklenmeyen bir yanıt verdi.', 'moda-instagram-top' );
		return new WP_Error( 'moda_ig_api', $message );
	}
	return $body;
}

/**
 * Fetch posts, rank them and store the winner.
 *
 * @return true|WP_Error
 */
function moda_ig_top_refresh() {
	$token = (string) get_option( 'moda_ig_top_token', '' );
	if ( '' === $token ) {
		return new WP_Error( 'moda_ig_token', __( 'Erişim anahtarı (token) girilmemiş.', 'moda-instagram-top' ) );
	}

	$state = moda_ig_top_get_state();

	// Long-lived tokens last 60 days; renew them monthly.
	if ( empty( $state['token_refreshed'] ) || time() - (int) $state['token_refreshed'] > 30 * DAY_IN_SECONDS ) {
		$renewed = moda_ig_top_request( 'refresh_access_token', array( 'grant_type' => 'ig_refresh_token' ), $token );
		if ( ! is_wp_error( $renewed ) && ! empty( $renewed['access_token'] ) ) {
			$token = preg_replace( '/[^A-Za-z0-9_\-]/', '', $renewed['access_token'] );
			update_option( 'moda_ig_top_token', $token, false );
			$state['token_refreshed'] = time();
		}
	}

	$media = moda_ig_top_request(
		'me/media',
		array(
			'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count',
			'limit'  => 50,
		),
		$token
	);
	if ( is_wp_error( $media ) ) {
		$state['error']   = $media->get_error_message();
		$state['checked'] = time();
		update_option( 'moda_ig_top_state', $state, false );
		return $media;
	}

	$days   = (int) get_option( 'moda_ig_top_days', 30 );
	$cutoff = $days > 0 ? time() - $days * DAY_IN_SECONDS : 0;
	$posts  = array();
	foreach ( (array) ( $media['data'] ?? array() ) as $item ) {
		$taken = isset( $item['timestamp'] ) ? (int) strtotime( $item['timestamp'] ) : 0;
		$image = 'VIDEO' === ( $item['media_type'] ?? '' ) ? ( $item['thumbnail_url'] ?? '' ) : ( $item['media_url'] ?? '' );
		if ( empty( $item['permalink'] ) || '' === $image || $taken < $cutoff ) {
			continue;
		}
		$insights = moda_ig_top_request( $item['id'] . '/insights', array( 'metric' => 'views,saved,shares' ), $token );
		$metrics  = array();
		if ( ! is_wp_error( $insights ) ) {
			foreach ( (array) ( $insights['data'] ?? array() ) as $metric ) {
				$metrics[ $metric['name'] ] = (int) ( $metric['values'][0]['value'] ?? 0 );
			}
		}
		$likes    = (int) ( $item['like_count'] ?? 0 );
		$comments = (int) ( $item['comments_count'] ?? 0 );
		$posts[]  = array(
			'permalink'  => esc_url_raw( $item['permalink'] ),
			'image'      => esc_url_raw( $image ),
			'caption'    => sanitize_textarea_field( $item['caption'] ?? '' ),
			'likes'      => $likes,
			'comments'   => $comments,
			'views'      => $metrics['views'] ?? null,
			'engagement' => $likes + $comments + ( $metrics['saved'] ?? 0 ) + ( $metrics['shares'] ?? 0 ),
		);
	}

	$metric   = 'engagement' === get_option( 'moda_ig_top_metric', 'views' ) ? 'engagement' : 'views';
	$all_view = ! empty( $posts ) && ! in_array( null, wp_list_pluck( $posts, 'views' ), true );
	if ( 'views' === $metric && ! $all_view ) {
		$metric = 'engagement';
	}
	usort( $posts, static fn( $a, $b ) => $b[ $metric ] <=> $a[ $metric ] );

	$state['checked'] = time();
	$state['error']   = empty( $posts ) ? __( 'Seçilen dönemde uygun paylaşım bulunamadı.', 'moda-instagram-top' ) : '';
	$state['metric']  = $metric;
	$state['top']     = $posts ? $posts[0] : array();
	update_option( 'moda_ig_top_state', $state, false );

	return empty( $posts ) ? new WP_Error( 'moda_ig_empty', $state['error'] ) : true;
}
add_action( 'moda_ig_top_cron', 'moda_ig_top_refresh' );

/**
 * Register the six-hour schedule.
 *
 * @param array $schedules Schedules.
 * @return array
 */
function moda_ig_top_cron_schedules( $schedules ) {
	$schedules['moda_ig_six_hours'] = array(
		'interval' => 6 * HOUR_IN_SECONDS,
		'display'  => __( 'Altı saatte bir', 'moda-instagram-top' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'moda_ig_top_cron_schedules' );

/**
 * Keep the six-hourly refresh scheduled.
 *
 * @return void
 */
function moda_ig_top_schedule() {
	if ( ! wp_next_scheduled( 'moda_ig_top_cron' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'moda_ig_six_hours', 'moda_ig_top_cron' );
	}
}
add_action( 'init', 'moda_ig_top_schedule' );

/**
 * Manual refresh handler.
 *
 * @return void
 */
function moda_ig_top_handle_refresh() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Bu işlem için yetkiniz yok.', 'moda-instagram-top' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'moda_ig_top_refresh' );
	moda_ig_top_refresh();
	wp_safe_redirect( admin_url( 'admin.php?page=moda-instagram-top&refreshed=1' ) );
	exit;
}
add_action( 'admin_post_moda_ig_top_refresh', 'moda_ig_top_handle_refresh' );

/**
 * Render the settings page.
 *
 * @return void
 */
function moda_ig_top_settings_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	$state  = moda_ig_top_get_state();
	$top    = $state['top'] ?? array();
	$metric = get_option( 'moda_ig_top_metric', 'views' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Instagram Vitrini', 'moda-instagram-top' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'moda_ig_top' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="moda_ig_top_token"><?php esc_html_e( 'Erişim anahtarı (token)', 'moda-instagram-top' ); ?></label></th>
					<td>
						<input type="password" id="moda_ig_top_token" name="moda_ig_top_token" class="regular-text" autocomplete="off" placeholder="<?php echo get_option( 'moda_ig_top_token' ) ? esc_attr__( 'Kayıtlı — değiştirmek için yeni anahtarı yazın', 'moda-instagram-top' ) : ''; ?>">
						<p class="description"><?php esc_html_e( 'Meta Developers > Instagram API (Instagram ile giriş) bölümünden business/creator hesabınız için üretilen uzun süreli anahtar. Anahtar 60 günde dolar; eklenti kendiliğinden yeniler.', 'moda-instagram-top' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="moda_ig_top_metric"><?php esc_html_e( 'Sıralama ölçütü', 'moda-instagram-top' ); ?></label></th>
					<td>
						<select id="moda_ig_top_metric" name="moda_ig_top_metric">
							<option value="views" <?php selected( $metric, 'views' ); ?>><?php esc_html_e( 'En çok görüntülenen', 'moda-instagram-top' ); ?></option>
							<option value="engagement" <?php selected( $metric, 'engagement' ); ?>><?php esc_html_e( 'En çok etkileşim (beğeni + yorum + kaydetme + paylaşım)', 'moda-instagram-top' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Görüntülenme verisi alınamazsa etkileşime göre sıralanır. Instagram tek tek bağlantı tıklamalarını vermez.', 'moda-instagram-top' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="moda_ig_top_days"><?php esc_html_e( 'Son kaç gün', 'moda-instagram-top' ); ?></label></th>
					<td><input type="number" id="moda_ig_top_days" name="moda_ig_top_days" min="0" max="365" value="<?php echo esc_attr( (string) get_option( 'moda_ig_top_days', 30 ) ); ?>" class="small-text"> <span class="description"><?php esc_html_e( 'Son 50 paylaşım arasından bu süre içindekiler değerlendirilir. 0 = süre sınırı yok.', 'moda-instagram-top' ); ?></span></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Ana sayfada göster', 'moda-instagram-top' ); ?></th>
					<td>
						<input type="hidden" name="moda_ig_top_enabled" value="0">
						<label><input type="checkbox" name="moda_ig_top_enabled" value="1" <?php checked( get_option( 'moda_ig_top_enabled', '1' ), '1' ); ?>> <?php esc_html_e( 'Etkin', 'moda-instagram-top' ); ?></label>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Değişiklikleri kaydet', 'moda-instagram-top' ) ); ?>
		</form>

		<h2><?php esc_html_e( 'Durum', 'moda-instagram-top' ); ?></h2>
		<?php if ( isset( $_GET['refreshed'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-info inline"><p><?php esc_html_e( 'Yenileme çalıştırıldı.', 'moda-instagram-top' ); ?></p></div>
		<?php endif; ?>
		<?php if ( ! empty( $state['error'] ) ) : ?>
			<div class="notice notice-error inline"><p><?php echo esc_html( $state['error'] ); ?></p></div>
		<?php endif; ?>
		<p>
			<?php
			echo ! empty( $state['checked'] )
				? esc_html( sprintf( /* translators: %s: date */ __( 'Son kontrol: %s', 'moda-instagram-top' ), wp_date( 'd.m.Y H:i', (int) $state['checked'] ) ) )
				: esc_html__( 'Henüz kontrol edilmedi.', 'moda-instagram-top' );
			?>
		</p>
		<?php if ( $top ) : ?>
			<p>
				<?php esc_html_e( 'Seçilen paylaşım:', 'moda-instagram-top' ); ?>
				<a href="<?php echo esc_url( $top['permalink'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $top['permalink'] ); ?></a>
				(<?php echo 'views' === ( $state['metric'] ?? '' ) ? esc_html( sprintf( /* translators: %s: number */ __( '%s görüntülenme', 'moda-instagram-top' ), number_format_i18n( (int) $top['views'] ) ) ) : esc_html( sprintf( /* translators: %s: number */ __( '%s etkileşim', 'moda-instagram-top' ), number_format_i18n( (int) $top['engagement'] ) ) ); ?>)
			</p>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="moda_ig_top_refresh">
			<?php wp_nonce_field( 'moda_ig_top_refresh' ); ?>
			<?php submit_button( __( 'Şimdi yenile', 'moda-instagram-top' ), 'secondary', 'submit', false ); ?>
		</form>
	</div>
	<?php
}

/**
 * Render the homepage card.
 *
 * @return void
 */
function moda_ig_top_render() {
	$state = moda_ig_top_get_state();
	$top   = $state['top'] ?? array();
	if ( '1' !== get_option( 'moda_ig_top_enabled', '1' ) || empty( $top['permalink'] ) || empty( $top['image'] ) ) {
		return;
	}
	wp_enqueue_style( 'moda-instagram-top', MODA_IG_TOP_URL . 'assets/instagram.css', array(), MODA_IG_TOP_VERSION );
	$caption = wp_trim_words( (string) $top['caption'], 24, '…' );
	?>
	<section class="ma-section moda-ig-top" aria-labelledby="moda-ig-top-title">
		<div class="ma-container">
			<div class="moda-ig-top-card">
				<a class="moda-ig-top-media" href="<?php echo esc_url( $top['permalink'] ); ?>" target="_blank" rel="noopener noreferrer">
					<img src="<?php echo esc_url( $top['image'] ); ?>" alt="<?php echo esc_attr( $caption ? $caption : __( 'Instagram paylaşımı', 'moda-instagram-top' ) ); ?>" loading="lazy" referrerpolicy="no-referrer">
				</a>
				<div class="moda-ig-top-body">
					<p class="ma-eyebrow"><?php esc_html_e( 'Instagram’da en çok ilgi gören', 'moda-instagram-top' ); ?></p>
					<h2 id="moda-ig-top-title"><?php esc_html_e( 'Şu an herkes bunu konuşuyor', 'moda-instagram-top' ); ?></h2>
					<?php if ( $caption ) : ?>
						<p class="moda-ig-top-caption"><?php echo esc_html( $caption ); ?></p>
					<?php endif; ?>
					<ul class="moda-ig-top-stats">
						<?php if ( 'views' === ( $state['metric'] ?? '' ) && null !== $top['views'] ) : ?>
							<li><?php echo esc_html( sprintf( /* translators: %s: number */ __( '%s görüntülenme', 'moda-instagram-top' ), number_format_i18n( (int) $top['views'] ) ) ); ?></li>
						<?php endif; ?>
						<?php if ( $top['likes'] > 0 ) : ?>
							<li><?php echo esc_html( sprintf( /* translators: %s: number */ __( '%s beğeni', 'moda-instagram-top' ), number_format_i18n( (int) $top['likes'] ) ) ); ?></li>
						<?php endif; ?>
					</ul>
					<a class="moda-ig-top-link" href="<?php echo esc_url( $top['permalink'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Instagram’da gör', 'moda-instagram-top' ); ?> <span aria-hidden="true">↗</span></a>
				</div>
			</div>
		</div>
	</section>
	<?php
}
add_action( 'moda_atelier_after_products', 'moda_ig_top_render' );
