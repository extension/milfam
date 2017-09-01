<?php

/*
Plugin Name: learn.extension.org Sidebar Widgets
Plugin URI: extension.org
Description: Adds a simple widget to display the learn.extension.org widget in a sidebar. Widget tag and title can be defined on the parent page.
Author: extension.org
Version: 1.0
*/

function prfx_custom_meta() {
	add_meta_box( 'prfx_meta', __( 'Learn widget preferences (learn.extension.org)', 'prfx-textdomain' ), 'prfx_meta_callback', 'page' );
}
add_action( 'add_meta_boxes', 'prfx_custom_meta' );


function prfx_meta_callback( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
	$prfx_stored_meta = get_post_meta( $post->ID );
	?>

	<p>
    <label for="meta-text-learn-widget-title" class="prfx-row-title"><?php _e( 'Widget Title', 'prfx-textdomain' )?></label>
		<input type="text" name="meta-text-learn-widget-title" id="meta-text-learn-widget-title" value="<?php if ( isset ( $prfx_stored_meta['meta-text-learn-widget-title'] ) ) echo $prfx_stored_meta['meta-text-learn-widget-title'][0]; ?>" />
  </p>
  <p>
		<label for="meta-text-learn-widget-tag" class="prfx-row-title"><?php _e( 'Learn tag', 'prfx-textdomain' )?></label>
		<input type="text" name="meta-text-learn-widget-tag" id="meta-text-learn-widget-tag" value="<?php if ( isset ( $prfx_stored_meta['meta-text-learn-widget-tag'] ) ) echo $prfx_stored_meta['meta-text-learn-widget-tag'][0]; ?>" />
	</p>

	<?php
}

function prfx_meta_save( $post_id ) {

	// Checks save status
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

	// Exits script depending on save status
	if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		return;
	}

	// Checks for input and sanitizes/saves if needed
	if( isset( $_POST[ 'meta-text-learn-widget-tag' ] ) ) {
		update_post_meta( $post_id, 'meta-text-learn-widget-tag', sanitize_text_field( $_POST[ 'meta-text-learn-widget-tag' ] ) );
	}
  if( isset( $_POST[ 'meta-text-learn-widget-title' ] ) ) {
		update_post_meta( $post_id, 'meta-text-learn-widget-title', sanitize_text_field( $_POST[ 'meta-text-learn-widget-title' ] ) );
	}
}
add_action( 'save_post', 'prfx_meta_save' );



//------------------------------------------------------------------------------
// Create a simple widget to display the learn.extension.org widget
//------------------------------------------------------------------------------

function check_for_learn_widget_tag() {
  $learn_widget_tag_meta_value = get_post_meta( get_the_ID(), 'meta-text-learn-widget-tag', true );
  return $learn_widget_tag_meta_value;
}

class Learn_Widget_Widget extends WP_Widget {

    function __construct() {

        parent::__construct(

            // base ID of the widget
            'learn_widget_widget',

            // name of the widget
            __('learn.extension.org widget'),

            // widget options
            array (
                'description' => __( 'Displays a learn.extension.org widget. Will use the tag value set on the page.')
            )

        );

    }

    function form( $instance ) {
      // placeholder to add a default learn.extension.org tag later
      echo "<p>This widget is configured on the page which displays this sidebar. The widget tag(s) and title can be set there.</p>";
    }

    function widget( $args, $instance ) {
      if ( is_category() ) {
        $cat_id = get_queried_object_id();
        $cat_id = (int)$cat_id;
	      $category = &get_category($cat_id);
        $ca_parent_page_slug = $category->slug;
      } else {
        $categories = get_the_category();
        $ca_parent_page_slug = $categories[0]->slug;
      }
      $ca_parent_page = get_page_by_path( $ca_parent_page_slug );

      $learn_widget_tag_meta_value = get_post_meta( $ca_parent_page->ID, 'meta-text-learn-widget-tag', true );
      $learn_widget_title_meta_value = get_post_meta( $ca_parent_page->ID, 'meta-text-learn-widget-title', true );
      echo "<div class='sidebar-learn-widget'>";
      echo "<h2 class='widget-title'>" . $learn_widget_title_meta_value . "</h2>";
      echo do_shortcode("[learn_widget key='exlw-ca295c9d' tags='" . $learn_widget_tag_meta_value . "' limit=3 match_all_tags=true]");
      if( get_page_by_path( $ca_parent_page_slug . '/webinars' )) {
          echo '<p><strong><a href="/' . $ca_parent_page_slug . '/webinars/">All Webinars</a></strong></p>';
      } else {
          echo '<p><strong><a href="/' . $ca_parent_page_slug . '/webinars/">All Webinars</a></strong></p>';
      }
      echo "</div>";
    }

}

function register_learn_widget_widget() {
  register_widget( 'Learn_Widget_Widget' );
}
add_action( 'widgets_init', 'register_learn_widget_widget' );
