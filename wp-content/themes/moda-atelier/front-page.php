<?php
/**
 * Homepage template.
 *
 * @package Moda_Atelier
 */

get_header();

$shop_url        = moda_atelier_shop_url();
$newsletter_url  = get_theme_mod( 'moda_atelier_newsletter_url', '' );
$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
$privacy_url     = $privacy_page_id ? get_permalink( $privacy_page_id ) : '';
$slides          = array(
	array(
		'eyebrow' => get_theme_mod( 'moda_atelier_hero_eyebrow', __( 'Chicago / Erkek koleksiyonu', 'moda-atelier' ) ),
		'title'   => get_theme_mod( 'moda_atelier_hero_title', __( 'Şehrin ritmi, senin stilin.', 'moda-atelier' ) ),
		'copy'    => get_theme_mod( 'moda_atelier_hero_description', __( 'Günlük kombinlerden şehir akşamlarına. Tarzını tamamlayan modern erkek giyim parçalarını keşfet.', 'moda-atelier' ) ),
		'button'  => get_theme_mod( 'moda_atelier_hero_button_label', __( 'Yeni sezonu keşfet', 'moda-atelier' ) ),
		'url'     => get_theme_mod( 'moda_atelier_hero_button_url', '' ),
		'image'   => (int) get_theme_mod( 'moda_atelier_hero_image', 0 ),
	),
);
foreach ( array( 2, 3 ) as $slide_number ) {
	$slides[] = array(
		'eyebrow' => get_theme_mod( 'moda_atelier_hero_slide_' . $slide_number . '_eyebrow', 2 === $slide_number ? __( 'Günlük şehir stili', 'moda-atelier' ) : __( 'Yeni sezon seçkisi', 'moda-atelier' ) ),
		'title'   => get_theme_mod( 'moda_atelier_hero_slide_' . $slide_number . '_title', 2 === $slide_number ? __( 'Her ana eşlik eden parçalar.', 'moda-atelier' ) : __( 'Tarzını yeni baştan kur.', 'moda-atelier' ) ),
		'copy'    => get_theme_mod( 'moda_atelier_hero_slide_' . $slide_number . '_copy', 2 === $slide_number ? __( 'Günlük giyimde konforu ve güçlü tasarımı bir araya getir.', 'moda-atelier' ) : __( 'Modern erkek gardırobuna özel yeni favorilerini keşfet.', 'moda-atelier' ) ),
		'button'  => get_theme_mod( 'moda_atelier_hero_slide_' . $slide_number . '_button', 2 === $slide_number ? __( 'Koleksiyonu incele', 'moda-atelier' ) : __( 'Tüm ürünleri gör', 'moda-atelier' ) ),
		'url'     => get_theme_mod( 'moda_atelier_hero_slide_' . $slide_number . '_url', '' ),
		'image'   => (int) get_theme_mod( 'moda_atelier_hero_slide_' . $slide_number . '_image', 0 ),
	);
}
?>
<main id="main-content">
	<section class="ma-hero" aria-label="<?php esc_attr_e( 'Öne çıkan koleksiyonlar', 'moda-atelier' ); ?>">
		<div class="ma-hero-copy" data-hero-copy aria-live="off">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<?php
				$image_url = $slide['image'] ? wp_get_attachment_image_url( $slide['image'], 'large' ) : '';
				$button_url = $slide['url'] ? $slide['url'] : $shop_url;
				?>
				<div class="ma-hero-copy-slide" data-hero-copy-slide="<?php echo esc_attr( $index ); ?>"<?php echo 0 === $index ? '' : ' hidden'; ?>>
					<p class="ma-eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></p>
					<h1><?php echo esc_html( $slide['title'] ); ?></h1>
					<p class="ma-hero-description"><?php echo esc_html( $slide['copy'] ); ?></p>
					<a class="ma-button" href="<?php echo esc_url( $button_url ); ?>"><?php echo esc_html( $slide['button'] ); ?></a>
					<span class="ma-hero-slide-count"><?php echo esc_html( sprintf( '%02d / %02d', $index + 1, count( $slides ) ) ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="ma-hero-art" data-hero-carousel data-autoplay="true" role="region" aria-roledescription="<?php esc_attr_e( 'carousel', 'moda-atelier' ); ?>" aria-label="<?php esc_attr_e( 'Kampanya görselleri', 'moda-atelier' ); ?>">
			<div class="ma-hero-slides" aria-live="off">
				<?php foreach ( $slides as $index => $slide ) : ?>
					<?php $image_url = $slide['image'] ? wp_get_attachment_image_url( $slide['image'], 'large' ) : ''; ?>
					<div class="ma-hero-slide<?php echo $image_url ? ' has-image' : ''; ?>" data-hero-slide="<?php echo esc_attr( $index ); ?>"<?php echo $image_url ? ' style="--ma-hero-image: url(&quot;' . esc_url( $image_url ) . '&quot;)"' : ''; ?> role="group" aria-roledescription="<?php esc_attr_e( 'slayt', 'moda-atelier' ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '%1$d / %2$d: %3$s', 'moda-atelier' ), $index + 1, count( $slides ), $slide['title'] ) ); ?>"<?php echo 0 === $index ? '' : ' hidden'; ?>>
						<?php if ( $image_url ) : ?>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="" <?php echo 0 === $index ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
						<?php else : ?>
							<svg class="ma-bull-mascot" viewBox="0 0 320 220" aria-hidden="true" focusable="false">
								<path class="ma-bull-body" d="M43 112c9-34 39-51 78-48 22 2 41 12 56 24l22-4 19-19 19 1 11-12 19 8-9 12-17 3-5 18-15 16-8 29-3 28h-17l-1-34-17-8-19 2-12 12-2 28h-17l3-33-15-7-16 8-4 32H94l2-36-17-7-14 12-2 31H47l2-34-15-7z"/>
								<path class="ma-bull-horn" d="M231 72c-13-14-17-27-18-39 17 8 28 18 33 33m7 5c11-14 19-21 33-24-4 16-12 27-26 34"/>
								<path class="ma-bull-tail" d="M45 111c-14-10-22-21-24-34 14 7 23 17 28 30"/>
								<path class="ma-bull-detail" d="m251 91 5 2-5 3"/>
							</svg>
							<span class="ma-art-label"><?php esc_html_e( 'Chicago erkek koleksiyonu', 'moda-atelier' ); ?></span>
							<span class="ma-art-index">EST. 2026</span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="ma-hero-controls" aria-label="<?php esc_attr_e( 'Görsel kontrolleri', 'moda-atelier' ); ?>">
				<button class="ma-hero-arrow" type="button" data-hero-previous aria-label="<?php esc_attr_e( 'Önceki slayt', 'moda-atelier' ); ?>">←</button>
				<div class="ma-hero-dots">
					<?php foreach ( $slides as $index => $slide ) : ?>
						<button class="ma-hero-dot" type="button" data-hero-go="<?php echo esc_attr( $index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '%d. slaytı göster', 'moda-atelier' ), $index + 1 ) ); ?>" aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>"></button>
					<?php endforeach; ?>
				</div>
				<button class="ma-hero-arrow" type="button" data-hero-next aria-label="<?php esc_attr_e( 'Sonraki slayt', 'moda-atelier' ); ?>">→</button>
				<button class="ma-hero-pause" type="button" data-hero-pause data-label-pause="<?php esc_attr_e( 'Otomatik geçişi duraklat', 'moda-atelier' ); ?>" data-label-play="<?php esc_attr_e( 'Otomatik geçişi başlat', 'moda-atelier' ); ?>" aria-label="<?php esc_attr_e( 'Otomatik geçişi duraklat', 'moda-atelier' ); ?>" aria-pressed="false">Ⅱ</button>
			</div>
		</div>
	</section>

	<?php if ( get_theme_mod( 'moda_atelier_show_campaigns', true ) ) : ?>
	<section class="ma-campaigns ma-container" id="campaigns" aria-label="<?php esc_attr_e( 'Kampanyalar', 'moda-atelier' ); ?>">
		<p class="ma-campaigns-label ma-eyebrow"><?php echo esc_html( get_theme_mod( 'moda_atelier_campaign_label', __( 'Sezonun öne çıkanları', 'moda-atelier' ) ) ); ?></p>
		<?php foreach ( array( 'campaign_one', 'campaign_two' ) as $campaign_key ) : ?>
			<?php
			$campaign_image_id  = (int) get_theme_mod( 'moda_atelier_' . $campaign_key . '_image', 0 );
			$campaign_image_url = $campaign_image_id ? wp_get_attachment_image_url( $campaign_image_id, 'large' ) : '';
			$campaign_url       = get_theme_mod( 'moda_atelier_' . $campaign_key . '_url', '' );
			$campaign_url       = $campaign_url ? $campaign_url : $shop_url;
			$campaign_headline  = get_theme_mod(
				'moda_atelier_' . $campaign_key . '_headline',
				'campaign_one' === $campaign_key
					? __( 'Şehir stiline yeni bir yorum.', 'moda-atelier' )
					: __( 'Günün her anına hazır.', 'moda-atelier' )
			);
			$campaign_copy = get_theme_mod(
				'moda_atelier_' . $campaign_key . '_copy',
				'campaign_one' === $campaign_key
					? __( 'Erkek koleksiyonundaki yeni parçaları keşfet.', 'moda-atelier' )
					: __( 'Günlük stilini tamamlayan seçili parçalar.', 'moda-atelier' )
			);
			?>
			<a class="ma-campaign-card<?php echo $campaign_image_url ? ' has-image' : ''; ?>" href="<?php echo esc_url( $campaign_url ); ?>"<?php echo $campaign_image_url ? ' style="--ma-campaign-image: url(&quot;' . esc_url( $campaign_image_url ) . '&quot;)"' : ''; ?>>
				<span class="ma-campaign-copy">
					<span class="ma-eyebrow"><?php echo esc_html( 'campaign_one' === $campaign_key ? __( 'Yeni sezon', 'moda-atelier' ) : __( 'Seçili parçalar', 'moda-atelier' ) ); ?></span>
					<strong><?php echo esc_html( $campaign_headline ); ?></strong>
					<span class="ma-campaign-description"><?php echo esc_html( $campaign_copy ); ?></span>
					<span class="ma-campaign-link"><?php echo esc_html( get_theme_mod( 'moda_atelier_campaign_link', __( 'Keşfet', 'moda-atelier' ) ) ); ?> <span aria-hidden="true">↗</span></span>
				</span>
			</a>
		<?php endforeach; ?>
	</section>
	<?php endif; ?>

	<?php if ( taxonomy_exists( 'product_cat' ) && get_theme_mod( 'moda_atelier_show_categories', true ) ) : ?>
		<section class="ma-section ma-category-section">
			<div class="ma-container">
				<div class="ma-section-heading">
					<div>
						<p class="ma-eyebrow"><?php echo esc_html( get_theme_mod( 'moda_atelier_category_eyebrow', __( 'Erkek koleksiyonu', 'moda-atelier' ) ) ); ?></p>
						<h2><?php echo esc_html( get_theme_mod( 'moda_atelier_category_title', __( 'Stiline göre keşfet', 'moda-atelier' ) ) ); ?></h2>
					</div>
					<a class="ma-text-link" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( get_theme_mod( 'moda_atelier_category_link', __( 'Tüm koleksiyon', 'moda-atelier' ) ) ); ?></a>
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
		<?php if ( get_theme_mod( 'moda_atelier_show_new_season', true ) ) : ?>
		<?php
		moda_atelier_product_section(
			'new_season',
			array(
				'orderby' => 'date',
				'order'   => 'DESC',
			),
			'new-season'
		);
		?>
		<?php endif; ?>

		<?php if ( get_theme_mod( 'moda_atelier_show_best_sellers', true ) ) : ?>
		<?php
		moda_atelier_product_section(
			'best_sellers',
			array(
				'orderby' => 'popularity',
				'order'   => 'DESC',
			),
			'best-sellers'
		);
		?>
		<?php endif; ?>

		<?php if ( get_theme_mod( 'moda_atelier_show_sale', true ) ) : ?>
		<?php
		moda_atelier_product_section(
			'sale',
			array(
				'on_sale' => true,
				'orderby' => 'date',
				'order'   => 'DESC',
			),
			'sale'
		);
		?>
		<?php endif; ?>

		<?php if ( get_theme_mod( 'moda_atelier_show_favorites', true ) ) : ?>
		<section class="ma-section ma-favorites-section" id="favorites">
			<div class="ma-container">
				<div class="ma-section-heading">
					<div>
						<p class="ma-eyebrow"><?php echo esc_html( get_theme_mod( 'moda_atelier_favorites_eyebrow', __( 'Sakladıkların', 'moda-atelier' ) ) ); ?></p>
						<h2><?php echo esc_html( get_theme_mod( 'moda_atelier_favorites_title', __( 'Favorilerin', 'moda-atelier' ) ) ); ?></h2>
					</div>
					<p class="ma-favorites-feedback" aria-live="polite"><?php echo esc_html( get_theme_mod( 'moda_atelier_favorites_feedback', __( 'Favoriler bu tarayıcıda saklanır.', 'moda-atelier' ) ) ); ?></p>
				</div>
				<div class="ma-product-grid ma-favorites-grid" aria-live="polite"></div>
				<p class="ma-favorites-empty"><?php echo esc_html( get_theme_mod( 'moda_atelier_favorites_empty', __( 'Favori ürün eklemek için ürün kartındaki kalp simgesine dokunun.', 'moda-atelier' ) ) ); ?></p>
			</div>
		</section>
		<?php endif; ?>
	<?php else : ?>
		<section class="ma-section">
			<div class="ma-container">
				<p class="ma-notice"><?php esc_html_e( 'Ürün koleksiyonları WooCommerce etkinleştirildiğinde görüntülenir.', 'moda-atelier' ); ?></p>
			</div>
		</section>
	<?php endif; ?>

	<?php do_action( 'moda_atelier_after_products' ); ?>

	<?php if ( get_theme_mod( 'moda_atelier_show_newsletter', true ) ) : ?>
	<section class="ma-newsletter">
		<div class="ma-container ma-newsletter-inner">
			<div>
				<p class="ma-eyebrow"><?php echo esc_html( get_theme_mod( 'moda_atelier_newsletter_eyebrow', __( 'Moda Chicago mektupları', 'moda-atelier' ) ) ); ?></p>
				<h2><?php echo esc_html( get_theme_mod( 'moda_atelier_newsletter_title', __( 'Şehrin enerjisi, her gün seninle.', 'moda-atelier' ) ) ); ?></h2>
				<p><?php echo esc_html( get_theme_mod( 'moda_atelier_newsletter_copy', __( 'Moda Chicago erkek koleksiyonundaki yeni parçaları ve seçkileri keşfet.', 'moda-atelier' ) ) ); ?></p>
				<?php if ( $privacy_url ) : ?>
					<a class="ma-privacy-link" href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Gizlilik politikası', 'moda-atelier' ); ?></a>
				<?php endif; ?>
			</div>
			<div class="ma-newsletter-action">
				<?php if ( $newsletter_url ) : ?>
					<a class="ma-button ma-button--light" href="<?php echo esc_url( $newsletter_url ); ?>"><?php echo esc_html( get_theme_mod( 'moda_atelier_newsletter_button', __( 'Bültene katıl', 'moda-atelier' ) ) ); ?></a>
					<span><?php esc_html_e( 'Bülten izin ve abonelik tercihleri sağlayıcı üzerinden yönetilir.', 'moda-atelier' ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>
</main>
<?php
get_footer();
