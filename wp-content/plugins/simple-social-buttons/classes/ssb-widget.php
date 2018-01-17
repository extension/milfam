<?php

/**
 *  Widget class for social share follower widget
 *
 * @sicne 2.0.2
 *
 *  Class Ssb_Follower_Widget
 */
class Ssb_Follower_Widget extends WP_Widget {

	/**
	 * Google console api key for google+ and youtube api for getting follower and subscriber
	 * @var string
	 */
	private $api_key = 'AIzaSyBkQDWiRWxWKUoavuajUSAs28ld0Pdx8a4';

	/**
	 * Transient Time
	 *
	 * 43200 = 12 Hours
	 */
	private $cache_time = 43200;

	/**
	 * Register ssb widget with WordPress.
	 *
	 * @since 2.0.2
	 */
	function __construct() {
		$widget_ops = array(
			'description' => 'Display Follow Button For your site',
		);
		parent::__construct( 'ssb_widget', 'Social Follow Widget', $widget_ops );

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget( $args, $instance ) {
		extract( $args );
		$display = '1';

		$widget_title           = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance );

		$show_facebook          = $instance['show_facebook'];
		$show_twitter           = $instance['show_twitter'];
		$show_google_plus       = $instance['show_google_plus'];
		$show_youtube           = $instance['show_youtube'];
		$show_pinterest         = $instance['show_pinterest'];

		$facebook_id            = $instance['facebook_id'];
		$facebook_show_counter  = $instance['facebook_show_counter'];
		$facebook_text          = $instance['facebook_text'];
		$facebook_access_token  = $instance['facebook_access_token'];

		$twitter_id             = $instance['twitter'];
		$twitter_show_counter   = $instance['twitter_show_counter'];
		$twitter_text           = $instance['twitter_text'];
		$twitter_api_key        = $instance['twitter_api_key'];
		$twitter_secret_key     = $instance['twitter_secret_key'];

		$google_id              = $instance['google'];
		$google_show_counter    = $instance['google_show_counter'];
		$google_text            = $instance['google_text'];

		$youtube_id             = $instance['youtube'];
		$youtube_show_counter   = $instance['youtube_show_counter'];
		$youtube_text           = $instance['youtube_text'];
		$youtube_type           = $instance['youtube_type'];

		$pinterest_id           = $instance['pinterest'];
		$pinterest_show_counter = $instance['pinterest_show_counter'];
		$pinterest_api_key      = $instance['pinterest_api_key'];
		$pinterest_text         = $instance['pinterest_text'];

		$fb_likes               = $this->get_facebook_likes_count( $facebook_id, $facebook_access_token, $facebook_show_counter );
		$twitter_follower       = $this->get_twitter_followers( $twitter_id, $twitter_api_key, $twitter_secret_key, $twitter_show_counter );
		$google_follower        = $this->get_google_plus_follower( $google_id, $google_show_counter );
		$youtube_subscriber     = $this->get_youtube_subscriber( $youtube_id, $youtube_show_counter, $youtube_type );
		$pinterest_follower     = $this->get_pinterest_followers( $pinterest_api_key, $pinterest_show_counter );

		include SSB_PLUGIN_DIR . '/inc/ssb-widget-front.php';
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$display = '1';

		//first time run when instance create
		if ( 0 == count( $instance ) ) {
			$instance['facebook_text']  = __( 'Follow us on Facebook', 'simple-social-buttons' );
			$instance['google_text']    = __( 'Follow us on Google+', 'simple-social-buttons' );
			$instance['youtube_text']   = __( 'Subscribe us on Youtube', 'simple-social-buttons' );
			$instance['twitter_text']   = __( 'Follow us on Twitter', 'simple-social-buttons' );
			$instance['pinterest_text'] = __( 'Pin us on Pinterest', 'simple-social-buttons' );

		}


		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Follow Us', 'simple-social-buttons' );

		$show_facebook    = ! empty( $instance['show_facebook'] ) ? $instance['show_facebook'] : '';
		$show_twitter     = ! empty( $instance['show_twitter'] ) ? $instance['show_twitter'] : '';
		$show_google_plus = ! empty( $instance['show_google_plus'] ) ? $instance['show_google_plus'] : '';
		$show_youtube     = ! empty( $instance['show_youtube'] ) ? $instance['show_youtube'] : '';
		$show_pinterest   = ! empty( $instance['show_pinterest'] ) ? $instance['show_pinterest'] : '';

		$facebook_id           = ! empty( $instance['facebook_id'] ) ? $instance['facebook_id'] : '';
		$facebook_show_counter = ! empty( $instance['facebook_show_counter'] ) ? $instance['facebook_show_counter'] : '';
		$facebook_text         = ! empty( $instance['facebook_text'] ) ? $instance['facebook_text'] : '';
		$facebook_app_id       = ! empty( $instance['facebook_app_id'] ) ? $instance['facebook_app_id'] : '';
		$facebook_security_key = ! empty( $instance['facebook_security_key'] ) ? $instance['facebook_security_key'] : '';
		$facebook_access_token = ! empty( $instance['facebook_access_token'] ) ? $instance['facebook_access_token'] : '';

		$twitter              = ! empty( $instance['twitter'] ) ? $instance['twitter'] : '';
		$twitter_api_key      = ! empty( $instance['twitter_api_key'] ) ? $instance['twitter_api_key'] : '';
		$twitter_show_counter = ! empty( $instance['twitter_show_counter'] ) ? $instance['twitter_show_counter'] : '';
		$twitter_text         = ! empty( $instance['twitter_text'] ) ? $instance['twitter_text'] : '';
		$twitter_secret_key   = ! empty( $instance['twitter_secret_key'] ) ? $instance['twitter_secret_key'] : '';

		$google              = ! empty( $instance['google'] ) ? $instance['google'] : '';
		$google_show_counter = ! empty( $instance['google_show_counter'] ) ? $instance['google_show_counter'] : '';
		$google_text         = ! empty( $instance['google_text'] ) ? $instance['google_text'] : '';

		$youtube              = ! empty( $instance['youtube'] ) ? $instance['youtube'] : '';
		$youtube_text         = ! empty( $instance['youtube_text'] ) ? $instance['youtube_text'] : '';
		$youtube_type         = ! empty( $instance['youtube_type'] ) ? $instance['youtube_type'] : '';
		$youtube_show_counter = ! empty( $instance['youtube_show_counter'] ) ? $instance['youtube_show_counter'] : '';

		$pinterest              = ! empty( $instance['pinterest'] ) ? $instance['pinterest'] : '';
		$pinterest_text         = ! empty( $instance['pinterest_text'] ) ? $instance['pinterest_text'] : '';
		$pinterest_show_counter = ! empty( $instance['pinterest_show_counter'] ) ? $instance['pinterest_show_counter'] : '';
		$pinterest_api_key      = ! empty( $instance['pinterest_api_key'] ) ? $instance['pinterest_api_key'] : '';

		include SSB_PLUGIN_DIR . '/inc/ssb-widget-fields.php';

	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		// delete transiant wheb user update widget settings.
		delete_transient( 'ssb_follow_facebook_counter' );
		delete_transient( 'ssb_follow_twitter_counter' );
		delete_transient( 'ssb_follow_google_counter' );
		delete_transient( 'ssb_follow_youtube_counter' );
		delete_transient( 'ssb_follow_pinterest_counter' );

		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		$instance['show_facebook']    = ! empty( $new_instance['show_facebook'] ) ? strip_tags( $new_instance['show_facebook'] ) : '0';
		$instance['show_twitter']     = ! empty( $new_instance['show_twitter'] ) ? strip_tags( $new_instance['show_twitter'] ) : '0';
		$instance['show_google_plus'] = ! empty( $new_instance['show_google_plus'] ) ? strip_tags( $new_instance['show_google_plus'] ) : '0';
		$instance['show_youtube']     = ! empty( $new_instance['show_youtube'] ) ? strip_tags( $new_instance['show_youtube'] ) : '0';
		$instance['show_pinterest']   = ! empty( $new_instance['show_pinterest'] ) ? strip_tags( $new_instance['show_pinterest'] ) : '0';

		$instance['facebook_id']           = sanitize_text_field( wp_unslash( $new_instance['facebook_id'] ) );
		$instance['facebook_app_id']       = sanitize_text_field( wp_unslash( $new_instance['facebook_app_id'] ) );
		$instance['facebook_security_key'] = sanitize_text_field( wp_unslash( $new_instance['facebook_security_key'] ) );
		$instance['facebook_access_token'] = sanitize_text_field( wp_unslash( $new_instance['facebook_access_token'] ) );
		$instance['facebook_show_counter'] = ( ! empty( $new_instance['facebook_show_counter'] ) ) ? strip_tags( $new_instance['facebook_show_counter'] ) : '0';
		$instance['facebook_text']         = sanitize_text_field( wp_unslash( $new_instance['facebook_text'] ) );

		$instance['twitter']              = sanitize_text_field( wp_unslash( $new_instance['twitter'] ) );
		$instance['twitter_api_key']      = sanitize_text_field( wp_unslash( $new_instance['twitter_api_key'] ) );
		$instance['twitter_secret_key']   = sanitize_text_field( wp_unslash( $new_instance['twitter_secret_key'] ) );
		$instance['twitter_show_counter'] = ( ! empty( $new_instance['twitter_show_counter'] ) ) ? strip_tags( $new_instance['twitter_show_counter'] ) : '0';
		$instance['twitter_text']         = sanitize_text_field( wp_unslash( $new_instance['twitter_text'] ) );

		$instance['google']              = sanitize_text_field( wp_unslash( $new_instance['google'] ) );
		$instance['google_show_counter'] = ( ! empty( $new_instance['google_show_counter'] ) ) ? strip_tags( $new_instance['google_show_counter'] ) : '0';
		$instance['google_text']         = sanitize_text_field( wp_unslash( $new_instance['google_text'] ) );

		$instance['youtube']              = sanitize_text_field( wp_unslash( $new_instance['youtube'] ) );
		$instance['youtube_show_counter'] = ( ! empty( $new_instance['youtube_show_counter'] ) ) ? strip_tags( $new_instance['youtube_show_counter'] ) : '0';
		$instance['youtube_text']         = sanitize_text_field( wp_unslash( $new_instance['youtube_text'] ) );
		$instance['youtube_type']         = sanitize_text_field( wp_unslash( $new_instance['youtube_type'] ) );

		$instance['pinterest']              = sanitize_text_field( wp_unslash( $new_instance['pinterest'] ) );
		$instance['pinterest_show_counter'] = ( ! empty( $new_instance['pinterest_show_counter'] ) ) ? strip_tags( $new_instance['pinterest_show_counter'] ) : '0';
		$instance['pinterest_text']         = sanitize_text_field( wp_unslash( $new_instance['pinterest_text'] ) );
		$instance['pinterest_api_key']      = sanitize_text_field( wp_unslash( $new_instance['pinterest_api_key'] ) );

		return $instance;
	}

