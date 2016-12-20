<?php
/*
Template Name: Home page
*/
?><?php get_header(); ?>

	<div id="home-page">

			<?php /* The loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        	<header class="entry-header">
        		<div class="entry-meta">
        			<?php edit_post_link( __( 'Edit', 'twentythirteen' ), '<span class="edit-link">', '</span>' ); ?>
        		</div><!-- .entry-meta -->
        	</header><!-- .entry-header -->


        	<div class="home-page-content">
        		<?php
        			/* translators: %s: Name of current post */
        			the_content( sprintf(
        				__( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'twentythirteen' ),
        				the_title( '<span class="screen-reader-text">', '</span>', false )
        			) );

        			wp_link_pages( array( 'before' => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentythirteen' ) . '</span>', 'after' => '</div>', 'link_before' => '<span>', 'link_after' => '</span>' ) );
        		?>
        	</div><!-- .entry-content -->

        </article><!-- #post -->

			<?php endwhile; ?>


	</div><!-- #primary -->

<?php get_footer(); ?>
