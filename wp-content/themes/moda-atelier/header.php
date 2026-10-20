<?php
/**
 * Site header.
 *
 * @package Moda_Atelier
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '';
$cart_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'cart' ) : '';
$announcement      = get_theme_mod( 'moda_atelier_announcement', __( 'Yeni sezon yayında', 'moda-atelier' ) );
$announcement_url  = get_theme_mod( 'moda_atelier_announcement_url', home_url( '/#campaigns' ) );
?>
<a class="ma-skip-link" href="#main-content"><?php esc_html_e( 'İçeriğe geç', 'moda-atelier' ); ?></a>
<?php if ( get_theme_mod( 'moda_atelier_show_announcement', true ) ) : ?>
<div class="ma-announcement">
	<span><?php echo esc_html( $announcement ); ?></span>
	<?php if ( $announcement_url ) : ?>
		<span aria-hidden="true">·</span>
		<a href="<?php echo esc_url( $announcement_url ); ?>"><?php esc_html_e( 'Keşfet', 'moda-atelier' ); ?></a>
	<?php endif; ?>
</div>
<?php endif; ?>
<header class="ma-site-header">
	<div class="ma-container ma-header-inner">
		<button class="ma-menu-toggle" type="button" aria-expanded="false" aria-controls="site-navigation">
			<span></span><span></span><span></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Menüyü aç', 'moda-atelier' ); ?></span>
		</button>

		<?php if ( has_custom_logo() ) : ?>
			<?php the_custom_logo(); ?>
		<?php else : ?>
			<a class="ma-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<?php bloginfo( 'name' ); ?>
			</a>
		<?php endif; ?>

		<nav class="ma-primary-nav" id="site-navigation" aria-label="<?php esc_attr_e( 'Ana gezinme', 'moda-atelier' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'menu',
				'fallback_cb'    => 'moda_atelier_primary_menu_fallback',
			) );
			?>
		</nav>

		<div class="ma-header-actions">
			<form class="ma-search-form" role="search" method="get" action="<?php echo esc_url( moda_atelier_shop_url() ); ?>">
				<label class="screen-reader-text" for="ma-product-search"><?php esc_html_e( 'Ürün ara', 'moda-atelier' ); ?></label>
				<input id="ma-product-search" class="ma-search-input" type="search" name="s" placeholder="<?php esc_attr_e( 'Ürün ara', 'moda-atelier' ); ?>" tabindex="-1">
				<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
					<input type="hidden" name="post_type" value="product">
				<?php endif; ?>
				<button class="ma-icon-button ma-search-submit" type="submit" aria-label="<?php esc_attr_e( 'Ara', 'moda-atelier' ); ?>" aria-expanded="false">
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="10.8" cy="10.8" r="6.4"/><path d="m16 16 4.5 4.5"/></svg>
				</button>
			</form>
			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
				<a class="ma-icon-button ma-favorites-link" href="<?php echo esc_url( home_url( '/#favorites' ) ); ?>" aria-label="<?php esc_attr_e( 'Favoriler', 'moda-atelier' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20.8 8.8c0 5.1-8.8 11-8.8 11s-8.8-5.9-8.8-11A4.8 4.8 0 0 1 12 6.1a4.8 4.8 0 0 1 8.8 2.7Z"/></svg>
					<span class="ma-favorite-count" aria-live="polite">0</span>
				</a>
				<?php if ( $account_url ) : ?>
					<a class="ma-icon-button ma-account-link" href="<?php echo esc_url( $account_url ); ?>" aria-label="<?php echo esc_attr( is_user_logged_in() ? __( 'Hesabım', 'moda-atelier' ) : __( 'Müşteri girişi ve kayıt', 'moda-atelier' ) ); ?>" title="<?php echo esc_attr( is_user_logged_in() ? __( 'Hesabım', 'moda-atelier' ) : __( 'Müşteri girişi ve kayıt', 'moda-atelier' ) ); ?>">
						<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20c.5-4 3.1-6.2 7.5-6.2s7 2.2 7.5 6.2"/></svg>
					</a>
				<?php endif; ?>
				<?php if ( $cart_url ) : ?>
					<a class="ma-icon-button ma-cart-link" href="<?php echo esc_url( $cart_url ); ?>" aria-label="<?php esc_attr_e( 'Sepet', 'moda-atelier' ); ?>">
						<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3.5 4.5h2l2.1 11.2h10.7l2-8.1H6.2"/><circle cx="9.2" cy="19.2" r="1.1"/><circle cx="17.2" cy="19.2" r="1.1"/></svg>
						<span class="ma-cart-count"><?php echo esc_html( function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ); ?></span>
					</a>
				<?php endif; ?>
			<?php else : ?>
				<a class="ma-icon-button ma-cart-link" href="<?php echo esc_url( moda_atelier_shop_url() ); ?>" aria-label="<?php esc_attr_e( 'Mağaza', 'moda-atelier' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3.5 4.5h2l2.1 11.2h10.7l2-8.1H6.2"/><circle cx="9.2" cy="19.2" r="1.1"/><circle cx="17.2" cy="19.2" r="1.1"/></svg>
				</a>
			<?php endif; ?>
		</div>
	</div>
</header>
