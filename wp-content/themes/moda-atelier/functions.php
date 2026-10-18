<?php
/**
 * Theme setup and storefront helpers.
 *
 * @package Moda_Atelier
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configure theme supports and navigation locations.
 *
 * @return void
 */
function moda_atelier_setup() {
	load_theme_textdomain( 'moda-atelier', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 260,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 560,
		'single_image_width'    => 900,
		'product_grid'           => array(
			'default_rows'    => 3,
			'min_rows'        => 1,
			'max_rows'        => 8,
			'default_columns' => 4,
			'min_columns'     => 2,
			'max_columns'     => 4,
		),
	) );

	register_nav_menus( array(
		'primary' => __( 'Ana menü', 'moda-atelier' ),
		'footer'  => __( 'Alt menü', 'moda-atelier' ),
	) );
}
add_action( 'after_setup_theme', 'moda_atelier_setup' );

/**
 * Enqueue theme assets.
 *
 * @return void
 */
function moda_atelier_enqueue_assets() {
	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style( 'moda-atelier-style', get_stylesheet_uri(), array(), $theme_version );
	wp_enqueue_script(
		'moda-atelier-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		$theme_version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'moda_atelier_enqueue_assets' );

/**
 * Register editable homepage hero copy.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @return void
 */
function moda_atelier_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'moda_atelier_homepage', array(
		'title'    => __( 'Ana sayfa hero alanı', 'moda-atelier' ),
		'priority' => 35,
	) );

	$fields = array(
		'hero_eyebrow' => array(
			'label'   => __( 'Hero üst metni', 'moda-atelier' ),
			'default' => __( 'Yeni sezon', 'moda-atelier' ),
			'type'    => 'text',
		),
		'hero_title' => array(
			'label'   => __( 'Hero başlığı', 'moda-atelier' ),
			'default' => __( 'Giyilmek için tasarlandı. Uzun yıllar için üretildi.', 'moda-atelier' ),
			'type'    => 'text',
		),
		'hero_description' => array(
			'label'   => __( 'Hero açıklaması', 'moda-atelier' ),
			'default' => __( 'Özenle seçilmiş temel parçalar, nitelikli kumaşlar ve zamansız bir stil.', 'moda-atelier' ),
			'type'    => 'textarea',
		),
		'hero_button_label' => array(
			'label'   => __( 'Hero buton metni', 'moda-atelier' ),
			'default' => __( 'Koleksiyonu keşfet', 'moda-atelier' ),
			'type'    => 'text',
		),
	);

	foreach ( $fields as $key => $field ) {
		$wp_customize->add_setting( 'moda_atelier_' . $key, array(
			'default'           => $field['default'],
			'sanitize_callback' => 'textarea' === $field['type'] ? 'sanitize_textarea_field' : 'sanitize_text_field',
			'transport'         => 'refresh',
		) );

		$wp_customize->add_control( 'moda_atelier_' . $key, array(
			'label'   => $field['label'],
			'section' => 'moda_atelier_homepage',
			'type'    => $field['type'],
		) );
	}

	$wp_customize->add_setting( 'moda_atelier_hero_image', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'moda_atelier_hero_image',
			array(
				'label'     => __( 'Hero görseli', 'moda-atelier' ),
				'section'   => 'moda_atelier_homepage',
				'mime_type' => 'image',
			)
		)
	);

	$wp_customize->add_setting( 'moda_atelier_newsletter_url', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'moda_atelier_newsletter_url', array(
		'label'       => __( 'Bülten abonelik sayfası bağlantısı', 'moda-atelier' ),
		'description' => __( 'Gerçek abonelik formunuzun sayfa veya sağlayıcı bağlantısını girin. Bağlantı eklenene kadar abonelik butonu gösterilmez.', 'moda-atelier' ),
		'section'     => 'moda_atelier_homepage',
		'type'        => 'url',
	) );

	$campaigns = array(
		'campaign_one' => array(
			'title'       => __( 'Kampanya 1', 'moda-atelier' ),
			'image_label' => __( 'Kampanya 1 görseli', 'moda-atelier' ),
			'headline'    => __( 'Günlük stil, yeniden yorumlandı.', 'moda-atelier' ),
			'copy'        => __( 'Mevsimin öne çıkan parçalarını keşfedin.', 'moda-atelier' ),
		),
		'campaign_two' => array(
			'title'       => __( 'Kampanya 2', 'moda-atelier' ),
			'image_label' => __( 'Kampanya 2 görseli', 'moda-atelier' ),
			'headline'    => __( 'Gardırobun yeni favorileri.', 'moda-atelier' ),
			'copy'        => __( 'Özenle seçilmiş zamansız parçalar.', 'moda-atelier' ),
		),
	);

	foreach ( $campaigns as $key => $campaign ) {
		$wp_customize->add_setting( 'moda_atelier_' . $key . '_image', array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'moda_atelier_' . $key . '_image',
				array(
					'label'     => $campaign['image_label'],
					'section'   => 'moda_atelier_homepage',
					'mime_type' => 'image',
				)
			)
		);

		$wp_customize->add_setting( 'moda_atelier_' . $key . '_url', array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( 'moda_atelier_' . $key . '_url', array(
			'label'       => $campaign['title'] . ': ' . __( 'Hedef bağlantı', 'moda-atelier' ),
			'description' => __( 'Boş bırakılırsa mağaza sayfası kullanılır.', 'moda-atelier' ),
			'section'     => 'moda_atelier_homepage',
			'type'        => 'url',
		) );

		foreach ( array( 'headline', 'copy' ) as $field ) {
			$wp_customize->add_setting( 'moda_atelier_' . $key . '_' . $field, array(
				'default'           => $campaign[ $field ],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			) );
			$wp_customize->add_control( 'moda_atelier_' . $key . '_' . $field, array(
				'label'   => $campaign['title'] . ': ' . ( 'headline' === $field ? __( 'Başlık', 'moda-atelier' ) : __( 'Açıklama', 'moda-atelier' ) ),
				'section' => 'moda_atelier_homepage',
				'type'    => 'text',
			) );
		}
	}
}
add_action( 'customize_register', 'moda_atelier_customize_register' );

