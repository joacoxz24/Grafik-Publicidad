<?php
defined( 'ABSPATH' ) || exit;

/** Keeps Flow's checkout, settings, credentials and URLs; only callbacks are hardened. */
class Grafik_Flow_Gateway extends WC_Flow_Gateway {
	private function grafik_verified_order(): WC_Order {
		$token = $_POST['token'] ?? null;
		if ( ! is_string( $token ) || ! preg_match( '/^[a-zA-Z0-9_-]{1,200}$/D', $token ) ) { throw new RuntimeException( 'Token Flow no válido.' ); }
		$mode = $this->get_option( 'mode' );
		$base = ! $mode || 'TEST' === $mode ? 'https://sandbox.flow.cl/api/v2' : 'https://www.flow.cl/api/v2';
		$response = wp_remote_get( $base . '/order/token/' . rawurlencode( $token ), array(
			'timeout' => 30, 'redirection' => 0, 'sslverify' => true,
			'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $this->get_option( 'api_key' ) . ':' ), 'Accept' => 'application/json' ),
		) );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) { throw new RuntimeException( 'No se pudo verificar el pago con Flow.' ); }
		$result = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $result ) || ! ctype_digit( (string) ( $result['commerce_order'] ?? '' ) ) || (int) $result['commerce_order'] < 1 ) { throw new RuntimeException( 'Respuesta de Flow no válida.' ); }
		$order_id = (int) $result['commerce_order'];
		// MySQL advisory lock is shared by callback/return and automatically released on disconnect.
		global $wpdb;
		$lock_name = 'grafik_flow_' . md5( $wpdb->prefix . ':' . $order_id );
		if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 10)', $lock_name ) ) ) { throw new RuntimeException( 'La confirmación está en proceso; Flow debe reintentar.' ); }
		try {
			$order = wc_get_order( $order_id );
			if ( ! $order ) { throw new RuntimeException( 'Pedido Flow no encontrado.' ); }
			$order->get_data_store()->read( $order );
			Grafik_Flow_Lifecycle::apply( $result, $order );
			return $order;
		} finally { $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) ); }
	}

	public function callback_confirm() {
		try {
			$this->grafik_verified_order();
			status_header( 200 ); echo 'OK';
		} catch ( Throwable $error ) {
			wc_get_logger()->error( $error->getMessage(), array( 'source' => 'grafik-flow' ) );
			status_header( 503 ); echo 'Confirmation could not be processed';
		}
		exit;
	}

	public function callback_return() {
		try {
			$order = $this->grafik_verified_order();
			if ( $order->is_paid() && WC()->cart ) { WC()->cart->empty_cart(); }
			wp_safe_redirect( $this->get_return_url( $order ) );
		} catch ( Throwable $error ) {
			wc_get_logger()->error( $error->getMessage(), array( 'source' => 'grafik-flow' ) );
			wc_add_notice( 'Estamos verificando el resultado del pago. Revisa tu pedido antes de intentar pagar nuevamente.', 'notice' );
			wp_safe_redirect( wc_get_checkout_url() );
		}
		exit;
	}
}
