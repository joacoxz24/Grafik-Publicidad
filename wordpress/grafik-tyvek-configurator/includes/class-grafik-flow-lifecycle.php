<?php
/** Adapt the installed Flow Payment gateway without changing vendor/core files. */
defined( 'ABSPATH' ) || exit;

final class Grafik_Flow_Lifecycle {
	public static function validate( array $result, WC_Order $order ): void {
		foreach ( array( 'commerce_order', 'status', 'currency', 'amount', 'id' ) as $key ) {
			if ( ! isset( $result[ $key ] ) || ! is_scalar( $result[ $key ] ) ) { throw new RuntimeException( 'Respuesta de Flow incompleta.' ); }
		}
		if ( ! ctype_digit( (string) $result['commerce_order'] ) || (int) $result['commerce_order'] !== $order->get_id() || 'flowpayment' !== $order->get_payment_method() ) { throw new RuntimeException( 'El pago no corresponde a este pedido/gateway.' ); }
		if ( ! in_array( (string) $result['status'], array( '1', '2', '3', '4' ), true ) || '' === (string) $result['id'] ) { throw new RuntimeException( 'Estado o transacción Flow no válidos.' ); }
		$decimals = 'CLP' === $order->get_currency() ? 0 : 2;
		if ( is_numeric( $result['amount'] ) && abs( (float) $result['amount'] - round( (float) $result['amount'], $decimals ) ) > 0.0000001 ) { throw new RuntimeException( 'Precisión del importe Flow no válida.' ); }
		if ( $result['currency'] !== $order->get_currency() || ! is_numeric( $result['amount'] ) || (float) $result['amount'] < 0 || number_format( (float) $result['amount'], $decimals, '.', '' ) !== number_format( (float) $order->get_total(), $decimals, '.', '' ) ) { throw new RuntimeException( 'Importe o moneda Flow no coinciden con el pedido.' ); }
	}

	/** Caller serializes workers, then loads a fresh order within the lock. */
	public static function apply( array $result, WC_Order $order ): void {
		self::validate( $result, $order );
		$paid = $order->is_paid() || $order->get_date_paid();
		$transaction = (string) $order->get_transaction_id();
		if ( $paid ) {
			if ( '2' === (string) $result['status'] && $transaction && $transaction !== (string) $result['id'] ) { throw new RuntimeException( 'Un pedido pagado recibió otra transacción. Requiere revisión.' ); }
			return; // Includes production and refunded orders; never regress them on late callbacks.
		}
		$status = (int) $result['status'];
		if ( 2 === $status ) {
			Grafik_Order_Files::finalize( $order );
			if ( ! $order->payment_complete( (string) $result['id'] ) || ! $order->get_date_paid() ) { throw new RuntimeException( 'WooCommerce no pudo completar el pago.' ); }
			$order->add_order_note( 'Pago Flow validado en servidor. Transacción: ' . sanitize_text_field( (string) $result['id'] ) );
		} elseif ( in_array( $order->get_status(), array( 'pending', 'on-hold', 'failed', 'cancelled' ), true ) ) {
			$next = 3 === $status ? 'failed' : ( 4 === $status ? 'cancelled' : '' );
			if ( $next && $order->get_status() !== $next ) { $order->update_status( $next, 'Estado verificado con Flow.' ); }
		}
	}
}

add_filter( 'woocommerce_payment_gateways', static function ( $gateways ) {
	if ( ! class_exists( 'WC_Flow_Gateway' ) ) { return $gateways; }
	foreach ( $gateways as $key => $gateway ) {
		if ( 'WC_Flow_Gateway' === $gateway ) {
			require_once __DIR__ . '/class-grafik-flow-gateway.php';
			$gateways[ $key ] = 'Grafik_Flow_Gateway';
		}
	}
	return $gateways;
}, 100 );
