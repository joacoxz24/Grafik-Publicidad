<?php defined( 'ABSPATH' ) || exit; get_header(); ?>
<main class="shell product-page" id="personaliza">
<a class="grafik-back" href="<?php echo esc_url( grafik_products_url() ); ?>">← Ver todos los productos</a>
<div class="section-title"><span>Personaliza tu pedido</span><h1>Pulseras Tyvek personalizadas</h1><p>Selecciona cantidad, color y adjunta tu logo o diseño de referencia.</p></div>
<?php if ( shortcode_exists( 'grafik_tyvek_configurator' ) ) { echo do_shortcode( '[grafik_tyvek_configurator]' ); } else { echo '<p>La personalización estará disponible pronto. Escríbenos para consultar por tu pedido.</p>'; } ?>
</main>
<?php get_footer(); ?>
