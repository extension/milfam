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

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

      <?php if ( have_posts() ) : ?>
    		<header class="page-header">
          <?php
          $categories = get_the_category();

          if( is_page( $categories[0]->slug )) {
            // nothing for now
          } else {
            if ( ! empty( $categories ) ) {
              echo '<h1 class="entry-title ca-main-title entry-title no-bottom-margin"><a href="/' . $categories[0]->slug . '">' . esc_html( $categories[0]->name ) . '</a></h1>';
            }
          }
          ?>
          <h2 class="entry-title ca-child-subhead">Blog Posts</h2>
    			<?php
    				the_archive_description( '<div class="taxonomy-description">', '</div>' );
    			?>
    		</header><!-- .page-header -->
    	<?php endif; ?>

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
    // The "List Category Posts" plugin uses the main query to get the current categories.
    // If called from a category page where the last item has multiple categories, this can result in the wrong current category.
    // To fix, override the global $category variable for archive pages
    $category = get_category( get_query_var( 'cat' ) );
    set_query_var( 'category', $category );
  ?>

	<?php get_sidebar('ca'); ?>
</div><!-- .wrap -->

<?php get_footer();
