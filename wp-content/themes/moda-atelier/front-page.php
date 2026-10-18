<?php
/**
 * Homepage template.
 *
 * @package Moda_Atelier
 */

get_header();

$shop_url        = moda_atelier_shop_url();
$hero_image_id   = (int) get_theme_mod( 'moda_atelier_hero_image', 0 );
$hero_image_url  = $hero_image_id ? wp_get_attachment_image_url( $hero_image_id, 'large' ) : '';
$newsletter_url  = get_theme_mod( 'moda_atelier_newsletter_url', '' );
$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
$privacy_url     = $privacy_page_id ? get_permalink( $privacy_page_id ) : '';
?>
<main id="main-content">
	<section class="ma-hero">
		<div class="ma-hero-copy">
			<p class="ma-eyebrow"><?php echo esc_html( get_theme_mod( 'moda_atelier_hero_eyebrow', __( 'Yeni sezon / 2026', 'moda-atelier' ) ) ); ?></p>
			<h1><?php echo esc_html( get_theme_mod( 'moda_atelier_hero_title', __( 'Zamansız parçalar. Kendine özgü bir stil.', 'moda-atelier' ) ) ); ?></h1>
			<p class="ma-hero-description"><?php echo esc_html( get_theme_mod( 'moda_atelier_hero_description', __( 'Özenle seçilmiş kumaşlar ve her gün yeniden giymek isteyeceğiniz modern temel parçalar.', 'moda-atelier' ) ) ); ?></p>
			<a class="ma-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( get_theme_mod( 'moda_atelier_hero_button_label', __( 'Koleksiyonu keşfet', 'moda-atelier' ) ) ); ?></a>
		</div>
		<div class="ma-hero-art<?php echo $hero_image_url ? ' has-image' : ''; ?>"<?php echo $hero_image_url ? ' style="--ma-hero-image: url(&quot;' . esc_url( $hero_image_url ) . '&quot;)"' : ''; ?> role="img" aria-label="<?php esc_attr_e( 'Marka kampanya görseli', 'moda-atelier' ); ?>">
			<span class="ma-art-label"><?php esc_html_e( 'Günün her anına.', 'moda-atelier' ); ?></span>
			<span class="ma-art-index">01 — 26</span>
		</div>
	</section>

	<section class="ma-campaigns ma-container" id="campaigns" aria-label="<?php esc_attr_e( 'Kampanyalar', 'moda-atelier' ); ?>">
		<?php foreach ( array( 'campaign_one', 'campaign_two' ) as $campaign_key ) : ?>
			<?php
			$campaign_image_id  = (int) get_theme_mod( 'moda_atelier_' . $campaign_key . '_image', 0 );
			$campaign_image_url = $campaign_image_id ? wp_get_attachment_image_url( $campaign_image_id, 'large' ) : '';
			$campaign_url       = get_theme_mod( 'moda_atelier_' . $campaign_key . '_url', '' );
			$campaign_url       = $campaign_url ? $campaign_url : $shop_url;
			$campaign_headline  = get_theme_mod(
				'moda_atelier_' . $campaign_key . '_headline',
				'campaign_one' === $campaign_key
					? __( 'Günlük stil, yeniden yorumlandı.', 'moda-atelier' )
					: __( 'Gardırobun yeni favorileri.', 'moda-atelier' )
			);
			$campaign_copy = get_theme_mod(
				'moda_atelier_' . $campaign_key . '_copy',
				'campaign_one' === $campaign_key
					? __( 'Mevsimin öne çıkan parçalarını keşfedin.', 'moda-atelier' )
					: __( 'Özenle seçilmiş zamansız parçalar.', 'moda-atelier' )
			);
			?>
			<a class="ma-campaign-card<?php echo $campaign_image_url ? ' has-image' : ''; ?>" href="<?php echo esc_url( $campaign_url ); ?>"<?php echo $campaign_image_url ? ' style="--ma-campaign-image: url(&quot;' . esc_url( $campaign_image_url ) . '&quot;)"' : ''; ?>>
				<span class="ma-campaign-copy">
					<span class="ma-eyebrow"><?php echo esc_html( 'campaign_one' === $campaign_key ? __( 'Yeni sezon', 'moda-atelier' ) : __( 'Seçili parçalar', 'moda-atelier' ) ); ?></span>
					<strong><?php echo esc_html( $campaign_headline ); ?></strong>
					<span class="ma-campaign-description"><?php echo esc_html( $campaign_copy ); ?></span>
					<span class="ma-campaign-link"><?php esc_html_e( 'Keşfet', 'moda-atelier' ); ?> <span aria-hidden="true">↗</span></span>
				</span>
			</a>
		<?php endforeach; ?>
	</section>

	<?php if ( taxonomy_exists( 'product_cat' ) ) : ?>
		<section class="ma-section ma-category-section">
			<div class="ma-container">
				<div class="ma-section-heading">
					<div>
						<p class="ma-eyebrow"><?php esc_html_e( 'Stilini keşfet', 'moda-atelier' ); ?></p>
						<h2><?php esc_html_e( 'Kategorilere göre alışveriş', 'moda-atelier' ); ?></h2>
					</div>
					<a class="ma-text-link" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Tüm koleksiyon', 'moda-atelier' ); ?></a>
				</div>
				<?php
				$categories = get_terms( array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => true,
					'parent'     => 0,
					'exclude'    => array( (int) get_option( 'default_product_cat', 0 ) ),
					'number'     => 5,
					'orderby'    => 'name',
				) );
				?>
				<?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
					<div class="ma-category-grid">
						<?php foreach ( $categories as $category ) : ?>
							<?php
							$category_image_id  = (int) get_term_meta( $category->term_id, 'thumbnail_id', true );
							$category_image_url = $category_image_id ? wp_get_attachment_image_url( $category_image_id, 'large' ) : '';
							?>
							<a class="ma-category-card<?php echo $category_image_url ? ' has-image' : ''; ?>" href="<?php echo esc_url( get_term_link( $category ) ); ?>"<?php echo $category_image_url ? ' style="--ma-category-image: url(&quot;' . esc_url( $category_image_url ) . '&quot;)"' : ''; ?>>
								<span><?php echo esc_html( $category->name ); ?></span>
								<span class="ma-category-arrow" aria-hidden="true">↗</span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p class="ma-empty-state"><?php esc_html_e( 'Burada görünmeleri için WooCommerce üzerinden ürün kategorileri ekleyin.', 'moda-atelier' ); ?></p>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( function_exists( 'wc_get_products' ) ) : ?>
		<?php
		moda_atelier_product_section(
			__( 'Yeni sezon', 'moda-atelier' ),
			array(
				'orderby' => 'date',
				'order'   => 'DESC',
			),
			'new-season',
			__( 'Yeni gelenleri gör', 'moda-atelier' )
		);

		moda_atelier_product_section(
			__( 'Çok satanlar', 'moda-atelier' ),
			array(
				'orderby' => 'popularity',
				'order'   => 'DESC',
			),
			'best-sellers',
			__( 'Çok satanları gör', 'moda-atelier' )
		);

		moda_atelier_product_section(
			__( 'Sezon indirimi', 'moda-atelier' ),
			array(
				'on_sale' => true,
				'orderby' => 'date',
				'order'   => 'DESC',
			),
			'sale',
			__( 'İndirimli ürünleri gör', 'moda-atelier' )
		);
		?>

		<section class="ma-section ma-favorites-section" id="favorites">
			<div class="ma-container">
				<div class="ma-section-heading">
					<div>
						<p class="ma-eyebrow"><?php esc_html_e( 'Sakladıkların', 'moda-atelier' ); ?></p>
						<h2><?php esc_html_e( 'Favorilerin', 'moda-atelier' ); ?></h2>
					</div>
					<p class="ma-favorites-feedback" aria-live="polite"><?php esc_html_e( 'Favoriler bu tarayıcıda saklanır.', 'moda-atelier' ); ?></p>
				</div>
				<div class="ma-product-grid ma-favorites-grid" aria-live="polite"></div>
				<p class="ma-favorites-empty"><?php esc_html_e( 'Favori ürün eklemek için ürün kartındaki kalp simgesine dokunun.', 'moda-atelier' ); ?></p>
			</div>
		</section>
	<?php else : ?>
		<section class="ma-section">
			<div class="ma-container">
				<p class="ma-notice"><?php esc_html_e( 'Ürün koleksiyonları WooCommerce etkinleştirildiğinde görüntülenir.', 'moda-atelier' ); ?></p>
			</div>
		</section>
	<?php endif; ?>

	<section class="ma-newsletter">
		<div class="ma-container ma-newsletter-inner">
			<div>
				<p class="ma-eyebrow"><?php esc_html_e( 'Moda Atelier mektupları', 'moda-atelier' ); ?></p>
				<h2><?php esc_html_e( 'Yeni sezon, önce seninle.', 'moda-atelier' ); ?></h2>
				<p><?php esc_html_e( 'Yeni koleksiyonlardan ve özel seçkilerimizden haberdar ol.', 'moda-atelier' ); ?></p>
				<?php if ( $privacy_url ) : ?>
					<a class="ma-privacy-link" href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Gizlilik politikası', 'moda-atelier' ); ?></a>
				<?php endif; ?>
			</div>
			<div class="ma-newsletter-action">
				<?php if ( $newsletter_url ) : ?>
					<a class="ma-button ma-button--light" href="<?php echo esc_url( $newsletter_url ); ?>"><?php esc_html_e( 'Bültene katıl', 'moda-atelier' ); ?></a>
					<span><?php esc_html_e( 'Bülten izin ve abonelik tercihleri sağlayıcı üzerinden yönetilir.', 'moda-atelier' ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
