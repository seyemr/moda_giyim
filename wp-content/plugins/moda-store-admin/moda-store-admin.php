<?php
/**
 * Plugin Name: Moda Store Admin
 * Description: Permission-aware shortcuts to the native WooCommerce store management screens.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * Text Domain: moda-store-admin
 *
 * @package Moda_Store_Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add the store dashboard to the WordPress admin menu.
 *
 * @return void
 */
function moda_store_admin_register_menu() {
	add_menu_page(
		__( 'Mağaza yönetimi', 'moda-store-admin' ),
		__( 'Mağaza yönetimi', 'moda-store-admin' ),
		'manage_woocommerce',
		'moda-store-admin',
		'moda_store_admin_render_page',
		'dashicons-store',
		56
	);
}
add_action( 'admin_menu', 'moda_store_admin_register_menu', 30 );

/**
 * Load styles only on the store management page.
 *
 * @param string $hook_suffix Current admin page hook.
 * @return void
 */
function moda_store_admin_enqueue_assets( $hook_suffix ) {
	if ( 'toplevel_page_moda-store-admin' !== $hook_suffix ) {
		return;
	}

	wp_enqueue_style(
		'moda-store-admin',
		plugin_dir_url( __FILE__ ) . 'assets/admin.css',
		array(),
		'1.0.0'
	);
}
add_action( 'admin_enqueue_scripts', 'moda_store_admin_enqueue_assets' );

/**
 * Build native admin links only when the current user has the matching capability.
 *
 * @return array
 */
