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
if ( ! $instagram_shortcode && shortcode_exists( 'instagram-feed' ) ) {
	$instagram_shortcode = '[instagram-feed feed=1]';
}

?>
<main>
	<?php get_template_part( 'template-parts/product-carousel' ); ?>

	<div class="strip">
		<span>✦ Impresión personalizada</span>
		<span>✦ Marcas y eventos</span>
		<span>✦ Despacho nacional</span>
		<span>✦ Atención directa</span>
	</div>

	<section class="shell catalog-families" id="productos">
		<div class="section-title"><span>Elige tu producto</span><h2>Tu idea, en el formato que quieras</h2><p>Productos personalizados para eventos, marcas y promociones.</p></div>
		<?php get_template_part( 'template-parts/product-families' ); ?>
	</section>

	<section class="steps" id="comprar">
		<div class="shell">
			<div class="section-title">
				<span>Simple y transparente</span>
				<h2>De tu idea a tus manos en 4 pasos</h2>
			</div>
			<div class="step-grid">
				<article><span>01</span><h3>Personaliza</h3><p>Elige tu producto, cantidad y adjunta tu referencia.</p></article>
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

		<?php endif; ?>
	</section>

	<section class="shell contact" id="contacto">
		<div>
			<span class="kicker">Hablemos de tu pedido</span>
			<h2>¿Necesitas ayuda antes de comprar?</h2>
			<p>Cuéntanos qué producto necesitas, la cantidad y tu ciudad.</p>
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
						<option value="5">5 unidades</option><option value="10">10 unidades</option><option value="50">50 unidades</option><option value="100">100 unidades</option>
						<option value="500" selected>500 unidades</option>
						<option value="1000">1.000 unidades</option>
						<option value="2000">2.000 o más</option>
					</select>
				</label>
			</div>
			<label>Cuéntanos sobre tu pedido<textarea required name="message" rows="5" placeholder="Producto, fecha, ciudad y detalles..."></textarea></label>
			<label class="check"><input type="checkbox" name="marketing" value="1"> Quiero recibir promociones y novedades.</label>
			<button class="cta full" type="submit">Enviar consulta <b>→</b></button>
		</form>
	</section>
</main>
<?php get_footer(); ?>
