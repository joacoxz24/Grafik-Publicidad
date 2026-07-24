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
	<?php woocommerce_content(); ?>
</main>
<?php get_footer(); ?>

