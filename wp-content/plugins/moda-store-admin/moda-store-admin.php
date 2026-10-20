<?php
/**
 * Plugin Name: Moda Store Admin
 * Description: Administrator-only Moda Chicago dashboard for native WooCommerce store management.
 * Version: 1.5.0
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
		__( 'Moda Chicago yönetim paneli', 'moda-store-admin' ),
		__( 'Moda Chicago', 'moda-store-admin' ),
		'manage_options',
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
		'1.5.0'
	);
}
add_action( 'admin_enqueue_scripts', 'moda_store_admin_enqueue_assets' );

add_action( 'admin_post_moda_store_admin_save_product', 'moda_store_admin_save_product' );
add_action( 'admin_post_moda_store_admin_delete_product', 'moda_store_admin_delete_product' );

/**
 * Get current store counts from WordPress and WooCommerce.
 *
 * @return array
 */
function moda_store_admin_get_overview() {
	$product_counts = wp_count_posts( 'product' );
	$order_page     = wc_get_orders( array(
		'limit'    => 1,
		'paginate' => true,
		'return'   => 'ids',
	) );

	return array(
		'published_products' => isset( $product_counts->publish ) ? (int) $product_counts->publish : 0,
		'draft_products'     => isset( $product_counts->draft ) ? (int) $product_counts->draft : 0,
		'orders'             => is_object( $order_page ) && isset( $order_page->total ) ? (int) $order_page->total : 0,
	);
}

/**
 * Return the custom product manager URL.
 *
 * @param array $args Additional query arguments.
 * @return string
 */
function moda_store_admin_get_url( $args = array() ) {
	return add_query_arg(
		array_merge( array( 'page' => 'moda-store-admin', 'view' => 'products' ), $args ),
		admin_url( 'admin.php' )
	);
}

/**
 * Redirect to the product manager with an operation notice.
 *
 * @param string $notice Notice key.
 * @param int    $product_id Optional product being edited.
 * @return void
 */
function moda_store_admin_redirect_product_manager( $notice, $product_id = 0 ) {
	$args = array( 'notice' => $notice );
	if ( $product_id ) {
		$args['product_id'] = absint( $product_id );
		$args['action']     = 'edit';
	}

	wp_safe_redirect( moda_store_admin_get_url( $args ) );
	exit;
}

/**
 * Parse a comma-separated product attribute option list.
 *
 * @param string $field Form field name.
 * @return array
 */
