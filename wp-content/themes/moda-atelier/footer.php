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
$whatsapp_url = moda_atelier_get_whatsapp_url();
?>
<footer class="ma-site-footer">
	<div class="ma-container">
		<div class="ma-footer-top">
			<div class="ma-footer-brand-column">
				<div class="ma-footer-brand-lockup">
					<img class="ma-footer-mascot" src="<?php echo esc_url( get_theme_file_uri( '/assets/images/mc-brand-mark.svg' ) ); ?>" alt="" aria-hidden="true">
					<div>
						<a class="ma-footer-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
						<span class="ma-footer-brand-tagline"><?php esc_html_e( 'CHICAGO · ERKEK GİYİM', 'moda-atelier' ); ?></span>
					</div>
				</div>
				<p class="ma-footer-description"><?php echo esc_html( get_theme_mod( 'moda_atelier_footer_description', __( 'Chicago’nun şehir ritminden ilham alan, kendi çizgisini taşıyan erkek stili.', 'moda-atelier' ) ) ); ?></p>
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
					<?php foreach ( array( 'sikca-sorulan-sorular' => __( 'Sıkça sorulan sorular', 'moda-atelier' ), 'destek-talebi' => __( 'Şikayet ve talep formu', 'moda-atelier' ) ) as $support_slug => $support_label ) : ?>
						<?php $support_page = get_page_by_path( $support_slug ); ?>
						<?php if ( $support_page instanceof WP_Post && 'publish' === $support_page->post_status ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $support_page ) ); ?>"><?php echo esc_html( $support_label ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
					<?php if ( $contact_page instanceof WP_Post ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $contact_page ) ); ?>"><?php esc_html_e( 'İletişim', 'moda-atelier' ); ?></a></li>
					<?php endif; ?>
					<?php if ( $privacy_page_id ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $privacy_page_id ) ); ?>"><?php esc_html_e( 'Gizlilik politikası', 'moda-atelier' ); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="ma-footer-contact">
				<h2 class="ma-footer-title"><?php esc_html_e( 'Moda Chicago ile iletişim', 'moda-atelier' ); ?></h2>
				<p><?php esc_html_e( 'Koleksiyon ve siparişler hakkında bize yazın.', 'moda-atelier' ); ?></p>
				<?php if ( $whatsapp_url ) : ?>
					<a class="ma-footer-whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer">
						<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20.1 11.7a8.1 8.1 0 0 1-11.9 7.1L4 20l1.2-4a8.1 8.1 0 1 1 14.9-4.3Z"/><path d="M8.5 8.2c.2-.5.5-.5.8-.5h.4c.2 0 .3.1.4.4l.6 1.5c.1.2 0 .4-.1.6l-.5.6c-.2.2-.2.3-.1.5.4.7 1 1.3 1.7 1.7.2.1.4.1.5-.1l.7-.8c.2-.2.3-.2.6-.1l1.4.7c.3.1.4.2.4.4 0 .3-.2 1-.6 1.3-.4.4-1 .6-1.5.5-.5-.1-1.2-.3-2.1-.8-1.2-.7-2.2-1.7-2.7-2.4-.5-.7-.8-1.5-.8-2.1 0-.5.4-1.1.8-1.4Z"/></svg>
						<span class="ma-footer-whatsapp-text">
							<small><?php esc_html_e( 'Hızlı yanıt için', 'moda-atelier' ); ?></small>
							<strong><?php esc_html_e( 'WhatsApp’tan yazın', 'moda-atelier' ); ?></strong>
						</span>
						<span aria-hidden="true">↗</span>
					</a>
				<?php endif; ?>
				<a class="ma-footer-instagram" href="https://www.instagram.com/moda.chicago27/" target="_blank" rel="noopener noreferrer">
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r=".6"/></svg>
					<span class="ma-footer-instagram-text">
						<small><?php esc_html_e( 'Instagram’da takip edin', 'moda-atelier' ); ?></small>
						<strong>Moda Chicago</strong>
					</span>
					<span aria-hidden="true">↗</span>
				</a>
				<button type="button" class="ma-footer-install" data-moda-pwa-install hidden>
					<?php esc_html_e( 'Mobil uygulamayı telefonuna yükle', 'moda-atelier' ); ?>
				</button>
				<p class="ma-footer-install-hint" data-moda-pwa-hint role="status" hidden></p>
			</div>
		</div>
		<div class="ma-footer-bottom">
			<span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
			<span><?php echo esc_html( get_theme_mod( 'moda_atelier_footer_legal', __( 'Tüm hakları saklıdır.', 'moda-atelier' ) ) ); ?></span>
			<span class="ma-footer-credit"><?php echo esc_html( get_theme_mod( 'moda_atelier_footer_developer_credit', __( 'Yazılım: Seyem Yazılım', 'moda-atelier' ) ) ); ?></span>
		</div>
	</div>
