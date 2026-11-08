<?php
/** Render the production theme and configurators using the catalog's WP/WC test doubles. */
ob_start();
require __DIR__ . '/catalog.test.php';
ob_end_clean();
$GLOBALS['ui_family'] = ( $argv[1] ?? 'chapitas' ) === 'pulseras' ? 'pulseras' : 'chapitas';
$GLOBALS['wc']->cart = null;
foreach ( Grafik_Chapitas::TYPES as $kind => $rule ) {
	$product = wc_get_product( Grafik_Chapitas::id( $kind ) );
	$product->fields['regular_price'] = $rule['price'];
	$product->fields['stock_status'] = 'instock';
	foreach ( array( 'minimum', 'threshold', 'bulk' ) as $field ) { $product->meta[ '_grafik_chapita_' . $field ] = $rule[ $field ]; }
}
function remove_action( ...$args ) {}
function esc_url( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
function esc_html_e( $value, $domain ) { echo esc_html( $value ); }
function esc_attr_e( $value, $domain ) { echo esc_attr( $value ); }
function get_theme_mod( $name, $fallback = false ) { return $fallback; }
function home_url( $path ) { return 'https://example.test' . $path; }
function get_template_directory_uri() { return home_url( '/theme' ); }
function get_page_by_path( $path ) { return null; }
function wc_get_page_permalink( $page ) { return home_url( '/' . $page . '/' ); }
function wc_get_cart_url() { return home_url( '/carrito/' ); }
function is_checkout() { return false; }
function is_order_received_page() { return false; }
function is_page( $name ) { return $name === $GLOBALS['ui_family']; }
function get_post_type( $id ) { return 'product'; }
function locate_template( $path ) { return false; }
function shortcode_exists( $shortcode ) { return true; }
function do_shortcode( $shortcode ) { return $GLOBALS['ui_family'] === 'chapitas' ? $GLOBALS['chapitas']->shortcode() : $GLOBALS['tyvek']->shortcode(); }
function wp_create_nonce( $action ) { return 'local-test-nonce'; }
function admin_url( $path ) { return home_url( '/wp-admin/' . $path ); }
function wp_date( $format ) { return '2026'; }
function wp_kses_post( $value ) { return $value; }
function wc_price( $value ) { return '$' . number_format( $value, 0, ',', '.' ); }
function wp_head() {}
function wp_footer() {}
function wp_body_open() {}
function language_attributes() { echo 'lang="es"'; }
function bloginfo( $key ) { echo 'UTF-8'; }
function body_class( $extra ) { echo 'class="' . esc_attr( $extra ) . '"'; }
function get_header() { require __DIR__ . '/../../wordpress/grafik-publicidad/header.php'; }
function get_footer() { require __DIR__ . '/../../wordpress/grafik-publicidad/footer.php'; }
function get_template_part( $slug ) { include __DIR__ . '/../../wordpress/grafik-publicidad/' . $slug . '.php'; }
require __DIR__ . '/../../wordpress/grafik-publicidad/functions.php';
require __DIR__ . '/../../wordpress/grafik-publicidad/page-' . $GLOBALS['ui_family'] . '.php';
