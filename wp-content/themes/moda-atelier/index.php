<?php
/**
 * Default template.
 *
 * @package Moda_Atelier
 */

get_header();
?>
<main id="main-content" class="ma-container ma-page-content">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<article <?php post_class(); ?>>
				<h1 class="ma-page-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
				<div class="entry-content"><?php the_excerpt(); ?></div>
			</article>
		<?php endwhile; ?>
		<?php the_posts_navigation(); ?>
	<?php else : ?>
		<h1 class="ma-page-title"><?php esc_html_e( 'Nothing found', 'moda-atelier' ); ?></h1>
		<p><?php esc_html_e( 'Try another search or return to the homepage.', 'moda-atelier' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
