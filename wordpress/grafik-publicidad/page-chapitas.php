<?php defined( 'ABSPATH' ) || exit; get_header(); ?>
<main class="shell product-page" id="chapitas">
<a class="grafik-back" href="<?php echo esc_url( grafik_products_url() ); ?>">← Ver todos los productos</a>
<div class="section-title"><span>Venta por mayor y al detalle</span><h1>Chapitas personalizadas de 58 mm</h1><p>Chapitas alfiler, llavero y llavero destapador para empresas, eventos y revendedores. Elige tu formato y cantidad para ver el precio de tu pedido.</p></div>
<aside class="grafik-chapitas-benefits" aria-label="Ventajas de tu pedido">
	<p><strong>Diseños gratis</strong> · Precios por volumen · Boleta o factura · Envíos a todo Chile</p>
	<p>¿Necesitas ayuda antes de comprar? <a href="<?php echo esc_url( grafik_instagram_url() ); ?>" target="_blank" rel="noopener">Cotiza por Instagram</a>, <a href="<?php echo esc_url( 'mailto:' . GRAFIK_SALES_EMAIL ); ?>">escríbenos por correo</a> o <a href="<?php echo esc_url( home_url( '/#contacto' ) ); ?>">envía una consulta web</a>. Coordinamos el plazo según tu cantidad y diseño.</p>
</aside>
<?php if ( shortcode_exists( 'grafik_chapitas_configurator' ) ) { echo do_shortcode( '[grafik_chapitas_configurator]' ); } else { echo '<p>La personalización estará disponible pronto. Escríbenos para consultar por tu pedido.</p>'; } ?>
<?php get_template_part( 'template-parts/product-guide' ); ?>
</main>
<?php get_footer(); ?>
