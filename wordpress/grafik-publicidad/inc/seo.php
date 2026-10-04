<?php
/** Basic SEO for the store's custom landing pages. */
defined( 'ABSPATH' ) || exit;

function grafik_seo_external(): bool {
	return (bool) apply_filters( 'grafik_external_seo', defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' ) );
}

function grafik_seo_page(): array {
	if ( is_front_page() ) {
		return array( 'title' => 'Pulseras y chapitas personalizadas | Grafik Publicidad', 'description' => 'Personaliza pulseras Tyvek, chapitas y llaveros para eventos y empresas. Retiro coordinado en Concepción o Linares y envíos por pagar a todo Chile.' );
	}
	if ( is_page( 'pulseras' ) ) {
		return array( 'title' => 'Pulseras Tyvek personalizadas para eventos | Grafik Publicidad', 'description' => 'Personaliza pulseras Tyvek con tu logo para eventos y control de acceso. Elige color y cantidad, adjunta tu diseño y coordina retiro en Concepción o Linares, o pide envío.' );
	}
	if ( is_page( 'chapitas' ) ) {
		return array( 'title' => 'Chapitas personalizadas por mayor y al detalle | Grafik Publicidad', 'description' => 'Chapitas de 58 mm: alfiler, llavero y llavero destapador. Diseños gratis, venta por mayor y al detalle. Cotiza en línea y recibe en todo Chile.' );
	}
	if ( ( function_exists( 'is_shop' ) && is_shop() ) || is_page( 'productos' ) ) {
		return array( 'title' => 'Productos personalizados para eventos | Grafik Publicidad', 'description' => 'Explora pulseras Tyvek, chapitas publicitarias y llaveros personalizados para marcas y eventos. Elige tu producto y personaliza tu pedido en línea.' );
	}
	return array();
}

add_filter( 'document_title_parts', static function ( $parts ) {
	$data = grafik_seo_page();
	if ( ! grafik_seo_external() && $data ) {
		$parts['title'] = $data['title'];
		unset( $parts['site'], $parts['tagline'] );
	}
	return $parts;
} );

add_action( 'wp_head', static function () {
	$data = grafik_seo_page();
	if ( grafik_seo_external() || ! $data || is_paged() ) { return; }
	echo '<meta name="description" content="' . esc_attr( $data['description'] ) . '">' . "\n";
	// Share the canonical landing page, never a cart, tracking or configurator URL.
	$url = is_front_page() ? home_url( '/' ) : ( ( function_exists( 'is_shop' ) && is_shop() ) ? grafik_products_url() : get_permalink() );
	$image = get_template_directory_uri() . '/assets/images/' . ( is_page( 'pulseras' ) ? 'pulseras-tyvek-reales.png' : 'chapitas-catalogo.png' );
	$tags = array(
		'og:type' => 'website',
		'og:locale' => 'es_CL',
		'og:site_name' => 'Grafik Publicidad',
		'og:title' => $data['title'],
		'og:description' => $data['description'],
		'og:url' => $url,
		'og:image' => $image,
	);
	foreach ( $tags as $property => $value ) {
		echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $value ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $data['title'] ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $data['description'] ) . '">' . "\n";
	echo '<meta name="twitter:image" content="' . esc_attr( $image ) . '">' . "\n";
	// WordPress/WooCommerce keep ownership of canonical URLs and Product schema.
	if ( ! is_front_page() ) { return; }
	$schema = array( '@context' => 'https://schema.org', '@type' => 'Organization', '@id' => home_url( '/#organization' ), 'name' => 'Grafik Publicidad', 'url' => home_url( '/' ), 'email' => GRAFIK_SALES_EMAIL, 'sameAs' => array( grafik_instagram_url() ) );
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . '</script>' . "\n";
}, 5 );
