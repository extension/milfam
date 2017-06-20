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
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		<?php twentyseventeen_edit_link( get_the_ID() ); ?>
	</header><!-- .entry-header -->
	<div class="entry-content">

    <?php if (has_post_thumbnail( $post->ID ) ){
            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
            ?>
                <img src="<?php echo $image[0]; ?>" alt="profile image">
            <?php
        } else {
            //Do nothing
        }
    ?>


    <?php //All shortcodes simply return the appropriate strings, so to print them all without the staff loop we have to echo do_shortcode() ?>
    <div class="staff-directory-profile-info <?php echo $published; ?>">
            <div class="single-staff">
                <?php if(do_shortcode("[name]")): ?>
                    <div class="name" title="Name">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        <?php echo do_shortcode("[name]"); ?>
                    </div>
                <?php endif; ?>
                <?php if(do_shortcode("[position]")): ?>
                    <div class="position" title="Position">
                        <i class="fa fa-briefcase" aria-hidden="true"></i>
                        <?php echo do_shortcode("[position]"); ?>
                    </div>
                <?php endif; ?>
                <?php if(do_shortcode("[email]")): ?>
                    <div class="email" title="E-mail address">
                        <i class="fa fa-envelope" aria-hidden="true"></i>
                        <?php echo do_shortcode("[email]"); ?>
                    </div>
                <?php endif; ?>
                <?php if(do_shortcode("[email]")): ?>
                    <div class="email" title="E-mail address">
                        <i class="fa fa-envelope" aria-hidden="true"></i>
                        <?php echo do_shortcode("	[email_(additional)]"); ?>
                    </div>
                <?php endif; ?>
                <?php if(do_shortcode("[phone_number]")): ?>
                    <div class="phone" title="Phone number">
                        <i class="fa fa-phone" aria-hidden="true"></i>
                        <?php echo do_shortcode("[phone_number]"); ?>
                    </div>
                <?php endif; ?>
            </div>
    </div>



    <?php
			the_content();

			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'twentyseventeen' ),
				'after'  => '</div>',
			) );
		?>

	</div><!-- .entry-content -->
</article><!-- #post-## -->