	/**
	 * passing facebook and access token return facebook like counter
	 *
	 * @since 2.0.2
	 *
	 * @param $facebook_id
	 * @param $access_token
	 *
	 * @return int
	 */
	function get_facebook_likes_count( $facebook_id, $access_token, $show_counter ) {

		if ( $show_counter ) {

			if ( false === get_transient( 'ssb_follow_facebook_counter' ) ) {
				$json_feed_url = "https://graph.facebook.com/$facebook_id/?fields=likes,fan_count&access_token=$access_token";

				$args      = array( 'httpversion' => '1.1' );
				$json_feed = wp_remote_get( $json_feed_url, $args );

				if ( is_wp_error( $json_feed ) || 200 !== wp_remote_retrieve_response_code( $json_feed ) ) {
					return 0;
				}
				$result  = json_decode( wp_remote_retrieve_body( $json_feed ) );
				$counter   = ( isset( $result->fan_count ) ? $result->fan_count : 0 );
				$counter   = $this->format_number( $counter );

				if ( ! empty( $counter ) ) {
					set_transient( 'ssb_follow_facebook_counter', $counter, $this->cache_time );
				}

				return $counter;
			} else {
				return get_transient( 'ssb_follow_facebook_counter' );
			}
		}
	}

	/**
	 * Pass twitter user name and api key return twitter follower
	 *
	 * @since 2.0.2
	 *
	 * @param $twitter_handle
	 * @param $api_key
	 * @param $secret_key
	 *
	 * @return mixed|void
	 */
	function get_twitter_followers( $twitter_handle, $api_key, $secret_key, $show_count ) {
		// some variables
		$consumerKey    = $api_key;
		$consumerSecret = $secret_key;
		$token          = get_option( 'ssb_follow_twitter_token' );

		// get follower count from cache
		$numberOfFollowers = get_transient( 'ssb_follow_twitter_counter' );

		if ( $show_count ) {

			// cache version does not exist or expired
			if ( false == get_transient( 'ssb_follow_twitter_counter' ) ) {

				// getting new auth bearer only if we don't have one
				if ( ! $token ) {
					// preparing credentials
					$credentials = $consumerKey . ':' . $consumerSecret;
					$toSend      = base64_encode( $credentials );

					$args = array(
						'method'      => 'POST',
						'httpversion' => '1.1',
						'blocking'    => true,
						'headers'     => array(
							'Authorization' => 'Basic ' . $toSend,
							'Content-Type'  => 'application/x-www-form-urlencoded;charset=UTF-8',
						),
						'body'        => array( 'grant_type' => 'client_credentials' ),
					);

					add_filter( 'https_ssl_verify', '__return_false' );
					$response = wp_remote_post( 'https://api.twitter.com/oauth2/token', $args );

					$keys = json_decode( wp_remote_retrieve_body( $response ) );

					if ( $keys && isset( $keys->access_token ) ) {
						// saving token to wp_options table.
						update_option( 'ssb_follow_twitter_token', $keys->access_token );
						$token = $keys->access_token;
					}
				}

				// we have bearer token wether we obtained it from API or from options.
				$args = array(
					'httpversion' => '1.1',
					'blocking'    => true,
					'headers'     => array(
						'Authorization' => "Bearer $token",
					),
				);

				add_filter( 'https_ssl_verify', '__return_false' );
				$api_url   = "https://api.twitter.com/1.1/users/show.json?screen_name=$twitter_handle";
				$response  = wp_remote_get( $api_url, $args );
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
					return 0;
				}

				$followers = json_decode( wp_remote_retrieve_body( $response ) );
				$counter   = isset( $followers->followers_count ) ? $followers->followers_count : 0;
				$counter   = $this->format_number( $counter );
				// cache for an hour
				if ( ! empty( $counter ) ) {
					set_transient( 'ssb_follow_twitter_counter', $counter, $this->cache_time );
				}

				return $counter;
			}

			return get_transient( 'ssb_follow_twitter_counter' );

		}
	}

	/**
	 * passing the google plus username  return google+ follower
	 *
	 * @since 2.0.2
	 *
	 * @param $google_iD
	 *
	 * @return int
	 */
	function get_google_plus_follower( $google_id, $show_counter ) {

		if ( $show_counter ) {

			if ( false === get_transient( 'ssb_follow_google_counter' ) ) {
				$json_feed_url = 'https://www.googleapis.com/plus/v1/people/' . $google_id . '?fields=circledByCount%2CplusOneCount&key=' . $this->api_key;
				$args          = array( 'httpversion' => '1.1' );
				$json_feed     = wp_remote_get( $json_feed_url, $args );
				if ( is_wp_error( $json_feed ) || 200 !== wp_remote_retrieve_response_code( $json_feed ) ) {
					return 0;
				}
				$result  = json_decode( wp_remote_retrieve_body( $json_feed ) );

				$counter = isset( $result->circledByCount ) ? $result->circledByCount : 0;

				$counter = $this->format_number( $counter );
				if ( ! empty( $counter ) ) {

					set_transient( 'ssb_follow_google_counter', $counter, $this->cache_time );
				}

				return $counter;
			} else {

				return get_transient( 'ssb_follow_google_counter' );
			}
		}
	}

	/**
	 * passing youtube channel id and access token return the channel subscriber counter
	 * @since 2.0.2
	 *
	 * @param $channel_id
	 * @param $access_token
	 *
	 * @return int
	 */
	function get_youtube_subscriber( $channel_id, $show_counter, $youtube_type ) {

		if ( $show_counter ) {

			if ( false === get_transient( 'ssb_follow_youtube_counter' ) ) {

				// Check if username of channel id.
				$_type = $youtube_type == 'username' ? 'forUsername' : 'id';

				$json_feed_url = 'https://www.googleapis.com/youtube/v3/channels?key=' . $this->api_key . '&part=contentDetails,statistics&'. $_type . '=' . $channel_id;
				$args  = array(
											'httpversion' => '1.1',
											'timeout'     => 15
										);
				$json_feed     = wp_remote_get( $json_feed_url, $args );
				if ( is_wp_error( $json_feed ) || 200 !== wp_remote_retrieve_response_code( $json_feed ) ) {
					return 0;
				}
				$result  = json_decode( wp_remote_retrieve_body( $json_feed ) );
				$counter       = isset( $result->items[0]->statistics->subscriberCount ) ? $result->items[0]->statistics->subscriberCount : 0;
				$counter       = $this->format_number( $counter );

				if ( ! empty( $counter ) ) {

					set_transient( 'ssb_follow_youtube_counter', $counter, $this->cache_time );
				}

				return $counter;
			} else {

				return get_transient( 'ssb_follow_youtube_counter' );
			}
		}

	}

	/**
	 * passing pinterest access_token  for getting pinterest follower counter
	 * @since 2.0.2
	 * @param $access_token
	 * @param $show_counter
	 *
	 * @return int|string
	 */
	function get_pinterest_followers(  $access_token, $show_counter ) {

		if ( $show_counter ) {

			if ( false === get_transient( 'ssb_follow_pinterest_counter' ) ) {
				$json_feed_url = 'https://api.pinterest.com/v1/me/followers/?access_token=' . $access_token;
				$args          = array( 'httpversion' => '1.1' );
				$json_feed     = wp_remote_get( $json_feed_url, $args );
				//$result        = json_decode( $json_feed['body'] );
				if ( is_wp_error( $json_feed ) || 200 !== wp_remote_retrieve_response_code( $json_feed ) ) {
					return 0;
				}
				$result  = json_decode( wp_remote_retrieve_body( $json_feed ),true );
				$counter = count($result['data'] );
				$counter = $this->format_number( $counter );

				if ( ! empty( $counter ) ) {

					set_transient( 'ssb_follow_pinterest_counter', $counter, $this->cache_time );
				}

				return $counter;
			} else {

				return get_transient( 'ssb_follow_pinterest_counter' );
			}
		}

	}

	/**
	 * Format the (int)number into easy readable format like 1K, 1M
	 * @since 2.0.2
	 *
	 * @param $value
	 *
	 * @return string
	 */
	function format_number( $value ) {
		if ( $value > 999 && $value <= 999999 ) {
			return $result = floor( $value / 1000 ) . 'K';
		} elseif ( $value > 999999 ) {
			return $result = floor( $value / 1000000 ) . '   M';
		} else {
			return $result = $value;
		}
	}

} // end class Ssb_Follower_Widget

/**
 * Register plugin widget.
 */
function ssb_register_widget() {
	register_widget( 'Ssb_Follower_Widget' );
}

add_action( 'widgets_init', 'ssb_register_widget' );
