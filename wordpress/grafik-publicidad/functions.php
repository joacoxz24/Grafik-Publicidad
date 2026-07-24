<?php
/**
 * Funciones del tema Grafik Publicidad.
 *
 * @package Grafik_Publicidad
 */

defined( 'ABSPATH' ) || exit;

define( 'GRAFIK_THEME_VERSION', '1.1.0' );

function grafik_theme_setup(): void {
	load_theme_textdomain( 'grafik-publicidad', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 90,
			'width'       => 280,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 520,
			'single_image_width'    => 900,
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary' => __( 'Navegación principal', 'grafik-publicidad' ),
			'footer'  => __( 'Navegación del pie', 'grafik-publicidad' ),
		)
	);
}
add_action( 'after_setup_theme', 'grafik_theme_setup' );

function grafik_theme_assets(): void {
	wp_enqueue_style( 'grafik-publicidad', get_stylesheet_uri(), array(), GRAFIK_THEME_VERSION );
	wp_enqueue_script(
		'grafik-publicidad',
		get_template_directory_uri() . '/assets/js/theme.js',
		array(),
		GRAFIK_THEME_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'grafik_theme_assets' );

function grafik_theme_activate(): void {
	if ( 'My Website' === get_option( 'blogname' ) || ! get_option( 'blogname' ) ) {
		update_option( 'blogname', 'Grafik Publicidad' );
	}

	if ( ! get_option( 'blogdescription' ) ) {
		update_option( 'blogdescription', 'Pulseras Tyvek personalizadas para eventos' );
	}

	$front_id = (int) get_option( 'page_on_front' );
	if ( ! $front_id ) {
		$page = get_page_by_path( 'inicio' );
		if ( ! $page ) {
			$front_id = wp_insert_post(
				array(
					'post_title'   => 'Inicio',
					'post_name'    => 'inicio',
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_content' => '',
				)
			);
		} else {
			$front_id = (int) $page->ID;
		}

		if ( $front_id && ! is_wp_error( $front_id ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $front_id );
		}
	}

	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'grafik_theme_activate' );

/**
 * Mantiene la página principal del catálogo lista para futuras migraciones.
 */
function grafik_theme_upgrade(): void {
	if ( GRAFIK_THEME_VERSION === get_option( 'grafik_theme_db_version' ) ) {
		return;
	}

	if ( function_exists( 'wc_get_page_id' ) ) {
		$shop_id = wc_get_page_id( 'shop' );

		if ( $shop_id > 0 ) {
			wp_update_post(
				array(
					'ID'         => $shop_id,
					'post_title' => 'Productos',
					'post_name'  => 'productos',
				)
			);
		} else {
			$shop_id = wp_insert_post(
				array(
					'post_title'  => 'Productos',
					'post_name'   => 'productos',
					'post_type'   => 'page',
					'post_status' => 'publish',
				)
			);

			if ( $shop_id && ! is_wp_error( $shop_id ) ) {
				update_option( 'woocommerce_shop_page_id', (int) $shop_id );
			}
		}
	}

	update_option( 'grafik_theme_db_version', GRAFIK_THEME_VERSION );
	flush_rewrite_rules( false );
}
add_action( 'init', 'grafik_theme_upgrade', 50 );

function grafik_theme_customize( WP_Customize_Manager $customizer ): void {
	$customizer->add_section(
		'grafik_store',
		array(
			'title'       => __( 'Grafik Publicidad', 'grafik-publicidad' ),
			'description' => __( 'Textos y enlaces principales de la tienda.', 'grafik-publicidad' ),
			'priority'    => 30,
		)
	);

	$fields = array(
		'grafik_instagram_url' => array(
			'label'    => __( 'URL de Instagram', 'grafik-publicidad' ),
			'default'  => 'https://www.instagram.com/grafikpublicidad.cl',
			'sanitize' => 'esc_url_raw',
			'type'     => 'url',
		),
		'grafik_instagram_shortcode' => array(
			'label'    => __( 'Shortcode del feed automático de Instagram', 'grafik-publicidad' ),
			'default'  => '',
			'sanitize' => 'sanitize_text_field',
			'type'     => 'text',
		),
		'grafik_hero_title'    => array(
			'label'    => __( 'Título principal', 'grafik-publicidad' ),
			'default'  => 'Pulseras Tyvek personalizadas para tu evento',
			'sanitize' => 'sanitize_text_field',
			'type'     => 'text',
		),
		'grafik_contact_email' => array(
			'label'    => __( 'Correo que recibe las consultas', 'grafik-publicidad' ),
			'default'  => get_option( 'admin_email' ),
			'sanitize' => 'sanitize_email',
			'type'     => 'email',
		),
	);

	foreach ( $fields as $key => $field ) {
		$customizer->add_setting(
			$key,
			array(
				'default'           => $field['default'],
				'sanitize_callback' => $field['sanitize'],
			)
		);
		$customizer->add_control(
			$key,
			array(
				'label'   => $field['label'],
				'section' => 'grafik_store',
				'type'    => $field['type'],
			)
		);
	}
}
add_action( 'customize_register', 'grafik_theme_customize' );

function grafik_instagram_url(): string {
	return (string) get_theme_mod( 'grafik_instagram_url', 'https://www.instagram.com/grafikpublicidad.cl' );
}

function grafik_products_url(): string {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( 'shop' );
		if ( $url ) {
			return $url;
		}
	}
	return home_url( '/productos/' );
}

