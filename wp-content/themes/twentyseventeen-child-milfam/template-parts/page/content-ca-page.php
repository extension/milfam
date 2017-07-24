<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
  <?php
  $categories = get_the_category();

  if( is_page( $categories[0]->slug )) {
    the_title( '<h1 class="entry-title">', '</h1>' );
  } else {
    if ( ! empty( $categories ) ) {
      echo '<h1 class="entry-title no-bottom-margin"><a href="/' . $categories[0]->slug . '">' . esc_html( $categories[0]->name ) . '</a></h1>';
      the_title( '<h2 class="entry-title ca-child-subhead">', '</h2>' );
    }
  }
?>


		<?php twentyseventeen_edit_link( get_the_ID() ); ?>
	</header><!-- .entry-header -->
	<div class="entry-content">
		<?php
			the_content();

			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'twentyseventeen' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->
</article><!-- #post-## -->
