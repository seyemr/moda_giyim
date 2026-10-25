<?php
/**
 * Plugin Name: Moda Commerce Tools
 * Description: Favoriler, beden rehberi, ürün bildirimleri, kargo takibi ve izinli sepet hatırlatmaları.
 * Version: 1.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Text Domain: moda-commerce-tools
 *
 * @package Moda_Commerce_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MODA_COMMERCE_TOOLS_VERSION', '1.1.0' );
define( 'MODA_COMMERCE_TOOLS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Register product question and notification records.
 *
 * @return void
 */
function moda_commerce_register_post_types() {
	register_post_type(
		'moda_product_question',
		array(
			'labels' => array(
				'name'          => __( 'Ürün Soruları', 'moda-commerce-tools' ),
				'singular_name' => __( 'Ürün Sorusu', 'moda-commerce-tools' ),
				'menu_name'     => __( 'Ürün Soruları', 'moda-commerce-tools' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-editor-help',
			'supports'            => array( 'title' ),
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'        => true,
		)
	);
	register_post_type(
		'moda_product_alert',
		array(
			'labels' => array(
				'name'          => __( 'Ürün Bildirimleri', 'moda-commerce-tools' ),
				'singular_name' => __( 'Ürün Bildirimi', 'moda-commerce-tools' ),
				'menu_name'     => __( 'Ürün Bildirimleri', 'moda-commerce-tools' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-email-alt',
			'supports'            => array( 'title' ),
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'        => true,
		)
	);
}
add_action( 'init', 'moda_commerce_register_post_types' );

/**
 * Register WooCommerce account endpoints and refresh rewrite rules once.
 *
 * @return void
 */
function moda_commerce_activate() {
	moda_commerce_register_post_types();
	add_rewrite_endpoint( 'moda-wishlist', EP_ROOT | EP_PAGES );
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'moda_commerce_activate' );

/**
 * Add the account wishlist endpoint.
 *
 * @return void
 */
function moda_commerce_add_account_endpoint() {
	add_rewrite_endpoint( 'moda-wishlist', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'moda_commerce_add_account_endpoint' );

/**
 * Add the wishlist account menu entry.
 *
 * @param array<string,string> $items Account navigation items.
 * @return array<string,string>
 */
function moda_commerce_account_menu_items( $items ) {
	$logout = isset( $items['customer-logout'] ) ? $items['customer-logout'] : null;
	unset( $items['customer-logout'] );
	$items['moda-wishlist'] = __( 'Favorilerim', 'moda-commerce-tools' );
	if ( null !== $logout ) {
		$items['customer-logout'] = $logout;
	}

	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'moda_commerce_account_menu_items', 40 );

/**
 * Render a customer's persisted wishlist.
 *
 * @return void
 */
function moda_commerce_render_wishlist_endpoint() {
	$ids = array_filter( array_map( 'absint', (array) get_user_meta( get_current_user_id(), '_moda_wishlist', true ) ) );
	echo '<div class="moda-commerce-wishlist">';
	echo '<h2>' . esc_html__( 'Favorilerim', 'moda-commerce-tools' ) . '</h2>';
	if ( ! $ids ) {
		echo '<p>' . esc_html__( 'Henüz favori ürününüz yok. Ürün sayfalarındaki kalp simgesiyle ürünleri listenize ekleyebilirsiniz.', 'moda-commerce-tools' ) . '</p></div>';
		return;
	}

	echo '<ul>';
	foreach ( $ids as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product || 'publish' !== get_post_status( $product_id ) ) {
			continue;
		}
		printf(
			'<li><a href="%1$s">%2$s</a><span>%3$s</span><button type="button" class="moda-wishlist-remove" data-product-id="%4$d">%5$s</button></li>',
			esc_url( $product->get_permalink() ),
			esc_html( $product->get_name() ),
			wp_kses_post( $product->get_price_html() ),
			(int) $product_id,
			esc_html__( 'Kaldır', 'moda-commerce-tools' )
		);
	}
	echo '</ul></div>';
}
add_action( 'woocommerce_account_moda-wishlist_endpoint', 'moda_commerce_render_wishlist_endpoint' );

/**
 * Persist a wishlist change for the current customer.
 *
 * @return void
 */
function moda_commerce_ajax_wishlist() {
	check_ajax_referer( 'moda_commerce_wishlist', 'nonce' );
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Favorileri hesabınıza kaydetmek için giriş yapın.', 'moda-commerce-tools' ) ), 401 );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
	$enabled    = isset( $_POST['enabled'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['enabled'] ) );
	if ( ! $product_id || 'product' !== get_post_type( $product_id ) || 'publish' !== get_post_status( $product_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Ürün bulunamadı.', 'moda-commerce-tools' ) ), 404 );
	}

	$user_id = get_current_user_id();
	$ids     = array_filter( array_map( 'absint', (array) get_user_meta( $user_id, '_moda_wishlist', true ) ) );
	$ids     = array_values( array_diff( $ids, array( $product_id ) ) );
	if ( $enabled ) {
		$ids[] = $product_id;
	}
	update_user_meta( $user_id, '_moda_wishlist', array_values( array_unique( $ids ) ) );
	wp_send_json_success( array( 'count' => count( $ids ), 'enabled' => $enabled ) );
}
add_action( 'wp_ajax_moda_commerce_wishlist', 'moda_commerce_ajax_wishlist' );

/**
 * Enqueue the frontend controls on WooCommerce pages.
 *
 * @return void
 */
function moda_commerce_enqueue_assets() {
	if ( ! function_exists( 'is_woocommerce' ) || ( ! is_woocommerce() && ! is_cart() && ! is_account_page() && ! is_front_page() ) ) {
		return;
	}
	wp_enqueue_style( 'moda-commerce-tools', MODA_COMMERCE_TOOLS_URL . 'assets/commerce.css', array(), MODA_COMMERCE_TOOLS_VERSION );
	wp_enqueue_script( 'moda-commerce-tools', MODA_COMMERCE_TOOLS_URL . 'assets/commerce.js', array(), MODA_COMMERCE_TOOLS_VERSION, true );
	wp_localize_script(
		'moda-commerce-tools',
		'modaCommerceTools',
		array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'moda_commerce_wishlist' ),
			'loggedIn'     => is_user_logged_in(),
			'wishlistIds'  => is_user_logged_in() ? array_values( array_map( 'strval', (array) get_user_meta( get_current_user_id(), '_moda_wishlist', true ) ) ) : array(),
			'accountUrl'   => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url(),
			'i18n'         => array(
				'added'   => __( 'Favorilere eklendi.', 'moda-commerce-tools' ),
				'removed' => __( 'Favorilerden kaldırıldı.', 'moda-commerce-tools' ),
				'error'   => __( 'İşlem tamamlanamadı. Lütfen tekrar deneyin.', 'moda-commerce-tools' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'moda_commerce_enqueue_assets' );

/**
 * Render a wishlist toggle on the single product page.
 *
 * @return void
 */
function moda_commerce_product_wishlist_button() {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	printf(
		'<button type="button" class="ma-favorite-toggle moda-commerce-favorite" data-product-id="%1$d" data-label-add="%2$s" data-label-remove="%3$s" aria-pressed="false" aria-label="%2$s"><span aria-hidden="true">♡</span></button>',
		(int) $product->get_id(),
		esc_attr( sprintf( __( '%s ürününü favorilere ekle', 'moda-commerce-tools' ), $product->get_name() ) ),
		esc_attr( sprintf( __( '%s ürününü favorilerden çıkar', 'moda-commerce-tools' ), $product->get_name() ) )
	);
}
add_action( 'woocommerce_single_product_summary', 'moda_commerce_product_wishlist_button', 34 );
add_action( 'woocommerce_after_shop_loop_item', 'moda_commerce_product_wishlist_button', 8 );

/**
 * Add the account-level consent for one abandoned-cart reminder.
 *
 * @return void
 */
function moda_commerce_render_cart_reminder_optin() {
	$enabled = (bool) get_user_meta( get_current_user_id(), '_moda_cart_reminder_optin', true );
	echo '<p class="form-row form-row-wide moda-cart-reminder-optin"><label><input type="checkbox" name="moda_cart_reminder_optin" value="1"' . checked( $enabled, true, false ) . '> ' . esc_html__( 'Terk ettiğim her sepet için en fazla bir kez hatırlatma e-postası almayı kabul ediyorum.', 'moda-commerce-tools' ) . '</label></p>';
}
add_action( 'woocommerce_edit_account_form', 'moda_commerce_render_cart_reminder_optin' );

/**
 * Save reminder consent and remove queued reminders when consent is withdrawn.
 *
 * @param int $user_id Current customer.
 * @return void
 */
function moda_commerce_save_cart_reminder_optin( $user_id ) {
	$enabled = isset( $_POST['moda_cart_reminder_optin'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['moda_cart_reminder_optin'] ) );
	update_user_meta( $user_id, '_moda_cart_reminder_optin', $enabled ? 1 : 0 );
	if ( ! $enabled ) {
		wp_clear_scheduled_hook( 'moda_commerce_send_cart_reminder', array( $user_id ) );
		delete_user_meta( $user_id, '_moda_cart_reminder_cart' );
	} elseif ( get_current_user_id() === absint( $user_id ) ) {
		moda_commerce_schedule_cart_reminder();
	}
}
add_action( 'woocommerce_save_account_details', 'moda_commerce_save_cart_reminder_optin' );

/**
 * Schedule one reminder after a customer's cart changes.
 *
 * @return void
 */
function moda_commerce_schedule_cart_reminder() {
	if ( ! is_user_logged_in() || ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}
	$user_id = get_current_user_id();
	if ( ! get_user_meta( $user_id, '_moda_cart_reminder_optin', true ) ) {
		return;
	}
	$cart = array();
	foreach ( WC()->cart->get_cart() as $item ) {
		$product = isset( $item['data'] ) && $item['data'] instanceof WC_Product ? $item['data'] : null;
		if ( $product ) {
			$cart[] = array(
				'product_id' => $product->get_id(),
				'quantity'   => max( 1, absint( $item['quantity'] ?? 1 ) ),
			);
		}
	}
	wp_clear_scheduled_hook( 'moda_commerce_send_cart_reminder', array( $user_id ) );
	if ( ! $cart ) {
		delete_user_meta( $user_id, '_moda_cart_reminder_cart' );
		return;
	}
	update_user_meta( $user_id, '_moda_cart_reminder_cart', $cart );
	wp_schedule_single_event( time() + DAY_IN_SECONDS, 'moda_commerce_send_cart_reminder', array( $user_id ) );
}
foreach ( array( 'woocommerce_add_to_cart', 'woocommerce_cart_item_removed', 'woocommerce_cart_item_restored', 'woocommerce_after_cart_item_quantity_update' ) as $moda_cart_change_hook ) {
	add_action( $moda_cart_change_hook, 'moda_commerce_schedule_cart_reminder', 20 );
}

/**
 * Send a single opt-in cart reminder email.
 *
 * @param int $user_id Customer ID.
 * @return void
 */
function moda_commerce_send_cart_reminder( $user_id ) {
	$user = get_user_by( 'id', absint( $user_id ) );
	$cart = (array) get_user_meta( $user_id, '_moda_cart_reminder_cart', true );
	if ( ! $user || ! get_user_meta( $user_id, '_moda_cart_reminder_optin', true ) || ! $cart ) {
		return;
	}
	$items = array();
	foreach ( $cart as $item ) {
		$product = wc_get_product( absint( $item['product_id'] ?? 0 ) );
		if ( $product && $product->is_visible() ) {
			$items[] = sprintf( '%s (x%d): %s', $product->get_name(), absint( $item['quantity'] ?? 1 ), $product->get_permalink() );
		}
	}
	if ( ! $items ) {
		delete_user_meta( $user_id, '_moda_cart_reminder_cart' );
		return;
	}
	$body = sprintf( "Merhaba %s,\n\nSepetinizde bıraktığınız ürünleri hatırlatmak istedik:\n\n%s\n\nSepetinizi görüntülemek için: %s\n\nBu hatırlatma, hesap ayarlarınızda verdiğiniz izin nedeniyle gönderildi. İzni Hesabım > Hesap detayları bölümünden kapatabilirsiniz.", $user->display_name, implode( "\n", $items ), wc_get_cart_url() );
	if ( wp_mail( $user->user_email, __( 'Sepetiniz sizi bekliyor', 'moda-commerce-tools' ), $body ) ) {
		delete_user_meta( $user_id, '_moda_cart_reminder_cart' );
		return;
	}
	error_log( 'Moda Commerce Tools: abandoned-cart reminder email failed for customer ' . absint( $user_id ) );
}
add_action( 'moda_commerce_send_cart_reminder', 'moda_commerce_send_cart_reminder' );

/**
 * Clear a pending reminder after a successful order.
 *
 * @param int $order_id Order ID.
 * @return void
 */
function moda_commerce_clear_cart_reminder_after_order( $order_id ) {
	$order   = wc_get_order( $order_id );
	$user_id = $order ? $order->get_customer_id() : 0;
	if ( ! $user_id && $order ) {
		$customer = get_user_by( 'email', $order->get_billing_email() );
		$user_id = $customer ? (int) $customer->ID : 0;
	}
	if ( $user_id ) {
		wp_clear_scheduled_hook( 'moda_commerce_send_cart_reminder', array( $user_id ) );
		delete_user_meta( $user_id, '_moda_cart_reminder_cart' );
	}
}
add_action( 'woocommerce_checkout_order_processed', 'moda_commerce_clear_cart_reminder_after_order' );

/**
 * Add the store's size chart to WooCommerce settings.
 *
 * @return void
 */
function moda_commerce_admin_menu() {
	add_submenu_page( 'woocommerce', __( 'Moda Mağaza Araçları', 'moda-commerce-tools' ), __( 'Moda Mağaza Araçları', 'moda-commerce-tools' ), 'manage_woocommerce', 'moda-commerce-tools', 'moda_commerce_settings_page' );
}
add_action( 'admin_menu', 'moda_commerce_admin_menu' );

/**
 * Register store tool settings.
 *
 * @return void
 */
function moda_commerce_register_settings() {
	register_setting(
		'moda_commerce_tools',
		'moda_commerce_size_chart',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'default'           => '',
		)
	);
	register_setting(
		'moda_commerce_tools',
		'moda_commerce_free_shipping_threshold',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'moda_commerce_sanitize_amount',
			'default'           => '',
		)
	);
	register_setting(
		'moda_commerce_tools',
		'moda_commerce_returns_note',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'default'           => '',
		)
	);
}
add_action( 'admin_init', 'moda_commerce_register_settings' );

/**
 * Keep a positive decimal amount or an empty string.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function moda_commerce_sanitize_amount( $value ) {
	$amount = function_exists( 'wc_format_decimal' ) ? wc_format_decimal( (string) $value ) : (string) $value;
	return is_numeric( $amount ) && (float) $amount > 0 ? (string) $amount : '';
}

/**
 * Return the configured free shipping threshold, or 0 when disabled.
 *
 * @return float
 */
function moda_commerce_free_shipping_threshold() {
	return (float) moda_commerce_sanitize_amount( (string) get_option( 'moda_commerce_free_shipping_threshold', '' ) );
}

/**
 * Current cart amount used for the free shipping progress.
 *
 * @return float
 */
function moda_commerce_cart_progress_amount() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0.0;
	}
	return max( 0.0, (float) WC()->cart->get_cart_contents_total() + (float) WC()->cart->get_cart_contents_tax() );
}

/**
 * Build the free shipping progress markup.
 *
 * @return string
 */
function moda_commerce_free_shipping_markup() {
	$threshold = moda_commerce_free_shipping_threshold();
	if ( $threshold <= 0 || ! function_exists( 'wc_price' ) ) {
		return '';
	}
	$amount    = moda_commerce_cart_progress_amount();
	$remaining = max( 0.0, $threshold - $amount );
	$percent   = min( 100, (int) floor( $amount / $threshold * 100 ) );
	$message   = $remaining > 0
		? sprintf( /* translators: %s: remaining amount */ __( 'Ücretsiz kargo için %s daha ekleyin.', 'moda-commerce-tools' ), wp_strip_all_tags( wc_price( $remaining ) ) )
		: __( 'Tebrikler, kargo ücretsiz!', 'moda-commerce-tools' );
	return sprintf(
		'<div class="moda-free-shipping" data-threshold="%1$s" data-minor-unit="%2$d"><p class="moda-free-shipping__text" role="status" aria-live="polite">%3$s</p><div class="moda-free-shipping__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="%4$d"><span style="width:%4$d%%"></span></div></div>',
		esc_attr( (string) $threshold ),
		(int) wc_get_price_decimals(),
		esc_html( $message ),
		$percent
	);
}

/**
 * Print the progress above the classic cart.
 *
 * @return void
 */
function moda_commerce_render_free_shipping_classic() {
	echo moda_commerce_free_shipping_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped when built.
}
add_action( 'woocommerce_before_cart', 'moda_commerce_render_free_shipping_classic' );

/**
 * Prepend the progress to the block cart.
 *
 * @param string $content Rendered block.
 * @param array  $block   Parsed block.
 * @return string
 */
function moda_commerce_render_free_shipping_block( $content, $block ) {
	if ( isset( $block['blockName'] ) && 'woocommerce/cart' === $block['blockName'] ) {
		return moda_commerce_free_shipping_markup() . $content;
	}
	return $content;
}
add_filter( 'render_block', 'moda_commerce_render_free_shipping_block', 10, 2 );

/**
 * Show the store-approved return note on product pages.
 *
 * @return void
 */
function moda_commerce_render_returns_note() {
	$note = trim( (string) get_option( 'moda_commerce_returns_note', '' ) );
	if ( '' === $note ) {
		return;
	}
	echo '<aside class="moda-returns-note"><strong>' . esc_html__( 'İade ve değişim', 'moda-commerce-tools' ) . '</strong><p>' . nl2br( esc_html( $note ) ) . '</p></aside>';
}
add_action( 'woocommerce_single_product_summary', 'moda_commerce_render_returns_note', 36 );

/**
 * Render the size chart settings.
 *
 * @return void
 */
function moda_commerce_settings_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Moda Mağaza Araçları', 'moda-commerce-tools' ); ?></h1>
		<form action="options.php" method="post">
			<?php settings_fields( 'moda_commerce_tools' ); ?>
			<table class="form-table"><tr>
				<th scope="row"><label for="moda_commerce_size_chart"><?php esc_html_e( 'Beden tablosu', 'moda-commerce-tools' ); ?></label></th>
				<td>
					<textarea class="large-text" rows="8" id="moda_commerce_size_chart" name="moda_commerce_size_chart"><?php echo esc_textarea( (string) get_option( 'moda_commerce_size_chart', '' ) ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Her satıra bir ölçü yazın; başlık satırında Beden | Göğüs | Bel gibi sütun adlarını, devamında S | ... biçiminde değerleri kullanın. Yalnızca mağazanızın doğrulanmış ölçülerini girin.', 'moda-commerce-tools' ); ?></p>
				</td>
			</tr><tr>
				<th scope="row"><label for="moda_commerce_free_shipping_threshold"><?php esc_html_e( 'Ücretsiz kargo eşiği (₺)', 'moda-commerce-tools' ); ?></label></th>
				<td>
					<input type="text" inputmode="decimal" id="moda_commerce_free_shipping_threshold" name="moda_commerce_free_shipping_threshold" value="<?php echo esc_attr( (string) get_option( 'moda_commerce_free_shipping_threshold', '' ) ); ?>">
					<p class="description"><?php esc_html_e( 'Boş bırakılırsa sepette ilerleme çubuğu gösterilmez. WooCommerce kargo ayarındaki ücretsiz kargo koşuluyla aynı tutarı girin; çubuk yalnızca bilgilendirir.', 'moda-commerce-tools' ); ?></p>
				</td>
			</tr><tr>
				<th scope="row"><label for="moda_commerce_returns_note"><?php esc_html_e( 'İade ve değişim notu', 'moda-commerce-tools' ); ?></label></th>
				<td>
					<textarea class="large-text" rows="4" id="moda_commerce_returns_note" name="moda_commerce_returns_note"><?php echo esc_textarea( (string) get_option( 'moda_commerce_returns_note', '' ) ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Ürün sayfasında gösterilir. Boş bırakılırsa kutu görünmez. Yalnızca mağazanızın onaylı iade koşullarını yazın.', 'moda-commerce-tools' ); ?></p>
				</td>
			</tr></table>
			<?php submit_button(); ?>
		</form>
		<p><?php esc_html_e( 'Ürün yorumları WooCommerce ürün değerlendirme ayarlarını kullanır. Yeni soru-cevap içerikleri Ürün Soruları bölümünden yanıtlanıp yayımlanır.', 'moda-commerce-tools' ); ?></p>
	</div>
	<?php
}

/**
 * Render configured measurements as a size table.
 *
 * @return void
 */
function moda_commerce_render_size_chart() {
	$raw = trim( (string) get_option( 'moda_commerce_size_chart', '' ) );
	if ( '' === $raw ) {
		return;
	}
	$rows = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$cells = array_map( 'trim', explode( '|', $line ) );
		if ( count( $cells ) > 1 && count( $cells ) <= 8 ) {
			$rows[] = $cells;
		}
	}
	if ( ! $rows ) {
		return;
	}
	echo '<details class="moda-size-chart"><summary>' . esc_html__( 'Beden tablosu', 'moda-commerce-tools' ) . '</summary><div class="moda-size-chart-scroll"><table><tbody>';
	foreach ( $rows as $index => $cells ) {
		echo '<tr>';
		foreach ( $cells as $cell ) {
			$tag = 0 === $index ? 'th' : 'td';
			printf( '<%1$s>%2$s</%1$s>', esc_attr( $tag ), esc_html( $cell ) );
		}
		echo '</tr>';
	}
	echo '</tbody></table></div></details>';
}
add_action( 'woocommerce_single_product_summary', 'moda_commerce_render_size_chart', 34 );

/**
 * Render product stock and price alert forms.
 *
 * @return void
 */
function moda_commerce_render_product_alerts() {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$email = is_user_logged_in() ? wp_get_current_user()->user_email : '';
	$product_id = $product->get_id();
	echo '<section class="moda-product-alerts"><h3>' . esc_html__( 'Ürün bildirimi al', 'moda-commerce-tools' ) . '</h3>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="moda-product-alert-form">';
	echo '<input type="hidden" name="action" value="moda_product_alert"><input type="hidden" name="product_id" value="' . esc_attr( (string) $product_id ) . '">';
	wp_nonce_field( 'moda_product_alert_' . $product_id, 'moda_product_alert_nonce' );
	echo '<input type="hidden" name="moda_form_time" value="' . esc_attr( (string) time() ) . '"><p class="moda-commerce-honeypot" aria-hidden="true"><label>Web sitesi<input type="text" name="moda_website" tabindex="-1" autocomplete="off"></label></p>';
	echo '<label>' . esc_html__( 'E-posta adresiniz', 'moda-commerce-tools' ) . '<input type="email" name="email" required maxlength="190" autocomplete="email" value="' . esc_attr( $email ) . '"></label>';
	if ( ! $product->is_in_stock() ) {
		echo '<label class="moda-product-alert-check"><input type="checkbox" name="stock_alert" value="1" checked> ' . esc_html__( 'Ürün tekrar stokta olduğunda haber ver', 'moda-commerce-tools' ) . '</label>';
	}
	echo '<label class="moda-product-alert-check"><input type="checkbox" name="price_alert" value="1"> ' . esc_html__( 'Fiyat belirlediğim tutara indiğinde haber ver', 'moda-commerce-tools' ) . '</label>';
	echo '<label>' . esc_html__( 'Hedef fiyat (₺)', 'moda-commerce-tools' ) . '<input type="number" min="1" step="0.01" name="target_price" inputmode="decimal"></label>';
	$privacy_url = get_privacy_policy_url();
	echo '<label class="moda-product-alert-check"><input type="checkbox" name="consent" value="1" required> ' . esc_html__( 'Seçtiğim ürün bildirimi için e-posta adresimin kullanılmasını kabul ediyorum.', 'moda-commerce-tools' ) . ( $privacy_url ? ' <a href="' . esc_url( $privacy_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Gizlilik politikası', 'moda-commerce-tools' ) . '</a>' : '' ) . '</label>';
	echo '<button type="submit">' . esc_html__( 'Bildirim oluştur', 'moda-commerce-tools' ) . '</button></form></section>';
}
add_action( 'woocommerce_single_product_summary', 'moda_commerce_render_product_alerts', 38 );

/**
 * Save a stock or price alert request.
 *
 * @return void
 */
function moda_commerce_handle_product_alert() {
	$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
	if ( ! $product_id || ! isset( $_POST['moda_product_alert_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moda_product_alert_nonce'] ) ), 'moda_product_alert_' . $product_id ) ) {
		wp_die( esc_html__( 'İstek doğrulanamadı. Lütfen ürünü yeniden açıp tekrar deneyin.', 'moda-commerce-tools' ), '', array( 'response' => 403 ) );
	}
	$product = wc_get_product( $product_id );
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$started = isset( $_POST['moda_form_time'] ) ? absint( wp_unslash( $_POST['moda_form_time'] ) ) : 0;
	if ( ! empty( $_POST['moda_website'] ) || ! $started || time() - $started < 2 ) {
		wp_safe_redirect( add_query_arg( 'moda_alert', 'invalid', get_permalink( $product_id ) ) );
		exit;
	}
	$consent = isset( $_POST['consent'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['consent'] ) );
	$stock   = isset( $_POST['stock_alert'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['stock_alert'] ) ) && $product && ! $product->is_in_stock();
	$price   = isset( $_POST['price_alert'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['price_alert'] ) );
	$target  = isset( $_POST['target_price'] ) ? wc_format_decimal( wp_unslash( $_POST['target_price'] ) ) : '';
	if ( ! $product || 'publish' !== get_post_status( $product_id ) || ! is_email( $email ) || ! $consent || ( ! $stock && ! $price ) || ( $price && ( '' === $target || (float) $target <= 0 || '' === $product->get_price() || (float) $target >= (float) $product->get_price() ) ) ) {
		wp_safe_redirect( add_query_arg( 'moda_alert', 'invalid', get_permalink( $product_id ) ) );
		exit;
	}
	$rate_key = 'moda_alert_' . md5( strtolower( $email ) . '|' . $product_id . '|' . ( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' ) );
	if ( get_transient( $rate_key ) ) {
		wp_safe_redirect( add_query_arg( 'moda_alert', 'duplicate', get_permalink( $product_id ) ) );
		exit;
	}
	$alert_id = wp_insert_post(
		array(
			'post_type'   => 'moda_product_alert',
			'post_status' => 'pending',
			'post_title'  => sprintf( '%s — %s', $product->get_name(), $email ),
		),
		true
	);
	if ( is_wp_error( $alert_id ) ) {
		wp_die( esc_html__( 'Bildirim kaydedilemedi. Lütfen daha sonra tekrar deneyin.', 'moda-commerce-tools' ) );
	}
	update_post_meta( $alert_id, '_moda_alert_product', $product_id );
	update_post_meta( $alert_id, '_moda_alert_email', $email );
	update_post_meta( $alert_id, '_moda_alert_stock', $stock ? 1 : 0 );
	update_post_meta( $alert_id, '_moda_alert_price', $price ? 1 : 0 );
	update_post_meta( $alert_id, '_moda_alert_target', $price ? (string) $target : '' );
	$token = wp_generate_password( 40, false, false );
	update_post_meta( $alert_id, '_moda_alert_verify_hash', hash( 'sha256', $token ) );
	$verify_url = add_query_arg(
		array(
			'action' => 'moda_alert_verify',
			'alert'  => $alert_id,
			'token'  => rawurlencode( $token ),
		),
		admin_url( 'admin-post.php' )
	);
	if ( ! wp_mail( $email, __( 'Ürün bildirim e-posta adresinizi doğrulayın', 'moda-commerce-tools' ), sprintf( "Ürün bildirim isteğinizi etkinleştirmek için bu bağlantıyı açın:\n\n%s\n\nBu isteği siz yapmadıysanız e-postayı dikkate almayın.", $verify_url ) ) ) {
		wp_delete_post( $alert_id, true );
		wp_die( esc_html__( 'Doğrulama e-postası gönderilemedi. E-posta servisi hazır olduğunda lütfen yeniden deneyin.', 'moda-commerce-tools' ), '', array( 'response' => 503 ) );
	}
	set_transient( $rate_key, 1, 10 * MINUTE_IN_SECONDS );
	wp_safe_redirect( add_query_arg( 'moda_alert', 'saved', get_permalink( $product_id ) ) );
	exit;
}
add_action( 'admin_post_nopriv_moda_product_alert', 'moda_commerce_handle_product_alert' );
add_action( 'admin_post_moda_product_alert', 'moda_commerce_handle_product_alert' );

/**
 * Verify ownership of the requested product-alert email address.
 *
 * @return void
 */
function moda_commerce_verify_product_alert() {
	$alert_id = isset( $_GET['alert'] ) ? absint( wp_unslash( $_GET['alert'] ) ) : 0;
	$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
	$stored_hash = (string) get_post_meta( $alert_id, '_moda_alert_verify_hash', true );
	if ( ! $alert_id || 'moda_product_alert' !== get_post_type( $alert_id ) || 'pending' !== get_post_status( $alert_id ) || '' === $stored_hash || ! hash_equals( $stored_hash, hash( 'sha256', $token ) ) ) {
		wp_die( esc_html__( 'Doğrulama bağlantısı geçersiz veya daha önce kullanılmış.', 'moda-commerce-tools' ), '', array( 'response' => 403 ) );
	}
	$product_id = absint( get_post_meta( $alert_id, '_moda_alert_product', true ) );
	$updated = wp_update_post( array( 'ID' => $alert_id, 'post_status' => 'publish' ), true );
	if ( is_wp_error( $updated ) ) {
		wp_die( esc_html__( 'Bildirim doğrulanamadı. Lütfen daha sonra tekrar deneyin.', 'moda-commerce-tools' ), '', array( 'response' => 500 ) );
	}
	delete_post_meta( $alert_id, '_moda_alert_verify_hash' );
	wp_safe_redirect( add_query_arg( 'moda_alert', 'verified', get_permalink( $product_id ) ) );
	exit;
}
add_action( 'admin_post_nopriv_moda_alert_verify', 'moda_commerce_verify_product_alert' );
add_action( 'admin_post_moda_alert_verify', 'moda_commerce_verify_product_alert' );

/**
 * Remove an alert using its emailed, nonce-protected unsubscribe link.
 *
 * @return void
 */
function moda_commerce_unsubscribe_product_alert() {
	$alert_id = isset( $_GET['alert'] ) ? absint( wp_unslash( $_GET['alert'] ) ) : 0;
	$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
	$stored_hash = (string) get_post_meta( $alert_id, '_moda_alert_unsubscribe_hash', true );
	if ( ! $alert_id || 'moda_product_alert' !== get_post_type( $alert_id ) || '' === $stored_hash || ! hash_equals( $stored_hash, hash( 'sha256', $token ) ) ) {
		wp_die( esc_html__( 'Bildirim bağlantısı geçersiz veya süresi dolmuş.', 'moda-commerce-tools' ), '', array( 'response' => 403 ) );
	}
	wp_delete_post( $alert_id, true );
	wp_die( esc_html__( 'Ürün bildirimi iptal edildi.', 'moda-commerce-tools' ) );
}
add_action( 'admin_post_nopriv_moda_alert_unsubscribe', 'moda_commerce_unsubscribe_product_alert' );
add_action( 'admin_post_moda_alert_unsubscribe', 'moda_commerce_unsubscribe_product_alert' );

/**
 * Render an alert submission result.
 *
 * @return void
 */
function moda_commerce_alert_notice() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only status code.
	$result = isset( $_GET['moda_alert'] ) ? sanitize_key( wp_unslash( $_GET['moda_alert'] ) ) : '';
	$messages = array(
		'saved'     => __( 'E-posta adresinizi doğrulamak için gelen kutunuzu kontrol edin.', 'moda-commerce-tools' ),
		'verified'  => __( 'E-posta doğrulandı. İlgili ürün değişikliği olduğunda size bildirim gönderilecektir.', 'moda-commerce-tools' ),
		'invalid'   => __( 'E-posta adresinizi ve bildirim tercihlerinizi kontrol edin. Fiyat bildirimi için hedef fiyat girin.', 'moda-commerce-tools' ),
		'duplicate' => __( 'Bu e-posta adresiyle kısa süre önce bildirim isteği gönderildi.', 'moda-commerce-tools' ),
	);
	if ( isset( $messages[ $result ] ) ) {
		wc_print_notice( esc_html( $messages[ $result ] ), 'notice' );
	}
}
add_action( 'woocommerce_before_single_product', 'moda_commerce_alert_notice' );

/**
 * Send pending product notifications when stock returns or price reaches target.
 *
 * @param int $product_id Product ID.
 * @param bool $stock_now Whether product is in stock.
 * @return void
 */
function moda_commerce_notify_product_alerts( $product_id, $stock_now = false ) {
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return;
	}
	$alerts = get_posts(
		array(
			'post_type'      => 'moda_product_alert',
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'fields'         => 'ids',
			'meta_query'     => array( array( 'key' => '_moda_alert_product', 'value' => absint( $product_id ) ) ),
		)
	);
	foreach ( $alerts as $alert_id ) {
		$email = sanitize_email( (string) get_post_meta( $alert_id, '_moda_alert_email', true ) );
		$stock_requested = (bool) get_post_meta( $alert_id, '_moda_alert_stock', true );
		$price_requested = (bool) get_post_meta( $alert_id, '_moda_alert_price', true );
		$target = (float) get_post_meta( $alert_id, '_moda_alert_target', true );
		$send_stock = $stock_now && $stock_requested;
		$send_price = $price_requested && $target > 0 && (float) $product->get_price() <= $target;
		if ( ! is_email( $email ) || ( ! $send_stock && ! $send_price ) ) {
			continue;
		}
		$reason = $send_stock && $send_price ? __( 'Stok yenilendi ve hedef fiyatınıza ulaştı.', 'moda-commerce-tools' ) : ( $send_stock ? __( 'Ürün yeniden stokta.', 'moda-commerce-tools' ) : __( 'Ürün hedef fiyatınıza ulaştı.', 'moda-commerce-tools' ) );
		$unsubscribe_token = wp_generate_password( 40, false, false );
		update_post_meta( $alert_id, '_moda_alert_unsubscribe_hash', hash( 'sha256', $unsubscribe_token ) );
		$unsubscribe = add_query_arg(
			array(
				'action' => 'moda_alert_unsubscribe',
				'alert'  => $alert_id,
				'token'  => rawurlencode( $unsubscribe_token ),
			),
			admin_url( 'admin-post.php' )
		);
		$body = $reason . "\n\n" . $product->get_name() . ': ' . $product->get_permalink() . "\n\n" . sprintf( __( 'Bu bildirimi iptal etmek için: %s', 'moda-commerce-tools' ), $unsubscribe );
		if ( wp_mail( $email, sprintf( '[%s] %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $product->get_name() ), $body ) ) {
			wp_delete_post( $alert_id, true );
		} else {
			error_log( 'Moda Commerce Tools: product alert email ' . absint( $alert_id ) . ' failed.' );
		}
	}
}
add_action( 'woocommerce_product_set_stock_status', 'moda_commerce_product_stock_changed', 10, 3 );

/**
 * Notify customers when stock becomes available.
 *
 * @param int    $product_id Product ID.
 * @param string $status     New stock status.
 * @param object $product    Product instance.
 * @return void
 */
function moda_commerce_product_stock_changed( $product_id, $status, $product = null ) {
	if ( 'product_variation' === get_post_type( $product_id ) ) {
		$product_id = wp_get_post_parent_id( $product_id );
		$product = $product_id ? wc_get_product( $product_id ) : false;
		$status = $product && $product->is_in_stock() ? 'instock' : 'outofstock';
	}
	moda_commerce_notify_product_alerts( $product_id, 'instock' === $status );
}

/**
 * Notify customers after price-related product fields are updated.
 *
 * @param WC_Product $product Product.
 * @param array      $updated_props Changed properties.
 * @return void
 */
function moda_commerce_product_price_changed( $product, $updated_props ) {
	if ( $product instanceof WC_Product && array_intersect( array( 'price', 'regular_price', 'sale_price' ), (array) $updated_props ) ) {
		$product_id = $product->get_id();
		if ( $product instanceof WC_Product_Variation ) {
			$product_id = $product->get_parent_id();
			$product = $product_id ? wc_get_product( $product_id ) : false;
		}
		if ( $product instanceof WC_Product ) {
			moda_commerce_notify_product_alerts( $product_id, $product->is_in_stock() );
		}
	}
}
add_action( 'woocommerce_product_object_updated_props', 'moda_commerce_product_price_changed', 10, 2 );

/**
 * Render the product question form and published answers.
 *
 * @return void
 */
function moda_commerce_render_product_questions() {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$questions = get_posts(
		array(
			'post_type'      => 'moda_product_question',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'meta_key'       => '_moda_question_product',
			'meta_value'     => $product->get_id(),
		)
	);
	echo '<section class="moda-product-questions"><h3>' . esc_html__( 'Ürün hakkında soru-cevap', 'moda-commerce-tools' ) . '</h3>';
	foreach ( $questions as $question ) {
		echo '<article><h4>' . esc_html( (string) get_post_meta( $question->ID, '_moda_question_text', true ) ) . '</h4><p>' . esc_html( (string) get_post_meta( $question->ID, '_moda_question_answer', true ) ) . '</p></article>';
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="moda-product-question-form">';
	echo '<input type="hidden" name="action" value="moda_product_question"><input type="hidden" name="product_id" value="' . esc_attr( (string) $product->get_id() ) . '">';
	wp_nonce_field( 'moda_product_question_' . $product->get_id(), 'moda_product_question_nonce' );
	echo '<input type="hidden" name="moda_form_time" value="' . esc_attr( (string) time() ) . '"><p class="moda-commerce-honeypot" aria-hidden="true"><label>Web sitesi<input type="text" name="moda_website" tabindex="-1" autocomplete="off"></label></p>';
	echo '<label>' . esc_html__( 'Sorunuz', 'moda-commerce-tools' ) . '<textarea name="question" required maxlength="1000"></textarea></label>';
	echo '<label>' . esc_html__( 'Yanıt için e-posta', 'moda-commerce-tools' ) . '<input type="email" name="email" required maxlength="190" value="' . esc_attr( is_user_logged_in() ? wp_get_current_user()->user_email : '' ) . '"></label>';
	$privacy_url = get_privacy_policy_url();
	echo '<label class="moda-product-alert-check"><input type="checkbox" name="consent" value="1" required> ' . esc_html__( 'Soruma yanıt verilmesi için e-posta adresimin kullanılmasını kabul ediyorum.', 'moda-commerce-tools' ) . ( $privacy_url ? ' <a href="' . esc_url( $privacy_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Gizlilik politikası', 'moda-commerce-tools' ) . '</a>' : '' ) . '</label>';
	echo '<button type="submit">' . esc_html__( 'Soruyu gönder', 'moda-commerce-tools' ) . '</button></form></section>';
}
add_action( 'woocommerce_after_single_product_summary', 'moda_commerce_render_product_questions', 15 );

/**
 * Save a question for store moderation.
 *
 * @return void
 */
function moda_commerce_handle_product_question() {
	$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
	if ( ! $product_id || ! isset( $_POST['moda_product_question_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moda_product_question_nonce'] ) ), 'moda_product_question_' . $product_id ) ) {
		wp_die( esc_html__( 'İstek doğrulanamadı.', 'moda-commerce-tools' ), '', array( 'response' => 403 ) );
	}
	$question = isset( $_POST['question'] ) ? sanitize_textarea_field( wp_unslash( $_POST['question'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$started = isset( $_POST['moda_form_time'] ) ? absint( wp_unslash( $_POST['moda_form_time'] ) ) : 0;
	$consent = isset( $_POST['consent'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['consent'] ) );
	if ( ! empty( $_POST['moda_website'] ) || ! $started || time() - $started < 2 ) {
		wp_safe_redirect( add_query_arg( 'moda_question', 'invalid', get_permalink( $product_id ) ) );
		exit;
	}
	if ( 'product' !== get_post_type( $product_id ) || 'publish' !== get_post_status( $product_id ) || mb_strlen( $question ) < 8 || ! is_email( $email ) || ! $consent ) {
		wp_safe_redirect( add_query_arg( 'moda_question', 'invalid', get_permalink( $product_id ) ) );
		exit;
	}
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$rate_key = 'moda_question_' . md5( strtolower( $email ) . '|' . absint( $product_id ) . '|' . $ip );
	if ( get_transient( $rate_key ) ) {
		wp_safe_redirect( add_query_arg( 'moda_question', 'duplicate', get_permalink( $product_id ) ) );
		exit;
	}
	$id = wp_insert_post(
		array(
			'post_type'   => 'moda_product_question',
			'post_status' => 'pending',
			'post_title'  => wp_trim_words( $question, 10, '…' ),
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		wp_die( esc_html__( 'Sorunuz kaydedilemedi. Lütfen daha sonra deneyin.', 'moda-commerce-tools' ) );
	}
	update_post_meta( $id, '_moda_question_product', $product_id );
	update_post_meta( $id, '_moda_question_text', $question );
	update_post_meta( $id, '_moda_question_email', $email );
	set_transient( $rate_key, 1, 5 * MINUTE_IN_SECONDS );
	$admin_notified = wp_mail(
		get_option( 'admin_email' ),
		sprintf( __( '[%s] Yeni ürün sorusu', 'moda-commerce-tools' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) ),
		sprintf( "Ürün: %s\nSoru: %s\nE-posta: %s\n\nYanıtlamak için: %s", get_the_title( $product_id ), $question, $email, admin_url( 'post.php?post=' . $id . '&action=edit' ) )
	);
	if ( ! $admin_notified ) {
		error_log( 'Moda Commerce Tools: admin notification for product question ' . absint( $id ) . ' failed.' );
	}
	wp_safe_redirect( add_query_arg( 'moda_question', 'saved', get_permalink( $product_id ) ) );
	exit;
}
add_action( 'admin_post_nopriv_moda_product_question', 'moda_commerce_handle_product_question' );
add_action( 'admin_post_moda_product_question', 'moda_commerce_handle_product_question' );

/**
 * Show question submission status.
 *
 * @return void
 */
function moda_commerce_question_notice() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only status code.
	$result = isset( $_GET['moda_question'] ) ? sanitize_key( wp_unslash( $_GET['moda_question'] ) ) : '';
	$messages = array(
		'saved'     => __( 'Sorunuz moderasyon için iletildi. Yanıt yayımlandığında e-posta alacaksınız.', 'moda-commerce-tools' ),
		'invalid'   => __( 'Sorunuzu ve geçerli e-posta adresinizi kontrol edin.', 'moda-commerce-tools' ),
		'duplicate' => __( 'Bu ürün için yakın zamanda soru gönderdiniz.', 'moda-commerce-tools' ),
	);
	if ( isset( $messages[ $result ] ) ) {
		wc_print_notice( esc_html( $messages[ $result ] ), 'notice' );
	}
}
add_action( 'woocommerce_before_single_product', 'moda_commerce_question_notice' );

/**
 * Add answer fields to the product question editor.
 *
 * @return void
 */
function moda_commerce_question_meta_box() {
	add_meta_box( 'moda_question_answer', __( 'Soru ve yanıt', 'moda-commerce-tools' ), 'moda_commerce_render_question_meta_box', 'moda_product_question', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'moda_commerce_question_meta_box' );

/**
 * Render the question and answer editing fields.
 *
 * @param WP_Post $post Current question.
 * @return void
 */
function moda_commerce_render_question_meta_box( $post ) {
	wp_nonce_field( 'moda_question_answer_' . $post->ID, 'moda_question_answer_nonce' );
	$product_id = absint( get_post_meta( $post->ID, '_moda_question_product', true ) );
	$email = sanitize_email( (string) get_post_meta( $post->ID, '_moda_question_email', true ) );
	echo '<p><strong>' . esc_html__( 'Ürün:', 'moda-commerce-tools' ) . '</strong> ' . esc_html( get_the_title( $product_id ) ) . '</p>';
	echo '<p><strong>' . esc_html__( 'Müşteri sorusu:', 'moda-commerce-tools' ) . '</strong><br>' . nl2br( esc_html( (string) get_post_meta( $post->ID, '_moda_question_text', true ) ) ) . '</p>';
	echo '<p><strong>' . esc_html__( 'E-posta:', 'moda-commerce-tools' ) . '</strong> ' . esc_html( $email ) . '</p>';
	echo '<label for="moda-question-answer"><strong>' . esc_html__( 'Yanıt', 'moda-commerce-tools' ) . '</strong></label><textarea id="moda-question-answer" name="moda_question_answer" class="large-text" rows="5">' . esc_textarea( (string) get_post_meta( $post->ID, '_moda_question_answer', true ) ) . '</textarea>';
}

/**
 * Save a moderated answer and notify the customer when published.
 *
 * @param int $post_id Question ID.
 * @return void
 */
function moda_commerce_save_question_answer( $post_id ) {
	if ( ! isset( $_POST['moda_question_answer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moda_question_answer_nonce'] ) ), 'moda_question_answer_' . $post_id ) || ! current_user_can( 'edit_post', $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
		return;
	}
	$answer = isset( $_POST['moda_question_answer'] ) ? sanitize_textarea_field( wp_unslash( $_POST['moda_question_answer'] ) ) : '';
	update_post_meta( $post_id, '_moda_question_answer', $answer );
	if ( '' !== $answer && 'publish' === get_post_status( $post_id ) && ! get_post_meta( $post_id, '_moda_question_answer_sent', true ) ) {
		$product_id = absint( get_post_meta( $post_id, '_moda_question_product', true ) );
		$email = sanitize_email( (string) get_post_meta( $post_id, '_moda_question_email', true ) );
		if ( is_email( $email ) ) {
			if ( wp_mail( $email, __( 'Ürün sorunuza yanıt verildi', 'moda-commerce-tools' ), $answer . "\n\n" . get_permalink( $product_id ) ) ) {
				update_post_meta( $post_id, '_moda_question_answer_sent', 1 );
			} else {
				error_log( 'Moda Commerce Tools: answer notification for product question ' . absint( $post_id ) . ' failed.' );
			}
		}
	}
}
add_action( 'save_post_moda_product_question', 'moda_commerce_save_question_answer' );

/**
 * Add order tracking fields to classic and HPOS order editors.
 *
 * @param WC_Order $order Order being edited.
 * @return void
 */
function moda_commerce_order_tracking_fields( $order ) {
	if ( ! $order instanceof WC_Order ) {
		return;
	}
	wp_nonce_field( 'moda_order_tracking_' . $order->get_id(), 'moda_order_tracking_nonce' );
	echo '<div class="address"><p><strong>' . esc_html__( 'Kargo takibi', 'moda-commerce-tools' ) . '</strong></p>';
	printf( '<p><label>%1$s<br><input type="text" name="moda_tracking_number" value="%2$s" class="widefat"></label></p>', esc_html__( 'Takip numarası', 'moda-commerce-tools' ), esc_attr( (string) $order->get_meta( '_moda_tracking_number' ) ) );
	printf( '<p><label>%1$s<br><input type="url" name="moda_tracking_url" value="%2$s" class="widefat" placeholder="https://"></label></p></div>', esc_html__( 'Takip bağlantısı', 'moda-commerce-tools' ), esc_attr( (string) $order->get_meta( '_moda_tracking_url' ) ) );
}
add_action( 'woocommerce_admin_order_data_after_order_details', 'moda_commerce_order_tracking_fields' );

/**
 * Save tracking metadata from the legacy order edit screen.
 *
 * @param int $order_id Order ID.
 * @return void
 */
function moda_commerce_save_order_tracking( $order_id ) {
	if ( ! isset( $_POST['moda_order_tracking_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moda_order_tracking_nonce'] ) ), 'moda_order_tracking_' . absint( $order_id ) ) || ! current_user_can( 'edit_shop_order', $order_id ) ) {
		return;
	}
	$number = isset( $_POST['moda_tracking_number'] ) ? sanitize_text_field( wp_unslash( $_POST['moda_tracking_number'] ) ) : '';
	$url = isset( $_POST['moda_tracking_url'] ) ? esc_url_raw( wp_unslash( $_POST['moda_tracking_url'] ), array( 'https', 'http' ) ) : '';
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	$order->update_meta_data( '_moda_tracking_number', $number );
	$order->update_meta_data( '_moda_tracking_url', $url );
	$order->save();
}
add_action( 'woocommerce_process_shop_order_meta', 'moda_commerce_save_order_tracking' );

/**
 * Save tracking metadata from the HPOS order editor.
 *
 * @param WC_Order $order Order being saved.
 * @return void
 */
function moda_commerce_save_hpos_order_tracking( $order ) {
	if ( ! $order instanceof WC_Order || ! isset( $_POST['moda_order_tracking_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moda_order_tracking_nonce'] ) ), 'moda_order_tracking_' . $order->get_id() ) || ! current_user_can( 'edit_shop_order', $order->get_id() ) ) {
		return;
	}
	$number = isset( $_POST['moda_tracking_number'] ) ? sanitize_text_field( wp_unslash( $_POST['moda_tracking_number'] ) ) : '';
	$url = isset( $_POST['moda_tracking_url'] ) ? esc_url_raw( wp_unslash( $_POST['moda_tracking_url'] ), array( 'https', 'http' ) ) : '';
	$order->update_meta_data( '_moda_tracking_number', $number );
	$order->update_meta_data( '_moda_tracking_url', $url );
}
add_action( 'woocommerce_admin_process_shop_order_object', 'moda_commerce_save_hpos_order_tracking' );

/**
 * Add tracking details to the customer's order view.
 *
 * @param WC_Order $order Order.
 * @return void
 */
function moda_commerce_render_order_tracking( $order ) {
	if ( ! $order instanceof WC_Order ) {
		return;
	}
	$number = (string) $order->get_meta( '_moda_tracking_number' );
	$url = (string) $order->get_meta( '_moda_tracking_url' );
	if ( '' === $number && '' === $url ) {
		return;
	}
	echo '<section class="moda-order-tracking"><h2>' . esc_html__( 'Kargo takibi', 'moda-commerce-tools' ) . '</h2>';
	if ( $number ) {
		echo '<p>' . esc_html__( 'Takip numarası:', 'moda-commerce-tools' ) . ' <strong>' . esc_html( $number ) . '</strong></p>';
	}
	if ( $url ) {
		echo '<p><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Kargo gönderisini takip et', 'moda-commerce-tools' ) . '</a></p>';
	}
	echo '</section>';
}
add_action( 'woocommerce_order_details_after_order_table', 'moda_commerce_render_order_tracking', 10 );

/**
 * Show a private verified-purchase note in the review section.
 *
 * @return void
 */
function moda_commerce_review_trust_note() {
	echo '<p class="moda-review-note">' . esc_html__( 'Yorumlar WooCommerce üzerinden yayınlanır. Satın alma doğrulaması mağazanın WooCommerce yorum ayarlarına bağlıdır.', 'moda-commerce-tools' ) . '</p>';
}
add_action( 'woocommerce_review_before', 'moda_commerce_review_trust_note' );
