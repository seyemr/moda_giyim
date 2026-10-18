<?php
/**
 * Page template.
 *
 * @package Moda_Atelier
 */

get_header();
?>
<main id="main-content" class="ma-container ma-page-content">
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<article <?php post_class(); ?>>
			<h1 class="ma-page-title"><?php the_title(); ?></h1>
			<div class="entry-content"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
