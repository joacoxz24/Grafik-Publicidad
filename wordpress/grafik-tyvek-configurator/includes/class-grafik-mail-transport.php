<?php
defined( 'ABSPATH' ) || exit;

// cPanel signs/authenticates the envelope domain, not the hosting account's hostname.
// Respect SMTP plugins and explicitly configured senders on other transports.
add_action( 'phpmailer_init', static function ( $mailer ) {
	if ( 'mail' === $mailer->Mailer && 'ventas@grafikpublicidad.cl' === strtolower( $mailer->From ) ) {
		$mailer->Sender = 'ventas@grafikpublicidad.cl';
	}
}, 20 );
