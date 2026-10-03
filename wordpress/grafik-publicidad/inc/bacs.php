<?php
/** Label Chilean account details stored in WooCommerce's generic BACS fields. */
defined( 'ABSPATH' ) || exit;

function grafik_bacs_account_labels( array $fields, $order_id ): array {
	$value = $fields['iban']['value'] ?? null;
	if ( is_string( $value ) && preg_match( '/^\d{7,8}-[\dkK]$/', str_replace( '.', '', trim( $value ) ) ) ) {
		$fields['iban']['label'] = 'RUT';
	}
	$bic = $fields['bic']['value'] ?? null;
	if ( is_string( $bic ) && false !== filter_var( trim( $bic ), FILTER_VALIDATE_EMAIL ) ) {
		$fields['bic']['label'] = 'Correo';
	}
	return $fields;
}
add_filter( 'woocommerce_bacs_account_fields', 'grafik_bacs_account_labels', 10, 2 );