function moda_store_admin_get_sections() {
	$sections = array(
		'products' => array(
			'title' => __( 'Ürünler ve varyasyonlar', 'moda-store-admin' ),
			'cap'   => 'edit_products',
			'links' => array(
				array( __( 'Ürün kataloğu', 'moda-store-admin' ), admin_url( 'edit.php?post_type=product' ), 'edit_products' ),
				array( __( 'Yeni ürün ekle', 'moda-store-admin' ), admin_url( 'post-new.php?post_type=product' ), 'edit_products' ),
				array( __( 'Kategori ve etiketler', 'moda-store-admin' ), admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' ), 'manage_product_terms' ),
			),
			'note'  => __( 'Varyasyonları ürün düzenleme ekranında; beden, renk, SKU ve varyasyon stoklarını burada yönetin.', 'moda-store-admin' ),
		),
		'inventory' => array(
			'title' => __( 'Stok ve toplu katalog işlemleri', 'moda-store-admin' ),
			'cap'   => 'edit_products',
			'links' => array(
				array( __( 'Ürünleri toplu düzenle / CSV içe aktar / dışa aktar', 'moda-store-admin' ), admin_url( 'edit.php?post_type=product' ), 'edit_products' ),
			),
			'note'  => __( 'Ürün listesindeki toplu işlemler ve WooCommerce CSV araçları kullanılır. İçe aktarmada mevcut ürünü SKU veya kimlikle eşleştirin ve önce küçük bir test dosyası deneyin.', 'moda-store-admin' ),
		),
		'orders' => array(
			'title' => __( 'Siparişler ve iadeler', 'moda-store-admin' ),
			'cap'   => 'edit_shop_orders',
			'links' => array(
				array( __( 'Siparişleri görüntüle', 'moda-store-admin' ), admin_url( 'admin.php?page=wc-orders' ), 'edit_shop_orders' ),
			),
			'note'  => __( 'Sipariş durumu ve iadeleri yalnızca WooCommerce sipariş ekranından yönetin. Ödeme sağlayıcısı iadesi gerekiyorsa sipariş ekranı ve sağlayıcı panelindeki sonucu birlikte doğrulayın.', 'moda-store-admin' ),
		),
		'customers' => array(
			'title' => __( 'Müşteriler', 'moda-store-admin' ),
			'cap'   => 'manage_woocommerce',
			'links' => array(
				array( __( 'WooCommerce müşteri listesi', 'moda-store-admin' ), admin_url( 'admin.php?page=wc-admin&path=%2Fcustomers' ), 'manage_woocommerce' ),
			),
			'note'  => __( 'Müşteri kişisel verilerine erişimi görev gereği olan personelle sınırlayın.', 'moda-store-admin' ),
		),
		'coupons' => array(
			'title' => __( 'Kuponlar ve indirimler', 'moda-store-admin' ),
			'cap'   => 'edit_shop_coupons',
			'links' => array(
				array( __( 'Kuponları yönet', 'moda-store-admin' ), admin_url( 'edit.php?post_type=shop_coupon' ), 'edit_shop_coupons' ),
				array( __( 'Yeni kupon oluştur', 'moda-store-admin' ), admin_url( 'post-new.php?post_type=shop_coupon' ), 'edit_shop_coupons' ),
			),
			'note'  => __( 'İndirim tarihleri, kullanım limitleri ve kupon koşulları WooCommerce kupon ayarlarından uygulanır.', 'moda-store-admin' ),
		),
		'campaigns' => array(
			'title' => __( 'Kampanya alanları', 'moda-store-admin' ),
			'cap'   => 'edit_theme_options',
			'links' => array(
				array(
					__( 'Ana sayfa kampanya görsellerini ve metinlerini düzenle', 'moda-store-admin' ),
					add_query_arg( array( 'autofocus[section]' => 'moda_atelier_homepage' ), admin_url( 'customize.php' ) ),
					'edit_theme_options',
				),
			),
			'note'  => __( 'Tema Özelleştirici bağlantısı yalnızca tema ayarlarını değiştirme yetkisi olan kullanıcılara gösterilir.', 'moda-store-admin' ),
		),
		'reports' => array(
			'title' => __( 'Satış ve stok raporları', 'moda-store-admin' ),
			'cap'   => 'view_woocommerce_reports',
			'links' => array(
				array( __( 'Satış analitiği', 'moda-store-admin' ), admin_url( 'admin.php?page=wc-admin&path=%2Fanalytics%2Foverview' ), 'view_woocommerce_reports' ),
				array( __( 'Stok analitiği', 'moda-store-admin' ), admin_url( 'admin.php?page=wc-admin&path=%2Fanalytics%2Fstock' ), 'view_woocommerce_reports' ),
			),
			'note'  => __( 'Analitik menüleri WooCommerce sürümüne ve etkin rapor modüllerine göre değişebilir.', 'moda-store-admin' ),
		),
	);

	return $sections;
}

/**
 * Render the capability-aware store dashboard.
 *
 * @return void
 */
function moda_store_admin_render_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Bu mağaza yönetim sayfasına erişim yetkiniz yok.', 'moda-store-admin' ) );
	}
	?>
	<div class="wrap ma-store-admin">
		<h1><?php esc_html_e( 'Mağaza yönetimi', 'moda-store-admin' ); ?></h1>
		<p class="ma-store-admin-intro"><?php esc_html_e( 'Günlük mağaza işlemlerini WooCommerce’in yerleşik ve yetki denetimli ekranlarından yönetin.', 'moda-store-admin' ); ?></p>

		<?php if ( ! class_exists( 'WooCommerce' ) ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Bu panel WooCommerce etkinleştirildiğinde kullanılabilir.', 'moda-store-admin' ); ?></p></div>
		<?php else : ?>
			<div class="notice notice-info inline">
				<p><?php esc_html_e( 'Personel için WordPress Kullanıcılar bölümünden WooCommerce Mağaza yöneticisi rolünü kullanın. Bu rol mağazayı yönetir ancak tam site yöneticisi değildir. Sadece görsel/kampanya düzenlemesi gereken personele tema ayarlarını değiştirme yetkisi vermeyin.', 'moda-store-admin' ); ?></p>
			</div>

			<div class="ma-store-admin-grid">
				<?php foreach ( moda_store_admin_get_sections() as $section ) : ?>
					<?php if ( ! current_user_can( $section['cap'] ) ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<section class="ma-store-admin-card">
						<h2><?php echo esc_html( $section['title'] ); ?></h2>
						<ul>
							<?php foreach ( $section['links'] as $link ) : ?>
								<?php if ( ! current_user_can( $link[2] ) ) : ?>
									<?php continue; ?>
								<?php endif; ?>
								<li><a href="<?php echo esc_url( $link[1] ); ?>"><?php echo esc_html( $link[0] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
						<p><?php echo esc_html( $section['note'] ); ?></p>
					</section>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}
