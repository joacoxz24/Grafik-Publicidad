<?php
defined( 'ABSPATH' ) || exit;
$slides = grafik_chapitas_gallery_slides();
?>
<section class="chapitas-photo grafik-photo-gallery" data-photo-gallery aria-label="Fotos de chapitas" aria-roledescription="carrusel">
	<?php foreach ( $slides as $i => $slide ) : ?>
		<figure class="grafik-photo-slide" <?php echo $i ? 'hidden' : ''; ?> role="group" aria-label="<?php echo esc_attr( ( $i + 1 ) . ' de ' . count( $slides ) ); ?>">
			<?php if ( $slide['id'] ) : ?>
				<?php echo wp_get_attachment_image( $slide['id'], 'large', false, array( 'loading' => $i ? 'lazy' : 'eager', 'alt' => get_post_meta( $slide['id'], '_wp_attachment_image_alt', true ) ?: $slide['caption'] ) ); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/chapitas-catalogo.png' ); ?>" width="1024" height="1024" alt="Imagen referencial de chapitas alfiler y llavero">
			<?php endif; ?>
			<figcaption><?php echo esc_html( $slide['caption'] ); ?></figcaption>
		</figure>
	<?php endforeach; ?>
	<?php if ( count( $slides ) > 1 ) : ?>
		<div class="grafik-photo-controls" hidden>
			<button type="button" data-photo-prev aria-label="Foto anterior">←</button>
			<span data-photo-count aria-live="off">1 / <?php echo esc_html( count( $slides ) ); ?></span>
			<button type="button" data-photo-next aria-label="Foto siguiente">→</button>
		</div>
	<?php endif; ?>
</section>
