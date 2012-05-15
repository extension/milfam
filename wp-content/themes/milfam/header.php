<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */
  $last_site_update = $wpdb->get_var( "SELECT post_modified FROM $wpdb->posts WHERE post_status = 'publish' ORDER BY post_modified DESC LIMIT 1" );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/scripts/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/scripts/jquery.cycle/jquery.cycle.all.min.js"></script>
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />


<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>

<?php wp_head(); ?>

<script type="text/javascript">
$(document).ready(function() {
    $('.slideshow').cycle({
		fx: 'fade', // choose your transition type, ex: fade, scrollUp, shuffle, etc...
		timeout: 4000,
		autostop: 1,
		autostopCount: 4
	});
});
</script>



</head>
<body <?php body_class(); ?>>
<div id="page">


<div id="header">
  <div id="page_actions">
    <div id="page_actions_primary">
      <p class="site_updated">Updated <?php echo date("F j, Y", strtotime($last_site_update) ); ?></p>
    
      <ul class="icons">
        <li><a href="#" title="print" onclick="window.print();return false;" ><img src="<?php bloginfo('template_url'); ?>/images/icon_print.gif" alt="Print" /></a></li>
        <li><a href="mailto:feedback@extension.org" title="email"><img src="<?php bloginfo('template_url'); ?>/images/icon_mailto.gif" alt="Email" /></a><li>
      </ul>
    </div>
    <div id="search">
	    <form method="get" id="searchform" action="<?php bloginfo('url'); ?>/">
        <input id="search_input" type="search" value="<?php the_search_query(); ?>" name="s" id="s" placeholder="Search"/>
        <input class="button gray" type="submit" id="searchsubmit" value="Go" />
      </form>
      
	  </div>
  </div>
    
  <div id="masthead">
    <h1><a href="<?php echo get_option('home'); ?>/"><span class="large"><span class="black">DoD-</span>USDA <br /><span class="black">Partner</span>ship</span> <span>Department of Defense &amp; United States Department of Agriculture</span></a></h1>
    
    <h3 class="description"><?php bloginfo('description'); ?></h3>
    
    <ul id="menu">
      <?php home_navigation("Home Navigation"); ?>
    </ul>
    </div>
</div>

