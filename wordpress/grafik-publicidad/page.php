<?php
/**
 * Plantilla de páginas.
 *
 * @package Grafik_Publicidad
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main class="grafik-content shell">
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<article <?php post_class( 'grafik-entry' ); ?>>
			<?php if ( ! is_cart() && ! is_checkout() && ! is_account_page() ) : ?>
				<h1><?php the_title(); ?></h1>
			<?php endif; ?>
			<?php the_content(); ?>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>