/**
 * Find the product attribute taxonomy used for size or color.
 *
 * @param string $kind Attribute kind: size or color.
 * @return string
 */
function moda_atelier_get_attribute_taxonomy( $kind ) {
	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return '';
	}

	$needles = 'size' === $kind
		? array( 'size', 'beden' )
		: array( 'color', 'colour', 'renk' );

	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		$slug  = strtolower( (string) $attribute->attribute_name );
		$label = function_exists( 'mb_strtolower' )
			? mb_strtolower( (string) $attribute->attribute_label, 'UTF-8' )
			: strtolower( (string) $attribute->attribute_label );

		foreach ( $needles as $needle ) {
			if ( false !== strpos( $slug, $needle ) || false !== strpos( $label, $needle ) ) {
				$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );

				if ( taxonomy_exists( $taxonomy ) ) {
					return $taxonomy;
				}
			}
		}
	}

	return '';
}

/**
 * Find a registered brand taxonomy supported by the store.
 *
 * @return string
 */
function moda_atelier_get_brand_taxonomy() {
	$taxonomies = get_object_taxonomies( 'product', 'objects' );

	foreach ( $taxonomies as $taxonomy => $object ) {
		$slug  = strtolower( $taxonomy );
		$label = function_exists( 'mb_strtolower' )
			? mb_strtolower( $object->labels->singular_name, 'UTF-8' )
			: strtolower( $object->labels->singular_name );

		if ( false !== strpos( $slug, 'brand' ) || false !== strpos( $slug, 'marka' ) || false !== strpos( $label, 'brand' ) || false !== strpos( $label, 'marka' ) ) {
			return $taxonomy;
		}
	}

	return '';
}

/**
 * Return a safe GET parameter.
 *
 * @param string $key Parameter key.
 * @return string
 */
