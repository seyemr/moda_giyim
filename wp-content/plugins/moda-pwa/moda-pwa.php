<?php
/**
 * Plugin Name: Moda Chicago PWA
 * Description: Turns the store into an installable mobile app (PWA) with offline fallback and push notifications.
 * Version: 1.0.1
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Text Domain: moda-pwa
 *
 * @package Moda_PWA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MODA_PWA_VERSION', '1.0.1' );
define( 'MODA_PWA_DIR', plugin_dir_path( __FILE__ ) );
define( 'MODA_PWA_URL', plugin_dir_url( __FILE__ ) );

/**
 * Base64url encode.
 *
 * @param string $data Raw bytes.
 * @return string
 */
function moda_pwa_b64url( $data ) {
	return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
}

/**
 * Return the VAPID key pair, generating it once.
 *
 * @return array{public:string,private:string}|null
 */
function moda_pwa_keys() {
	$keys = get_option( 'moda_pwa_vapid' );
	if ( is_array( $keys ) && ! empty( $keys['public'] ) && ! empty( $keys['private'] ) ) {
		return $keys;
	}

	if ( ! function_exists( 'openssl_pkey_new' ) ) {
		return null;
	}

	$key = openssl_pkey_new(
		array(
			'curve_name'       => 'prime256v1',
			'private_key_type' => OPENSSL_KEYTYPE_EC,
		)
	);
	if ( ! $key ) {
		return null;
	}

	$details = openssl_pkey_get_details( $key );
	if ( empty( $details['ec']['x'] ) || empty( $details['ec']['y'] ) ) {
		return null;
	}

	if ( ! openssl_pkey_export( $key, $pem ) || ! is_string( $pem ) || '' === $pem ) {
		return null;
	}

	$keys = array(
		'public'  => moda_pwa_b64url( "\x04" . str_pad( $details['ec']['x'], 32, "\0", STR_PAD_LEFT ) . str_pad( $details['ec']['y'], 32, "\0", STR_PAD_LEFT ) ),
		'private' => $pem,
	);
	update_option( 'moda_pwa_vapid', $keys, false );

	return $keys;
}

/**
 * Serve manifest, service worker and offline page from the site root.
 *
 * @return void
 */
