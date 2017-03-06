<?php

function remove_footer_admin () {
  echo 'Thank you for creating with <a href="https://wordpress.org/">WordPress</a> | <a href="http://www.extension.org/main/termsofuse" target="_blank">Terms of Use</a>';
}
add_filter('admin_footer_text', 'remove_footer_admin');



if ( ! function_exists( 'twentythirteen_entry_meta' ) ) :
/**
 * Print HTML with meta information for current post: categories, tags, permalink, author, and date.
 *
 * Create your own twentythirteen_entry_meta() to override in a child theme.
 *
 * @since Twenty Thirteen 1.0
 */
function twentythirteen_entry_meta() {
	if ( is_sticky() && is_home() && ! is_paged() )
		echo '<span class="featured-post">' . esc_html__( 'Sticky', 'twentythirteen' ) . '</span>';

	if ( ! has_post_format( 'link' ) && 'post' == get_post_type() )
		twentythirteen_entry_date();

	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'twentythirteen' ) );
	if ( $categories_list ) {
		echo '<span class="categories-links">' . $categories_list . '</span>';
	}

	// Post author
	if ( 'post' == get_post_type() ) {
		printf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_attr( sprintf( __( 'View all posts by %s', 'twentythirteen' ), get_the_author() ) ),
			get_the_author()
		);
	}
}
endif;


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
