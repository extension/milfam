<?php
/**
 * Template Name: General MFLN page
 */

get_header(); ?>

<div class="wrap ca-landing-page">
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<?php
			while ( have_posts() ) : the_post();

				get_template_part( 'template-parts/page/content', 'ca-page' );

			endwhile; // End of the loop.
			?>

		</main><!-- #main -->
	</div><!-- #primary -->
  <?php get_sidebar('general'); ?>
</div><!-- .wrap -->

<?php get_footer();
