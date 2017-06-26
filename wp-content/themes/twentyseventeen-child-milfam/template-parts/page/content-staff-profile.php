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

<div id="content_staff_profile">
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<h1 class="entry-title"><?php echo the_title(); ?><?php echo do_shortcode("[credentials]"); ?></h1>
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

    <?php if(do_shortcode("[name]")): ?>
        <h2>
            <i class="fa fa-user" aria-hidden="true"></i>
            <?php echo the_title(); ?><?php echo do_shortcode("[credentials]"); ?>
        </h2>
    <?php endif; ?>

    <?php if(do_shortcode("[position]")): ?>
        <h3 class="person-position">
          <?php echo nl2br(get_post_meta( get_the_ID(), 'position', true )); ?>
        </h3>
    <?php endif; ?>

    <?php the_content(); ?>

    <?php if(do_shortcode("[address]")): ?>
        <p>
          <?php echo nl2br(get_post_meta( get_the_ID(), 'address', true )); ?>
        </p>
    <?php endif; ?>


    <?php //All shortcodes simply return the appropriate strings, so to print them all without the staff loop we have to echo do_shortcode() ?>
    <div class="staff-directory-profile-info <?php echo $published; ?>">
            <div class="single-staff">


                <?php if(do_shortcode("[email]")): ?>
                    <p class="email" title="E-mail address">
                        <i class="fa fa-envelope" aria-hidden="true"></i>
                        <?php echo do_shortcode("[email]"); ?>
                    </p>
                <?php endif; ?>
                <?php if(do_shortcode("[email2]")): ?>
                    <div class="email" title="E-mail address">
                        <i class="fa fa-envelope" aria-hidden="true"></i>
                        <?php echo do_shortcode("[email2]"); ?>
                    </div>
                <?php endif; ?>
                <?php if(do_shortcode("[phone_number]")): ?>
                    <p class="phone" title="Phone number">
                        <i class="fa fa-phone" aria-hidden="true"></i>
                        <?php echo do_shortcode("[phone_number]"); ?>
                    </p>
                <?php endif; ?>
            </div>
    </div>

	</div><!-- .entry-content -->
</article><!-- #post-## -->
</div><!-- content_staff_profile -->
