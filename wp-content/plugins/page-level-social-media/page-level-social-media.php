<?php

/*
Plugin Name: Page Level Social Media Links
Plugin URI: extension.org
Description: Adds a widget to display social media icons and URLs on a per-page level. URLs are defined on the parent page.
Author: extension.org
Version: 1.0
*/

function page_level_social_media_custom_meta() {
	add_meta_box( 'page_level_social_media_meta', __( 'Set custom social media links', 'page_level_social_media-textdomain' ), 'page_level_social_media_meta_callback', 'page' );
}
add_action( 'add_meta_boxes', 'page_level_social_media_custom_meta' );


function page_level_social_media_meta_callback( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'page_level_social_media_nonce' );
	$page_level_social_media_stored_meta = get_post_meta( $post->ID );
	?>

	<p>
    <label for="meta-text-facebook-link" class="page_level_social_media-row-title"><?php _e( 'Facebook', 'page_level_social_media-textdomain' )?></label>
		<input class="plsm-input" type="text" name="meta-text-facebook-link" id="meta-text-facebook-link" value="<?php if ( isset ( $page_level_social_media_stored_meta['meta-text-facebook-link'] ) ) echo $page_level_social_media_stored_meta['meta-text-facebook-link'][0]; ?>" />
  </p>

  <p>
    <label for="meta-text-twitter-link" class="page_level_social_media-row-title"><?php _e( 'Twitter', 'page_level_social_media-textdomain' )?></label>
		<input class="plsm-input" type="text" name="meta-text-twitter-link" id="meta-text-twitter-link" value="<?php if ( isset ( $page_level_social_media_stored_meta['meta-text-twitter-link'] ) ) echo $page_level_social_media_stored_meta['meta-text-twitter-link'][0]; ?>" />
  </p>

  <p>
    <label for="meta-text-linkedin-link" class="page_level_social_media-row-title"><?php _e( 'Linkedin', 'page_level_social_media-textdomain' )?></label>
		<input class="plsm-input" type="text" name="meta-text-linkedin-link" id="meta-text-linkedin-link" value="<?php if ( isset ( $page_level_social_media_stored_meta['meta-text-linkedin-link'] ) ) echo $page_level_social_media_stored_meta['meta-text-linkedin-link'][0]; ?>" />
  </p>

  <p>
    <label for="meta-text-youtube-link" class="page_level_social_media-row-title"><?php _e( 'YouTube', 'page_level_social_media-textdomain' )?></label>
		<input class="plsm-input" type="text" name="meta-text-youtube-link" id="meta-text-youtube-link" value="<?php if ( isset ( $page_level_social_media_stored_meta['meta-text-youtube-link'] ) ) echo $page_level_social_media_stored_meta['meta-text-youtube-link'][0]; ?>" />
  </p>

	<?php
}

function page_level_social_media_meta_save( $post_id ) {

	// Checks save status
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST[ 'page_level_social_media_nonce' ] ) && wp_verify_nonce( $_POST[ 'page_level_social_media_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

	// Exits script depending on save status
	if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		return;
	}

	// Checks for input and sanitizes/saves if needed
	if( isset( $_POST[ 'meta-text-facebook-link' ] ) ) {
		update_post_meta( $post_id, 'meta-text-facebook-link', sanitize_text_field( $_POST[ 'meta-text-facebook-link' ] ) );
	}
  if( isset( $_POST[ 'meta-text-twitter-link' ] ) ) {
		update_post_meta( $post_id, 'meta-text-twitter-link', sanitize_text_field( $_POST[ 'meta-text-twitter-link' ] ) );
	}
  if( isset( $_POST[ 'meta-text-linkedin-link' ] ) ) {
		update_post_meta( $post_id, 'meta-text-linkedin-link', sanitize_text_field( $_POST[ 'meta-text-linkedin-link' ] ) );
	}
  if( isset( $_POST[ 'meta-text-youtube-link' ] ) ) {
		update_post_meta( $post_id, 'meta-text-youtube-link', sanitize_text_field( $_POST[ 'meta-text-youtube-link' ] ) );
	}
}
add_action( 'save_post', 'page_level_social_media_meta_save' );



//------------------------------------------------------------------------------
// Create a simple widget to display the social media links
//------------------------------------------------------------------------------


class Page_Level_Social_Media_Widget extends WP_Widget {

    function __construct() {

        parent::__construct(

            // base ID of the widget
            'page_level_social_media_widget',

            // name of the widget
            __('page level social media widget'),

            // widget options
            array (
                'description' => __( 'Displays a social media widget. Uses the social media value set on the page.')
            )

        );

    }

    function form( $instance ) {
      // placeholder to add a default learn.extension.org tag later
      echo "<p>This widget is configured on the page which displays this sidebar. The widget tag(s) and title can be set there.</p>";
    }

    function widget( $args, $instance ) {
      $theme_path = get_stylesheet_directory_uri();
      $meta_text_facebook_link = get_post_meta( get_the_ID(), 'meta-text-facebook-link', true );
      $meta_text_twitter_link = get_post_meta( get_the_ID(), 'meta-text-twitter-link', true );
      $meta_text_linkedin_link = get_post_meta( get_the_ID(), 'meta-text-linkedin-link', true );
      $meta_text_youtube_link = get_post_meta( get_the_ID(), 'meta-text-youtube-link', true );

      echo "<div class='sidebar-page-level-social-media'>";
      echo "<ul class='social-media-list'>";
      if (!empty($meta_text_facebook_link)) {
          echo "<li class='social-media-item'><a href='" . $meta_text_facebook_link . "'><img src='" . $theme_path . "/images/social-media-icon-facebook.png' /></a></li>";
      }
      if (!empty($meta_text_twitter_link)) {
        echo "<li class='social-media-item'><a href='" . $meta_text_twitter_link . "'><img src='" . $theme_path . "/images/social-media-icon-twitter.png' /></a></li>";
      }
      if (!empty($meta_text_linkedin_link)) {
        echo "<li class='social-media-item'><a href='" . $meta_text_linkedin_link . "'><img src='" . $theme_path . "/images/social-media-icon-linkedin.png' /></a></li>";
      }
      if (!empty($meta_text_youtube_link)) {
        echo "<li class='social-media-item'><a href='" . $meta_text_youtube_link . "'><img src='" . $theme_path . "/images/social-media-icon-youtube.png' /></a></li>";
      }
      echo "</ul>";
      echo "</div>";
    }

}

function register_page_level_social_media_widget() {
  register_widget( 'Page_Level_Social_Media_Widget' );
}
add_action( 'widgets_init', 'register_page_level_social_media_widget' );

function load_plsm_admin_style() {
        wp_register_style( 'plsm_admin_css', plugin_dir_url( __FILE__ ) . '/admin-plsm.css', false, '1.0.0' );
        wp_enqueue_style( 'plsm_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'load_plsm_admin_style' );
