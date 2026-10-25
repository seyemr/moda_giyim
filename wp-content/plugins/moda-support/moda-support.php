<?php
/**
 * Plugin Name: Moda Support
 * Description: Sıkça sorulan sorular sayfası ile görsel eklenebilen şikayet, istek ve talep formu.
 * Version: 1.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Text Domain: moda-support
 *
 * @package Moda_Support
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MODA_SUPPORT_VERSION', '1.1.0' );
define( 'MODA_SUPPORT_DIR', plugin_dir_path( __FILE__ ) );
define( 'MODA_SUPPORT_URL', plugin_dir_url( __FILE__ ) );
define( 'MODA_SUPPORT_MAX_FILES', 3 );
define( 'MODA_SUPPORT_MAX_BYTES', 5 * MB_IN_BYTES );

/**
 * Supported request types.
 *
 * @return array<string,string>
 */
function moda_support_types() {
	return array(
		'talep'    => __( 'Talep', 'moda-support' ),
		'sikayet'  => __( 'Şikayet', 'moda-support' ),
		'istek'    => __( 'İstek', 'moda-support' ),
		'oneri'    => __( 'Öneri', 'moda-support' ),
		'iade'     => __( 'İade / Değişim', 'moda-support' ),
		'kargo'    => __( 'Kargo / Teslimat', 'moda-support' ),
	);
}

/**
 * Ticket statuses.
 *
 * @return array<string,string>
 */
function moda_support_statuses() {
	return array(
		'yeni'       => __( 'Yeni', 'moda-support' ),
		'inceleniyor' => __( 'İnceleniyor', 'moda-support' ),
		'cozuldu'    => __( 'Çözüldü', 'moda-support' ),
	);
}

/**
 * Default FAQ content, grouped by topic.
 *
 * @return array<int,array{title:string,items:array<int,array{q:string,a:string}>}>
 */
function moda_support_faqs() {
	$refund   = get_page_by_path( 'refund_returns' );
	$shipping = get_page_by_path( 'teslimat-ve-kargo-bilgileri' );
	$tracking = get_page_by_path( 'siparis-takibi' );
	$privacy  = get_privacy_policy_url();

	$link = static function ( $page, $label ) {
		if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
			return '<a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( $label ) . '</a>';
		}
		return esc_html( $label );
	};

	$groups = array(
		array(
			'title' => __( 'Sipariş ve ödeme', 'moda-support' ),
			'items' => array(
				array(
					'q' => __( 'Nasıl sipariş verebilirim?', 'moda-support' ),
					'a' => '<p>' . esc_html__( 'Beğendiğiniz ürünün bedenini seçip sepete ekleyin, sepetinizden ödeme adımına geçin. Teslimat bilgilerinizi girip ödemeyi tamamladığınızda sipariş onayı e-posta adresinize gönderilir.', 'moda-support' ) . '</p>',
				),
				array(
					'q' => __( 'Hangi ödeme yöntemleriyle ödeme yapabilirim?', 'moda-support' ),
					'a' => '<p>' . esc_html__( 'Geçerli ödeme yöntemleri ödeme ekranında listelenir. Ödeme öncesinde sipariş özetinden ürünleri, kargo bedelini ve toplam tutarı kontrol edebilirsiniz.', 'moda-support' ) . '</p>',
				),
				array(
					'q' => __( 'Siparişimi vermeden önce sözleşmeyi görebilir miyim?', 'moda-support' ),
					'a' => '<p>' . esc_html__( 'Evet. Mesafeli Satış Sözleşmesi ve Gizlilik/KVKK metinleri ödeme adımında onayınıza sunulur ve sitenin alt bölümünden her zaman okunabilir.', 'moda-support' ) . '</p>',
				),
			),
		),
		array(
			'title' => __( 'Kargo ve teslimat', 'moda-support' ),
			'items' => array(
				array(
					'q' => __( 'Siparişim ne zaman kargoya verilir?', 'moda-support' ),
					'a' => '<p>' . esc_html__( 'Ödeme onaylandıktan sonra siparişiniz 1–3 iş günü içinde PTT Kargo’ya teslim edilir. Adresinize ulaşma süresi PTT’nin dağıtım planına göre değişebilir.', 'moda-support' ) . ' ' . $link( $shipping, __( 'Teslimat ve Kargo Bilgileri', 'moda-support' ) ) . '</p>',
				),
				array(
					'q' => __( 'Siparişimi nasıl takip ederim?', 'moda-support' ),
					'a' => '<p>' . esc_html__( 'Kargo takip bilgisi oluşturulduğunda sipariş numaranızla gönderinizi takip edebilirsiniz.', 'moda-support' ) . ' ' . $link( $tracking, __( 'Sipariş takibi sayfasını', 'moda-support' ) ) . ' ' . esc_html__( 'kullanabilirsiniz.', 'moda-support' ) . '</p>',
				),
				array(
					'q' => __( 'Paketim hasarlı geldi, ne yapmalıyım?', 'moda-support' ),
					'a' => '<p>' . esc_html__( 'Mümkünse teslim sırasında kargo görevlisine tutanak tutturun, ardından aşağıdaki destek formundan paketin fotoğraflarıyla birlikte bize yazın.', 'moda-support' ) . '</p>',
				),
			),
		),
		array(
			'title' => __( 'İade ve değişim', 'moda-support' ),
			'items' => array(
				array(
					'q' => __( 'Ürünü iade edebilir miyim?', 'moda-support' ),
					'a' => '<p>' . esc_html__( 'Yasal istisnalar dışında ürünü teslim aldığınız tarihten itibaren 14 gün içinde cayma hakkınızı kullanabilirsiniz. Ayrıntılar için', 'moda-support' ) . ' ' . $link( $refund, __( 'İptal ve İade Koşulları', 'moda-support' ) ) . ' ' . esc_html__( 'sayfasına bakın.', 'moda-support' ) . '</p>',
				),
				array(
					'q' => __( 'Yanlış veya kusurlu ürün gelirse ne olur?', 'moda-support' ),
					'a' => '<p>' . esc_html__( 'Ayıplı veya yanlış ürün durumunda tüketici mevzuatından doğan haklarınız saklıdır. Sipariş numaranız ve ürün fotoğraflarıyla destek formunu doldurmanız süreci hızlandırır.', 'moda-support' ) . '</p>',
				),
			),
		),
		array(
			'title' => __( 'Hesap ve gizlilik', 'moda-support' ),
			'items' => array(
				array(
					'q' => __( 'Üye olmadan sipariş verebilir miyim?', 'moda-support' ),
					'a' => '<p>' . esc_html__( 'Ödeme sayfasında misafir olarak devam edip etmeyeceğiniz mağaza ayarlarına bağlıdır. Hesap oluşturmanız siparişlerinizi tek yerden görmenizi kolaylaştırır.', 'moda-support' ) . '</p>',
				),
				array(
					'q' => __( 'Kişisel verilerim nasıl korunuyor?', 'moda-support' ),
					'a' => '<p>' . esc_html__( 'Verileriniz yürürlükteki mevzuata uygun işlenir.', 'moda-support' ) . ( $privacy ? ' <a href="' . esc_url( $privacy ) . '">' . esc_html__( 'Gizlilik ve KVKK aydınlatma metnini', 'moda-support' ) . '</a> ' . esc_html__( 'inceleyebilirsiniz.', 'moda-support' ) : '' ) . '</p>',
				),
			),
		),
	);

	return apply_filters( 'moda_support_faqs', $groups );
}

