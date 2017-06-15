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
    <div class="wrap">
      <div class="primary-logo">
        <?php the_custom_logo(); ?>
      </div>
      <div class="primary-tagline">
        <?php $description = get_bloginfo( 'description', 'display' );
          if ( $description || is_customize_preview() ) : ?>
            <p class="site-description"><?php echo $description; ?></p>
        <?php endif; ?>
      </div>
    </div><!-- .wrap -->



    <div class="custom-header">
      <?php the_custom_header_markup(); ?>
    </div><!-- .custom-header -->

    <div class="branding-and-menu">
      <div class="wrap">

      <?php if ( has_nav_menu( 'top' ) ) : ?>
  			<div class="navigation-top">
          <div class="navigation-top-menu"><nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php _e( 'Top Menu', 'twentyseventeen' ); ?>">
            	<button class="menu-toggle" aria-controls="top-menu" aria-expanded="false"><?php echo twentyseventeen_get_svg( array( 'icon' => 'bars' ) ); echo twentyseventeen_get_svg( array( 'icon' => 'close' ) ); _e( 'Menu', 'twentyseventeen' ); ?></button>
              <!-- <div id="amobile-nav"> -->
            	<?php wp_nav_menu( array(
            		'theme_location' => 'top',
            		'menu_id'        => 'top-menu',
            	) ); ?>
              <!-- </div> -->
            </nav><!-- #site-navigation -->
          </div>
          <div class="navigation-top-search">
            <?php $unique_id = esc_attr( uniqid( 'search-form-' ) ); ?>

            <form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            	<label for="<?php echo $unique_id; ?>">
            		<span class="screen-reader-text"><?php echo _x( 'Search for:', 'label', 'twentyseventeen' ); ?></span>
            	</label>
            	<input type="search" id="<?php echo $unique_id; ?>" class="search-field" placeholder="<?php echo esc_attr_x( 'Search &hellip;', 'placeholder', 'twentyseventeen' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
            	<button type="submit" class="search-submit"><?php echo twentyseventeen_get_svg( array( 'icon' => 'search' ) ); ?><span class="screen-reader-text"><?php echo _x( 'Search', 'submit button', 'twentyseventeen' ); ?></span></button>
            </form>
          </div>

  			</div><!-- .navigation-top -->

  		<?php endif; ?>

      </div><!-- .wrap -->
    </div><!-- end .branding-and-menu -->







	</header><!-- #masthead -->

	<div class="site-content-contain">
		<div id="content" class="site-content">
