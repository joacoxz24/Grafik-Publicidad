<?php
/**
 * Contenedor principal de WooCommerce.
 *
 * @package Grafik_Publicidad
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main class="grafik-content grafik-woocommerce shell">
	<?php if ( function_exists( 'is_shop' ) && is_shop() ) : ?>
		<section class="grafik-products-hero">
			<span class="kicker">Tienda Grafik</span>
			<h1>Productos personalizados</h1>
			<p>Encuentra aquí todos nuestros productos. Iremos agregando nuevas opciones y podrás administrarlas directamente desde WooCommerce.</p>
		</section>
		<section class="grafik-featured-product">
			<div>
				<span class="kicker">Producto destacado</span>
				<h2>Pulseras Tyvek personalizadas</h2>
				<p>Elige cantidad, color, detalles y adjunta tu logo o diseño. Desde 100 unidades.</p>
				<strong>100 unidades · $10.500</strong>
			</div>
			<a class="cta small" href="<?php echo esc_url( home_url( '/#personaliza' ) ); ?>">Personalizar <b>→</b></a>
		</section>
	<?php endif; ?>
	<?php woocommerce_content(); ?>
</main>
<?php get_footer(); ?>