function moda_atelier_get_filter_value( $key ) {
	if ( ! isset( $_GET[ $key ] ) || ! is_scalar( $_GET[ $key ] ) ) {
		return '';
	}

	return sanitize_text_field( wp_unslash( (string) $_GET[ $key ] ) );
}

/**
 * Get a taxonomy term for a filter only when it belongs to the expected taxonomy.
 *
 * @param string $key Parameter key.
 * @param string $taxonomy Taxonomy name.
 * @return WP_Term|null
 */
function moda_atelier_get_filter_term( $key, $taxonomy ) {
	$slug = moda_atelier_get_filter_value( $key );

	if ( ! $slug || ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
		return null;
	}

	$term = get_term_by( 'slug', sanitize_title( $slug ), $taxonomy );

	return $term instanceof WP_Term ? $term : null;
}

/**
 * Add archive filters before WooCommerce runs the main product query.
 *
 * @param array    $meta_query Existing product meta query.
 * @param WC_Query $wc_query WooCommerce query wrapper.
 * @return array
 */
function moda_atelier_filter_product_meta_query( $meta_query, $wc_query ) {
	if ( is_admin() ) {
		return $meta_query;
	}

	$stock_status = moda_atelier_get_filter_value( 'stock_status' );
	if ( in_array( $stock_status, array( 'instock', 'outofstock' ), true ) ) {
		$meta_query[] = array(
			'key'     => '_stock_status',
			'value'   => $stock_status,
			'compare' => '=',
		);
	}

	return $meta_query;
}
add_filter( 'woocommerce_product_query_meta_query', 'moda_atelier_filter_product_meta_query', 20, 2 );

/**
 * Add category, size, color and brand filters to WooCommerce product queries.
 *
 * @param array    $tax_query Existing product taxonomy query.
 * @param WC_Query $wc_query WooCommerce query wrapper.
 * @return array
 */
function moda_atelier_filter_product_tax_query( $tax_query, $wc_query ) {
	if ( is_admin() ) {
		return $tax_query;
	}

	$filters = array(
		array( 'key' => 'filter_category', 'taxonomy' => 'product_cat' ),
		array( 'key' => 'filter_size', 'taxonomy' => moda_atelier_get_attribute_taxonomy( 'size' ) ),
		array( 'key' => 'filter_color', 'taxonomy' => moda_atelier_get_attribute_taxonomy( 'color' ) ),
		array( 'key' => 'filter_brand', 'taxonomy' => moda_atelier_get_brand_taxonomy() ),
	);

	foreach ( $filters as $filter ) {
		$term = moda_atelier_get_filter_term( $filter['key'], $filter['taxonomy'] );
		if ( $term ) {
			$tax_query[] = array(
				'taxonomy' => $filter['taxonomy'],
				'field'    => 'term_id',
				'terms'    => array( (int) $term->term_id ),
			);
		}
	}

	if ( 'outofstock' === moda_atelier_get_filter_value( 'stock_status' ) && function_exists( 'wc_get_product_visibility_term_ids' ) ) {
		$visibility_terms = wc_get_product_visibility_term_ids();
		$outofstock_term  = isset( $visibility_terms['outofstock'] ) ? (int) $visibility_terms['outofstock'] : 0;

		if ( $outofstock_term ) {
			foreach ( $tax_query as $index => $clause ) {
				if ( ! is_array( $clause ) || empty( $clause['taxonomy'] ) || 'product_visibility' !== $clause['taxonomy'] || empty( $clause['terms'] ) ) {
					continue;
				}

				$tax_query[ $index ]['terms'] = array_values( array_filter( (array) $clause['terms'], function ( $term_id ) use ( $outofstock_term ) {
					return (int) $term_id !== $outofstock_term;
				} ) );
			}
		}
	}

	return $tax_query;
}
add_filter( 'woocommerce_product_query_tax_query', 'moda_atelier_filter_product_tax_query', 20, 2 );

/**
 * Render accessible, shareable WooCommerce archive filters.
 *
 * @return void
 */
