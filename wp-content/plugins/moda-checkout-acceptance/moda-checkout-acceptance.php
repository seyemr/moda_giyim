<?php
/**
 * Plugin Name: Moda Checkout Acceptance
 * Description: Requires checkout acceptance of the sales terms and acknowledgment of the privacy notice.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * Text Domain: moda-checkout-acceptance
 *
 * @package Moda_Checkout_Acceptance
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register required order-level checkboxes for the WooCommerce Checkout Block.
 *
 * @return void
 */
function moda_checkout_acceptance_register_block_fields() {
	if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
		return;
	}

	woocommerce_register_additional_checkout_field(
		array(
			'id'       => 'moda-checkout/sales-terms-acceptance',
			'label'    => __( 'Mesafeli Satış Sözleşmesi ve Ön Bilgilendirme Formu’nu okudum ve kabul ediyorum.', 'moda-checkout-acceptance' ),
			'location' => 'order',
			'type'     => 'checkbox',
			'required' => true,
		)
	);

	woocommerce_register_additional_checkout_field(
		array(
			'id'       => 'moda-checkout/privacy-notice-acknowledgment',
			'label'    => __( 'Gizlilik ve Kişisel Verilerin Korunması (KVKK) Aydınlatma Metni’ni okudum ve bilgilendirildim.', 'moda-checkout-acceptance' ),
			'location' => 'order',
			'type'     => 'checkbox',
			'required' => true,
		)
	);
}
add_action( 'woocommerce_init', 'moda_checkout_acceptance_register_block_fields' );

/**
 * Explain when the active WooCommerce version cannot register Checkout Block fields.
 *
 * @return void
 */
function moda_checkout_acceptance_block_support_notice() {
	if ( function_exists( 'woocommerce_register_additional_checkout_field' ) || ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	echo '<div class="notice notice-warning"><p>' . esc_html__( 'WooCommerce sürümü Checkout Block için zorunlu sözleşme alanlarını desteklemiyor. Bu alanlar için WooCommerce 8.9 veya üzeri gerekir; yükseltin ya da klasik checkout kullanın.', 'moda-checkout-acceptance' ) . '</p></div>';
}
add_action( 'admin_notices', 'moda_checkout_acceptance_block_support_notice' );

/**
 * Render acceptance checkboxes on the classic WooCommerce checkout.
 *
 * @return void
 */
function moda_checkout_acceptance_render_classic_fields() {
	$sales_terms_page = get_page_by_path( 'mesafeli-satis-sozlesmesi' );
	$sales_terms_url  = $sales_terms_page instanceof WP_Post && 'publish' === $sales_terms_page->post_status
		? get_permalink( $sales_terms_page )
		: home_url( '/mesafeli-satis-sozlesmesi/' );
	$privacy_url      = get_privacy_policy_url();
	if ( ! $privacy_url ) {
		$privacy_url = home_url( '/privacy-policy/' );
	}

	echo '<div class="moda-checkout-acceptance">';
	echo '<p class="form-row validate-required"><label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">';
	echo '<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" type="checkbox" name="moda_sales_terms_acceptance" value="1" />';
	echo '<span><a href="' . esc_url( $sales_terms_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Mesafeli Satış Sözleşmesi', 'moda-checkout-acceptance' ) . '</a> ';
	echo esc_html__( 've Ön Bilgilendirme Formu’nu okudum ve kabul ediyorum.', 'moda-checkout-acceptance' );
	echo ' <abbr class="required" title="' . esc_attr__( 'gerekli', 'moda-checkout-acceptance' ) . '">*</abbr></span></label></p>';
	echo '<p class="form-row validate-required"><label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">';
	echo '<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" type="checkbox" name="moda_privacy_notice_acknowledgment" value="1" />';
	echo '<span><a href="' . esc_url( $privacy_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Gizlilik ve Kişisel Verilerin Korunması (KVKK) Aydınlatma Metni', 'moda-checkout-acceptance' ) . '</a>';
	echo esc_html__( '’ni okudum ve bilgilendirildim.', 'moda-checkout-acceptance' );
	echo ' <abbr class="required" title="' . esc_attr__( 'gerekli', 'moda-checkout-acceptance' ) . '">*</abbr></span></label></p>';
	echo '</div>';
}
add_action( 'woocommerce_review_order_before_submit', 'moda_checkout_acceptance_render_classic_fields', 8 );

/**
 * Prevent classic checkout submission unless both required boxes are checked.
 *
 * @return void
 */
function moda_checkout_acceptance_validate_classic_fields() {
	$sales_terms_accepted = isset( $_POST['moda_sales_terms_acceptance'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['moda_sales_terms_acceptance'] ) );
	$privacy_notice_read  = isset( $_POST['moda_privacy_notice_acknowledgment'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['moda_privacy_notice_acknowledgment'] ) );

	if ( ! $sales_terms_accepted ) {
		wc_add_notice( __( 'Siparişe devam etmek için Mesafeli Satış Sözleşmesi ve Ön Bilgilendirme Formu’nu kabul etmelisiniz.', 'moda-checkout-acceptance' ), 'error' );
	}

	if ( ! $privacy_notice_read ) {
		wc_add_notice( __( 'Siparişe devam etmek için KVKK Aydınlatma Metni’ni okuduğunuzu onaylamalısınız.', 'moda-checkout-acceptance' ), 'error' );
	}
}
add_action( 'woocommerce_checkout_process', 'moda_checkout_acceptance_validate_classic_fields' );

/**
 * Store the classic checkout acknowledgments with the order.
 *
 * @param WC_Order $order The order being created.
 * @param array    $data Checkout data.
 * @return void
 */
function moda_checkout_acceptance_save_classic_fields( $order, $data ) {
	unset( $data );

	$sales_terms_accepted = isset( $_POST['moda_sales_terms_acceptance'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['moda_sales_terms_acceptance'] ) );
	$privacy_notice_read  = isset( $_POST['moda_privacy_notice_acknowledgment'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['moda_privacy_notice_acknowledgment'] ) );

	$order->update_meta_data( '_moda_sales_terms_acceptance', $sales_terms_accepted ? 'yes' : 'no' );
	$order->update_meta_data( '_moda_privacy_notice_acknowledgment', $privacy_notice_read ? 'yes' : 'no' );
}
add_action( 'woocommerce_checkout_create_order', 'moda_checkout_acceptance_save_classic_fields', 10, 2 );
