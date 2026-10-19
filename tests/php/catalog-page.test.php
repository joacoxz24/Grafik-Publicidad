<?php
/** Render the real shop and shared cards to check heading structure and scope. */
define( 'ABSPATH', __DIR__ );
function get_header() {}
function get_footer() {}
function is_shop() { return true; }
function esc_url( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
function home_url( $path ) { return 'https://example.test' . $path; }
function get_template_directory_uri() { return home_url( '/theme' ); }
function grafik_configurator_url( $family ) { return home_url( '/' . $family . '/' ); }
function grafik_tyvek_price_label() { return 'Precio de pulseras'; }
function grafik_chapita_price_label() { return 'Precio de chapitas'; }
function woocommerce_content() { echo '<div id="woocommerce-catalog"></div>'; }
function get_template_part( $slug, $name = null, $args = array() ) { include __DIR__ . '/../../wordpress/grafik-publicidad/' . $slug . '.php'; }
ob_start(); include __DIR__ . '/../../wordpress/grafik-publicidad/woocommerce.php'; $shop = ob_get_clean();
preg_match_all( '/<h([1-6])\b/', $shop, $headings );
if ( $headings[1] !== array( '1', '2', '2', '2', '3', '3', '3', '3' ) ) { throw new RuntimeException( 'Unexpected shop heading hierarchy.' ); }
if ( strpos( $shop, 'woocommerce-catalog' ) > strpos( $shop, 'grafik-catalog-guide' ) ) { throw new RuntimeException( 'The purchase guide must follow the product catalog.' ); }
foreach ( array( array(), array( 'heading_level' => '<script>' ) ) as $args ) {
	ob_start(); get_template_part( 'template-parts/product-families', null, $args ); $cards = ob_get_clean();
	if ( substr_count( $cards, '<h3>' ) !== 2 || str_contains( $cards, '<script>' ) ) { throw new RuntimeException( 'Homepage cards must keep safe H3 headings.' ); }
}
echo "PASS: shop hierarchy, catalog ordering and homepage card checks.\n";