function moda_atelier_render_archive_filters() {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return;
	}

	$size_taxonomy  = moda_atelier_get_attribute_taxonomy( 'size' );
	$color_taxonomy = moda_atelier_get_attribute_taxonomy( 'color' );
	$brand_taxonomy = moda_atelier_get_brand_taxonomy();
	$taxonomies     = array(
		'filter_category' => array(
			'label'    => __( 'Kategori', 'moda-atelier' ),
			'taxonomy' => 'product_cat',
		),
		'filter_size' => array(
			'label'    => __( 'Beden', 'moda-atelier' ),
			'taxonomy' => $size_taxonomy,
		),
		'filter_color' => array(
			'label'    => __( 'Renk', 'moda-atelier' ),
			'taxonomy' => $color_taxonomy,
		),
		'filter_brand' => array(
			'label'    => __( 'Marka', 'moda-atelier' ),
			'taxonomy' => $brand_taxonomy,
		),
	);
	$has_filters = false;
	foreach ( $taxonomies as $filter ) {
		if ( $filter['taxonomy'] && taxonomy_exists( $filter['taxonomy'] ) ) {
			$has_filters = true;
			break;
		}
	}

	$action_url = function_exists( 'is_product_category' ) && is_product_category()
		? get_term_link( get_queried_object() )
		: moda_atelier_shop_url();
	if ( is_wp_error( $action_url ) ) {
		$action_url = moda_atelier_shop_url();
	}

	$minimum = moda_atelier_get_filter_value( 'min_price' );
	$maximum = moda_atelier_get_filter_value( 'max_price' );
	$invalid_range = '' !== $minimum && '' !== $maximum && is_numeric( $minimum ) && is_numeric( $maximum ) && (float) $minimum > (float) $maximum;
	$price_step   = number_format( pow( 10, -1 * wc_get_price_decimals() ), wc_get_price_decimals(), '.', '' );
	$has_active_filter = '' !== $minimum || '' !== $maximum || '' !== moda_atelier_get_filter_value( 'stock_status' );
	foreach ( array_keys( $taxonomies ) as $key ) {
		$has_active_filter = $has_active_filter || '' !== moda_atelier_get_filter_value( $key );
	}
	?>
	<div class="ma-archive-tools">
		<button class="ma-filter-toggle" type="button" aria-expanded="false" aria-controls="ma-product-filters">
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 6h16M7 12h10m-7 6h4"/><circle cx="8" cy="6" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="12" cy="18" r="1.5"/></svg>
			<?php esc_html_e( 'Filtrele', 'moda-atelier' ); ?>
		</button>
		<?php if ( $has_active_filter ) : ?>
			<a class="ma-clear-filters" href="<?php echo esc_url( $action_url ); ?>"><?php esc_html_e( 'Filtreleri temizle', 'moda-atelier' ); ?></a>
		<?php endif; ?>
	</div>
	<form class="ma-product-filters" id="ma-product-filters" method="get" action="<?php echo esc_url( $action_url ); ?>">
		<?php
		$ordering_options = wc_get_catalog_ordering_options();
		$current_orderby  = moda_atelier_get_filter_value( 'orderby' );
		if ( isset( $ordering_options[ $current_orderby ] ) ) :
			?>
			<input type="hidden" name="orderby" value="<?php echo esc_attr( $current_orderby ); ?>">
		<?php endif; ?>
		<div class="ma-filter-grid">
			<?php foreach ( $taxonomies as $key => $filter ) : ?>
				<?php if ( ! $filter['taxonomy'] || ! taxonomy_exists( $filter['taxonomy'] ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<?php
				$terms = get_terms( array(
					'taxonomy'   => $filter['taxonomy'],
					'hide_empty' => true,
					'number'     => 150,
					'orderby'    => 'name',
				) );
				if ( is_wp_error( $terms ) || ! $terms ) {
					continue;
				}
				?>
				<div class="ma-filter-field">
					<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $filter['label'] ); ?></label>
					<select id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>">
						<option value=""><?php echo esc_html( sprintf( __( 'Tüm %s', 'moda-atelier' ), $filter['label'] ) ); ?></option>
						<?php foreach ( $terms as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( moda_atelier_get_filter_value( $key ), $term->slug ); ?>>
								<?php echo esc_html( $term->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endforeach; ?>

			<div class="ma-filter-field ma-price-filter">
				<label for="ma-min-price"><?php esc_html_e( 'En düşük fiyat', 'moda-atelier' ); ?></label>
				<input id="ma-min-price" type="number" name="min_price" min="0" step="<?php echo esc_attr( $price_step ); ?>" value="<?php echo esc_attr( $minimum ); ?>" inputmode="decimal">
			</div>
			<div class="ma-filter-field ma-price-filter">
				<label for="ma-max-price"><?php esc_html_e( 'En yüksek fiyat', 'moda-atelier' ); ?></label>
				<input id="ma-max-price" type="number" name="max_price" min="0" step="<?php echo esc_attr( $price_step ); ?>" value="<?php echo esc_attr( $maximum ); ?>" inputmode="decimal">
			</div>
			<div class="ma-filter-field">
				<label for="ma-stock-status"><?php esc_html_e( 'Stok durumu', 'moda-atelier' ); ?></label>
				<select id="ma-stock-status" name="stock_status">
					<option value=""><?php esc_html_e( 'Tüm ürünler', 'moda-atelier' ); ?></option>
					<option value="instock" <?php selected( moda_atelier_get_filter_value( 'stock_status' ), 'instock' ); ?>><?php esc_html_e( 'Stokta olanlar', 'moda-atelier' ); ?></option>
					<option value="outofstock" <?php selected( moda_atelier_get_filter_value( 'stock_status' ), 'outofstock' ); ?>><?php esc_html_e( 'Stokta olmayanlar', 'moda-atelier' ); ?></option>
				</select>
			</div>
		</div>
		<?php if ( $invalid_range ) : ?>
			<p class="ma-filter-error" role="alert"><?php esc_html_e( 'En düşük fiyat, en yüksek fiyattan büyük olamaz.', 'moda-atelier' ); ?></p>
		<?php endif; ?>
		<div class="ma-filter-actions">
			<button class="ma-button" type="submit"><?php esc_html_e( 'Ürünleri göster', 'moda-atelier' ); ?></button>
			<?php if ( $has_active_filter ) : ?>
				<a class="ma-clear-filters" href="<?php echo esc_url( $action_url ); ?>"><?php esc_html_e( 'Temizle', 'moda-atelier' ); ?></a>
			<?php endif; ?>
		</div>
	</form>
	<?php
}
add_action( 'woocommerce_before_shop_loop', 'moda_atelier_render_archive_filters', 5 );
add_action( 'woocommerce_no_products_found', 'moda_atelier_render_archive_filters', 1 );

/**
 * Return a safe shop URL, falling back to the site root before WooCommerce setup.
 *
 * @return string
 */
function moda_atelier_shop_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$shop_url = wc_get_page_permalink( 'shop' );

		if ( $shop_url ) {
			return $shop_url;
		}
	}

	return home_url( '/' );
}

