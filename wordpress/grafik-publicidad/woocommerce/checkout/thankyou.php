<?php
/** Grafik order result. WooCommerce validates access before loading this template. */
defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-order grafik-result">
<?php if ( $order ) :
	do_action( 'woocommerce_before_thankyou', $order->get_id() );
	$status = $order->get_status();
	$paid = $order->is_paid();
	$state = 'pending';
	$title = 'Pedido recibido, pago pendiente';
	$message = 'Todavía no tenemos la confirmación del pago. Si acabas de pagar, espera unos minutos y actualiza el estado antes de intentarlo otra vez.';
	if ( 'failed' === $status ) {
		$state = 'failed'; $title = 'No se pudo completar el pago';
		$message = 'Tu pedido está registrado, pero el pago figura como fallido. Puedes volver a intentarlo con el mismo pedido. Si ves un cargo en tu cuenta, contáctanos antes de pagar nuevamente.';
	} elseif ( 'cancelled' === $status ) {
		$state = 'failed'; $title = 'Pedido cancelado';
		$message = 'Este pedido está cancelado. Si necesitas ayuda para retomarlo, contáctanos indicando el número de pedido.';
	} elseif ( 'refunded' === $status ) {
		$title = 'Pedido reembolsado'; $message = 'Este pedido figura como reembolsado. El plazo para ver el abono depende de tu medio de pago.';
	} elseif ( $paid ) {
		$state = 'success'; $title = '¡Compra completada!';
		$message = 'Gracias por confiar en Grafik Publicidad. Tu pago está confirmado. Puedes revisar los datos y el estado actual de tu pedido a continuación.';
	}
	?>
	<section class="grafik-result-card grafik-result-<?php echo esc_attr( $state ); ?>" aria-labelledby="grafik-result-title">
		<span class="grafik-result-icon" aria-hidden="true"><?php echo 'success' === $state ? '✓' : ( 'failed' === $state ? '!' : '…' ); ?></span>
		<span class="kicker">Grafik Publicidad · Tu pedido</span>
		<h1 id="grafik-result-title"><?php echo esc_html( $title ); ?></h1>
		<p><?php echo esc_html( $message ); ?></p>
		<dl class="grafik-result-summary">
			<div><dt>Número de pedido</dt><dd>#<?php echo esc_html( $order->get_order_number() ); ?></dd></div>
			<?php if ( $order->get_date_created() ) : ?><div><dt>Fecha</dt><dd><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></dd></div><?php endif; ?>
			<div><dt>Total</dt><dd><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></dd></div>
			<div><dt>Estado del pedido</dt><dd><?php echo esc_html( wc_get_order_status_name( $status ) ); ?></dd></div>
			<?php if ( $order->get_payment_method_title() ) : ?><div><dt>Medio de pago</dt><dd><?php echo esc_html( $order->get_payment_method_title() ); ?></dd></div><?php endif; ?>
		</dl>
		<?php if ( $paid ) : ?><div class="grafik-result-next"><h2>¿Qué sigue ahora?</h2><p>Revisaremos la personalización y coordinaremos contigo la aprobación del diseño antes de producirlo. Te informaremos cuando esté listo para retiro o despacho.</p></div><?php endif; ?>
		<div class="grafik-result-actions">
			<?php if ( 'failed' === $status && $order->needs_payment() ) : ?><a class="cta small" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>">Reintentar pago</a><?php endif; ?>
			<?php if ( 'pending' === $state && 'refunded' !== $status ) : ?><a class="cta small" href="<?php echo esc_url( $order->get_checkout_order_received_url() ); ?>">Actualizar estado</a><?php endif; ?>
			<a class="grafik-result-link" href="<?php echo esc_url( home_url( '/#contacto' ) ); ?>">Necesito ayuda</a>
			<a class="grafik-result-link" href="<?php echo esc_url( grafik_products_url() ); ?>">Volver a productos</a>
		</div>
	</section>
	<?php
	// Preserve gateway instructions, order items, delivery details and WooCommerce extensions.
	do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
	do_action( 'woocommerce_thankyou', $order->get_id() );
else : ?>
	<section class="grafik-result-card"><h1>No pudimos mostrar este pedido</h1><p>Abre el enlace de confirmación de tu compra o accede a tu cuenta para consultar tus pedidos.</p><a class="cta small" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">Mi cuenta</a></section>
<?php endif; ?>
</div>
