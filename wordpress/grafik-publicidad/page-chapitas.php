<?php defined( 'ABSPATH' ) || exit; get_header(); ?>
<main class="shell product-page" id="chapitas">
<a class="grafik-back" href="<?php echo esc_url( grafik_products_url() ); ?>">← Ver todos los productos</a>
<div class="section-title"><span>Venta por mayor y al detalle</span><h1>Chapitas personalizadas de 58 mm de diámetro</h1><p>Chapitas alfiler, llavero y llavero destapador para empresas, eventos y revendedores. Elige tu formato y cantidad para ver el precio de tu pedido.</p></div>
<?php if ( shortcode_exists( 'grafik_chapitas_configurator' ) ) { echo do_shortcode( '[grafik_chapitas_configurator]' ); } else { echo '<p>La personalización estará disponible pronto. Escríbenos para consultar por tu pedido.</p>'; } ?>
<aside class="grafik-chapitas-benefits" aria-label="Ventajas de tu pedido">
	<template id="grafik-chapitas-quote">
		<a class="grafik-chapitas-quote" href="<?php echo esc_url( GRAFIK_WHATSAPP_URL ); ?>" target="_blank" rel="noopener noreferrer">
			<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true" focusable="false"><path d="M20.52 3.45A11.8 11.8 0 0 0 2.02 17.63L.35 23.75l6.26-1.64A11.8 11.8 0 0 0 20.52 3.45Zm-8.35 18.1a9.8 9.8 0 0 1-5-1.37l-.36-.21-3.72.97.99-3.62-.24-.38a9.8 9.8 0 1 1 8.33 4.61Zm5.38-7.34c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.66.15-.2.3-.76.96-.93 1.16-.17.2-.34.22-.64.07-1.75-.87-2.9-1.55-4.05-3.53-.31-.53.31-.49.88-1.64.1-.2.05-.37-.02-.52-.08-.15-.67-1.6-.91-2.2-.24-.57-.49-.49-.67-.5h-.57c-.2 0-.52.07-.79.37-.27.29-1.03 1.01-1.03 2.46s1.06 2.85 1.2 3.05c.15.2 2.08 3.18 5.04 4.46 1.87.81 2.6.88 3.53.74.57-.09 1.75-.72 2-1.41.24-.69.24-1.28.17-1.41-.07-.12-.27-.19-.57-.34Z"/></svg>
			Cotizar por WhatsApp
		</a>
	</template>
	<p><strong>Diseños gratis</strong> · Precios por volumen · Boleta o factura · Envíos a todo Chile</p>
	<p>¿Necesitas ayuda antes de comprar? <a href="<?php echo esc_url( grafik_instagram_url() ); ?>" target="_blank" rel="noopener">Cotiza por Instagram</a>, <a href="<?php echo esc_url( 'mailto:' . GRAFIK_SALES_EMAIL ); ?>">escríbenos por correo</a> o <a href="<?php echo esc_url( home_url( '/#contacto' ) ); ?>">envía una consulta web</a>. Coordinamos el plazo según tu cantidad y diseño.</p>
</aside>
<?php get_template_part( 'template-parts/product-guide' ); ?>
</main>
<?php get_footer(); ?>