/**
 * Render a product card using WooCommerce product data.
 *
 * @param WC_Product $product Product to render.
 * @return void
 */
function moda_atelier_render_product_card( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$product_id = $product->get_id();
	$terms      = get_the_terms( $product_id, 'product_cat' );
	$category   = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
	$image      = $product->get_image( 'woocommerce_thumbnail', array(
		'loading' => 'lazy',
		'alt'     => $product->get_name(),
	) );
	?>
	<article class="ma-product-card" data-product-id="<?php echo esc_attr( $product_id ); ?>">
		<a class="ma-product-image<?php echo $product->get_image_id() ? '' : ' ma-product-image--empty'; ?>" href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
			<?php echo wp_kses_post( $image ); ?>
		</a>
		<div class="ma-product-info">
			<?php if ( $category ) : ?>
				<p class="ma-product-category"><?php echo esc_html( $category ); ?></p>
			<?php endif; ?>
			<h3 class="ma-product-title"><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
			<div class="ma-product-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
			<div class="ma-product-card-actions">
				<a class="ma-product-action" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php esc_html_e( 'Ürünü incele', 'moda-atelier' ); ?></a>
				<button class="ma-favorite-toggle" type="button" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-label-add="<?php echo esc_attr( sprintf( __( '%s ürününü favorilere ekle', 'moda-atelier' ), $product->get_name() ) ); ?>" data-label-remove="<?php echo esc_attr( sprintf( __( '%s ürününü favorilerden çıkar', 'moda-atelier' ), $product->get_name() ) ); ?>" aria-pressed="false" aria-label="<?php echo esc_attr( sprintf( __( '%s ürününü favorilere ekle', 'moda-atelier' ), $product->get_name() ) ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20.8 8.8c0 5.1-8.8 11-8.8 11s-8.8-5.9-8.8-11A4.8 4.8 0 0 1 12 6.1a4.8 4.8 0 0 1 8.8 2.7Z"/></svg>
				</button>
			</div>
		</div>
	</article>
	<?php
}

