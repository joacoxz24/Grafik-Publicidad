<?php
/** Display a Chilean RUT stored in the BACS IBAN field with its correct label. */
defined( 'ABSPATH' ) || exit;

function grafik_bacs_rut_label( array $fields, $order_id ): array {
	$value = $fields['iban']['value'] ?? null;
	if ( is_string( $value ) && preg_match( '/^\d{7,8}-[\dkK]$/', str_replace( '.', '', trim( $value ) ) ) ) {
		$fields['iban']['label'] = 'RUT';
	}
	return $fields;
}
add_filter( 'woocommerce_bacs_account_fields', 'grafik_bacs_rut_label', 10, 2 );