/**
 * Register the private support ticket post type.
 *
 * @return void
 */
function moda_support_register_post_type() {
	register_post_type(
		'moda_ticket',
		array(
			'labels'              => array(
				'name'          => __( 'Destek Talepleri', 'moda-support' ),
				'singular_name' => __( 'Destek Talebi', 'moda-support' ),
				'menu_name'     => __( 'Destek Talepleri', 'moda-support' ),
				'all_items'     => __( 'Tüm Talepler', 'moda-support' ),
				'edit_item'     => __( 'Talebi Görüntüle', 'moda-support' ),
				'search_items'  => __( 'Talep ara', 'moda-support' ),
				'not_found'     => __( 'Talep bulunamadı.', 'moda-support' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-sos',
			'menu_position'       => 56,
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'        => true,
		)
	);
}
add_action( 'init', 'moda_support_register_post_type' );

/**
 * Register the customer support-ticket account endpoint.
 *
 * @return void
 */
function moda_support_add_account_endpoint() {
	add_rewrite_endpoint( 'moda-support-tickets', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'moda_support_add_account_endpoint' );

/**
 * Flush rewrite rules once after installing the customer account endpoint.
 *
 * @return void
 */
function moda_support_upgrade_account_endpoint() {
	if ( get_option( 'moda_support_version' ) === MODA_SUPPORT_VERSION ) {
		return;
	}
	flush_rewrite_rules();
	update_option( 'moda_support_version', MODA_SUPPORT_VERSION );
}
add_action( 'admin_init', 'moda_support_upgrade_account_endpoint' );

/**
 * Add support requests to WooCommerce account navigation.
 *
 * @param array<string,string> $items Account menu items.
 * @return array<string,string>
 */
function moda_support_account_menu_item( $items ) {
	$logout = isset( $items['customer-logout'] ) ? $items['customer-logout'] : null;
	unset( $items['customer-logout'] );
	$items['moda-support-tickets'] = __( 'Destek taleplerim', 'moda-support' );
	if ( null !== $logout ) {
		$items['customer-logout'] = $logout;
	}
	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'moda_support_account_menu_item', 45 );

/**
 * Show only the current customer's support requests and store responses.
 *
 * @return void
 */
function moda_support_render_customer_tickets() {
	$tickets = get_posts(
		array(
			'post_type'      => 'moda_ticket',
			'post_status'    => 'publish',
			'author'         => get_current_user_id(),
			'posts_per_page' => 50,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
	echo '<h2>' . esc_html__( 'Destek taleplerim', 'moda-support' ) . '</h2>';
	if ( ! $tickets ) {
		echo '<p>' . esc_html__( 'Henüz oluşturulmuş bir destek talebiniz yok.', 'moda-support' ) . '</p>';
		return;
	}
	echo '<div class="moda-support-account-list">';
	foreach ( $tickets as $ticket ) {
		$status = (string) get_post_meta( $ticket->ID, '_moda_ticket_status', true );
		$statuses = moda_support_statuses();
		$reply = (string) get_post_meta( $ticket->ID, '_moda_ticket_reply', true );
		echo '<article><h3>' . esc_html( (string) get_post_meta( $ticket->ID, '_moda_ticket_reference', true ) ) . ' · ' . esc_html( $ticket->post_title ) . '</h3>';
		echo '<p><strong>' . esc_html__( 'Durum:', 'moda-support' ) . '</strong> ' . esc_html( $statuses[ $status ] ?? $status ) . ' · ' . esc_html( get_the_date( '', $ticket ) ) . '</p>';
		echo '<details><summary>' . esc_html__( 'Talep metnini görüntüle', 'moda-support' ) . '</summary><p>' . nl2br( esc_html( $ticket->post_content ) ) . '</p></details>';
		if ( $reply ) {
			echo '<div class="moda-support-account-reply"><strong>' . esc_html__( 'Mağaza yanıtı', 'moda-support' ) . '</strong><p>' . nl2br( esc_html( $reply ) ) . '</p></div>';
		}
		echo '</article>';
	}
	echo '</div>';
}
add_action( 'woocommerce_account_moda-support-tickets_endpoint', 'moda_support_render_customer_tickets' );

/**
 * Create the FAQ and support pages when missing.
 *
 * @return void
 */
function moda_support_create_pages() {
	$pages = array(
		'sikca-sorulan-sorular' => array( __( 'Sıkça Sorulan Sorular', 'moda-support' ), '[moda_faq]' ),
		'destek-talebi'         => array( __( 'Destek ve Talep Formu', 'moda-support' ), '[moda_support_form]' ),
	);

	foreach ( $pages as $slug => $page ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => $slug,
				'post_title'   => $page[0],
				'post_content' => $page[1],
			)
		);
	}
}

/**
 * Plugin activation.
 *
 * @return void
 */
function moda_support_activate() {
	moda_support_register_post_type();
	moda_support_add_account_endpoint();
	moda_support_create_pages();
	flush_rewrite_rules();
	update_option( 'moda_support_version', MODA_SUPPORT_VERSION );
}
register_activation_hook( __FILE__, 'moda_support_activate' );

/**
 * Enqueue assets only when a support shortcode is on the page.
 *
 * @return void
 */
function moda_support_enqueue() {
	$post = get_post();
	$has_shortcode_content = $post instanceof WP_Post && ( has_shortcode( $post->post_content, 'moda_faq' ) || has_shortcode( $post->post_content, 'moda_support_form' ) );
	$account_page = function_exists( 'is_account_page' ) && is_account_page();
	if ( ! $has_shortcode_content && ! $account_page ) {
		return;
	}

	wp_enqueue_style( 'moda-support', MODA_SUPPORT_URL . 'assets/support.css', array(), MODA_SUPPORT_VERSION );
	if ( ! $has_shortcode_content ) {
		return;
	}
	wp_enqueue_script( 'moda-support', MODA_SUPPORT_URL . 'assets/support.js', array(), MODA_SUPPORT_VERSION, true );
	wp_localize_script(
		'moda-support',
		'modaSupport',
		array(
			'maxFiles' => MODA_SUPPORT_MAX_FILES,
			'maxBytes' => MODA_SUPPORT_MAX_BYTES,
			'i18n'     => array(
				'tooMany'  => __( 'En fazla 3 görsel ekleyebilirsiniz.', 'moda-support' ),
				'tooLarge' => __( 'Her görsel en fazla 5 MB olabilir.', 'moda-support' ),
				'badType'  => __( 'Yalnızca JPG, PNG veya WebP görsel ekleyebilirsiniz.', 'moda-support' ),
				'remove'   => __( 'Kaldır', 'moda-support' ),
				'sending'  => __( 'Gönderiliyor…', 'moda-support' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'moda_support_enqueue' );

/**
 * Render the FAQ accordion.
 *
 * @return string
 */
function moda_support_faq_shortcode() {
	$groups = moda_support_faqs();
	$schema = array();
	$form   = get_page_by_path( 'destek-talebi' );

	ob_start();
	echo '<div class="moda-faq">';
	echo '<label class="moda-faq-search"><span class="screen-reader-text">' . esc_html__( 'Sorularda ara', 'moda-support' ) . '</span><input type="search" data-moda-faq-search placeholder="' . esc_attr__( 'Sorunuzu arayın…', 'moda-support' ) . '"></label>';
	foreach ( $groups as $group ) {
		echo '<section class="moda-faq-group"><h2>' . esc_html( $group['title'] ) . '</h2>';
		foreach ( $group['items'] as $item ) {
			echo '<details class="moda-faq-item"><summary>' . esc_html( $item['q'] ) . '</summary><div class="moda-faq-answer">' . wp_kses_post( $item['a'] ) . '</div></details>';
			$schema[] = array(
				'@type'          => 'Question',
				'name'           => $item['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_strip_all_tags( $item['a'] ),
				),
			);
		}
		echo '</section>';
	}
	echo '<p class="moda-faq-empty" data-moda-faq-empty hidden>' . esc_html__( 'Aramanızla eşleşen soru bulunamadı.', 'moda-support' ) . '</p>';
	if ( $form instanceof WP_Post && 'publish' === $form->post_status ) {
		echo '<aside class="moda-faq-cta"><div><strong>' . esc_html__( 'Aradığınızı bulamadınız mı?', 'moda-support' ) . '</strong><span>' . esc_html__( 'Şikayet, istek ve taleplerinizi bize iletin.', 'moda-support' ) . '</span></div><a href="' . esc_url( get_permalink( $form ) ) . '">' . esc_html__( 'Destek talebi oluştur', 'moda-support' ) . '</a></aside>';
	}
	echo '</div>';
	echo '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $schema,
		),
		JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
	) . '</script>';

	return (string) ob_get_clean();
}
add_shortcode( 'moda_faq', 'moda_support_faq_shortcode' );

/**
 * Return a submission result code from the URL.
 *
 * @return string
 */
function moda_support_result() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only status code.
	return isset( $_GET['moda_support'] ) ? sanitize_key( wp_unslash( $_GET['moda_support'] ) ) : '';
}

/**
 * Render the support request form.
 *
 * @return string
 */
function moda_support_form_shortcode() {
	$result = moda_support_result();
	$types  = moda_support_types();
	$user   = wp_get_current_user();
	$old    = get_transient( 'moda_support_old_' . moda_support_visitor_key() );
	$old    = is_array( $old ) ? $old : array();
	$val    = static function ( $key, $fallback = '' ) use ( $old ) {
		return isset( $old[ $key ] ) ? $old[ $key ] : $fallback;
	};

	ob_start();
	echo '<div class="moda-support">';

	if ( 'ok' === $result ) {
		$ref = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="moda-support-notice is-success" role="status"><strong>' . esc_html__( 'Talebiniz alındı.', 'moda-support' ) . '</strong><span>' . esc_html__( 'En kısa sürede e-posta veya telefonla dönüş yapacağız.', 'moda-support' ) . ( $ref ? ' ' . esc_html__( 'Talep numaranız:', 'moda-support' ) . ' <b>' . esc_html( $ref ) . '</b>' : '' ) . '</span></div>';
	} elseif ( $result ) {
		$messages = array(
			'invalid' => __( 'Lütfen zorunlu alanları eksiksiz ve doğru doldurun.', 'moda-support' ),
			'consent' => __( 'Devam etmek için KVKK aydınlatma metnini onaylamanız gerekir.', 'moda-support' ),
			'file'    => __( 'Görseller yüklenemedi. JPG, PNG veya WebP biçiminde, en fazla 3 adet ve her biri en fazla 5 MB olmalıdır.', 'moda-support' ),
			'limit'   => __( 'Kısa sürede çok fazla talep gönderildi. Lütfen daha sonra tekrar deneyin.', 'moda-support' ),
			'error'   => __( 'Talebiniz kaydedilemedi. Lütfen tekrar deneyin veya bize telefonla ulaşın.', 'moda-support' ),
		);
		if ( isset( $messages[ $result ] ) ) {
			echo '<div class="moda-support-notice is-error" role="alert">' . esc_html( $messages[ $result ] ) . '</div>';
		}
	}

	if ( 'ok' !== $result ) {
		$privacy = get_privacy_policy_url();
		echo '<form class="moda-support-form" method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" novalidate>';
		echo '<input type="hidden" name="action" value="moda_support_submit">';
		wp_nonce_field( 'moda_support_submit', 'moda_support_nonce' );
		echo '<input type="hidden" name="moda_support_time" value="' . esc_attr( (string) time() ) . '">';
		echo '<p class="moda-support-hp" aria-hidden="true"><label>Web sitesi<input type="text" name="moda_support_website" tabindex="-1" autocomplete="off"></label></p>';

		echo '<fieldset class="moda-support-types"><legend>' . esc_html__( 'Konu', 'moda-support' ) . '</legend>';
		$current = $val( 'type', 'talep' );
		foreach ( $types as $key => $label ) {
			echo '<label><input type="radio" name="type" value="' . esc_attr( $key ) . '"' . checked( $current, $key, false ) . '><span>' . esc_html( $label ) . '</span></label>';
		}
		echo '</fieldset>';

		echo '<div class="moda-support-grid">';
		echo '<label>' . esc_html__( 'Ad soyad', 'moda-support' ) . ' <abbr title="' . esc_attr__( 'Zorunlu', 'moda-support' ) . '">*</abbr><input type="text" name="name" required maxlength="100" autocomplete="name" value="' . esc_attr( $val( 'name', $user->exists() ? $user->display_name : '' ) ) . '"></label>';
		echo '<label>' . esc_html__( 'E-posta', 'moda-support' ) . ' <abbr title="' . esc_attr__( 'Zorunlu', 'moda-support' ) . '">*</abbr><input type="email" name="email" required maxlength="150" autocomplete="email" value="' . esc_attr( $val( 'email', $user->exists() ? $user->user_email : '' ) ) . '"></label>';
		echo '<label>' . esc_html__( 'Telefon', 'moda-support' ) . '<input type="tel" name="phone" maxlength="30" autocomplete="tel" inputmode="tel" value="' . esc_attr( $val( 'phone' ) ) . '"></label>';
		echo '<label>' . esc_html__( 'Sipariş numarası', 'moda-support' ) . '<input type="text" name="order" maxlength="40" placeholder="' . esc_attr__( 'Varsa', 'moda-support' ) . '" value="' . esc_attr( $val( 'order' ) ) . '"></label>';
		echo '</div>';

		echo '<label class="moda-support-wide">' . esc_html__( 'Başlık', 'moda-support' ) . ' <abbr title="' . esc_attr__( 'Zorunlu', 'moda-support' ) . '">*</abbr><input type="text" name="subject" required maxlength="140" value="' . esc_attr( $val( 'subject' ) ) . '"></label>';
		echo '<label class="moda-support-wide">' . esc_html__( 'Mesajınız', 'moda-support' ) . ' <abbr title="' . esc_attr__( 'Zorunlu', 'moda-support' ) . '">*</abbr><textarea name="message" required rows="6" minlength="10" maxlength="3000">' . esc_textarea( $val( 'message' ) ) . '</textarea></label>';

		echo '<div class="moda-support-upload"><span class="moda-support-upload-title">' . esc_html__( 'Görsel ekle', 'moda-support' ) . ' <small>' . esc_html__( '(isteğe bağlı · en fazla 3 adet · JPG, PNG, WebP · 5 MB)', 'moda-support' ) . '</small></span>';
		echo '<label class="moda-support-drop"><input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple data-moda-files><span>' . esc_html__( 'Fotoğraf seçmek için dokunun', 'moda-support' ) . '</span></label>';
		echo '<ul class="moda-support-previews" data-moda-previews></ul><p class="moda-support-file-error" data-moda-file-error role="alert" hidden></p></div>';

		echo '<label class="moda-support-consent"><input type="checkbox" name="consent" value="1" required><span>' . ( $privacy ? '<a href="' . esc_url( $privacy ) . '" target="_blank" rel="noopener">' . esc_html__( 'KVKK Aydınlatma Metni', 'moda-support' ) . '</a>’ni ' : esc_html__( 'KVKK Aydınlatma Metni’ni ', 'moda-support' ) ) . esc_html__( 'okudum; bilgilerimin talebim kapsamında işlenmesini kabul ediyorum.', 'moda-support' ) . '</span></label>';
		echo '<button type="submit" class="moda-support-submit">' . esc_html__( 'Talebi gönder', 'moda-support' ) . '</button>';
		echo '</form>';
	}

	echo '</div>';
	return (string) ob_get_clean();
}
add_shortcode( 'moda_support_form', 'moda_support_form_shortcode' );

/**
 * Stable per-visitor key for rate limiting and form repopulation.
 *
 * @return string
 */
function moda_support_visitor_key() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return md5( $ip . wp_salt() );
}

/**
 * Redirect back to the form with a status code.
 *
 * @param string               $code   Result code.
 * @param array<string,string> $extra  Extra query args.
 * @return never
 */
function moda_support_redirect( $code, $extra = array() ) {
	$back = wp_get_referer();
	if ( ! $back ) {
		$page = get_page_by_path( 'destek-talebi' );
		$back = $page instanceof WP_Post ? get_permalink( $page ) : home_url( '/' );
	}
	$back = remove_query_arg( array( 'moda_support', 'ref' ), $back );
	wp_safe_redirect( add_query_arg( array_merge( array( 'moda_support' => $code ), $extra ), $back ) . '#talep-formu' );
	exit;
}

/**
 * Collect valid uploaded images.
 *
 * @return array<int,array<string,string>>|WP_Error
 */
function moda_support_collect_images() {
	$images = array();
	if ( empty( $_FILES['images'] ) || ! is_array( $_FILES['images']['name'] ) ) {
		return $images;
	}

	$allowed = array(
		'jpg|jpeg' => 'image/jpeg',
		'png'      => 'image/png',
		'webp'     => 'image/webp',
	);
	$count   = count( $_FILES['images']['name'] );
	$files   = array();
	for ( $i = 0; $i < $count; $i++ ) {
		if ( UPLOAD_ERR_NO_FILE === (int) $_FILES['images']['error'][ $i ] ) {
			continue;
		}
		$files[] = array(
			'name'     => $_FILES['images']['name'][ $i ],
			'type'     => $_FILES['images']['type'][ $i ],
			'tmp_name' => $_FILES['images']['tmp_name'][ $i ],
			'error'    => $_FILES['images']['error'][ $i ],
			'size'     => $_FILES['images']['size'][ $i ],
		);
	}

	if ( count( $files ) > MODA_SUPPORT_MAX_FILES ) {
		return new WP_Error( 'file' );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';

	foreach ( $files as $file ) {
		if ( UPLOAD_ERR_OK !== (int) $file['error'] || $file['size'] > MODA_SUPPORT_MAX_BYTES || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error( 'file' );
		}

		$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $allowed );
		$info  = @getimagesize( $file['tmp_name'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Invalid files are rejected below.
		if ( empty( $check['type'] ) || ! in_array( $check['type'], $allowed, true ) || false === $info ) {
			return new WP_Error( 'file' );
		}

		$file['name'] = wp_generate_password( 16, false ) . '.' . $check['ext'];
		$moved        = wp_handle_upload(
			$file,
			array(
				'test_form' => false,
				'mimes'     => $allowed,
			)
		);
		if ( ! empty( $moved['error'] ) || empty( $moved['url'] ) ) {
			return new WP_Error( 'file' );
		}
		$images[] = array(
			'url'  => $moved['url'],
			'file' => $moved['file'],
		);
	}

	return $images;
}

/**
 * Handle a support form submission.
 *
 * @return void
 */
function moda_support_handle_submit() {
	if ( ! isset( $_POST['moda_support_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moda_support_nonce'] ) ), 'moda_support_submit' ) ) {
		moda_support_redirect( 'error' );
	}

	// Bots fill the honeypot or submit within a second.
	$started = isset( $_POST['moda_support_time'] ) ? (int) $_POST['moda_support_time'] : 0;
	if ( ! empty( $_POST['moda_support_website'] ) || ( $started && time() - $started < 3 ) ) {
		moda_support_redirect( 'ok' );
	}

	$key   = 'moda_support_rate_' . moda_support_visitor_key();
	$count = (int) get_transient( $key );
	if ( $count >= 5 ) {
		moda_support_redirect( 'limit' );
	}

	$types = moda_support_types();
	$data  = array(
		'type'    => isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : 'talep',
		'name'    => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
		'email'   => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
		'phone'   => isset( $_POST['phone'] ) ? preg_replace( '/[^0-9+\s()-]/', '', sanitize_text_field( wp_unslash( $_POST['phone'] ) ) ) : '',
		'order'   => isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : '',
		'subject' => isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '',
		'message' => isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '',
	);

	set_transient( 'moda_support_old_' . moda_support_visitor_key(), $data, 10 * MINUTE_IN_SECONDS );

	if ( ! isset( $types[ $data['type'] ] ) || '' === $data['name'] || ! is_email( $data['email'] ) || '' === $data['subject'] || mb_strlen( $data['message'] ) < 10 || mb_strlen( $data['message'] ) > 3000 ) {
		moda_support_redirect( 'invalid' );
	}
	if ( empty( $_POST['consent'] ) ) {
		moda_support_redirect( 'consent' );
	}

	$images = moda_support_collect_images();
	if ( is_wp_error( $images ) ) {
		moda_support_redirect( 'file' );
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'moda_ticket',
			'post_status'  => 'publish',
			'post_author'  => is_user_logged_in() ? get_current_user_id() : 0,
			'post_title'   => sprintf( '[%s] %s', $types[ $data['type'] ], $data['subject'] ),
			'post_content' => $data['message'],
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		foreach ( $images as $image ) {
			wp_delete_file( $image['file'] );
		}
		moda_support_redirect( 'error' );
	}

	foreach ( array( 'type', 'name', 'email', 'phone', 'order' ) as $field ) {
		update_post_meta( $post_id, '_moda_ticket_' . $field, $data[ $field ] );
	}
	update_post_meta( $post_id, '_moda_ticket_images', wp_list_pluck( $images, 'url' ) );
	update_post_meta( $post_id, '_moda_ticket_status', 'yeni' );

	$reference = 'MC-' . str_pad( (string) $post_id, 5, '0', STR_PAD_LEFT );
	update_post_meta( $post_id, '_moda_ticket_reference', $reference );

	set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	delete_transient( 'moda_support_old_' . moda_support_visitor_key() );

	$lines = array(
		sprintf( 'Talep no: %s', $reference ),
		sprintf( 'Konu: %s', $types[ $data['type'] ] ),
		sprintf( 'Ad soyad: %s', $data['name'] ),
		sprintf( 'E-posta: %s', $data['email'] ),
		sprintf( 'Telefon: %s', $data['phone'] ),
		sprintf( 'Sipariş no: %s', $data['order'] ),
		sprintf( 'Başlık: %s', $data['subject'] ),
		'',
		$data['message'],
	);
	if ( $images ) {
		$lines[] = '';
		$lines[] = 'Görseller:';
		foreach ( $images as $image ) {
			$lines[] = $image['url'];
		}
	}
	$lines[] = '';
	$lines[] = admin_url( 'post.php?post=' . $post_id . '&action=edit' );

	$admin_email_sent = wp_mail(
		get_option( 'admin_email' ),
		sprintf( '[%s] Yeni destek talebi %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $reference ),
		implode( "\n", $lines ),
		array( 'Reply-To: ' . $data['name'] . ' <' . $data['email'] . '>' )
	);
	$customer_email_sent = wp_mail(
		$data['email'],
		sprintf( 'Talebiniz alındı (%s)', $reference ),
		sprintf( "Merhaba %s,\n\n%s numaralı talebinizi aldık. En kısa sürede size dönüş yapacağız.\n\n%s", $data['name'], $reference, wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) )
	);
	if ( ! $admin_email_sent || ! $customer_email_sent ) {
		error_log( 'Moda Support: one or more notification emails failed for ticket ' . absint( $post_id ) . '.' );
	}

	moda_support_redirect( 'ok', array( 'ref' => $reference ) );
}
add_action( 'admin_post_nopriv_moda_support_submit', 'moda_support_handle_submit' );
add_action( 'admin_post_moda_support_submit', 'moda_support_handle_submit' );

/**
 * Add ticket details meta box.
 *
 * @return void
 */
function moda_support_meta_boxes() {
	add_meta_box( 'moda_ticket_details', __( 'Talep bilgileri', 'moda-support' ), 'moda_support_render_meta_box', 'moda_ticket', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'moda_support_meta_boxes' );

/**
 * Render ticket details.
 *
 * @param WP_Post $post Ticket post.
 * @return void
 */
function moda_support_render_meta_box( $post ) {
	$types  = moda_support_types();
	$status = (string) get_post_meta( $post->ID, '_moda_ticket_status', true );
	$type   = (string) get_post_meta( $post->ID, '_moda_ticket_type', true );
	$email  = (string) get_post_meta( $post->ID, '_moda_ticket_email', true );
	$images = (array) get_post_meta( $post->ID, '_moda_ticket_images', true );

	wp_nonce_field( 'moda_ticket_status', 'moda_ticket_nonce' );
	echo '<table class="form-table"><tbody>';
	printf( '<tr><th>Talep no</th><td>%s</td></tr>', esc_html( (string) get_post_meta( $post->ID, '_moda_ticket_reference', true ) ) );
	printf( '<tr><th>Konu</th><td>%s</td></tr>', esc_html( $types[ $type ] ?? $type ) );
	printf( '<tr><th>Ad soyad</th><td>%s</td></tr>', esc_html( (string) get_post_meta( $post->ID, '_moda_ticket_name', true ) ) );
	printf( '<tr><th>E-posta</th><td><a href="mailto:%1$s">%1$s</a></td></tr>', esc_attr( $email ) );
	printf( '<tr><th>Telefon</th><td>%s</td></tr>', esc_html( (string) get_post_meta( $post->ID, '_moda_ticket_phone', true ) ) );
	printf( '<tr><th>Sipariş no</th><td>%s</td></tr>', esc_html( (string) get_post_meta( $post->ID, '_moda_ticket_order', true ) ) );
	printf( '<tr><th>Mesaj</th><td>%s</td></tr>', wp_kses_post( nl2br( esc_html( $post->post_content ) ) ) );
	echo '<tr><th>Görseller</th><td>';
	foreach ( $images as $url ) {
		if ( is_string( $url ) && '' !== $url ) {
			printf( '<a href="%1$s" target="_blank" rel="noopener"><img src="%1$s" alt="" style="width:110px;height:110px;object-fit:cover;margin:0 8px 8px 0;border:1px solid #ccd0d4"></a>', esc_url( $url ) );
		}
	}
	echo $images ? '' : esc_html__( 'Görsel eklenmemiş.', 'moda-support' );
	echo '</td></tr>';
	$reply = (string) get_post_meta( $post->ID, '_moda_ticket_reply', true );
	echo '<tr><th><label for="moda_ticket_reply">' . esc_html__( 'Müşteriye yanıt', 'moda-support' ) . '</label></th><td><textarea id="moda_ticket_reply" name="moda_ticket_reply" class="large-text" rows="5">' . esc_textarea( $reply ) . '</textarea><p class="description">' . esc_html__( 'Yanıt kaydedildiğinde müşterinin Hesabım ekranında görünür ve e-postayla bildirilir.', 'moda-support' ) . '</p></td></tr>';
	echo '<tr><th><label for="moda_ticket_status">Durum</label></th><td><select name="moda_ticket_status" id="moda_ticket_status">';
	foreach ( moda_support_statuses() as $key => $label ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $status, $key, false ), esc_html( $label ) );
	}
	echo '</select></td></tr></tbody></table>';
}

/**
 * Save the ticket status.
 *
 * @param int $post_id Ticket ID.
 * @return void
 */
function moda_support_save_status( $post_id ) {
	if ( ! isset( $_POST['moda_ticket_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moda_ticket_nonce'] ) ), 'moda_ticket_status' ) || ! current_user_can( 'edit_post', $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
		return;
	}
	$status = isset( $_POST['moda_ticket_status'] ) ? sanitize_key( wp_unslash( $_POST['moda_ticket_status'] ) ) : 'yeni';
	if ( isset( moda_support_statuses()[ $status ] ) ) {
		update_post_meta( $post_id, '_moda_ticket_status', $status );
	}

	$reply = isset( $_POST['moda_ticket_reply'] ) ? sanitize_textarea_field( wp_unslash( $_POST['moda_ticket_reply'] ) ) : '';
	$previous_reply = (string) get_post_meta( $post_id, '_moda_ticket_reply', true );
	if ( '' !== $reply ) {
		update_post_meta( $post_id, '_moda_ticket_reply', $reply );
		$email = sanitize_email( (string) get_post_meta( $post_id, '_moda_ticket_email', true ) );
		$reply_hash = md5( $reply );
		if ( is_email( $email ) && $reply_hash !== get_post_meta( $post_id, '_moda_ticket_reply_sent', true ) ) {
			if ( wp_mail( $email, sprintf( __( 'Destek talebinize yanıt verildi (%s)', 'moda-support' ), get_post_meta( $post_id, '_moda_ticket_reference', true ) ), $reply ) ) {
				update_post_meta( $post_id, '_moda_ticket_reply_sent', $reply_hash );
			} else {
				error_log( 'Moda Support: response notification failed for ticket ' . absint( $post_id ) . '.' );
			}
		}
	} elseif ( '' !== $previous_reply ) {
		delete_post_meta( $post_id, '_moda_ticket_reply' );
	}
}
add_action( 'save_post_moda_ticket', 'moda_support_save_status' );

/**
 * Ticket list columns.
 *
 * @param array<string,string> $columns Columns.
 * @return array<string,string>
 */
function moda_support_columns( $columns ) {
	return array(
		'cb'     => $columns['cb'] ?? '',
		'title'  => __( 'Talep', 'moda-support' ),
		'ref'    => __( 'Talep no', 'moda-support' ),
		'person' => __( 'Gönderen', 'moda-support' ),
		'status' => __( 'Durum', 'moda-support' ),
		'date'   => __( 'Tarih', 'moda-support' ),
	);
}
add_filter( 'manage_moda_ticket_posts_columns', 'moda_support_columns' );

/**
 * Render ticket list columns.
 *
 * @param string $column  Column.
 * @param int    $post_id Ticket ID.
 * @return void
 */
function moda_support_column_content( $column, $post_id ) {
	if ( 'ref' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_moda_ticket_reference', true ) );
	} elseif ( 'person' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_moda_ticket_name', true ) . ' · ' . get_post_meta( $post_id, '_moda_ticket_email', true ) );
	} elseif ( 'status' === $column ) {
		$statuses = moda_support_statuses();
		$status   = (string) get_post_meta( $post_id, '_moda_ticket_status', true );
		echo esc_html( $statuses[ $status ] ?? $status );
	}
}
add_action( 'manage_moda_ticket_posts_custom_column', 'moda_support_column_content', 10, 2 );

/**
 * Remove uploaded images from disk when a ticket is permanently deleted.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function moda_support_delete_images( $post_id ) {
	if ( 'moda_ticket' !== get_post_type( $post_id ) ) {
		return;
	}

	$uploads = wp_get_upload_dir();
	foreach ( (array) get_post_meta( $post_id, '_moda_ticket_images', true ) as $url ) {
		if ( is_string( $url ) && 0 === strpos( $url, $uploads['baseurl'] . '/' ) ) {
			$path = wp_normalize_path( $uploads['basedir'] . substr( $url, strlen( $uploads['baseurl'] ) ) );
			if ( 0 === strpos( $path, wp_normalize_path( $uploads['basedir'] ) . '/' ) && false === strpos( $path, '..' ) ) {
				wp_delete_file( $path );
			}
		}
	}
}
add_action( 'before_delete_post', 'moda_support_delete_images' );
