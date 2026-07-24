<?php
/**
 * Plantilla general.
 *
 * @package Grafik_Publicidad
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main class="grafik-content shell">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<article <?php post_class( 'grafik-entry' ); ?>>
				<h1><?php the_title(); ?></h1>
				<?php the_content(); ?>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No encontramos contenido.', 'grafik-publicidad' ); ?></p>
	<?php endif; ?>
</main>
<?php get_footer(); ?>

