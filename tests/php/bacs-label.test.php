<?php
define( 'ABSPATH', __DIR__ );
function add_filter( ...$args ) {}
require __DIR__ . '/../../wordpress/grafik-publicidad/inc/bacs.php';

$rut = array( 'iban' => array( 'label' => 'IBAN', 'value' => '78.033.169-0' ) );
if ( 'RUT' !== grafik_bacs_rut_label( $rut, 71 )['iban']['label'] ) {
	throw new Exception( 'Chilean RUT label was not updated.' );
}
$iban = array( 'iban' => array( 'label' => 'IBAN', 'value' => 'DE89370400440532013000' ) );
if ( 'IBAN' !== grafik_bacs_rut_label( $iban, 71 )['iban']['label'] ) {
	throw new Exception( 'A real IBAN must retain its label.' );
}
echo "PASS: BACS RUT label checks.\n";
