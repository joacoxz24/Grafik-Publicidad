<?php
/**
 * Portada de Grafik Publicidad.
 *
 * @package Grafik_Publicidad
 */

defined( 'ABSPATH' ) || exit;
get_header();

$instagram = grafik_instagram_url();
$instagram_shortcode = trim( (string) get_theme_mod( 'grafik_instagram_shortcode', '' ) );
$hero      = get_theme_mod( 'grafik_hero_title', 'Pulseras Tyvek personalizadas para tu evento' );
?>
<main>
	<section class="hero" id="inicio">
		<div class="hero-copy">
			<span class="eyebrow">Hechas para tu evento</span>
			<h1>
				<?php
				$hero_parts = explode( 'personalizadas', $hero, 2 );
				echo esc_html( $hero_parts[0] );
				if ( isset( $hero_parts[1] ) ) {
					echo '<em>personalizadas</em>' . esc_html( $hero_parts[1] );
				}
				?>
			</h1>
			<div class="hero-price">
				<strong>100 unidades · $10.500</strong>
				<span><b>20% DCTO.</b> desde 1.000 unidades</span>
			</div>
			<a class="cta" href="#personaliza">Personalizar ahora <b>→</b></a>
			<div class="benefits">
				<span>↑ Carga tu diseño</span>
				<span>◷ Producción rápida</span>
				<span>▱ Envíos a todo Chile</span>
			</div>
		</div>
		<div class="hero-art" aria-label="Pulseras Tyvek reales personalizadas">
			<img
				src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/pulseras-tyvek-reales.png' ); ?>"
				alt="Pulseras Tyvek reales impresas en colores fucsia, azul, rojo y amarillo"
			>
			<div class="material"><b>TYVEK®</b><span>Resistentes al agua</span></div>
		</div>
	</section>

	<div class="strip">
		<span>✦ Impresión personalizada</span>
		<span>✦ Control de acceso</span>
		<span>✦ Despacho nacional</span>
		<span>✦ Atención directa</span>
	</div>

	<section class="shell configurator" id="personaliza">
		<div class="section-title">
			<span>Tu pulsera, a tu manera</span>
			<h2>Personaliza y cotiza al instante</h2>
			<p>Selecciona cantidad, color y adjunta tu logo o diseño de referencia.</p>
		</div>
		<?php if ( shortcode_exists( 'grafik_tyvek_configurator' ) ) : ?>
			<?php echo do_shortcode( '[grafik_tyvek_configurator]' ); ?>
		<?php else : ?>
			<div class="grafik-plugin-required">
				<p>Activa el plugin <strong>Grafik Configurador Tyvek</strong> para habilitar la compra.</p>
			</div>
		<?php endif; ?>
	</section>

	<section class="steps" id="comprar">
		<div class="shell">
			<div class="section-title">
				<span>Simple y transparente</span>
				<h2>De tu idea al evento en 4 pasos</h2>
			</div>
			<div class="step-grid">
				<article><span>01</span><h3>Personaliza</h3><p>Elige cantidad, color y adjunta tu referencia.</p></article>
				<article><span>02</span><h3>Completa tus datos</h3><p>Elige retiro o envío y revisa el detalle de tu compra.</p></article>
				<article><span>03</span><h3>Paga con Flow</h3><p>Selecciona el método disponible y paga de forma segura.</p></article>
				<article><span>04</span><h3>Confirma el diseño</h3><p>Revisamos tu pedido y confirmamos contigo cómo quedará el diseño final.</p></article>
			</div>
		</div>
	</section>

	<section class="shell instagram">
		<div class="instagram-head">
			<div>
				<span class="kicker">Síguenos en Instagram</span>
				<h2>@grafikpublicidad.cl</h2>
				<p>Trabajos reales, nuevos colores y pedidos recién terminados.</p>
			</div>
			<a class="outline" href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener">Ver perfil en Instagram ↗</a>
		</div>
		<?php if ( $instagram_shortcode && shortcode_exists( 'instagram-feed' ) ) : ?>
			<div class="grafik-instagram-live">
				<?php echo do_shortcode( $instagram_shortcode ); ?>
			</div>
			<small>Este contenido se actualiza automáticamente desde Instagram.</small>
		<?php else : ?>
			<div
				class="insta-grid"
				style="--grafik-instagram-image:url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/instagram-grafik.png' ); ?>')"
			>
				<?php for ( $post = 1; $post <= 4; $post++ ) : ?>
					<a class="insta-post post-<?php echo esc_attr( (string) $post ); ?>" href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener">
						<span>Ver en Instagram ↗</span>
					</a>
				<?php endfor; ?>
			</div>
			<small>Conecta el plugin de Instagram y pega su shortcode en el Personalizador para activar la actualización automática.</small>
		<?php endif; ?>
	</section>

	<section class="shell contact" id="contacto">
		<div>
			<span class="kicker">Hablemos de tu pedido</span>
			<h2>¿Necesitas ayuda antes de comprar?</h2>
			<p>Cuéntanos la fecha de tu evento, cantidad y ciudad.</p>
			<ul>
				<li>Atención personalizada</li>
				<li>Envíos a todo Chile</li>
				<li>Diseño sujeto a aprobación</li>
			</ul>
		</div>
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="grafik_contact">
			<?php wp_nonce_field( 'grafik_contact', 'grafik_contact_nonce' ); ?>
			<?php if ( isset( $_GET['contacto'] ) && 'enviado' === sanitize_key( wp_unslash( $_GET['contacto'] ) ) ) : ?>
				<p class="grafik-form-success">Mensaje enviado. Te responderemos lo antes posible.</p>
			<?php elseif ( isset( $_GET['contacto'] ) && 'error' === sanitize_key( wp_unslash( $_GET['contacto'] ) ) ) : ?>
				<p class="grafik-form-error">No pudimos enviar el mensaje. Revisa tus datos e inténtalo otra vez.</p>
			<?php endif; ?>
			<div class="row">
				<label>Nombre<input required name="name" autocomplete="name" placeholder="Tu nombre"></label>
				<label>Correo<input required name="email" type="email" autocomplete="email" placeholder="tu@correo.cl"></label>
			</div>
			<div class="row">
				<label>Teléfono<input name="phone" type="tel" autocomplete="tel" placeholder="+56 9"></label>
				<label>
					Cantidad estimada
					<select name="quantity">
						<option value="100">100 unidades</option>
						<option value="500" selected>500 unidades</option>
						<option value="1000">1.000 unidades</option>
						<option value="2000">2.000 o más</option>
					</select>
				</label>
			</div>
			<label>Cuéntanos sobre tu evento<textarea required name="message" rows="5" placeholder="Fecha, ciudad y detalles..."></textarea></label>
			<label class="check"><input type="checkbox" name="marketing" value="1"> Quiero recibir promociones y novedades.</label>
			<button class="cta full" type="submit">Enviar consulta <b>→</b></button>
		</form>
	</section>
</main>
<?php get_footer(); ?>
