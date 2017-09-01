<?php
/**
 * Template part for displaying pages on front page
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

global $twentyseventeencounter;

?>

<article id="banner<?php echo $twentyseventeencounter; ?>" <?php post_class( 'twentyseventeen-banner ' ); ?> >



	<div class="panel-content">
		<div class="wrap">
			<header class="entry-header">
				<?php twentyseventeen_edit_link( get_the_ID() ); ?>

			</header><!-- .entry-header -->

			<div class="homepage-banner">
				<?php
					/* translators: %s: Name of current post */
					the_content( sprintf(
						__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'twentyseventeen' ),
						get_the_title()
					) );
				?>
			</div><!-- .entry-content -->


		</div><!-- .wrap -->
	</div><!-- .panel-content -->

</article><!-- #post-## -->
