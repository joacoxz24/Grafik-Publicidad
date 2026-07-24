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
	<footer>
		<a class="logo" href="<?php echo esc_url( home_url( '/#inicio' ) ); ?>">
			<i><span></span></i><span>Grafik <b>Publicidad</b></span>
		</a>
		<p><?php esc_html_e( 'Pulseras para eventos, estampados y publicidad.', 'grafik-publicidad' ); ?></p>
		<div>
			<a href="<?php echo esc_url( grafik_instagram_url() ); ?>" target="_blank" rel="noopener">Instagram</a>
			<a href="<?php echo esc_url( home_url( '/#contacto' ) ); ?>">Contacto</a>
			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">Mi cuenta</a>
			<?php endif; ?>
		</div>
		<small>© <?php echo esc_html( wp_date( 'Y' ) ); ?> Grafik Publicidad · Concepción, Chile</small>
	</footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>

