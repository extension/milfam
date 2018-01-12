<?php
/**
 * The template for displaying archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header(); ?>

<div class="wrap">

	<?php if ( have_posts() ) : ?>
		<header class="page-header">
      <h1 class="archive-page-title"><?php echo single_cat_title(); ?></h1>
			<?php
				the_archive_description( '<div class="taxonomy-description">', '</div>' );
			?>
		</header><!-- .page-header -->
	<?php endif; ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
		if ( have_posts() ) : ?>
			<?php
			/* Start the Loop */
			while ( have_posts() ) : the_post();

				/*
				 * Include the Post-Format-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
				 */
				get_template_part( 'template-parts/post/content-title', get_post_format() );

			endwhile;

			the_posts_pagination( array(
				'prev_text' => twentyseventeen_get_svg( array( 'icon' => 'arrow-left' ) ) . '<span class="screen-reader-text">' . __( 'Previous page', 'twentyseventeen' ) . '</span>',
				'next_text' => '<span class="screen-reader-text">' . __( 'Next page', 'twentyseventeen' ) . '</span>' . twentyseventeen_get_svg( array( 'icon' => 'arrow-right' ) ),
				'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'twentyseventeen' ) . ' </span>',
			) );

		else :

			get_template_part( 'template-parts/post/content', 'none' );

		endif; ?>

		</main><!-- #main -->
	</div><!-- #primary -->

	<?php

  $tax = $wp_query->get_queried_object();
  $tax_slug = $tax->slug;

  // Get series ID
  $series_id = 0;
  if ( $tax_slug ) {
  	$series = get_term_by( 'slug', $tax_slug, 'series' );
  	$series_id = $series->term_id;
  }

  $image = get_option( 'ss_podcasting_data_image', '' );
  if ( $series_id ) {
  	$series_image = get_option( 'ss_podcasting_data_image_' . $series_id, 'no-image' );
  	if ( 'no-image' != $series_image ) {
  		$image = $series_image;
  	}
  }
  ?>

  <aside id="secondary" class="widget-area sidebar-general" role="complementary">
  	<?php if ( $image ) { echo "<img src=\"" . $image . "\">"; } ?>
  </aside><!-- #secondary -->

</div><!-- .wrap -->

<?php get_footer();