function moda_pwa_serve_root_files() {
	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';
	$home = untrailingslashit( (string) wp_parse_url( home_url(), PHP_URL_PATH ) );
	$path = '/' . ltrim( substr( (string) $path, strlen( $home ) ), '/' );

	if ( '/manifest.webmanifest' === $path ) {
		nocache_headers();
		header( 'Content-Type: application/manifest+json; charset=utf-8' );
		echo wp_json_encode(
			array(
				'name'             => get_bloginfo( 'name' ),
				'short_name'       => 'Moda Chicago',
				'description'      => get_bloginfo( 'description' ),
				'lang'             => 'tr',
				'start_url'        => home_url( '/?utm_source=pwa' ),
				'scope'            => home_url( '/' ),
				'id'               => home_url( '/' ),
				'display'          => 'standalone',
				'orientation'      => 'portrait',
				'background_color' => '#111010',
				'theme_color'      => '#111010',
				'icons'            => array(
					array(
						'src'     => MODA_PWA_URL . 'assets/icon-192.png',
						'sizes'   => '192x192',
						'type'    => 'image/png',
						'purpose' => 'any',
					),
					array(
						'src'     => MODA_PWA_URL . 'assets/icon-512.png',
						'sizes'   => '512x512',
						'type'    => 'image/png',
						'purpose' => 'any',
					),
					array(
						'src'     => MODA_PWA_URL . 'assets/icon-512.png',
						'sizes'   => '512x512',
						'type'    => 'image/png',
						'purpose' => 'maskable',
					),
				),
			)
		);
		exit;
	}

	if ( '/moda-sw.js' === $path ) {
		$script = file_get_contents( MODA_PWA_DIR . 'assets/sw.js' );
		$script = strtr(
			(string) $script,
			array(
				'__VERSION__'     => MODA_PWA_VERSION . '-' . (string) filemtime( MODA_PWA_DIR . 'assets/sw.js' ),
				'__OFFLINE_URL__' => esc_url_raw( home_url( '/?moda_pwa=offline' ) ),
				'__LATEST_URL__'  => esc_url_raw( rest_url( 'moda-pwa/v1/latest' ) ),
				'__ICON_URL__'    => esc_url_raw( MODA_PWA_URL . 'assets/icon-192.png' ),
			)
		);
		nocache_headers();
		header( 'Content-Type: application/javascript; charset=utf-8' );
		header( 'Service-Worker-Allowed: /' );
		echo $script; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static script with escaped URLs.
		exit;
	}

	if ( isset( $_GET['moda_pwa'] ) && 'offline' === sanitize_key( wp_unslash( $_GET['moda_pwa'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );
		?>
<!doctype html>
<html lang="tr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#111010">
	<title>Çevrimdışı — Moda Chicago</title>
	<style>
		body{display:grid;min-height:100vh;margin:0;place-items:center;background:#111010;color:#f5f1f0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;text-align:center}
		main{max-width:360px;padding:32px}
		img{width:88px;height:88px;border-radius:20px}
		h1{margin:22px 0 8px;font-size:24px}
		p{margin:0 0 24px;color:#b9b5ad;line-height:1.6}
		a{display:inline-flex;min-height:46px;align-items:center;justify-content:center;padding:0 28px;border-radius:6px;background:#9b1c35;color:#fff;font:600 14px -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;text-decoration:none}
	</style>
</head>
<body>
	<main>
		<img src="<?php echo esc_url( MODA_PWA_URL . 'assets/icon-192.png' ); ?>" alt="">
		<h1>İnternet bağlantısı yok</h1>
		<p>Moda Chicago’yu görüntülemek için bağlantınızı kontrol edip tekrar deneyin.</p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Tekrar dene</a>
	</main>
</body>
</html>
		<?php
		exit;
	}
}
add_action( 'init', 'moda_pwa_serve_root_files', 1 );

/**
 * Add manifest and iOS/Android meta tags.
 *
 * @return void
 */
function moda_pwa_head() {
	?>
<link rel="manifest" href="<?php echo esc_url( home_url( '/manifest.webmanifest' ) ); ?>">
<meta name="theme-color" content="#111010">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Moda Chicago">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<link rel="apple-touch-icon" href="<?php echo esc_url( MODA_PWA_URL . 'assets/apple-touch-icon.png' ); ?>">
	<?php
}
add_action( 'wp_head', 'moda_pwa_head', 3 );

/**
 * Load the install/push client.
 *
 * @return void
 */
function moda_pwa_enqueue() {
	$keys = moda_pwa_keys();

	wp_enqueue_style( 'moda-pwa', MODA_PWA_URL . 'assets/pwa.css', array(), MODA_PWA_VERSION );
	wp_enqueue_script( 'moda-pwa', MODA_PWA_URL . 'assets/pwa.js', array(), MODA_PWA_VERSION, true );
	wp_localize_script(
		'moda-pwa',
		'modaPwa',
		array(
			'swUrl'     => home_url( '/moda-sw.js' ),
			'restUrl'   => esc_url_raw( rest_url( 'moda-pwa/v1/' ) ),
			'publicKey' => $keys ? $keys['public'] : '',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'moda_pwa_enqueue' );

/**
 * Register REST endpoints.
 *
 * @return void
 */
function moda_pwa_register_routes() {
	register_rest_route(
		'moda-pwa/v1',
		'/subscribe',
		array(
			'methods'             => 'POST',
			'permission_callback' => '__return_true',
			'callback'            => 'moda_pwa_rest_subscribe',
		)
	);
	register_rest_route(
		'moda-pwa/v1',
		'/latest',
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => 'moda_pwa_rest_latest',
		)
	);
}
add_action( 'rest_api_init', 'moda_pwa_register_routes' );

/**
 * Store a push subscription endpoint.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function moda_pwa_rest_subscribe( $request ) {
	$endpoint = esc_url_raw( (string) $request->get_param( 'endpoint' ), array( 'https' ) );
	if ( ! $endpoint || strlen( $endpoint ) > 1000 ) {
		return new WP_Error( 'moda_pwa_invalid', __( 'Geçersiz abonelik.', 'moda-pwa' ), array( 'status' => 400 ) );
	}

	$subscriptions = get_option( 'moda_pwa_subscriptions', array() );
	if ( ! is_array( $subscriptions ) ) {
		$subscriptions = array();
	}
	if ( count( $subscriptions ) < 20000 ) {
		$subscriptions[ md5( $endpoint ) ] = $endpoint;
		update_option( 'moda_pwa_subscriptions', $subscriptions, false );
	}

	return rest_ensure_response( array( 'ok' => true ) );
}

/**
 * Return the most recent notification for the service worker.
 *
 * @return WP_REST_Response
 */
function moda_pwa_rest_latest() {
	$latest = get_option( 'moda_pwa_latest', array() );
	$latest = is_array( $latest ) ? $latest : array();

	$response = rest_ensure_response(
		array(
			'title' => isset( $latest['title'] ) ? $latest['title'] : 'Moda Chicago',
			'body'  => isset( $latest['body'] ) ? $latest['body'] : 'Yeni bir bildiriminiz var.',
			'url'   => isset( $latest['url'] ) ? $latest['url'] : home_url( '/' ),
		)
	);
	$response->header( 'Cache-Control', 'no-store' );

	return $response;
}

/**
 * Convert an OpenSSL DER ECDSA signature to the raw R||S form JWT requires.
 *
 * @param string $der DER signature.
 * @return string
 */
function moda_pwa_der_to_raw( $der ) {
	$offset = 3;
	$r_len  = ord( $der[ $offset ] );
	$r      = substr( $der, $offset + 1, $r_len );
	$offset = $offset + 1 + $r_len + 1;
	$s_len  = ord( $der[ $offset ] );
	$s      = substr( $der, $offset + 1, $s_len );

	return str_pad( ltrim( $r, "\0" ), 32, "\0", STR_PAD_LEFT ) . str_pad( ltrim( $s, "\0" ), 32, "\0", STR_PAD_LEFT );
}

/**
 * Build the VAPID Authorization header for a push endpoint.
 *
 * @param string $endpoint Push service endpoint.
 * @return string|null
 */
function moda_pwa_vapid_header( $endpoint ) {
	$keys = moda_pwa_keys();
	if ( ! $keys ) {
		return null;
	}

	$parts = wp_parse_url( $endpoint );
	if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
		return null;
	}

	$audience = $parts['scheme'] . '://' . $parts['host'] . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' );
	$subject  = 'mailto:' . sanitize_email( get_option( 'admin_email' ) );
	$unsigned = moda_pwa_b64url( wp_json_encode( array( 'typ' => 'JWT', 'alg' => 'ES256' ) ) ) . '.' . moda_pwa_b64url(
		wp_json_encode(
			array(
				'aud' => $audience,
				'exp' => time() + 12 * HOUR_IN_SECONDS - 60,
				'sub' => $subject,
			)
		)
	);

	if ( ! openssl_sign( $unsigned, $der, $keys['private'], OPENSSL_ALGO_SHA256 ) ) {
		return null;
	}

	return 'vapid t=' . $unsigned . '.' . moda_pwa_b64url( moda_pwa_der_to_raw( $der ) ) . ', k=' . $keys['public'];
}

/**
 * Send a notification to every subscriber.
 *
 * @param string $title Title.
 * @param string $body Body.
 * @param string $url Target URL.
 * @return array{sent:int,removed:int,failed:int}
 */
function moda_pwa_broadcast( $title, $body, $url ) {
	update_option(
		'moda_pwa_latest',
		array(
			'title' => $title,
			'body'  => $body,
			'url'   => $url,
		),
		false
	);

	$subscriptions = get_option( 'moda_pwa_subscriptions', array() );
	$subscriptions = is_array( $subscriptions ) ? $subscriptions : array();
	$result        = array(
		'sent'    => 0,
		'removed' => 0,
		'failed'  => 0,
	);

	foreach ( $subscriptions as $id => $endpoint ) {
		$authorization = moda_pwa_vapid_header( $endpoint );
		if ( ! $authorization ) {
			++$result['failed'];
			continue;
		}

		$response = wp_safe_remote_post(
			$endpoint,
			array(
				'timeout' => 10,
				'headers' => array(
					'Authorization'  => $authorization,
					'TTL'            => '86400',
					'Urgency'        => 'normal',
					'Content-Length' => '0',
				),
				'body'    => '',
			)
		);

		$code = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
		if ( $code >= 200 && $code < 300 ) {
			++$result['sent'];
		} elseif ( 404 === $code || 410 === $code ) {
			unset( $subscriptions[ $id ] );
			++$result['removed'];
		} else {
			++$result['failed'];
		}
	}

	update_option( 'moda_pwa_subscriptions', $subscriptions, false );

	return $result;
}

/**
 * Add the admin screen.
 *
 * @return void
 */
function moda_pwa_admin_menu() {
	add_menu_page( 'Mobil Uygulama', 'Mobil Uygulama', 'manage_options', 'moda-pwa', 'moda_pwa_admin_page', 'dashicons-smartphone', 58 );
}
add_action( 'admin_menu', 'moda_pwa_admin_menu' );

/**
 * Render the admin screen and handle sending.
 *
 * @return void
 */
function moda_pwa_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$notice = '';
	if ( isset( $_POST['moda_pwa_send'] ) && check_admin_referer( 'moda_pwa_send' ) ) {
		$title  = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$body   = isset( $_POST['body'] ) ? sanitize_text_field( wp_unslash( $_POST['body'] ) ) : '';
		$url    = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		$home   = wp_parse_url( home_url( '/' ) );
		$target = $url ? wp_parse_url( $url ) : false;
		$home_scheme = is_array( $home ) && isset( $home['scheme'] ) ? strtolower( $home['scheme'] ) : '';
		$target_scheme = is_array( $target ) && isset( $target['scheme'] ) ? strtolower( $target['scheme'] ) : '';
		$home_port = is_array( $home ) && isset( $home['port'] ) ? $home['port'] : ( 'https' === $home_scheme ? 443 : 80 );
		$target_port = is_array( $target ) && isset( $target['port'] ) ? $target['port'] : ( 'https' === $target_scheme ? 443 : 80 );
		if (
			! is_array( $home )
			|| ! is_array( $target )
			|| empty( $home['host'] )
			|| empty( $target['host'] )
			|| strtolower( $home['host'] ) !== strtolower( $target['host'] )
			|| $home_scheme !== $target_scheme
			|| $home_port !== $target_port
			|| isset( $target['user'] )
			|| isset( $target['pass'] )
		) {
			$url = home_url( '/' );
		}

		if ( '' === $title || '' === $body ) {
			$notice = '<div class="notice notice-error"><p>Başlık ve mesaj zorunludur.</p></div>';
		} else {
			$result = moda_pwa_broadcast( $title, $body, $url );
			$notice_class = $result['sent'] > 0
				? ( $result['failed'] > 0 ? 'notice-warning' : 'notice-success' )
				: 'notice-error';
			$notice = sprintf(
				'<div class="notice %4$s"><p>Gönderildi: %1$d, kaldırılan geçersiz abonelik: %2$d, başarısız: %3$d.</p></div>',
				$result['sent'],
				$result['removed'],
				$result['failed'],
				$notice_class
			);
		}
	}

	$subscriptions = get_option( 'moda_pwa_subscriptions', array() );
	$count         = is_array( $subscriptions ) ? count( $subscriptions ) : 0;
	$keys          = moda_pwa_keys();
	?>
	<div class="wrap">
		<h1>Mobil Uygulama</h1>
		<?php echo wp_kses_post( $notice ); ?>
		<?php if ( ! $keys ) : ?>
			<div class="notice notice-warning"><p>Push bildirimleri için sunucuda OpenSSL EC desteği gerekir. Uygulama kurulumu ve çevrimdışı ekran bu durumdan etkilenmez.</p></div>
		<?php endif; ?>
		<p>Ziyaretçiler siteyi telefonlarına uygulama olarak yükleyebilir. Bildirim abone sayısı: <strong><?php echo esc_html( (string) $count ); ?></strong></p>
		<p>Manifest: <a href="<?php echo esc_url( home_url( '/manifest.webmanifest' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( home_url( '/manifest.webmanifest' ) ); ?></a></p>
		<h2>Bildirim gönder</h2>
		<form method="post">
			<?php wp_nonce_field( 'moda_pwa_send' ); ?>
			<table class="form-table" role="presentation">
				<tr><th><label for="moda-pwa-title">Başlık</label></th><td><input id="moda-pwa-title" name="title" type="text" class="regular-text" maxlength="60" required></td></tr>
				<tr><th><label for="moda-pwa-body">Mesaj</label></th><td><input id="moda-pwa-body" name="body" type="text" class="large-text" maxlength="140" required></td></tr>
				<tr><th><label for="moda-pwa-url">Açılacak sayfa</label></th><td><input id="moda-pwa-url" name="url" type="url" class="large-text" value="<?php echo esc_attr( home_url( '/' ) ); ?>"></td></tr>
			</table>
			<?php submit_button( 'Tüm abonelere gönder', 'primary', 'moda_pwa_send' ); ?>
		</form>
	</div>
	<?php
}
