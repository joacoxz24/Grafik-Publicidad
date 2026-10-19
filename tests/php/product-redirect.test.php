<?php
/** Verify canonical product redirects without running WordPress or sending headers. */
define( 'ABSPATH', __DIR__ );
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function add_shortcode( ...$args ) {}
function absint( $value ) { return abs( (int) $value ); }
function is_product() { return $GLOBALS['is_product']; }
function get_queried_object_id() { return $GLOBALS['product_id']; }
function get_option( $key ) { return $GLOBALS['options'][ $key ] ?? 0; }
function grafik_configurator_url( $family ) { return 'https://example.test/' . $family . '/'; }
class Captured_Redirect extends RuntimeException {
	public function __construct( public string $url, public int $status ) { parent::__construct(); }
}
function wp_safe_redirect( $url, $status = 302 ) { throw new Captured_Redirect( $url, $status ); }
require __DIR__ . '/../../wordpress/grafik-tyvek-configurator/includes/class-grafik-chapitas.php';
$plugin = new Grafik_Chapitas();
$GLOBALS['options'] = array( 'grafik_tyvek_product_id' => 10, 'grafik_chapita_alfiler_id' => 11, 'grafik_chapita_llavero_id' => 12, 'grafik_chapita_destapador_id' => 13 );
$GLOBALS['is_product'] = true;
$checks = 0;
foreach ( array( 10 => 'pulseras', 11 => 'chapitas', 12 => 'chapitas', 13 => 'chapitas' ) as $id => $family ) {
	$GLOBALS['product_id'] = $id;
	foreach ( array( 'GET' => 301, 'HEAD' => 301, 'POST' => 302 ) as $method => $status ) {
		$_SERVER['REQUEST_METHOD'] = $method;
		try { $plugin->product_redirect(); throw new RuntimeException( 'Expected a redirect.' ); }
		catch ( Captured_Redirect $redirect ) {
			if ( $redirect->url !== grafik_configurator_url( $family ) || $redirect->status !== $status ) { throw new RuntimeException( 'Wrong destination or redirect status.' ); }
			++$checks;
		}
	}
}
$GLOBALS['product_id'] = 999;
$plugin->product_redirect(); ++$checks;
$GLOBALS['product_id'] = 11; $GLOBALS['is_product'] = false;
$plugin->product_redirect(); ++$checks;
echo "PASS: $checks product redirect checks.\n";