/**
 * Show useful links until a store owner assigns a navigation menu.
 *
 * @return void
 */
function moda_atelier_primary_menu_fallback() {
	?>
	<ul class="menu">
		<li class="menu-item-has-children">
			<a href="<?php echo esc_url( moda_atelier_shop_url() ); ?>"><?php esc_html_e( 'Koleksiyon', 'moda-atelier' ); ?></a>
			<?php if ( taxonomy_exists( 'product_cat' ) ) : ?>
				<?php
				$categories = get_terms( array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => true,
					'parent'     => 0,
					'exclude'    => array( (int) get_option( 'default_product_cat', 0 ) ),
					'number'     => 8,
				) );
				?>
				<?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
					<ul class="sub-menu">
						<?php foreach ( $categories as $category ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			<?php endif; ?>
		</li>
		<?php if ( function_exists( 'wc_get_products' ) ) : ?>
			<li><a href="<?php echo esc_url( home_url( '/#new-season' ) ); ?>"><?php esc_html_e( 'Yeni sezon', 'moda-atelier' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/#best-sellers' ) ); ?>"><?php esc_html_e( 'Çok satanlar', 'moda-atelier' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/#sale' ) ); ?>"><?php esc_html_e( 'İndirim', 'moda-atelier' ); ?></a></li>
		<?php endif; ?>
		<?php if ( taxonomy_exists( 'product_cat' ) ) : ?>
			<?php
			$shop_page = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';
			?>
			<?php if ( $shop_page ) : ?>
				<li><a href="<?php echo esc_url( $shop_page ); ?>"><?php esc_html_e( 'Tüm ürünler', 'moda-atelier' ); ?></a></li>
			<?php endif; ?>
		<?php endif; ?>
	</ul>
	<?php
}

/**
 * Render a WooCommerce-driven product section.
 *
 * @param string $title Section title.
 * @param array  $query Product query arguments.
 * @param string $anchor Section anchor.
 * @param string $link_label Link text.
 * @return void
 */
function moda_atelier_product_section( $title, $query, $anchor, $link_label ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return;
	}

	$products = wc_get_products( array_merge(
		array(
			'status'  => 'publish',
			'limit'   => 4,
			'return'  => 'objects',
		),
		$query
	) );
	?>
	<section class="ma-section" id="<?php echo esc_attr( $anchor ); ?>">
		<div class="ma-container">
			<div class="ma-section-heading">
				<div>
					<p class="ma-eyebrow"><?php esc_html_e( 'Sizin için seçtiklerimiz', 'moda-atelier' ); ?></p>
					<h2><?php echo esc_html( $title ); ?></h2>
				</div>
				<a class="ma-text-link" href="<?php echo esc_url( home_url( '/#' . $anchor ) ); ?>"><?php echo esc_html( $link_label ); ?></a>
			</div>
			<?php if ( $products ) : ?>
				<div class="ma-product-grid">
					<?php foreach ( $products as $product ) : ?>
						<?php moda_atelier_render_product_card( $product ); ?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="ma-empty-state"><?php esc_html_e( 'WooCommerce üzerinden ürün eklendiğinde bu koleksiyon burada görüntülenir.', 'moda-atelier' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
