<?php
/**
 * Site footer.
 *
 * @package Moda_Atelier
 */

$footer_categories = taxonomy_exists( 'product_cat' ) ? get_terms( array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
	'parent'     => 0,
	'exclude'    => array( (int) get_option( 'default_product_cat', 0 ) ),
	'number'     => 5,
) ) : array();
$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
$order_tracking  = get_page_by_path( 'siparis-takibi' );
$contact_page    = get_page_by_path( 'iletisim' );
if ( ! $contact_page ) {
	$contact_page = get_page_by_path( 'contact' );
}
?>
<footer class="ma-site-footer">
	<div class="ma-container">
		<div class="ma-footer-top">
			<div class="ma-footer-brand-column">
				<a class="ma-footer-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
				<p class="ma-footer-description"><?php echo esc_html( get_theme_mod( 'moda_atelier_footer_description', __( 'Özenle seçilmiş kumaşlar ve her gün yeniden giymek isteyeceğiniz modern temel parçalar.', 'moda-atelier' ) ) ); ?></p>
			</div>
			<div>
				<h2 class="ma-footer-title"><?php echo esc_html( get_theme_mod( 'moda_atelier_footer_discover_title', __( 'Keşfet', 'moda-atelier' ) ) ); ?></h2>
				<?php if ( has_nav_menu( 'footer' ) ) : ?>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'ma-footer-menu',
						'depth'          => 1,
					) );
					?>
				<?php else : ?>
					<ul class="ma-footer-menu">
						<li><a href="<?php echo esc_url( moda_atelier_shop_url() ); ?>"><?php esc_html_e( 'Tüm ürünler', 'moda-atelier' ); ?></a></li>
						<?php if ( $footer_categories && ! is_wp_error( $footer_categories ) ) : ?>
							<?php foreach ( $footer_categories as $category ) : ?>
								<li><a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a></li>
							<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				<?php endif; ?>
			</div>
			<div>
				<h2 class="ma-footer-title"><?php echo esc_html( get_theme_mod( 'moda_atelier_footer_service_title', __( 'Müşteri hizmetleri', 'moda-atelier' ) ) ); ?></h2>
				<ul class="ma-footer-menu">
					<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
						<?php if ( wc_get_page_permalink( 'myaccount' ) ) : ?>
							<li><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Hesabım', 'moda-atelier' ); ?></a></li>
						<?php endif; ?>
						<?php if ( $order_tracking instanceof WP_Post && 'publish' === $order_tracking->post_status ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $order_tracking ) ); ?>"><?php esc_html_e( 'Sipariş takibi', 'moda-atelier' ); ?></a></li>
						<?php endif; ?>
						<?php if ( wc_get_page_permalink( 'cart' ) ) : ?>
							<li><a href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>"><?php esc_html_e( 'Sepetim', 'moda-atelier' ); ?></a></li>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( $contact_page instanceof WP_Post ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $contact_page ) ); ?>"><?php esc_html_e( 'İletişim', 'moda-atelier' ); ?></a></li>
					<?php endif; ?>
					<?php if ( $privacy_page_id ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $privacy_page_id ) ); ?>"><?php esc_html_e( 'Gizlilik politikası', 'moda-atelier' ); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
		<div class="ma-footer-bottom">
			<span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
			<span><?php echo esc_html( get_theme_mod( 'moda_atelier_footer_legal', __( 'Tüm hakları saklıdır.', 'moda-atelier' ) ) ); ?></span>
			<span class="ma-footer-credit"><?php echo esc_html( get_theme_mod( 'moda_atelier_footer_developer_credit', __( 'Yazılım: Seyem Yazılım', 'moda-atelier' ) ) ); ?></span>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
