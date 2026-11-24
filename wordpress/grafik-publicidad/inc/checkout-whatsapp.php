<?php
/** Share a quote from the current WooCommerce payment fragment. */
defined( 'ABSPATH' ) || exit;

function grafik_checkout_whatsapp_quote(): void {
	$cart = WC()->cart;
	if ( ! $cart || $cart->is_empty() ) {
		return;
	}

	$lines = array( 'Hola, Grafik Publicidad. Quisiera cotizar este carrito y confirmar la cantidad o los datos para factura antes de pagar.', '' );
	foreach ( $cart->get_cart() as $item ) {
		$quantity = (int) $item['quantity'];
		// WooCommerce counts Tyvek in packs of 100. Quote the physical units.
		if ( isset( $item['grafik_units'] ) ) {
			$quantity *= 100;
		}
		$name = wp_strip_all_tags( $item['data']->get_name() );
		$line = sprintf( '• %s × %s unidades', $name, number_format_i18n( $quantity ) );
		if ( ! empty( $item['grafik_color'] ) ) {
			$line .= ' · ' . wp_strip_all_tags( $item['grafik_color'] );
		}
		$lines[] = $line;
	}

	$total = html_entity_decode( wp_strip_all_tags( $cart->get_total() ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$lines[] = '';
	$lines[] = 'Total del carrito: ' . $total;
	$url = 'https://wa.me/56985074469?text=' . rawurlencode( implode( "\n", $lines ) );
	?>
	<div class="grafik-checkout-quote">
		<p>¿Necesitas confirmar cantidad o factura antes de pagar?</p>
		<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">Enviar carrito por WhatsApp</a>
	</div>
	<?php
}
add_action( 'woocommerce_review_order_after_submit', 'grafik_checkout_whatsapp_quote' );