</footer>
<?php if ( $whatsapp_url ) : ?>
	<a class="ma-whatsapp-float" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'WhatsApp üzerinden Moda Chicago ile iletişime geçin', 'moda-atelier' ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20.1 11.7a8.1 8.1 0 0 1-11.9 7.1L4 20l1.2-4a8.1 8.1 0 1 1 14.9-4.3Z"/><path d="M8.5 8.2c.2-.5.5-.5.8-.5h.4c.2 0 .3.1.4.4l.6 1.5c.1.2 0 .4-.1.6l-.5.6c-.2.2-.2.3-.1.5.4.7 1 1.3 1.7 1.7.2.1.4.1.5-.1l.7-.8c.2-.2.3-.2.6-.1l1.4.7c.3.1.4.2.4.4 0 .3-.2 1-.6 1.3-.4.4-1 .6-1.5.5-.5-.1-1.2-.3-2.1-.8-1.2-.7-2.2-1.7-2.7-2.4-.5-.7-.8-1.5-.8-2.1 0-.5.4-1.1.8-1.4Z"/></svg>
		<span><?php esc_html_e( 'WhatsApp', 'moda-atelier' ); ?></span>
	</a>
<?php endif; ?>
<?php
$has_wc      = function_exists( 'wc_get_page_permalink' );
$nav_account = $has_wc ? wc_get_page_permalink( 'myaccount' ) : '';
$nav_cart    = $has_wc ? wc_get_page_permalink( 'cart' ) : '';
$nav_items   = array(
	array(
		'url'    => home_url( '/' ),
		'label'  => __( 'Ana sayfa', 'moda-atelier' ),
		'active' => is_front_page(),
		'icon'   => '<path d="M4 11 12 4l8 7"/><path d="M6 10v9.5h4.5V14h3v5.5H18V10"/>',
	),
	array(
		'url'    => moda_atelier_shop_url(),
		'label'  => __( 'Mağaza', 'moda-atelier' ),
		'active' => $has_wc && ( is_shop() || is_product_taxonomy() || is_product() ),
		'icon'   => '<path d="M5 8h14l-1 11.5H6L5 8Z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/>',
	),
	array(
		'url'    => home_url( '/#favorites' ),
		'label'  => __( 'Favoriler', 'moda-atelier' ),
		'active' => false,
		'icon'   => '<path d="M20.8 8.8c0 5.1-8.8 11-8.8 11s-8.8-5.9-8.8-11A4.8 4.8 0 0 1 12 6.1a4.8 4.8 0 0 1 8.8 2.7Z"/>',
		'badge'  => 'ma-favorite-count',
	),
);
if ( $nav_cart ) {
	$nav_items[] = array(
		'url'    => $nav_cart,
		'label'  => __( 'Sepet', 'moda-atelier' ),
		'active' => is_cart(),
		'icon'   => '<path d="M3.5 4.5h2l2.1 11.2h10.7l2-8.1H6.2"/><circle cx="9.2" cy="19.2" r="1.1"/><circle cx="17.2" cy="19.2" r="1.1"/>',
		'badge'  => 'ma-cart-count',
		'count'  => function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
	);
}
if ( $nav_account ) {
	$nav_items[] = array(
		'url'    => $nav_account,
		'label'  => is_user_logged_in() ? __( 'Hesabım', 'moda-atelier' ) : __( 'Giriş', 'moda-atelier' ),
		'active' => is_account_page(),
		'icon'   => '<circle cx="12" cy="8" r="3.5"/><path d="M4.5 20c.5-4 3.1-6.2 7.5-6.2s7 2.2 7.5 6.2"/>',
	);
}
?>
<nav class="ma-bottom-nav" aria-label="<?php esc_attr_e( 'Hızlı gezinme', 'moda-atelier' ); ?>">
	<?php foreach ( $nav_items as $nav_item ) : ?>
		<a class="ma-bottom-nav-item<?php echo $nav_item['active'] ? ' is-active' : ''; ?>" href="<?php echo esc_url( $nav_item['url'] ); ?>"<?php echo $nav_item['active'] ? ' aria-current="page"' : ''; ?>>
			<span class="ma-bottom-nav-icon">
				<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?php echo $nav_item['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup defined above. ?></svg>
				<?php if ( ! empty( $nav_item['badge'] ) ) : ?>
					<span class="<?php echo esc_attr( $nav_item['badge'] ); ?> ma-bottom-nav-badge"><?php echo esc_html( (string) ( $nav_item['count'] ?? 0 ) ); ?></span>
				<?php endif; ?>
			</span>
			<span class="ma-bottom-nav-label"><?php echo esc_html( $nav_item['label'] ); ?></span>
		</a>
	<?php endforeach; ?>
</nav>
<?php wp_footer(); ?>
</body>
</html>
