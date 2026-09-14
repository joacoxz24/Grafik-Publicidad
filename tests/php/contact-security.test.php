<?php
define( 'ABSPATH', __DIR__ );
$cache = array();
function wp_salt( $scheme ) { return 'test-secret'; }
function get_transient( $key ) { return $GLOBALS['cache'][ $key ] ?? false; }
function set_transient( $key, $value, $expiry ) { if ( 60 !== $expiry ) { throw new Exception( 'Expiry' ); } $GLOBALS['cache'][ $key ] = $value; }
require __DIR__ . '/../../wordpress/grafik-publicidad/inc/contact-security.php';
$checks = 0;
function check( $ok ) { global $checks; ++$checks; if ( ! $ok ) { throw new Exception( 'Failed check ' . $checks ); } }
check( '' === grafik_contact_input_error( array( 'name' => 'María', 'message' => 'Necesito 100 pulseras', 'email' => 'test@example.test' ) ) );
check( '' !== grafik_contact_input_error( array( 'name' => array( 'malformed' ) ) ) );
check( '' !== grafik_contact_input_error( array( 'grafik_contact_nonce' => array() ) ) );
check( '' !== grafik_contact_input_error( array( 'message' => str_repeat( 'x', 6001 ) ) ) );
check( '' !== grafik_contact_input_error( array( 'website' => 'bot.example' ) ) );
$_SERVER['REMOTE_ADDR'] = '192.0.2.1';
check( grafik_contact_allow_request() );
check( ! grafik_contact_allow_request() );
$_SERVER['HTTP_X_FORWARDED_FOR'] = '192.0.2.2';
check( ! grafik_contact_allow_request() );
$_SERVER['REMOTE_ADDR'] = '192.0.2.3';
check( grafik_contact_allow_request() );
$cache = array();
check( grafik_contact_allow_request() );
echo "PASS: $checks quotation validation and throttling checks.\n";