function moda_store_admin_parse_attribute_options( $field ) {
	$value = isset( $_POST[ $field ] ) && is_string( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : '';
	$items = array_map( 'trim', explode( ',', sanitize_text_field( $value ) ) );
	$items = array_filter( $items, static function ( $item ) {
		return '' !== $item;
	} );

	$options = array();
	foreach ( $items as $item ) {
		$slug = sanitize_title( $item );
		if ( '' === $slug ) {
			return new WP_Error( 'invalid_product_attribute_option', $field );
		}
		if ( isset( $options[ $slug ] ) ) {
			continue;
		}
		if ( count( $options ) >= 20 ) {
			return new WP_Error( 'too_many_product_attribute_options', $field );
		}
		$options[ $slug ] = $item;
	}

	return array_values( $options );
}

/**
 * Read the values for a size or color attribute from a product.
 *
 * @param WC_Product $product Product being edited.
 * @param string     $wanted_slug Attribute slug without the pa_ prefix.
 * @return array
 */
function moda_store_admin_get_attribute_options( $product, $wanted_slug ) {
	foreach ( $product->get_attributes() as $attribute ) {
		if ( $attribute->is_taxonomy() || moda_store_admin_normalize_attribute_slug( $attribute->get_name() ) !== $wanted_slug ) {
			continue;
		}

		return array_map( 'strval', $attribute->get_options() );
	}

	return array();
}

/**
 * Check whether a variable product uses attributes outside the quick editor.
 *
 * @param WC_Product $product Product being checked.
 * @return bool
 */
function moda_store_admin_has_unsupported_variation_attributes( $product ) {
	foreach ( $product->get_attributes() as $attribute ) {
		if ( $attribute->get_variation() && ( $attribute->is_taxonomy() || ! in_array( moda_store_admin_normalize_attribute_slug( $attribute->get_name() ), array( 'beden', 'renk' ), true ) ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Keep per-variation pricing and inventory data in the native WooCommerce editor.
 *
 * @param WC_Product $product Product being checked.
 * @return bool
 */
function moda_store_admin_has_unsupported_variation_data( $product ) {
	$variation_ids = $product->get_children();
	if ( ! $variation_ids ) {
		return true;
	}

	$regular_price = null;
	$sale_price    = null;
	foreach ( $variation_ids as $variation_id ) {
		$variation = wc_get_product( $variation_id );
		if ( ! $variation instanceof WC_Product_Variation || $variation->get_manage_stock() || $variation->get_sku() || $variation->get_image_id() ) {
			return true;
		}

		$current_regular_price = $variation->get_regular_price();
		$current_sale_price    = $variation->get_sale_price();
		if ( '' === $current_regular_price || ! is_numeric( $current_regular_price ) ) {
			return true;
		}

		if ( null === $regular_price ) {
			$regular_price = (float) $current_regular_price;
			$sale_price    = '' === $current_sale_price ? '' : (float) $current_sale_price;
			continue;
		}

		if ( (float) $current_regular_price !== $regular_price || ( '' === $current_sale_price ? '' : (float) $current_sale_price ) !== $sale_price ) {
			return true;
		}
	}

	return false;
}

/**
 * Normalize custom and global WooCommerce attribute names.
 *
 * @param string $name Attribute name.
 * @return string
 */
function moda_store_admin_normalize_attribute_slug( $name ) {
	return sanitize_title( preg_replace( '/^pa_/', '', (string) $name ) );
}

/**
 * Synchronize the size/color variation combinations for a variable product.
 *
 * Stock is tracked on the parent product, so all size/color combinations draw
 * from one shared inventory quantity entered in the product form.
 *
 * @param int    $product_id Parent product ID.
 * @param array  $sizes Size labels.
 * @param array  $colors Color labels.
 * @param string $regular_price Regular price.
 * @param string $sale_price Sale price.
 * @return void
 */
function moda_store_admin_sync_product_variations( $product_id, $sizes, $colors, $regular_price, $sale_price ) {
	$attributes = array();
	if ( $sizes ) {
		$attributes['beden'] = $sizes;
	}
	if ( $colors ) {
		$attributes['renk'] = $colors;
	}

	$existing_variations = array();
	$matching_variations = array();
	$product             = wc_get_product( $product_id );
	foreach ( $product->get_children() as $variation_id ) {
		$variation = wc_get_product( $variation_id );
		if ( ! $variation instanceof WC_Product_Variation ) {
			continue;
		}

		$variation_attributes = array();
		foreach ( $variation->get_attributes() as $name => $value ) {
			$variation_attributes[ moda_store_admin_normalize_attribute_slug( $name ) ] = sanitize_title( $value );
		}
		$variation_key_parts = array();
		foreach ( array_keys( $attributes ) as $name ) {
			$variation_key_parts[] = isset( $variation_attributes[ $name ] ) ? $variation_attributes[ $name ] : '';
		}
		if ( count( $variation_attributes ) === count( $attributes ) && ! in_array( '', $variation_key_parts, true ) ) {
			$matching_variations[ implode( '|', $variation_key_parts ) ] = $variation;
		}
		$existing_variations[ $variation_id ] = $variation;
	}

	$active_variation_ids = array();
	$combinations = array( array() );
	foreach ( $attributes as $name => $options ) {
		$next_combinations = array();
		foreach ( $combinations as $combination ) {
			foreach ( $options as $option ) {
				$next = $combination;
				$next[ $name ] = $option;
				$next_combinations[] = $next;
			}
		}
		$combinations = $next_combinations;
	}

	foreach ( $combinations as $combination ) {
		$key = implode( '|', array_map( 'sanitize_title', array_values( $combination ) ) );
		$variation = isset( $matching_variations[ $key ] )
			? $matching_variations[ $key ]
			: new WC_Product_Variation();

		$variation->set_parent_id( $product_id );
		$variation->set_attributes( $combination );
		$variation->set_regular_price( $regular_price );
		$variation->set_sale_price( $sale_price );
		$variation->set_manage_stock( false );
		$variation->set_status( 'publish' );
		$variation->save();
		$active_variation_ids[] = $variation->get_id();
	}

	foreach ( $existing_variations as $variation_id => $variation ) {
		if ( ! in_array( $variation_id, $active_variation_ids, true ) ) {
			$variation->set_status( 'private' );
			$variation->save();
		}
	}
}

/**
 * Save a WooCommerce product submitted from the dashboard.
 *
 * @return void
 */
function moda_store_admin_save_product() {
	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_products' ) ) {
		wp_die( esc_html__( 'Bu işlemi yapma yetkiniz yok.', 'moda-store-admin' ), '', array( 'response' => 403 ) );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	check_admin_referer( 'moda_store_admin_save_product_' . $product_id );

	if ( $product_id && ! current_user_can( 'edit_post', $product_id ) ) {
		wp_die( esc_html__( 'Bu ürünü düzenleme yetkiniz yok.', 'moda-store-admin' ), '', array( 'response' => 403 ) );
	}

	$name          = isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '';
	$description   = isset( $_POST['product_description'] ) ? wp_kses_post( wp_unslash( $_POST['product_description'] ) ) : '';
	$short_desc    = isset( $_POST['product_short_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['product_short_description'] ) ) : '';
	$regular_price = isset( $_POST['regular_price'] ) ? wc_format_decimal( wp_unslash( $_POST['regular_price'] ) ) : '';
	$sale_price    = isset( $_POST['sale_price'] ) ? wc_format_decimal( wp_unslash( $_POST['sale_price'] ) ) : '';
	$sku           = isset( $_POST['sku'] ) ? wc_clean( wp_unslash( $_POST['sku'] ) ) : '';
	$status        = isset( $_POST['product_status'] ) && 'publish' === sanitize_key( wp_unslash( $_POST['product_status'] ) ) ? 'publish' : 'draft';

	if ( '' === $name || '' === $regular_price || ! is_numeric( $regular_price ) || ( '' !== $sale_price && ( ! is_numeric( $sale_price ) || (float) $sale_price > (float) $regular_price ) ) ) {
		moda_store_admin_redirect_product_manager( 'invalid', $product_id );
	}
	if ( 'publish' === $status && ! current_user_can( 'publish_products' ) ) {
		wp_die( esc_html__( 'Ürünü yayımlama yetkiniz yok.', 'moda-store-admin' ), '', array( 'response' => 403 ) );
	}

	$sizes = moda_store_admin_parse_attribute_options( 'product_sizes' );
	$colors = moda_store_admin_parse_attribute_options( 'product_colors' );
	if ( is_wp_error( $sizes ) || is_wp_error( $colors ) ) {
		moda_store_admin_redirect_product_manager( 'invalid_variation_options', $product_id );
	}
	$has_variations = ! empty( $sizes ) || ! empty( $colors );
	if ( ( $sizes && count( $sizes ) * max( 1, count( $colors ) ) > 60 ) || ( $colors && count( $colors ) * max( 1, count( $sizes ) ) > 60 ) ) {
		moda_store_admin_redirect_product_manager( 'too_many_variations', $product_id );
	}

	$existing_product = $product_id ? wc_get_product( $product_id ) : null;
	if ( $product_id && ( ! $existing_product || ( ! $existing_product->is_type( 'simple' ) && ! $existing_product->is_type( 'variable' ) ) ) ) {
		wp_die( esc_html__( 'Bu ürün türünü WooCommerce ürün düzenleyicisinde yönetin.', 'moda-store-admin' ), '', array( 'response' => 400 ) );
	}
	if ( $existing_product && $existing_product->is_type( 'variable' ) && ( moda_store_admin_has_unsupported_variation_attributes( $existing_product ) || moda_store_admin_has_unsupported_variation_data( $existing_product ) ) ) {
		wp_die( esc_html__( 'Bu üründe hızlı panelin desteklemediği varyasyon özellikleri veya stok/fiyat değerleri var. Tüm bilgileri korumak için WooCommerce ürün düzenleyicisini kullanın.', 'moda-store-admin' ), '', array( 'response' => 400 ) );
	}
	if ( $existing_product && $existing_product->is_type( 'variable' ) && ! $has_variations ) {
		moda_store_admin_redirect_product_manager( 'variation_options_required', $product_id );
	}

	if ( ! $existing_product ) {
		$product = $has_variations ? new WC_Product_Variable() : new WC_Product_Simple();
	} elseif ( $has_variations && ! $existing_product->is_type( 'variable' ) ) {
		$product = new WC_Product_Variable( $product_id );
	} elseif ( ! $has_variations && ! $existing_product->is_type( 'simple' ) ) {
		$product = new WC_Product_Simple( $product_id );
	} else {
		$product = $existing_product;
	}

	try {
		$product->set_name( $name );
		$product->set_status( $status );
		$product->set_description( $description );
		$product->set_short_description( $short_desc );
		$product->set_regular_price( $has_variations ? '' : $regular_price );
		$product->set_sale_price( $has_variations ? '' : $sale_price );
		$product->set_sku( $sku );

		$manage_stock = isset( $_POST['manage_stock'] );
		$product->set_manage_stock( $manage_stock );
		if ( $manage_stock ) {
			$stock_quantity = isset( $_POST['stock_quantity'] ) ? wp_unslash( $_POST['stock_quantity'] ) : '';
			if ( '' === $stock_quantity || ! is_numeric( $stock_quantity ) || (float) $stock_quantity < 0 ) {
				moda_store_admin_redirect_product_manager( 'invalid_stock', $product_id );
			}
			$stock_quantity = wc_stock_amount( $stock_quantity );
			$product->set_stock_quantity( $stock_quantity );
			$product->set_stock_status( (float) $stock_quantity > 0 ? 'instock' : 'outofstock' );
		}

		if ( $has_variations ) {
			$product_attributes = $product->get_attributes();
			foreach ( $product_attributes as $attribute_key => $attribute ) {
				if ( in_array( moda_store_admin_normalize_attribute_slug( $attribute->get_name() ), array( 'beden', 'renk' ), true ) ) {
					unset( $product_attributes[ $attribute_key ] );
				}
			}

			foreach ( array( 'beden' => array( 'Beden', $sizes ), 'renk' => array( 'Renk', $colors ) ) as $slug => $definition ) {
				if ( empty( $definition[1] ) ) {
					continue;
				}
				$attribute = new WC_Product_Attribute();
				$attribute->set_id( 0 );
				$attribute->set_name( $definition[0] );
				$attribute->set_options( $definition[1] );
				$attribute->set_position( count( $product_attributes ) );
				$attribute->set_visible( true );
				$attribute->set_variation( true );
				$product_attributes[ $slug ] = $attribute;
			}
			$product->set_attributes( $product_attributes );
		}

		$category_ids = isset( $_POST['product_categories'] ) && is_array( $_POST['product_categories'] )
			? array_values( array_filter( array_map( 'absint', wp_unslash( $_POST['product_categories'] ) ) ) )
			: array();
		$product->set_category_ids( $category_ids );
		$product_id = $product->save();

		if ( $has_variations ) {
			moda_store_admin_sync_product_variations( $product_id, $sizes, $colors, $regular_price, $sale_price );
		}

		if ( ! empty( $_FILES['product_image']['name'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$image_id = media_handle_upload( 'product_image', 0 );
			if ( is_wp_error( $image_id ) ) {
				moda_store_admin_redirect_product_manager( 'image_error', $product_id );
			}
			$product->set_image_id( $image_id );
			$product->save();
		}

		if ( ! empty( $_FILES['product_video']['name'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			$video_id = media_handle_upload( 'product_video', $product_id );
			if ( is_wp_error( $video_id ) ) {
				moda_store_admin_redirect_product_manager( 'video_error', $product_id );
			}

			$video_mime_type = get_post_mime_type( $video_id );
			if ( ! in_array( $video_mime_type, array( 'video/mp4', 'video/webm' ), true ) ) {
				wp_delete_attachment( $video_id, true );
				moda_store_admin_redirect_product_manager( 'invalid_video', $product_id );
			}
			update_post_meta( $product_id, '_moda_store_product_video_id', $video_id );
		} elseif ( isset( $_POST['remove_product_video'] ) ) {
			delete_post_meta( $product_id, '_moda_store_product_video_id' );
		}
	} catch ( Throwable $error ) {
		wc_get_logger()->error(
			'Product save failed: ' . $error->getMessage(),
			array(
				'source'      => 'moda-store-admin',
				'product_id'  => $product_id,
				'user_id'     => get_current_user_id(),
			)
		);
		moda_store_admin_redirect_product_manager( 'save_error', $product_id );
	}

	moda_store_admin_redirect_product_manager( 'saved' );
}

/**
 * Move a product to the trash from the custom product manager.
 *
 * @return void
 */
function moda_store_admin_delete_product() {
	$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
	if ( ! current_user_can( 'manage_options' ) || ! $product_id || ! current_user_can( 'delete_post', $product_id ) ) {
		wp_die( esc_html__( 'Bu ürünü silme yetkiniz yok.', 'moda-store-admin' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'moda_store_admin_delete_product_' . $product_id );

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		moda_store_admin_redirect_product_manager( 'not_found' );
	}
	$product->delete( false );
	moda_store_admin_redirect_product_manager( 'deleted' );
}

/**
 * Render notices for product manager actions.
 *
 * @return void
 */
function moda_store_admin_product_notices() {
	$notice = isset( $_GET['notice'] ) && is_scalar( $_GET['notice'] ) ? sanitize_key( wp_unslash( $_GET['notice'] ) ) : '';
	$messages = array(
		'saved'         => array( 'success', __( 'Ürün ve varsa beden/renk seçenekleri kaydedildi.', 'moda-store-admin' ) ),
		'deleted'       => array( 'success', __( 'Ürün çöp kutusuna taşındı.', 'moda-store-admin' ) ),
		'video_error'   => array( 'error', __( 'Ürün videosu yüklenemedi. Dosya boyutunu, sunucunun yükleme sınırını ve MP4/WebM biçimini kontrol edin.', 'moda-store-admin' ) ),
		'invalid_video' => array( 'error', __( 'Yalnızca geçerli MP4 veya WebM video yükleyebilirsiniz.', 'moda-store-admin' ) ),
		'invalid'       => array( 'error', __( 'Ürün adı ve geçerli normal fiyat zorunludur. İndirimli fiyat normal fiyattan yüksek olamaz.', 'moda-store-admin' ) ),
		'invalid_stock' => array( 'error', __( 'Stok miktarı sıfır veya daha büyük bir sayı olmalıdır.', 'moda-store-admin' ) ),
		'too_many_variations' => array( 'error', __( 'En fazla 60 beden/renk kombinasyonu oluşturabilirsiniz. Seçenek sayısını azaltın.', 'moda-store-admin' ) ),
		'invalid_variation_options' => array( 'error', __( 'Beden ve renk adları harf veya rakam içermeli; her alanda en fazla 20 farklı seçenek olabilir.', 'moda-store-admin' ) ),
		'variation_options_required' => array( 'error', __( 'Varyasyonlu üründe en az bir beden veya renk seçeneği bulunmalıdır.', 'moda-store-admin' ) ),
		'image_error'   => array( 'error', __( 'Ürün kaydedildi ancak görsel yüklenemedi. Dosyanın resim türünü ve boyutunu kontrol edip yeniden deneyin.', 'moda-store-admin' ) ),
		'save_error'    => array( 'error', __( 'Ürün kaydedilemedi. SKU değerinin başka bir üründe kullanılmadığını ve alanları kontrol edin.', 'moda-store-admin' ) ),
		'not_found'     => array( 'error', __( 'Ürün bulunamadı veya zaten silinmiş.', 'moda-store-admin' ) ),
	);

	if ( isset( $messages[ $notice ] ) ) {
		printf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $messages[ $notice ][0] ),
			esc_html( $messages[ $notice ][1] )
		);
	}
}

/**
 * Render the simple or variable product creation/edit form.
 *
 * @param WC_Product|null $product Product being edited, if any.
 * @return void
 */
function moda_store_admin_render_product_form( $product = null ) {
	$is_editing = $product instanceof WC_Product;
	$product_id = $is_editing ? $product->get_id() : 0;
	$categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
	$selected_categories = $is_editing ? $product->get_category_ids() : array();
	$image_id = $is_editing ? $product->get_image_id() : 0;
	$video_id = $is_editing ? absint( get_post_meta( $product_id, '_moda_store_product_video_id', true ) ) : 0;
	if ( $video_id && ! in_array( get_post_mime_type( $video_id ), array( 'video/mp4', 'video/webm' ), true ) ) {
		$video_id = 0;
	}
	$sizes = $is_editing ? moda_store_admin_get_attribute_options( $product, 'beden' ) : array();
	$colors = $is_editing ? moda_store_admin_get_attribute_options( $product, 'renk' ) : array();
	$regular_price = $is_editing && $product->is_type( 'variable' ) ? $product->get_variation_regular_price( 'min', true ) : ( $is_editing ? $product->get_regular_price() : '' );
	$sale_price = $is_editing && $product->is_type( 'variable' ) ? $product->get_variation_sale_price( 'min', true ) : ( $is_editing ? $product->get_sale_price() : '' );
	?>
	<section class="ma-store-admin-product-editor">
		<div class="ma-store-admin-section-heading">
			<div>
				<p class="ma-store-admin-eyebrow"><?php esc_html_e( 'Ürün kataloğu', 'moda-store-admin' ); ?></p>
				<h2><?php echo $is_editing ? esc_html__( 'Ürünü düzenle', 'moda-store-admin' ) : esc_html__( 'Yeni ürün oluştur', 'moda-store-admin' ); ?></h2>
			</div>
			<a class="button" href="<?php echo esc_url( moda_store_admin_get_url() ); ?>"><?php esc_html_e( 'Ürün listesine dön', 'moda-store-admin' ); ?></a>
		</div>
		<p class="description"><?php esc_html_e( 'Beden ve renk seçeneklerini virgülle ayırarak yazın. En az bir seçenek girildiğinde WooCommerce’de seçilebilir varyasyonlar oluşturulur; fiyat tüm kombinasyonlar için aynıdır.', 'moda-store-admin' ); ?></p>
		<form class="ma-store-admin-product-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<input type="hidden" name="action" value="moda_store_admin_save_product">
			<input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">
			<?php wp_nonce_field( 'moda_store_admin_save_product_' . $product_id ); ?>
			<div class="ma-store-admin-form-grid">
				<label class="ma-store-admin-field ma-store-admin-field-wide"><?php esc_html_e( 'Ürün adı', 'moda-store-admin' ); ?>
					<input type="text" name="product_name" required maxlength="200" value="<?php echo $is_editing ? esc_attr( $product->get_name() ) : ''; ?>">
				</label>
				<label class="ma-store-admin-field"><?php esc_html_e( 'Normal fiyat', 'moda-store-admin' ); ?>
					<input type="number" name="regular_price" min="0" step="0.01" required value="<?php echo esc_attr( $regular_price ); ?>">
				</label>
				<label class="ma-store-admin-field"><?php esc_html_e( 'İndirimli fiyat (isteğe bağlı)', 'moda-store-admin' ); ?>
					<input type="number" name="sale_price" min="0" step="0.01" value="<?php echo esc_attr( $sale_price ); ?>">
				</label>
				<label class="ma-store-admin-field"><?php esc_html_e( 'SKU / Stok kodu (isteğe bağlı)', 'moda-store-admin' ); ?>
					<input type="text" name="sku" maxlength="100" value="<?php echo $is_editing ? esc_attr( $product->get_sku() ) : ''; ?>">
				</label>
				<label class="ma-store-admin-field"><?php esc_html_e( 'Yayın durumu', 'moda-store-admin' ); ?>
					<select name="product_status">
						<option value="draft" <?php selected( ! $is_editing || 'publish' !== $product->get_status() ); ?>><?php esc_html_e( 'Taslak', 'moda-store-admin' ); ?></option>
						<option value="publish" <?php selected( $is_editing && 'publish' === $product->get_status() ); ?>><?php esc_html_e( 'Yayımla', 'moda-store-admin' ); ?></option>
					</select>
				</label>
				<label class="ma-store-admin-field ma-store-admin-field-wide"><?php esc_html_e( 'Kısa açıklama', 'moda-store-admin' ); ?>
					<textarea name="product_short_description" rows="3"><?php echo $is_editing ? esc_textarea( $product->get_short_description() ) : ''; ?></textarea>
				</label>
				<label class="ma-store-admin-field ma-store-admin-field-wide"><?php esc_html_e( 'Ürün açıklaması', 'moda-store-admin' ); ?>
					<textarea name="product_description" rows="6"><?php echo $is_editing ? esc_textarea( $product->get_description() ) : ''; ?></textarea>
				</label>
				<label class="ma-store-admin-field"><?php esc_html_e( 'Beden seçenekleri', 'moda-store-admin' ); ?>
					<input type="text" name="product_sizes" maxlength="500" value="<?php echo esc_attr( implode( ', ', $sizes ) ); ?>" placeholder="<?php esc_attr_e( 'Örn. S, M, L, XL', 'moda-store-admin' ); ?>">
					<span class="description"><?php esc_html_e( 'Virgülle ayırın. Ürün bedensizse boş bırakın.', 'moda-store-admin' ); ?></span>
				</label>
				<label class="ma-store-admin-field"><?php esc_html_e( 'Renk seçenekleri', 'moda-store-admin' ); ?>
					<input type="text" name="product_colors" maxlength="500" value="<?php echo esc_attr( implode( ', ', $colors ) ); ?>" placeholder="<?php esc_attr_e( 'Örn. Siyah, Beyaz, Kırmızı', 'moda-store-admin' ); ?>">
					<span class="description"><?php esc_html_e( 'Virgülle ayırın. Tek renkli ürünse boş bırakın.', 'moda-store-admin' ); ?></span>
				</label>
				<label class="ma-store-admin-field"><?php esc_html_e( 'Ürün görseli', 'moda-store-admin' ); ?>
					<?php if ( $image_id ) : ?><span class="ma-store-admin-current-image"><?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'thumbnail' ) ); ?></span><?php endif; ?>
					<input type="file" name="product_image" accept="image/jpeg,image/png,image/webp">
					<span class="description"><?php echo esc_html( sprintf( __( 'Yeni görsel seçmezsen mevcut görsel korunur. WordPress yükleme sınırı: %s.', 'moda-store-admin' ), size_format( wp_max_upload_size() ) ) ); ?></span>
				</label>
				<div class="ma-store-admin-field ma-store-admin-field-wide">
					<span><?php esc_html_e( 'Ürün videosu (isteğe bağlı)', 'moda-store-admin' ); ?></span>
					<?php if ( $video_id ) : ?>
						<video class="ma-store-admin-current-video" controls preload="metadata">
							<source src="<?php echo esc_url( wp_get_attachment_url( $video_id ) ); ?>" type="<?php echo esc_attr( get_post_mime_type( $video_id ) ); ?>">
						</video>
						<label class="ma-store-admin-video-remove"><input type="checkbox" name="remove_product_video" value="1"> <?php esc_html_e( 'Mevcut videoyu kaldır (yeni video yüklemezsen)', 'moda-store-admin' ); ?></label>
					<?php endif; ?>
					<input type="file" name="product_video" accept="video/mp4,video/webm">
					<span class="description"><?php echo esc_html( sprintf( __( 'MP4 veya WebM. Sunucunun en fazla yükleme boyutu: %s. Yeni video yüklemezsen mevcut video korunur.', 'moda-store-admin' ), size_format( wp_max_upload_size() ) ) ); ?></span>
				</div>
				<fieldset class="ma-store-admin-field ma-store-admin-categories">
					<legend><?php esc_html_e( 'Kategoriler', 'moda-store-admin' ); ?></legend>
					<?php if ( ! is_wp_error( $categories ) && $categories ) : ?>
						<?php foreach ( $categories as $category ) : ?>
							<label><input type="checkbox" name="product_categories[]" value="<?php echo esc_attr( $category->term_id ); ?>" <?php checked( in_array( (int) $category->term_id, $selected_categories, true ) ); ?>> <?php echo esc_html( $category->name ); ?></label>
						<?php endforeach; ?>
					<?php else : ?>
						<span class="description"><?php esc_html_e( 'Henüz kategori yok. Önce ürün kategorisi oluşturabilirsiniz.', 'moda-store-admin' ); ?></span>
					<?php endif; ?>
				</fieldset>
				<label class="ma-store-admin-stock-toggle"><input type="checkbox" name="manage_stock" value="1" <?php checked( $is_editing && $product->get_manage_stock() ); ?>> <?php esc_html_e( 'Bu ürün için stok miktarını takip et', 'moda-store-admin' ); ?><span class="description"><?php esc_html_e( 'Varyasyonlu ürünlerde stok tüm beden ve renkler için ortaktır.', 'moda-store-admin' ); ?></span></label>
				<label class="ma-store-admin-field"><?php esc_html_e( 'Stok miktarı', 'moda-store-admin' ); ?>
					<input type="number" name="stock_quantity" min="0" step="1" value="<?php echo $is_editing && $product->get_manage_stock() ? esc_attr( $product->get_stock_quantity() ) : '0'; ?>">
				</label>
			</div>
			<div class="ma-store-admin-form-actions">
				<button type="submit" class="button button-primary button-hero"><?php echo $is_editing ? esc_html__( 'Değişiklikleri kaydet', 'moda-store-admin' ) : esc_html__( 'Ürünü oluştur', 'moda-store-admin' ); ?></button>
				<?php if ( $is_editing ) : ?>
					<a class="button" href="<?php echo esc_url( get_edit_post_link( $product_id, 'url' ) ); ?>"><?php esc_html_e( 'WooCommerce’de ayrıntılı düzenle', 'moda-store-admin' ); ?></a>
				<?php endif; ?>
			</div>
		</form>
	</section>
	<?php
}

/**
 * Render searchable, paginated product list.
 *
 * @return void
 */
function moda_store_admin_render_product_list() {
	$search = isset( $_GET['product_search'] ) && is_scalar( $_GET['product_search'] ) ? sanitize_text_field( wp_unslash( $_GET['product_search'] ) ) : '';
	$page   = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
	$result = wc_get_products( array(
		'status'  => array( 'publish', 'draft', 'pending', 'private' ),
		'limit'   => 20,
		'page'    => $page,
		'paginate'=> true,
		'orderby' => 'date',
		'order'   => 'DESC',
		'return'  => 'objects',
		's'       => $search,
	) );
	$products = is_object( $result ) && isset( $result->products ) ? $result->products : array();
	$total_pages = is_object( $result ) && isset( $result->max_num_pages ) ? (int) $result->max_num_pages : 1;
	?>
	<section class="ma-store-admin-product-list">
		<div class="ma-store-admin-section-heading">
			<div>
				<p class="ma-store-admin-eyebrow"><?php esc_html_e( 'Kolay ürün yönetimi', 'moda-store-admin' ); ?></p>
				<h2><?php esc_html_e( 'Ürünler', 'moda-store-admin' ); ?></h2>
			</div>
			<a class="button button-primary" href="<?php echo esc_url( moda_store_admin_get_url( array( 'action' => 'new' ) ) ); ?>"><?php esc_html_e( '＋ Yeni ürün ekle', 'moda-store-admin' ); ?></a>
		</div>
		<form class="ma-store-admin-search" method="get">
			<input type="hidden" name="page" value="moda-store-admin">
			<input type="hidden" name="view" value="products">
			<label class="screen-reader-text" for="ma-store-product-search"><?php esc_html_e( 'Ürün ara', 'moda-store-admin' ); ?></label>
			<input id="ma-store-product-search" type="search" name="product_search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Ürün adına, açıklamaya veya SKU’ya göre ara', 'moda-store-admin' ); ?>">
			<button class="button" type="submit"><?php esc_html_e( 'Ara', 'moda-store-admin' ); ?></button>
		</form>
		<div class="ma-store-admin-table-wrap">
			<table class="widefat striped ma-store-admin-products-table">
				<thead><tr><th><?php esc_html_e( 'Ürün', 'moda-store-admin' ); ?></th><th><?php esc_html_e( 'Tür', 'moda-store-admin' ); ?></th><th><?php esc_html_e( 'Fiyat', 'moda-store-admin' ); ?></th><th><?php esc_html_e( 'Stok', 'moda-store-admin' ); ?></th><th><?php esc_html_e( 'Durum', 'moda-store-admin' ); ?></th><th><?php esc_html_e( 'İşlemler', 'moda-store-admin' ); ?></th></tr></thead>
				<tbody>
					<?php if ( $products ) : ?>
						<?php foreach ( $products as $product ) : ?>
							<?php
							$product_id = $product->get_id();
							$quick_editable = $product->is_type( 'simple' ) || ( $product->is_type( 'variable' ) && ! moda_store_admin_has_unsupported_variation_attributes( $product ) );
							$edit_url   = $quick_editable
								? moda_store_admin_get_url( array( 'action' => 'edit', 'product_id' => $product_id ) )
								: get_edit_post_link( $product_id, 'url' );
							?>
							<tr>
								<td><div class="ma-store-admin-product-name"><?php echo wp_kses_post( $product->get_image( array( 48, 48 ) ) ); ?><strong><?php echo esc_html( $product->get_name() ); ?></strong></div><small><?php echo esc_html( $product->get_sku() ? $product->get_sku() : sprintf( __( 'Ürün #%d', 'moda-store-admin' ), $product_id ) ); ?></small></td>
								<td><?php echo esc_html( $product->get_type() ); ?></td>
								<td><?php echo wp_kses_post( $product->get_price_html() ? $product->get_price_html() : '—' ); ?></td>
								<td><?php echo esc_html( $product->get_stock_status() === 'instock' ? ( $product->get_manage_stock() ? (string) $product->get_stock_quantity() : __( 'Stokta', 'moda-store-admin' ) ) : __( 'Stokta yok', 'moda-store-admin' ) ); ?></td>
								<td><?php echo esc_html( 'publish' === $product->get_status() ? __( 'Yayında', 'moda-store-admin' ) : __( 'Taslak', 'moda-store-admin' ) ); ?></td>
								<td class="ma-store-admin-row-actions"><a class="button button-small" href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $quick_editable ? __( 'Düzenle', 'moda-store-admin' ) : __( 'WooCommerce’de düzenle', 'moda-store-admin' ) ); ?></a>
									<?php if ( current_user_can( 'delete_post', $product_id ) ) : ?>
										<a class="button button-small ma-store-admin-delete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=moda_store_admin_delete_product&product_id=' . $product_id ), 'moda_store_admin_delete_product_' . $product_id ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Bu ürünü çöp kutusuna taşımak istiyor musunuz?', 'moda-store-admin' ) ); ?>');"><?php esc_html_e( 'Çöpe taşı', 'moda-store-admin' ); ?></a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="6"><?php esc_html_e( 'Ürün bulunamadı. İlk ürününüzü ekleyerek başlayın.', 'moda-store-admin' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php if ( $total_pages > 1 ) : ?>
			<nav class="ma-store-admin-pagination" aria-label="<?php esc_attr_e( 'Ürün sayfaları', 'moda-store-admin' ); ?>">
				<?php for ( $page_number = 1; $page_number <= $total_pages; $page_number++ ) : ?>
					<a class="button <?php echo $page_number === $page ? 'button-primary' : ''; ?>" href="<?php echo esc_url( moda_store_admin_get_url( array( 'paged' => $page_number, 'product_search' => $search ) ) ); ?>"><?php echo esc_html( (string) $page_number ); ?></a>
				<?php endfor; ?>
			</nav>
		<?php endif; ?>
	</section>
	<?php
}

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
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Bu yönetim paneli yalnızca site yöneticilerine açıktır.', 'moda-store-admin' ) );
	}
	?>
	<div class="wrap ma-store-admin">
		<h1><?php esc_html_e( 'Moda Chicago yönetim paneli', 'moda-store-admin' ); ?></h1>
		<p class="ma-store-admin-intro"><?php esc_html_e( 'Bu özel mağaza paneli yalnızca WordPress site yöneticilerine açıktır. WordPress yönetici hesabınızla giriş yapın; ayrı veya ortak bir panel parolası kullanmayın.', 'moda-store-admin' ); ?></p>

		<?php if ( ! class_exists( 'WooCommerce' ) ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Bu panel WooCommerce etkinleştirildiğinde kullanılabilir.', 'moda-store-admin' ); ?></p></div>
		<?php else : ?>
			<?php
			$overview     = moda_store_admin_get_overview();
			$theme_active = 'moda-atelier' === get_option( 'stylesheet' );
			?>
			<div class="ma-store-admin-overview" aria-label="<?php esc_attr_e( 'Mağaza özeti', 'moda-store-admin' ); ?>">
				<div class="ma-store-admin-stat">
					<span><?php esc_html_e( 'Yayındaki ürün', 'moda-store-admin' ); ?></span>
					<strong><?php echo esc_html( number_format_i18n( $overview['published_products'] ) ); ?></strong>
				</div>
				<div class="ma-store-admin-stat">
					<span><?php esc_html_e( 'Taslak ürün', 'moda-store-admin' ); ?></span>
					<strong><?php echo esc_html( number_format_i18n( $overview['draft_products'] ) ); ?></strong>
				</div>
				<div class="ma-store-admin-stat">
					<span><?php esc_html_e( 'Sipariş', 'moda-store-admin' ); ?></span>
					<strong><?php echo esc_html( number_format_i18n( $overview['orders'] ) ); ?></strong>
				</div>
			</div>

			<?php if ( ! $theme_active ) : ?>
				<div class="notice notice-warning inline">
					<p><?php esc_html_e( 'Moda Chicago teması henüz etkin değil. Görünüm > Temalar bölümünden Moda Chicago temasını etkinleştirin.', 'moda-store-admin' ); ?></p>
				</div>
			<?php endif; ?>
			<?php if ( 0 === $overview['published_products'] ) : ?>
				<div class="notice notice-info inline">
					<p><?php esc_html_e( 'Mağazada henüz yayında ürün yok. Önce bir ürün ve fiyat ekleyin; ödeme yöntemlerini ancak ürün, teslimat ve yasal bilgiler doğrulandıktan sonra etkinleştirin.', 'moda-store-admin' ); ?></p>
				</div>
			<?php endif; ?>

			<?php moda_store_admin_product_notices(); ?>

			<nav class="ma-store-admin-quick-links" aria-label="<?php esc_attr_e( 'Hızlı işlemler', 'moda-store-admin' ); ?>">
				<?php if ( current_user_can( 'edit_products' ) ) : ?>
					<a class="button button-primary" href="<?php echo esc_url( moda_store_admin_get_url( array( 'action' => 'new' ) ) ); ?>"><?php esc_html_e( '＋ Kolay ürün ekle', 'moda-store-admin' ); ?></a>
					<a class="button" href="<?php echo esc_url( moda_store_admin_get_url() ); ?>"><?php esc_html_e( 'Ürünleri yönet', 'moda-store-admin' ); ?></a>
				<?php endif; ?>
				<?php if ( current_user_can( 'edit_shop_orders' ) ) : ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-orders' ) ); ?>"><?php esc_html_e( 'Siparişler', 'moda-store-admin' ); ?></a>
				<?php endif; ?>
				<?php if ( current_user_can( 'manage_woocommerce' ) ) : ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings' ) ); ?>"><?php esc_html_e( 'Mağaza ayarları', 'moda-store-admin' ); ?></a>
				<?php endif; ?>
				<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Siteyi görüntüle', 'moda-store-admin' ); ?></a>
			</nav>

			<?php
			$product_action = isset( $_GET['action'] ) && is_scalar( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
			$product_id     = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
			if ( 'new' === $product_action && current_user_can( 'edit_products' ) ) {
				moda_store_admin_render_product_form();
			} elseif ( 'edit' === $product_action && $product_id && current_user_can( 'edit_post', $product_id ) ) {
				$editing_product = wc_get_product( $product_id );
				if ( $editing_product && ( $editing_product->is_type( 'simple' ) || ( $editing_product->is_type( 'variable' ) && ! moda_store_admin_has_unsupported_variation_attributes( $editing_product ) && ! moda_store_admin_has_unsupported_variation_data( $editing_product ) ) ) ) {
					moda_store_admin_render_product_form( $editing_product );
				} elseif ( $editing_product ) {
					?>
					<div class="notice notice-info inline"><p><?php esc_html_e( 'Bu ürünün varyasyon özellikleri veya fiyat/stok yapısı hızlı düzenleyicide desteklenmiyor. Tüm bilgileri korumak için WooCommerce’in ayrıntılı ürün düzenleyicisini kullanın.', 'moda-store-admin' ); ?> <a href="<?php echo esc_url( get_edit_post_link( $product_id, 'url' ) ); ?>"><?php esc_html_e( 'WooCommerce’de düzenle', 'moda-store-admin' ); ?></a></p></div>
					<?php
					moda_store_admin_render_product_list();
				} else {
					moda_store_admin_render_product_list();
				}
			} else {
				moda_store_admin_render_product_list();
			}
			?>

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
