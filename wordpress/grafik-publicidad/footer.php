<?php
/**
 * Pie del tema.
 *
 * @package Grafik_Publicidad
 */

defined( 'ABSPATH' ) || exit;

$checkout = function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page();
?>
<?php if ( ! $checkout ) : ?>
	<footer class="grafik-footer">
		<div class="grafik-footer-brand">
			<a class="logo" href="<?php echo esc_url( home_url( '/#inicio' ) ); ?>">
				<i><span></span></i><span>Grafik <b>Publicidad</b></span>
			</a>
			<p><?php esc_html_e( 'Pulseras, chapitas y productos personalizados con entregas presenciales en Concepción y Linares, y envíos a todo Chile.', 'grafik-publicidad' ); ?></p>
		</div>
		<section class="grafik-footer-payments" aria-labelledby="grafik-footer-payments-title">
			<h2 id="grafik-footer-payments-title">Métodos de pago</h2>
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/webpay-white.png' ); ?>" alt="Webpay de Transbank" width="1309" height="424" loading="lazy" decoding="async">
			<p>Tarjetas de débito y crédito<br>Transferencia bancaria</p>
		</section>
		<nav class="grafik-footer-links" aria-label="Enlaces del pie de página">
			<a href="<?php echo esc_url( grafik_instagram_url() ); ?>" target="_blank" rel="noopener">Instagram</a>
			<a href="<?php echo esc_url( home_url( '/#contacto' ) ); ?>">Contacto</a>
			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">Mi cuenta</a>
			<?php endif; ?>
		</nav>
		<small class="grafik-footer-copyright">© <?php echo esc_html( wp_date( 'Y' ) ); ?> Grafik Publicidad · Concepción, Chile</small>
	</footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>

