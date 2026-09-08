<?php defined( 'ABSPATH' ) || exit; get_header(); ?>
<main class="shell product-page" id="chapitas">
<a class="grafik-back" href="<?php echo esc_url( grafik_products_url() ); ?>">← Ver todos los productos</a>
<div class="section-title"><span>Personaliza tu pedido</span><h1>Chapitas publicitarias de 58 mm</h1><p>Alfiler, llavero o destapador llavero. Elige el formato y la cantidad para ver tu precio.</p></div>
<?php if ( shortcode_exists( 'grafik_chapitas_configurator' ) ) { echo do_shortcode( '[grafik_chapitas_configurator]' ); } else { echo '<p>La personalización estará disponible pronto. Escríbenos para consultar por tu pedido.</p>'; } ?>
</main>
<?php get_footer(); ?>