/**
 * Imprime el logo cargado en WordPress o el identificador gráfico predeterminado.
 */
function grafik_logo_content(): void {
	$logo_id = absint( get_theme_mod( 'custom_logo' ) );
	if ( $logo_id ) {
		echo wp_get_attachment_image(
			$logo_id,
			'full',
			false,
			array(
				'class' => 'grafik-logo-image',
				'alt'   => get_bloginfo( 'name' ),
			)
		);
		return;
	}
	?>
	<i><span></span></i><span>Grafik <b>Publicidad</b></span>
	<?php
}

function grafik_cart_count_fragment( array $fragments ): array {
	ob_start();
	?>
	<b class="grafik-cart-count"><?php echo esc_html( WC()->cart ? (string) WC()->cart->get_cart_contents_count() : '0' ); ?></b>
	<?php
	$fragments['b.grafik-cart-count'] = ob_get_clean();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'grafik_cart_count_fragment' );

function grafik_order_button_text(): string {
	return __( 'Pagar de forma segura', 'grafik-publicidad' );
}
add_filter( 'woocommerce_order_button_text', 'grafik_order_button_text' );

function grafik_hide_shop_title( bool $show ): bool {
	return function_exists( 'is_shop' ) && is_shop() ? false : $show;
}
add_filter( 'woocommerce_show_page_title', 'grafik_hide_shop_title' );

function grafik_no_products_message(): void {
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		echo '<p class="grafik-products-empty">Aquí aparecerán automáticamente los nuevos productos que publiques en WooCommerce.</p>';
		return;
	}
	wc_no_products_found();
}
remove_action( 'woocommerce_no_products_found', 'wc_no_products_found', 10 );
add_action( 'woocommerce_no_products_found', 'grafik_no_products_message', 10 );

function grafik_checkout_intro(): void {
	if ( is_order_received_page() ) {
		return;
	}
	?>
	<section class="grafik-checkout-intro">
		<span class="kicker"><?php esc_html_e( 'Finalizar compra', 'grafik-publicidad' ); ?></span>
		<h1><?php esc_html_e( 'Datos de compra', 'grafik-publicidad' ); ?></h1>
		<p>
			<?php esc_html_e( 'Revisaremos y confirmaremos tu pedido lo más pronto posible. Recibirás una confirmación de cómo quedará el diseño final.', 'grafik-publicidad' ); ?>
		</p>
	</section>
	<?php
}
add_action( 'woocommerce_before_checkout_form', 'grafik_checkout_intro', 5 );

function grafik_contact_submit(): void {
	if (
		! isset( $_POST['grafik_contact_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['grafik_contact_nonce'] ) ), 'grafik_contact' )
	) {
		wp_die( esc_html__( 'La sesión del formulario expiró. Vuelve a intentarlo.', 'grafik-publicidad' ), 403 );
	}

	$name      = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email     = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone     = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$quantity  = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 0;
	$message   = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$marketing = isset( $_POST['marketing'] ) ? 'Sí' : 'No';

	if ( ! $name || ! is_email( $email ) || ! $message ) {
		wp_safe_redirect( add_query_arg( 'contacto', 'error', home_url( '/#contacto' ) ) );
		exit;
	}

	$to      = sanitize_email( get_theme_mod( 'grafik_contact_email', get_option( 'admin_email' ) ) );
	$subject = sprintf( 'Nueva consulta web de %s', $name );
	$body    = implode(
		"\n",
		array(
			'Nombre: ' . $name,
			'Correo: ' . $email,
			'Teléfono: ' . $phone,
			'Cantidad estimada: ' . $quantity,
			'Acepta promociones: ' . $marketing,
			'',
			'Mensaje:',
			$message,
		)
	);
	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
	$sent    = wp_mail( $to, $subject, $body, $headers );

	wp_safe_redirect( add_query_arg( 'contacto', $sent ? 'enviado' : 'error', home_url( '/#contacto' ) ) );
	exit;
}
add_action( 'admin_post_nopriv_grafik_contact', 'grafik_contact_submit' );
add_action( 'admin_post_grafik_contact', 'grafik_contact_submit' );
