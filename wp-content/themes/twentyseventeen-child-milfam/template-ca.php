<?php
/**
 * Template Name: CA Landing
 */

get_header(); ?>

<div class="wrap">
	<div id="primary" class="content-area ca-landing-page">
		<main id="main" class="site-main" role="main">
			<?php
			while ( have_posts() ) : the_post();

				get_template_part( 'template-parts/page/content', 'page' );

			endwhile; // End of the loop.
			?>

		</main><!-- #main -->
	</div><!-- #primary -->
  <?php get_sidebar('ca'); ?>
</div><!-- .wrap -->

<?php get_footer();
