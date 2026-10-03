<?php
define( 'ABSPATH', __DIR__ );
function add_filter( ...$args ) {}
require __DIR__ . '/../../wordpress/grafik-publicidad/inc/bacs.php';

$rut = array( 'iban' => array( 'label' => 'IBAN', 'value' => '78.033.169-0' ) );
if ( 'RUT' !== grafik_bacs_account_labels( $rut, 71 )['iban']['label'] ) {
	throw new Exception( 'Chilean RUT label was not updated.' );
}
$iban = array( 'iban' => array( 'label' => 'IBAN', 'value' => 'DE89370400440532013000' ) );
if ( 'IBAN' !== grafik_bacs_account_labels( $iban, 71 )['iban']['label'] ) {
	throw new Exception( 'A real IBAN must retain its label.' );
}
$email = array( 'bic' => array( 'label' => 'BIC', 'value' => 'ventas@grafikpublicidad.cl' ) );
if ( 'Correo' !== grafik_bacs_account_labels( $email, 71 )['bic']['label'] ) {
	throw new Exception( 'An email in the BIC field must be labeled Correo.' );
}
$bic = array( 'bic' => array( 'label' => 'BIC', 'value' => 'BCHICLRMXXX' ) );
if ( 'BIC' !== grafik_bacs_account_labels( $bic, 71 )['bic']['label'] ) {
	throw new Exception( 'A real BIC must retain its label.' );
}
echo "PASS: BACS account label checks.\n";
