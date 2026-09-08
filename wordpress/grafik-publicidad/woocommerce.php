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
			<p>Elige entre pulseras Tyvek y chapitas publicitarias. Personaliza tu diseño y cotiza al instante.</p>
		</section>
		<?php get_template_part( 'template-parts/product-families' ); ?>

	<?php endif; ?>
	<?php woocommerce_content(); ?>
</main>
<?php get_footer(); ?>
