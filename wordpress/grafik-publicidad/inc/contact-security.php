<?php
/** Validation and basic abuse protection for the public quotation form. */
defined( 'ABSPATH' ) || exit;

function grafik_contact_input_error( array $input ): string {
	foreach ( array( 'name' => 150, 'email' => 254, 'phone' => 50, 'quantity' => 10, 'message' => 6000, 'grafik_contact_nonce' => 100, 'website' => 200 ) as $key => $limit ) {
		if ( isset( $input[ $key ] ) && ( ! is_string( $input[ $key ] ) || strlen( $input[ $key ] ) > $limit ) ) {
			return 'Revisa los campos de la consulta: hay un valor no válido o demasiado largo.';
		}
	}
	return ! empty( $input['website'] ) ? 'No se pudo validar el formulario.' : '';
}

function grafik_contact_allow_request(): bool {
	// Do not trust client-supplied forwarding headers or store the raw IP.
	$key = 'grafik_contact_' . hash_hmac( 'sha256', (string) ( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ), wp_salt( 'nonce' ) );
	if ( get_transient( $key ) ) { return false; }
	set_transient( $key, 1, 60 );
	return true;
}
