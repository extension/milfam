<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'twentyseventeen' ); ?></a>

	<header id="masthead" class="site-header" role="banner">

    <div class="custom-header">
      <?php the_custom_header_markup(); ?>
    </div><!-- .custom-header -->

    <div class="branding-and-menu">
      <div class="wrap">
      <div class="site-branding">
      	<!-- <div class="wrap"> -->
      		<?php the_custom_logo(); ?>
          <?php $description = get_bloginfo( 'description', 'display' );
            if ( $description || is_customize_preview() ) : ?>
              <p class="site-description"><?php echo $description; ?></p>
            <?php endif; ?>
      	<!-- </div> -->
        <!-- .wrap -->
      </div><!-- .site-branding -->

      <?php if ( has_nav_menu( 'top' ) ) : ?>
  			<div class="navigation-top">
  				
            <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php _e( 'Top Menu', 'twentyseventeen' ); ?>">
            	<button class="menu-toggle" aria-controls="top-menu" aria-expanded="false"><?php echo twentyseventeen_get_svg( array( 'icon' => 'bars' ) ); echo twentyseventeen_get_svg( array( 'icon' => 'close' ) ); _e( 'Menu', 'twentyseventeen' ); ?></button>
            	<?php wp_nav_menu( array(
            		'theme_location' => 'top',
            		'menu_id'        => 'top-menu',
            	) ); ?>
            </nav><!-- #site-navigation -->


  			</div><!-- .navigation-top -->
  		<?php endif; ?>

      </div><!-- .wrap -->
    </div><!-- end .branding-and-menu -->







	</header><!-- #masthead -->

	<div class="site-content-contain">
		<div id="content" class="site-content">
