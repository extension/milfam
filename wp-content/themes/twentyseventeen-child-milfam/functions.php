<?php

function remove_footer_admin () {
  echo 'Thank you for creating with <a href="https://wordpress.org/">WordPress</a> | <a href="https://extension.org/terms-of-use/" target="_blank">Terms of Use</a>';
}
add_filter('admin_footer_text', 'remove_footer_admin');

add_filter( 'body_class','my_body_classes' );
function my_body_classes( $classes ) {
    $classes[] = 'has-sidebar';
    return $classes;
}


function show_learn_widget( $atts ) {
  $a = shortcode_atts( array(
    'key' => '',
    'tags' => '',
    'limit' => '5',
    'match_all_tags' => false,
  ), $atts );
  $a['operator'] = ($a['match_all_tags'] == "true" ? "and" : '');
  ob_start();
  include(locate_template('learn-widget.php'));
  return ob_get_clean();
}

add_shortcode( 'learn_widget', 'show_learn_widget' );

add_action( 'widgets_init', 'register_custom_sidebars' );
function  register_custom_sidebars() {
	// $title_tag = pinboard_get_option( 'widget_title_tag' );

	register_sidebar(
		array(
      'name' => 'CA Landing Page Sidebar ',
      'id' => 'sidebar-ca',
			'description' => 'Displays in in the sidebar on the CA landing page.',
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>'
		)
	);
}


function categorize_page_settings() {
  // Add category metabox to page
  register_taxonomy_for_object_type('category', 'page');
}
 // Add to the admin_init hook of your theme functions.php file
add_action( 'init', 'categorize_page_settings' );
