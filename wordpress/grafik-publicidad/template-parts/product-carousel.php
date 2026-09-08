<?php
/** Carrusel: mismos espacios y tiempos para ambas familias. */
defined( 'ABSPATH' ) || exit;
$slides = array(
	array( 'tag' => 'Para tus eventos', 'title' => get_theme_mod( 'grafik_hero_title', 'Pulseras Tyvek' ), 'emphasis' => 'personalizadas', 'copy' => 'Identifica a tus invitados con el diseño y los colores de tu evento.', 'price' => grafik_tyvek_price_label(), 'detail' => sprintf( '%s%% de descuento desde %s unidades', get_option( 'grafik_tyvek_discount_percent', 20 ), number_format_i18n( get_option( 'grafik_tyvek_discount_threshold', 1000 ) ) ), 'image' => 'pulseras-tyvek-reales.png', 'alt' => 'Pulseras Tyvek personalizadas de distintos colores', 'href' => grafik_configurator_url( 'pulseras' ), 'cta' => 'Personalizar pulseras' ),
	array( 'tag' => 'Para tu marca', 'title' => 'Chapitas de 58 mm', 'emphasis' => 'con tu diseño', 'copy' => 'Alfiler, llavero o destapador llavero. Pequeños detalles para llevar tu marca contigo.', 'price' => grafik_chapita_price_label(), 'detail' => 'Tres formatos para personalizar', 'image' => 'chapitas-catalogo.png', 'alt' => 'Composición referencial de chapitas alfiler y llavero personalizadas', 'href' => grafik_configurator_url( 'chapitas' ), 'cta' => 'Personalizar chapitas' ),
);
?>
<section class="catalog-carousel" id="inicio" aria-label="Productos destacados" aria-roledescription="carrusel" data-grafik-carousel>
	<h1 class="grafik-sr-only">Productos personalizados para marcas y eventos</h1>
	<?php foreach ( $slides as $index => $slide ) : ?>
		<div class="hero catalog-slide" <?php echo $index ? 'hidden' : ''; ?> role="group" aria-roledescription="diapositiva" aria-label="<?php echo esc_attr( ( $index + 1 ) . ' de 2: ' . $slide['title'] ); ?>">
			<div class="hero-copy"><span class="eyebrow"><?php echo esc_html( $slide['tag'] ); ?></span><h2><?php echo esc_html( $slide['title'] ); ?><em><?php echo esc_html( $slide['emphasis'] ); ?></em></h2><p class="hero-description"><?php echo esc_html( $slide['copy'] ); ?></p><div class="hero-price"><strong><?php echo esc_html( $slide['price'] ); ?></strong><span><?php echo esc_html( $slide['detail'] ); ?></span></div><a class="cta" href="<?php echo esc_url( $slide['href'] ); ?>"><?php echo esc_html( $slide['cta'] ); ?><b aria-hidden="true">→</b></a><p class="hero-delivery">Tu diseño · Tu cantidad · Envíos a todo Chile</p></div>
			<div class="hero-art"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/' . $slide['image'] ); ?>" alt="<?php echo esc_attr( $slide['alt'] ); ?>" width="1024" height="1024"></div>
		</div>
	<?php endforeach; ?>
	<div class="carousel-controls" hidden><button type="button" data-prev aria-label="Producto anterior">←</button><button type="button" data-slide="0" aria-current="true">Pulseras Tyvek</button><button type="button" data-slide="1">Chapitas de 58 mm</button><button type="button" data-next aria-label="Producto siguiente">→</button><button type="button" data-pause aria-pressed="false">Pausar</button></div>
</section>
