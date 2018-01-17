<?php
/**
 * Plugin Name: Simple Social Buttons
 * Plugin URI: http://www.WPBrigade.com/wordpress/plugins/simple-social-buttons/
 * Description: Simple Social Buttons adds an advanced set of social media sharing buttons to your WordPress sites, such as: Google +1, Facebook, WhatsApp, Viber, Twitter, Reddit, LinkedIn and Pinterest. This makes it the most <code>Flexible Social Sharing Plugin ever for Everyone.</code>
 * Version: 2.0.6
 * Author: WPBrigade
 * Author URI: http://www.WPBrigade.com/
 * Text Domain: simple-social-buttons
 * Domain Path: /lang
 */

/*
  Copyright 2011, Muhammad Adnan (WPBrigade)  (email : captain@wpbrigade.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class SimpleSocialButtonsPR {
	public $pluginName        = 'Simple Social Buttons';
	public $pluginVersion     = '2.0.6';
	public $pluginPrefix      = 'ssb_pr_';
	public $hideCustomMetaKey = '_ssb_hide';

	// plugin default settings
	public $pluginDefaultSettings = array(
		'googleplus'    => '1',
		'twitter'       => '3',
		'pinterest'     => '0',
		'beforepost'    => '1',
		'afterpost'     => '0',
		'beforepage'    => '1',
		'afterpage'     => '0',
		'beforearchive' => '0',
		'afterarchive'  => '0',
		'fbshare'       => '0',
		'fblike'        => '0',
		'linkedin'      => '0',
		'cache'         => 'on',
	);

	// defined buttons
	public $arrKnownButtons = array( 'googleplus', 'twitter', 'pinterest', 'fbshare', 'linkedin', 'reddit', 'whatsapp', 'viber', 'fblike' );

	// an array to store current settings, to avoid passing them between functions
	public $settings = array();

	public $selected_networks = array();
	public $selected_theme    = '';
	public $selected_position = '';
	public $inline_option     = '';
	public $sidebar_option    = '';
	public $extra_option      = '';

	/**
	 * Constructor
	 */
	function __construct() {

		$this->constants();
		include_once SSB_PLUGIN_DIR . '/inc/upgrade-routine.php';

		register_activation_hook( __FILE__, array( $this, 'plugin_install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'plugin_uninstall' ) );

		$this->_includes();
		$this->set_selected_networks();
		$this->set_selected_theme();
		$this->set_selected_position();
		$this->set_inline_option();
		$this->set_sidebar_option();
		$this->set_extra_option();

		add_action( 'plugins_loaded', array( $this, 'load_plugin_domain' ) );

		/**
		 * Filter hooks
		 */
		add_filter( 'the_content', array( $this, 'insert_buttons' ) );
		add_filter( 'the_excerpt', array( $this, 'insert_excerpt_buttons' ) );

		add_filter( 'plugin_row_meta', array( $this, '_row_meta' ), 10, 2 );

		add_action( 'wp_enqueue_scripts', array( $this, 'front_enqueue_scripts' ) );

		// Queue up our hook function
		add_action( 'wp_footer', array( $this, 'ssb_footer_functions' ), 99 );

		add_filter( 'ssb_footer_scripts', array( $this, 'ssb_output_cache_trigger' ) );

		add_action( 'wp_ajax_ssb_fetch_data', array( $this, 'ajax_fetch_fresh_data' ) );
		add_action( 'wp_ajax_nopriv_ssb_fetch_data', array( $this, 'ajax_fetch_fresh_data' ) );

		add_action( 'wp_footer', array( $this, 'include_sidebar' ) );
		add_action( 'wp_head', array( $this, 'css_file' ) );

		add_action( 'admin_notices', array( $this, 'update_notice' ) );
		add_action( 'admin_init', array( $this, 'review_update_notice' ) );
		add_action( 'wp_footer', array( $this, 'fblike_script' ) );

		add_shortcode( 'SSB', array( $this, 'short_code_content' ) );
	}

	function set_selected_networks() {
		$networks                = get_option( 'ssb_networks' );
		$this->selected_networks = array_flip( array_merge( array( 0 ), explode( ',', $networks['icon_selection'] ) ) );

	}

	function set_selected_theme() {
		$theme                = get_option( 'ssb_themes' );
		$this->selected_theme = $theme['icon_style'];

	}

	function set_selected_position() {
		$theme                   = get_option( 'ssb_positions' );
		$this->selected_position = $theme['position'];
	}

	function set_inline_option() {
		$this->inline_option = get_option( 'ssb_inline' );
	}

	function set_sidebar_option() {
		$this->sidebar_option = get_option( 'ssb_sidebar' );
	}


	function set_extra_option() {
		$this->extra_option = get_option( 'ssb_advanced' );
	}

	function ajax_fetch_fresh_data() {

		$order   = array();
		$post_id = $_POST['postID'];
		foreach ( $this->arrKnownButtons as $button_name ) {

			if ( isset( $this->selected_networks[ $button_name ] ) && $this->selected_networks[ $button_name ] > 0 ) {
				$order[ $button_name ] = $this->selected_networks[ $button_name ];
			}
		}

		$_share_links = array();
		foreach ( $order as $social_name => $priority ) {
			if ( 'totalshare' == $social_name || 'viber' == $social_name || 'fblike' == $social_name || 'whatsapp' == $social_name ) {
				continue; }
			$_share_links[ $social_name ] = call_user_func( 'ssb_' . $social_name . '_generate_link', get_permalink( $post_id ) );
		}

		 $result = ssb_fetch_shares_via_curl_multi( array_filter( $_share_links ) );

		// $result = ssb_fetch_shares_via_curl_multi(
		// array(
		// 'linkedin' => ssb_linkedin_generate_link( 'https://wpbrigade.com/first-wordcamp-talk/' ),
		// 'fbshare' => ssb_fbshare_generate_link( 'http://www.blc.lu/' ),
		// 'googleplus' => ssb_googleplus_generate_link( 'https://wpbrigade.com/first-wordcamp-talk/' ),
		// 'twitter' => ssb_twitter_generate_link( 'https://wptavern.com/jetpack-5-3-adds-php-7-1-compatibility-better-control-for-wordads-placement' ),
		// 'pinterest' => ssb_pinterest_generate_link( 'http://websitehostingcost.com/tag/dedicated/' ),
		// 'reddit' => ssb_reddit_generate_link( 'http://stackoverflow.com/q/811074/1288' )
		// )
		// );
			$share_counts = ssb_fetch_fresh_counts( $result, $post_id );

			update_post_meta( $post_id, 'ssb_cache_timestamp', floor( ( ( date( 'U' ) / 60 ) / 60 ) ) );
			  echo json_encode( $share_counts );
			wp_die();
	}

	function ssb_output_cache_trigger( $info ) {

		// Return early if we're not on a single page or we have fresh cache.
		// if ( ( ! is_singular() || ssb_is_cache_fresh( $info['postID'], true )) && empty( $_GET['ssb_cache'] ) ) {
		if ( ( ssb_is_cache_fresh( $info['postID'], true ) ) && empty( $_GET['ssb_cache'] ) ) {
			return $info;
		}

		// Return if we're on a WooCommerce account page.
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return $info;
		}

		// Return if caching is off.
		// if (  'on' != $this->settings['cache'] ) {
		// return $info;
		// }
		ob_start();

		?>
		var ssb_admin_ajax = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
		var is_ssb_used = jQuery('.simplesocialbuttons');
		var postID = <?php echo $info['postID']; ?> ;
		if( is_ssb_used ) {

			var data = {
			'action': 'ssb_fetch_data',
			'postID': postID
		};
			jQuery.post(ssb_admin_ajax, data, function(data, textStatus, xhr) {
				var array = JSON.parse(data);

				jQuery.each( array, function( index, value ){

					if( index == 'total' ){
						jQuery('.ssb_'+ index +'_counter').html(value + '<span>Shares</span>');
					}else{
						jQuery('.ssb_'+ index +'_counter').html(value);
					}
				});


			});
		}

		<?php
		$info['footer_output'] .= ob_get_clean();

		return $info;
	}


	function ssb_footer_functions() {

		// Fetch a few variables.
		$info['postID']        = get_the_ID();
		$info['footer_output'] = '';

		// Pass the array through our custom filters.
		$info = apply_filters( 'ssb_footer_scripts', $info );

		// If we have output, output it.
		if ( $info['footer_output'] ) {
			echo '<script type="text/javascript">';
			echo $info['footer_output'];
			echo '</script>';
		}
	}



	function front_enqueue_scripts() {

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'ssb-front-js', plugins_url( 'assets/js/front.js', __FILE__ ), array( 'jquery' ), SSB_VERSION );
		wp_enqueue_style( 'ssb-front-css', plugins_url( 'assets/css/front.css', __FILE__ ), false, SSB_VERSION );
	}

	/**
	 * Includes all files.
	 *
	 * @since 2.0
	 */
	function _includes() {

		include_once SSB_PLUGIN_DIR . '/inc/utils.php';
		include_once SSB_PLUGIN_DIR . '/ssb-social-counts/facebook.php';
		include_once SSB_PLUGIN_DIR . '/ssb-social-counts/linkedin.php';
		include_once SSB_PLUGIN_DIR . '/ssb-social-counts/twitter.php';
		include_once SSB_PLUGIN_DIR . '/ssb-social-counts/googleplus.php';
		include_once SSB_PLUGIN_DIR . '/ssb-social-counts/pinterest.php';
		include_once SSB_PLUGIN_DIR . '/ssb-social-counts/reddit.php';
	}


	function load_plugin_domain() {
		load_plugin_textdomain( 'simple-social-buttons', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	}

	function constants() {
		define( 'SSB_FEEDBACK_SERVER', 'https://wpbrigade.com/' );
		define( 'SSB_VERSION', $this->pluginVersion );
		define( 'SSB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}


	/**
	 * Set default settings.
	 */
	function plugin_install() {

		if ( ! get_option( 'ssb_networks' ) ) {
			$_default = array(
				'icon_selection' => 'fbshare,twitter,googleplus,linkedin,fblike',
			);
			update_option( 'ssb_networks', $_default );
		}

		if ( ! get_option( 'ssb_themes' ) ) {
			$_default = array(
				'icon_style' => 'simple-icons',
			);
			update_option( 'ssb_themes', $_default );
		}

		if ( ! get_option( 'ssb_positions' ) ) {
			$_default = array(
				'position' => array(
					'inline' => 'inline',
				),
			);
			update_option( 'ssb_positions', $_default );
		}

		if ( ! get_option( 'ssb_inline' ) ) {
			$_default = array(
				'location' => 'below',
				'posts'    => array(
					'post' => 'post',
				),
			);
			update_option( 'ssb_inline', $_default );
		}

		update_option( $this->pluginPrefix . 'version', $this->pluginVersion );

	}

	/**
	 * Plugin unistall and database clean up
	 */
	function plugin_uninstall() {
		if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			exit();
		}

		delete_option( $this->pluginPrefix . 'settings' );
		delete_option( $this->pluginPrefix . 'version' );

	}


	function _get_settings( $section, $value, $default = false ) {
		$section = $section . '_option';
		$_arr    = $this->$section;
		return isset( $_arr[ $value ] ) && ! empty( $_arr[ $value ] ) ? $_arr[ $value ] : $default;
	}

	/**
	 * Returns true on pages where buttons should be shown
	 */
	function where_to_insert() {

		$return = false;

		// Single Page/Post
		if ( isset( $this->selected_position['inline'] ) && 'false' == get_post_meta( get_the_ID(), $this->hideCustomMetaKey, true ) ) {
			$return = true;
		}

		return $return;

	}


	/**
	 * Add Thumbs Up Icon
	 *
	 * @since 1.9.0
	 */
	public function _row_meta( $links, $file ) {

		if ( strpos( $file, 'simple-social-buttons.php' ) !== false ) {

			// Set link for Reviews.
			$new_links = array(
				'<a href="https://wordpress.org/support/plugin/simple-social-buttons/reviews/?filter=5" target="_blank"><span class="dashicons dashicons-thumbs-up"></span> ' . __( 'Vote!', 'simplesocialbuttons' ) . '</a>',
			);

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}


	/**
	 * Add inline for the excerpt.
	 *
	 * @since 2.0
	 */
	function insert_excerpt_buttons( $content ) {

		if ( is_single() ) {
			return $content;
		}

		return $this->insert_buttons( $content );
	}

	/**
	 * Return class
	 *
	 * @since 2.0.4
	 */
	function add_post_class( $post_id = null ) {
		$post = get_post( $post_id );

		$classes = '';

		if ( ! $post ) {
			return $classes;
		}

		$classes .= 'post-' . $post->ID . ' ';
		$classes .= $post->post_type . ' ';

		return $classes;
	}


	/**
	 * Add Inline Buttons.
	 *
	 * @since 1.0
	 */
	function insert_buttons( $content ) {

		// Return Content if hide ssb.
		if ( get_post_meta( get_the_id(), $this->hideCustomMetaKey, true ) == 'true' ) {
			return $content;
		}

		if ( is_archive() && ! $this->inline_option['show_on_category'] ) {
			return $content; }
		if ( is_category() && ! $this->inline_option['show_on_archive'] ) {
			return $content; }
		if ( is_tag() && ! $this->inline_option['show_on_tag'] ) {
			return $content; }

		// && 'false' == get_post_meta( get_the_ID(), $this->hideCustomMetaKey , true )
		if ( isset( $this->selected_position['inline'] ) ) {
			// Show Total at the end.
			if ( $this->_get_settings( 'inline', 'total_share' ) ) {
				$show_total = true;
			} else {
				$show_total = false;
			}

			$extra_class = 'simplesocialbuttons_inline simplesocialbuttons-align-' . $this->_get_settings( 'inline', 'icon_alignment', 'left' ) . ' ' . $this->add_post_class();

			// if ( $this->inline['share_counts'] ) {
			if ( $this->_get_settings( 'inline', 'share_counts' ) ) {
				$show_count   = true;
				$extra_class .= ' ssb_counter-activate';
			} else {
				$show_count = false;
			}

			if ( $this->_get_settings( 'inline', 'hide_mobile' ) ) {
				$extra_class .= ' simplesocialbuttons-mobile-hidden'; }
			$extra_class .= ' simplesocialbuttons-inline-' . $this->_get_settings( 'inline', 'animation', 'no-animation' );

			$_selected_network = apply_filters( 'ssb_inline_social_networks', $this->selected_networks );
			$ssb_buttonscode   = $this->generate_buttons_code( $_selected_network, $show_count, $show_total, $extra_class );

			if ( in_array( $this->get_post_type(), $this->inline_option['posts'] ) ) {
				if ( $this->inline_option['location'] == 'above' || $this->inline_option['location'] == 'above_below' ) {
					$content = $ssb_buttonscode . $content;
				}
				if ( $this->inline_option['location'] == 'below' || $this->inline_option['location'] == 'above_below' ) {
					$content = $content . $ssb_buttonscode;
				}
			}
		}

		return $content;

	}


	/**
	 * Generate buttons html code with specified order
	 *
	 * @param mixed $order - order of social buttons
	 */
	function generate_buttons_code( $order = null, $show_count = false, $show_total = false, $extra_class = '' ) {

		// define empty buttons code to use
		$ssb_buttonscode = '';

		// get post permalink and title
		$permalink = get_permalink();
		$title     = get_the_title();

		// Sorting the buttons
		$arrButtons = array();
		foreach ( $this->arrKnownButtons as $button_name ) {
			if ( ! empty( $order[ $button_name ] ) && (int) $order[ $button_name ] != 0 ) {
				$arrButtons[ $button_name ] = $order[ $button_name ];
			}
		}
		// echo '<pre>'; print_r( $arrButtons ); echo '</pre>';
		@asort( $arrButtons );

		// add total share index in array.
		if ( $show_total ) {
			$arrButtons['totalshare'] = '100'; }
		$post_id = get_the_id();

		// // Reset the cache timestamp if needed
		// // if false fetch the new share counts.
		if ( isset( $this->settings['cache'] ) && $this->settings['cache'] == 'off' ) {

			$_share_links = array();
			foreach ( $arrButtons as $social_name => $priority ) {
				if ( 'totalshare' == $social_name || 'viber' == $social_name || 'fblike' == $social_name || 'whatsapp' == $social_name ) {
					continue; }
				$_share_links[ $social_name ] = call_user_func( 'ssb_' . $social_name . '_generate_link', get_permalink() );
			}

			$result = ssb_fetch_shares_via_curl_multi( array_filter( $_share_links ) );

			// $result = ssb_fetch_shares_via_curl_multi(
			// array(
			// 'linkedin' => ssb_linkedin_generate_link( 'https://wpbrigade.com/first-wordcamp-talk/' ),
			// 'fbshare' => ssb_fbshare_generate_link( 'https://propakistani.pk/2017/09/06/lahore-get-600-million-disneyland-like-amusement-park/' ),
			// 'googleplus' => ssb_googleplus_generate_link( 'https://wpbrigade.com/first-wordcamp-talk/' ),
			// 'twitter' => ssb_twitter_generate_link( 'https://wptavern.com/jetpack-5-3-adds-php-7-1-compatibility-better-control-for-wordads-placement' ),
			// 'pinterest' => ssb_pinterest_generate_link( 'http://websitehostingcost.com/tag/dedicated/' ),
			// 'reddit' => ssb_reddit_generate_link( 'http://stackoverflow.com/q/811074/1288' )
			// )
			// );
			$share_counts = ssb_fetch_fresh_counts( $result, $post_id );
			// update_post_meta( $post_id,'ssb_cache_timestamp',floor( ( ( date( 'U' ) / 60) / 60 ) ) );
		} else {
			$share_counts = ssb_fetch_cached_counts( array_flip( $arrButtons ), $post_id );
		}

		$arrButtonsCode = array();
		foreach ( $arrButtons as $button_name => $button_sort ) {
			switch ( $button_name ) {
				case 'googleplus':
					$googleplus_share = $share_counts['googleplus'] ? $share_counts['googleplus'] : 0;

					if ( $this->selected_theme == 'simple-icons' ) {
						$_html = '<button class="ssb_gplus-icon" data-href="https://plus.google.com/share?url=' . $permalink . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;">
						<span class="icon"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet" width="30px" height="18px" viewBox="-10 -6 60 36" class="ozWidgetRioButtonSvg_ ozWidgetRioButtonPlusOne_"><path d="M30 7h-3v4h-4v3h4v4h3v-4h4v-3h-4V7z"></path><path d="M11 9.9v4h5.4C16 16.3 14 18 11 18c-3.3 0-5.9-2.8-5.9-6S7.7 6 11 6c1.5 0 2.8.5 3.8 1.5l2.9-2.9C15.9 3 13.7 2 11 2 5.5 2 1 6.5 1 12s4.5 10 10 10c5.8 0 9.6-4.1 9.6-9.8 0-.7-.1-1.5-.2-2.2H11z"></path></svg></span>
						<span class="simplesocialtxt">Google Plus </span>';
						if ( $show_count ) {
							$_html .= '<span class="ssb_counter">'. $googleplus_share .'</span>';
						}
						$_html .= '</button>';
					} else {

						$_html = '<button class="simplesocial-gplus-share" data-href="https://plus.google.com/share?url=' . $permalink . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"><span class="simplesocialtxt">Google+</span>';
						if ( $show_count ) {
							$_html .= '<span class="ssb_counter ssb_googleplus_counter">' . $googleplus_share . '</span>';
						}
						$_html .= '</button>';
					}

					$arrButtonsCode[] = $_html;

					break;

				case 'fbshare':
					$fbshare_share = $share_counts['fbshare'] ? $share_counts['fbshare'] : 0;

					if ( $this->selected_theme == 'simple-icons' ) {
						$_html = '		<button class="ssb_fbshare-icon" target="_blank" data-href="https://www.facebook.com/sharer/sharer.php?u=' . $permalink . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;">
						<span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" class="_1pbq" color="#ffffff"><path fill="#ffffff" fill-rule="evenodd" class="icon" d="M8 14H3.667C2.733 13.9 2 13.167 2 12.233V3.667A1.65 1.65 0 0 1 3.667 2h8.666A1.65 1.65 0 0 1 14 3.667v8.566c0 .934-.733 1.667-1.667 1.767H10v-3.967h1.3l.7-2.066h-2V6.933c0-.466.167-.9.867-.9H12v-1.8c.033 0-.933-.266-1.533-.266-1.267 0-2.434.7-2.467 2.133v1.867H6v2.066h2V14z"></path></svg></span>
						<span class="simplesocialtxt">Share </span>';

						if ( $show_count ) {
							$_html .= ' <span class="ssb_counter">'. $fbshare_share .'</span>';;
						}

						$_html .= ' </button>';
					} else{

						$_html         = '<button class="simplesocial-fb-share" target="_blank" data-href="https://www.facebook.com/sharer/sharer.php?u=' . $permalink . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"><span class="simplesocialtxt">Facebook </span> ';

						if ( $show_count ) {
							$_html .= '<span class="ssb_counter ssb_fbshare_counter">' . $fbshare_share . '</span>';
						}
						$_html .= '</button>';
					}

					$arrButtonsCode[] = $_html;

					break;
				case 'twitter':
					$twitter_share = $share_counts['twitter'] ? $share_counts['twitter'] : 0;
					$via           = ! empty( $this->extra_option['twitter_handle'] ) ? '&via=' . $this->extra_option['twitter_handle'] : '';

					if ( $this->selected_theme == 'simple-icons' ) {

						$_html = '<button class="ssb_tweet-icon"  data-href="https://twitter.com/share?text=' . $title . '&url=' . $permalink . '' . $via . '" rel="nofollow" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;">
						<span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 72 72"><path fill="none" d="M0 0h72v72H0z"/><path class="icon" fill="#fff" d="M68.812 15.14c-2.348 1.04-4.87 1.744-7.52 2.06 2.704-1.62 4.78-4.186 5.757-7.243-2.53 1.5-5.33 2.592-8.314 3.176C56.35 10.59 52.948 9 49.182 9c-7.23 0-13.092 5.86-13.092 13.093 0 1.026.118 2.02.338 2.98C25.543 24.527 15.9 19.318 9.44 11.396c-1.125 1.936-1.77 4.184-1.77 6.58 0 4.543 2.312 8.552 5.824 10.9-2.146-.07-4.165-.658-5.93-1.64-.002.056-.002.11-.002.163 0 6.345 4.513 11.638 10.504 12.84-1.1.298-2.256.457-3.45.457-.845 0-1.666-.078-2.464-.23 1.667 5.2 6.5 8.985 12.23 9.09-4.482 3.51-10.13 5.605-16.26 5.605-1.055 0-2.096-.06-3.122-.184 5.794 3.717 12.676 5.882 20.067 5.882 24.083 0 37.25-19.95 37.25-37.25 0-.565-.013-1.133-.038-1.693 2.558-1.847 4.778-4.15 6.532-6.774z"/></svg></span>';

						if ( $show_count ) {
							$_html .= '<span class="simplesocialtxt">Tweet '. $twitter_share .'</span>';
						} else{
							$_html .= '<span class="simplesocialtxt">Tweet </span>';

						}

						$_html .=	'</button>';


					} else {

						$_html         = '<button class="simplesocial-twt-share" data-href="https://twitter.com/share?text=' . $title . '&url=' . $permalink . '' . $via . '" rel="nofollow" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"><span class="simplesocialtxt">Twitter</span> ';

						if ( $show_count ) {
							$_html .= '<span class="ssb_counter ssb_twitter_counter">' . $twitter_share . '</span>';
						}
						$_html .= '</button>';
					}

					$arrButtonsCode[] = $_html;

					break;
				case 'linkedin':
					$linkedin_share = $share_counts['linkedin'] ? $share_counts['linkedin'] : 0;

					if ( $this->selected_theme == 'simple-icons' ) {

						$_html = '<button class="ssb_linkedin-icon" data-href="https://www.linkedin.com/cws/share?url=' . get_permalink() . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" >
						<span class="icon"> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="15px" height="14.1px" viewBox="-301.4 387.5 15 14.1" enable-background="new -301.4 387.5 15 14.1" xml:space="preserve"> <g id="XMLID_398_"> <path id="XMLID_399_" fill="#FFFFFF" d="M-296.2,401.6c0-3.2,0-6.3,0-9.5h0.1c1,0,2,0,2.9,0c0.1,0,0.1,0,0.1,0.1c0,0.4,0,0.8,0,1.2 c0.1-0.1,0.2-0.3,0.3-0.4c0.5-0.7,1.2-1,2.1-1.1c0.8-0.1,1.5,0,2.2,0.3c0.7,0.4,1.2,0.8,1.5,1.4c0.4,0.8,0.6,1.7,0.6,2.5 c0,1.8,0,3.6,0,5.4v0.1c-1.1,0-2.1,0-3.2,0c0-0.1,0-0.1,0-0.2c0-1.6,0-3.2,0-4.8c0-0.4,0-0.8-0.2-1.2c-0.2-0.7-0.8-1-1.6-1 c-0.8,0.1-1.3,0.5-1.6,1.2c-0.1,0.2-0.1,0.5-0.1,0.8c0,1.7,0,3.4,0,5.1c0,0.2,0,0.2-0.2,0.2c-1,0-1.9,0-2.9,0 C-296.1,401.6-296.2,401.6-296.2,401.6z"/> <path id="XMLID_400_" fill="#FFFFFF" d="M-298,401.6L-298,401.6c-1.1,0-2.1,0-3,0c-0.1,0-0.1,0-0.1-0.1c0-3.1,0-6.1,0-9.2 c0-0.1,0-0.1,0.1-0.1c1,0,2,0,2.9,0h0.1C-298,395.3-298,398.5-298,401.6z"/> <path id="XMLID_401_" fill="#FFFFFF" d="M-299.6,390.9c-0.7-0.1-1.2-0.3-1.6-0.8c-0.5-0.8-0.2-2.1,1-2.4c0.6-0.2,1.2-0.1,1.8,0.2 c0.5,0.4,0.7,0.9,0.6,1.5c-0.1,0.7-0.5,1.1-1.1,1.3C-299.1,390.8-299.4,390.8-299.6,390.9L-299.6,390.9z"/> </g> </svg> </span>
						<span class="simplesocialtxt">Share</span>';

						if ( $show_count ) {
							$_html .= ' <span class="ssb_counter">'. $linkedin_share .'</span>';
						}
						$_html .= ' </button>';
					} else {

						$_html = '<button target="popup" class="simplesocial-linkedin-share" data-href="https://www.linkedin.com/cws/share?url=' . get_permalink() . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"><span class="simplesocialtxt">LinkedIn</span>';

						if ( $show_count ) {
							$_html .= '<span class="ssb_counter ssb_linkedin_counter">' . $linkedin_share . '</span>';
						}
						$_html .= '</button>';
					}

					$arrButtonsCode[] = $_html;

					break;
				case 'pinterest':
					$pinterest_share = $share_counts['pinterest'] ? $share_counts['pinterest'] : 0;

					if ( $this->selected_theme == 'simple-icons' ) {

						$_html = ' <button class="ssb_pinterest-icon" onclick="var e=document.createElement(\'script\');e.setAttribute(\'type\',\'text/javascript\');e.setAttribute(\'charset\',\'UTF-8\');e.setAttribute(\'src\',\'//assets.pinterest.com/js/pinmarklet.js?r=\'+Math.random()*99999999);document.body.appendChild(e);return false;">
						<span class="icon"> <svg xmlns="http://www.w3.org/2000/svg" height="30px" width="30px" viewBox="-1 -1 31 31"><g><path d="M29.449,14.662 C29.449,22.722 22.868,29.256 14.75,29.256 C6.632,29.256 0.051,22.722 0.051,14.662 C0.051,6.601 6.632,0.067 14.75,0.067 C22.868,0.067 29.449,6.601 29.449,14.662" fill="#fff" stroke="#fff" stroke-width="1"></path><path d="M14.733,1.686 C7.516,1.686 1.665,7.495 1.665,14.662 C1.665,20.159 5.109,24.854 9.97,26.744 C9.856,25.718 9.753,24.143 10.016,23.022 C10.253,22.01 11.548,16.572 11.548,16.572 C11.548,16.572 11.157,15.795 11.157,14.646 C11.157,12.842 12.211,11.495 13.522,11.495 C14.637,11.495 15.175,12.326 15.175,13.323 C15.175,14.436 14.462,16.1 14.093,17.643 C13.785,18.935 14.745,19.988 16.028,19.988 C18.351,19.988 20.136,17.556 20.136,14.046 C20.136,10.939 17.888,8.767 14.678,8.767 C10.959,8.767 8.777,11.536 8.777,14.398 C8.777,15.513 9.21,16.709 9.749,17.359 C9.856,17.488 9.872,17.6 9.84,17.731 C9.741,18.141 9.52,19.023 9.477,19.203 C9.42,19.44 9.288,19.491 9.04,19.376 C7.408,18.622 6.387,16.252 6.387,14.349 C6.387,10.256 9.383,6.497 15.022,6.497 C19.555,6.497 23.078,9.705 23.078,13.991 C23.078,18.463 20.239,22.062 16.297,22.062 C14.973,22.062 13.728,21.379 13.302,20.572 C13.302,20.572 12.647,23.05 12.488,23.657 C12.193,24.784 11.396,26.196 10.863,27.058 C12.086,27.434 13.386,27.637 14.733,27.637 C21.95,27.637 27.801,21.828 27.801,14.662 C27.801,7.495 21.95,1.686 14.733,1.686" fill="#bd081c"></path></g></svg> </span>
						<span class="simplesocialtxt">Save</span>';
						if ( $show_count ) {
							$_html .= '<span class="ssb_counter">'. $pinterest_share .'</span>';
						}
						$_html .= ' </button>';
					} else {

						$_html = '<button rel="nofollow" class="simplesocial-pinterest-share" onclick="var e=document.createElement(\'script\');e.setAttribute(\'type\',\'text/javascript\');e.setAttribute(\'charset\',\'UTF-8\');e.setAttribute(\'src\',\'//assets.pinterest.com/js/pinmarklet.js?r=\'+Math.random()*99999999);document.body.appendChild(e);return false;" ><span class="simplesocialtxt">Pinterest</span>';

						if ( $show_count ) {
							$_html .= '<span class="ssb_counter ssb_pinterest_counter">' . $pinterest_share . '</span>';
						}
						$_html .= '</button>';
					}

					$arrButtonsCode[] = $_html;

					break;
				case 'totalshare':
					$total_share      = $share_counts['total'] ? $share_counts['total'] : 0;
					$arrButtonsCode[] = "<span class='ssb_total_counter'>" . $total_share . '<span>Shares</span></span>';
					break;

				case 'reddit':
					$reddit_score = $share_counts['reddit'] ? $share_counts['reddit'] : 0;

					if ( $this->selected_theme == 'simple-icons' ) {
						$_html = ' <button class="ssb_reddit-icon" data-href="https://reddit.com/submit?url=' . $permalink . '&title=' . $title . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;">
						<span class="icon"> <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
						width="430.117px" height="430.117px" viewBox="0 0 430.117 430.117" style="enable-background:new 0 0 430.117 430.117;"
						xml:space="preserve"> <g> <path id="reddit" d="M307.523,231.062c1.11,2.838,1.614,5.769,1.614,8.681c0,5.862-2.025,11.556-5.423,16.204 c-3.36,4.593-8.121,8.158-13.722,9.727h0.01c-0.047,0.019-0.094,0.019-0.117,0.037c-0.023,0-0.061,0.019-0.079,0.019 c-2.623,0.896-5.312,1.316-7.98,1.316c-6.254,0-12.396-2.254-17.306-6.096c-4.872-3.826-8.56-9.324-9.717-15.845h-0.01 c0-0.019,0-0.042-0.009-0.069c0-0.019,0-0.038-0.019-0.065h0.019c-0.364-1.681-0.551-3.36-0.551-5.021 c0-5.647,1.923-11.07,5.097-15.551c3.164-4.453,7.626-7.99,12.848-9.811c0.019,0,0.038-0.01,0.038-0.01 c0.027,0,0.027-0.027,0.051-0.027c2.954-1.092,6.072-1.639,9.157-1.639c5.619,0,11.154,1.704,15.821,4.821 c4.611,3.066,8.354,7.561,10.23,13.143c0.019,0.037,0.019,0.07,0.037,0.103c0,0.037,0.019,0.057,0.037,0.084H307.523z M290.329,300.349c-2.202-1.428-4.751-2.291-7.448-2.291c-2.175,0-4.434,0.621-6.445,1.955l0,0 c-19.004,11.342-41.355,17.558-63.547,17.558c-16.65,0-33.199-3.514-48.192-10.879l-0.077-0.037l-0.075-0.028 c-2.261-0.924-4.837-2.889-7.647-4.76c-1.428-0.925-2.919-1.844-4.574-2.521c-1.633-0.695-3.447-1.181-5.386-1.181 c-1.605,0-3.292,0.359-4.957,1.115c-0.086,0.033-0.168,0.065-0.252,0.098h0.009c-2.616,0.999-4.66,2.829-5.974,4.994 c-1.372,2.23-2.046,4.826-2.046,7.411c0,2.334,0.551,4.667,1.691,6.786c1.085,2.007,2.754,3.762,4.938,4.938 c21.429,14.454,46.662,21.002,71.992,20.979c22.838,0,45.814-5.287,66.27-14.911l0.107-0.065l0.103-0.056 c2.697-1.597,6.282-3.029,9.661-5.115c1.671-1.064,3.304-2.296,4.704-3.897c1.4-1.591,2.525-3.551,3.16-5.875v-0.01 c0.266-1.026,0.392-2.025,0.392-3.024c0-1.899-0.467-3.701-1.241-5.32C294.361,303.775,292.504,301.778,290.329,300.349z M139.875,265.589c0.037,0,0.086,0.014,0.128,0.037c2.735,0.999,5.554,1.493,8.345,1.493c6.963,0,13.73-2.852,18.853-7.5 c5.115-4.662,8.618-11.257,8.618-18.775c0-0.196,0-0.392-0.009-0.625c0.019-0.336,0.028-0.705,0.028-1.083 c0-7.458-3.456-14.08-8.522-18.762c-5.085-4.686-11.836-7.551-18.825-7.551c-1.867,0-3.769,0.219-5.628,0.653 c-0.028,0-0.049,0.009-0.077,0.009c0,0-0.019,0-0.028,0c-9.252,1.937-17.373,8.803-20.37,18.248l0,0v0.01 c0,0.019-0.009,0.037-0.009,0.037c-0.861,2.586-1.262,5.255-1.262,7.896c0,5.787,1.913,11.426,5.211,16.064 c3.269,4.56,7.894,8.145,13.448,9.819C139.816,265.561,139.835,265.571,139.875,265.589z M430.033,198.094v0.038 c0.066,0.94,0.084,1.878,0.084,2.81c0,10.447-3.351,20.493-8.941,29.016c-5.218,7.976-12.414,14.649-20.703,19.177 c0.532,4.158,0.84,8.349,0.84,12.526c-0.01,22.495-7.766,44.607-21.272,62.329v0.009h-0.028 c-24.969,33.216-63.313,52.804-102.031,62.684h-0.01l-0.027,0.023c-20.647,5.013-41.938,7.574-63.223,7.574 c-31.729,0-63.433-5.722-93.018-17.585l-0.009-0.028h-0.028c-30.672-12.643-59.897-32.739-77.819-62.184 c-9.642-15.71-14.935-34.141-14.935-52.659c0-4.19,0.283-8.387,0.843-12.536c-8.072-4.545-15.063-10.99-20.255-18.687 c-5.542-8.266-9.056-17.95-9.5-28.187v-0.04v-0.037v-0.082c0.009-14.337,6.237-27.918,15.915-37.932 c9.677-10.011,22.896-16.554,37.075-16.554c0.196,0,0.392,0,0.588,0c1.487-0.101,2.987-0.159,4.488-0.159 c7.122,0,14.26,1.153,21.039,3.752l0.037,0.028l0.038,0.012c5.787,2.437,11.537,5.377,16.662,9.449 c1.661-0.871,3.472-1.851,5.504-2.625c31.064-18.395,67.171-25.491,102.358-27.538c0.306-17.431,2.448-35.68,10.949-51.65 c7.08-13.269,19.369-23.599,34-27.179l0.061-0.03l0.079-0.009c5.573-1.078,11.192-1.575,16.774-1.575 c14.869,0,29.561,3.521,43.31,9.017c6.086-9.185,14.776-16.354,24.97-20.375l0.098-0.056l0.098-0.037 c5.983-1.864,12.303-2.954,18.646-2.954c6.692,0,13.437,1.223,19.756,4.046v-0.023c0.009,0.023,0.019,0.023,0.019,0.023 c0.047,0.016,0.084,0.044,0.116,0.044c9.059,3.489,16.727,9.937,22.164,17.95c5.442,8.048,8.644,17.688,8.644,27.599 c0,1.827-0.103,3.657-0.317,5.489l-0.019,0.037c0,0.028,0,0.068-0.01,0.096c-1.063,12.809-7.551,24.047-16.736,32.063 c-9.24,8.048-21.207,12.909-33.49,12.909c-1.97,0-3.958-0.11-5.937-0.374c-12.182-0.931-23.541-6.826-31.886-15.595 c-8.373-8.755-13.768-20.453-13.768-33.08c0-0.611,0.056-1.237,0.074-1.843c-11.435-5.092-23.578-9.316-35.646-9.306 c-1.746,0-3.491,0.096-5.237,0.273h-0.019c-9.035,0.871-17.436,6.566-21.506,14.757v0.009v0.028 c-6.179,12.034-7.411,26.101-7.598,40.064c34.639,2.259,69.483,10.571,100.043,28.138h0.047l0.438,0.259 c0.579,0.343,1.652,0.931,2.623,1.449c2.101-1.704,4.322-3.456,6.856-4.966c9.264-6.17,20.241-9.238,31.223-9.238 c4.872,0,9.749,0.621,14.481,1.834h0.019l0.196,0.058c0.07,0.01,0.121,0.033,0.178,0.033v0.009 c11.183,2.845,21.3,9.267,28.917,17.927c7.612,8.674,12.731,19.648,13.73,31.561v0.025H430.033z M328.002,84.733 c0,0.469,0.01,0.95,0.057,1.44v0.028v0.056c0.224,6.018,3.065,11.619,7.383,15.756c4.34,4.14,10.1,6.702,15.942,6.725h0.08h0.079 c0.42,0.033,0.85,0.033,1.26,0.033c5.899,0.009,11.752-2.532,16.148-6.655c4.405-4.144,7.309-9.78,7.542-15.849l0.009-0.028v-0.037 c0.038-0.464,0.057-0.903,0.057-1.377c0-6.247-2.922-12.202-7.496-16.612c-4.555-4.406-10.688-7.136-16.735-7.12 c-1.951,0-3.884,0.266-5.778,0.854l-0.065,0.005l-0.056,0.023c-4.984,1.295-9.656,4.368-13.012,8.449 C330.046,74.486,328.002,79.508,328.002,84.733z M72.312,177.578c-4.63-2.156-9.418-3.696-14.15-3.676 c-0.794,0-1.597,0.047-2.39,0.133h-0.11l-0.11,0.014c-6.795,0.187-13.653,3.15-18.801,7.899 c-5.152,4.732-8.559,11.122-8.821,18.167v0.065l-0.012,0.058c-0.046,0.57-0.065,1.137-0.065,1.683 c0,4.345,1.333,8.545,3.593,12.368c1.673,2.847,3.867,5.441,6.348,7.701C45.735,204.602,58.142,189.845,72.312,177.578z M374.066,262.635c0-15.5-5.592-31.069-14.646-43.604c-18.053-25.119-46.055-41.502-75.187-50.636l-0.205-0.072 c-5.592-1.715-11.238-3.234-16.933-4.534c-17.025-3.876-34.48-5.806-51.917-5.806c-23.414,0-46.827,3.465-69.245,10.379 c-29.125,9.243-57.221,25.51-75.233,50.71v0.019c-9.129,12.587-14.475,28.208-14.475,43.763c0,5.727,0.716,11.453,2.23,17.025 l0.019,0.01c3.278,12.508,9.689,23.671,17.989,33.393c8.295,9.745,18.472,18.058,29.176,24.839c2.371,1.47,4.751,2.87,7.187,4.237 c31.094,17.356,66.898,24.964,102.445,24.964c6.012,0,12.06-0.214,18.033-0.644c35.797-2.959,71.742-13.525,100.8-35.115 l0.01-0.023c9.25-6.837,17.818-15.112,24.595-24.525c6.805-9.418,11.789-19.947,14.002-31.382V275.6l0.009-0.01 C373.627,271.32,374.066,266.985,374.066,262.635z M402.32,200.95c-0.009-3.762-0.868-7.507-2.753-11l-0.047-0.044l-0.019-0.056 c-2.521-5.19-6.479-9.11-11.248-11.782c-4.77-2.69-10.352-4.056-15.952-4.056c-5.063,0-10.1,1.132-14.57,3.379 c14.216,12.344,26.687,27.179,34.746,44.636c2.595-2.259,4.808-5.018,6.464-8.084C401.098,209.92,402.32,205.405,402.32,200.95z"/></svg></span>
						<span class="simplesocialtxt">reddit </span>';
						if ( $show_count ) {
							$_html .= '<span class="ssb_counter">'. $reddit_score .'</span>';
						}

						$_html .=	'</button>';
					} else {
						$_html        = '<button class="simplesocial-reddit-share"  data-href="https://reddit.com/submit?url=' . $permalink . '&title=' . $title . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ><span class="simplesocialtxt">Reddit</span> ';

						if ( $show_count ) {
							$_html .= '<span class="ssb_counter ssb_reddit_counter">' . $reddit_score . '</span>';
						}
						$_html .= '</button>';

					}

					$arrButtonsCode[] = $_html;
					break;
				case 'whatsapp':
					if ( $this->selected_theme == 'simple-icons' ) {
						$arrButtonsCode[] = ' <button class="ssb_whatsapp-icon" onclick="javascript:window.open(this.dataset.href, \'_blank\' );return false;" class="simplesocial-whatsapp-share" data-href="https://api.whatsapp.com/send?text=' . $permalink . '"><span class="simplesocialtxt">
									<span class="icon"> <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="512px" height="512px" viewBox="0 0 90 90" style="enable-background:new 0 0 90 90;" xml:space="preserve" class=""><g><g> <path id="WhatsApp" d="M90,43.841c0,24.213-19.779,43.841-44.182,43.841c-7.747,0-15.025-1.98-21.357-5.455L0,90l7.975-23.522   c-4.023-6.606-6.34-14.354-6.34-22.637C1.635,19.628,21.416,0,45.818,0C70.223,0,90,19.628,90,43.841z M45.818,6.982   c-20.484,0-37.146,16.535-37.146,36.859c0,8.065,2.629,15.534,7.076,21.61L11.107,79.14l14.275-4.537   c5.865,3.851,12.891,6.097,20.437,6.097c20.481,0,37.146-16.533,37.146-36.857S66.301,6.982,45.818,6.982z M68.129,53.938   c-0.273-0.447-0.994-0.717-2.076-1.254c-1.084-0.537-6.41-3.138-7.4-3.495c-0.993-0.358-1.717-0.538-2.438,0.537   c-0.721,1.076-2.797,3.495-3.43,4.212c-0.632,0.719-1.263,0.809-2.347,0.271c-1.082-0.537-4.571-1.673-8.708-5.333   c-3.219-2.848-5.393-6.364-6.025-7.441c-0.631-1.075-0.066-1.656,0.475-2.191c0.488-0.482,1.084-1.255,1.625-1.882   c0.543-0.628,0.723-1.075,1.082-1.793c0.363-0.717,0.182-1.344-0.09-1.883c-0.27-0.537-2.438-5.825-3.34-7.977   c-0.902-2.15-1.803-1.792-2.436-1.792c-0.631,0-1.354-0.09-2.076-0.09c-0.722,0-1.896,0.269-2.889,1.344   c-0.992,1.076-3.789,3.676-3.789,8.963c0,5.288,3.879,10.397,4.422,11.113c0.541,0.716,7.49,11.92,18.5,16.223   C58.2,65.771,58.2,64.336,60.186,64.156c1.984-0.179,6.406-2.599,7.312-5.107C68.398,56.537,68.398,54.386,68.129,53.938z"/> </g></g> </svg> </span>
									<span class="simplesocialtxt">Whatsapp</span>
								</button>';
					} else {

						$arrButtonsCode[] = '<button onclick="javascript:window.open(this.dataset.href, \'_blank\' );return false;" class="simplesocial-whatsapp-share" data-href="https://api.whatsapp.com/send?text=' . $permalink . '"><span class="simplesocialtxt">Share on WhatsApp</span></button>';
					}
					break;

				case 'viber':
					if ( $this->selected_theme == 'simple-icons' ) {
						$arrButtonsCode[] = '<button class="ssb_viber-icon" onclick="javascript:window.open(this.dataset.href, \'_self\' );return false;" class="simplesocial-viber-share" data-href="viber://forward?text=' . $permalink . '">
						<span class="icon"> <svg aria-labelledby="simpleicons-viber-icon" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title id="simpleicons-viber-icon">Viber icon</title><path d="M20.812 2.343c-.596-.549-3.006-2.3-8.376-2.325 0 0-6.331-.38-9.415 2.451C1.302 4.189.698 6.698.634 9.82.569 12.934.487 18.774 6.12 20.36h.005l-.005 2.416s-.034.979.609 1.178c.779.24 1.236-.504 1.98-1.303.409-.439.972-1.088 1.397-1.582 3.851.322 6.813-.416 7.149-.525.777-.254 5.176-.816 5.893-6.658.738-6.021-.357-9.83-2.338-11.547v.004zm.652 11.112c-.615 4.876-4.184 5.187-4.83 5.396-.285.092-2.895.738-6.164.525 0 0-2.445 2.941-3.195 3.705-.121.121-.271.166-.361.145-.135-.029-.164-.18-.164-.404l.015-4.006c-.015 0 0 0 0 0-4.771-1.336-4.485-6.301-4.425-8.91.044-2.596.538-4.726 1.994-6.167 2.611-2.371 7.997-2.012 7.997-2.012 4.543.016 6.721 1.385 7.223 1.846 1.674 1.432 2.529 4.865 1.904 9.893l.006-.011zM7.741 4.983c.242 0 .459.109.629.311.004.002.58.695.83 1.034.235.32.551.83.711 1.115.285.51.104 1.032-.172 1.248l-.566.45c-.285.229-.25.653-.25.653s.84 3.157 3.959 3.953c0 0 .426.039.654-.246l.451-.569c.213-.285.734-.465 1.244-.181.285.15.795.466 1.116.704.339.24 1.032.826 1.036.826.33.271.404.689.18 1.109v.016c-.23.405-.541.78-.934 1.141h-.008c-.314.27-.629.42-.944.449-.03 0-.075.016-.136 0-.135 0-.27-.029-.404-.061v-.014c-.48-.135-1.275-.48-2.596-1.216-.855-.479-1.574-.96-2.189-1.455-.315-.255-.645-.54-.976-.87l-.076-.028-.03-.03-.029-.029c-.331-.33-.615-.66-.871-.98-.48-.609-.96-1.327-1.439-2.189-.735-1.32-1.08-2.115-1.215-2.596H5.7c-.045-.134-.075-.269-.06-.404-.015-.061 0-.105 0-.141.03-.299.189-.614.458-.944h.005c.355-.39.738-.704 1.146-.933.164-.091.329-.135.479-.135h.016l-.003.012zm4.095-.683h.116l.076.002h.02l.089.005h.511l.135.015h.074l.15.016h.03l.104.015h.016l.074.015c.046 0 .076.016.105.016h.091l.075.029.06.016.06.015.03.015h.045l.046.016h.029l.074.016.045.014.046.016.06.016.03.014c.03 0 .06.016.091.016l.044.015.046.016.119.044.061.031.135.06.045.015.045.016.09.045.061.015.029.015.076.031.029.014.061.031.045.014.045.03.059.03.046.029.03.016.061.03.044.03.075.045.045.016.074.044.016.015.045.031.09.074.046.03.044.03.031.014.045.031.074.074.061.045.045.03.016.015.029.016.074.061.046.044.03.03.045.029.045.031.029.015.12.12.06.061.135.135.031.029c.016.016.045.045.061.075l.029.03.166.194.045.06c.014.016.014.031.029.031l.09.135.045.045.09.12.076.12.045.09.059.105.045.09.016.029.029.061.076.15.074.149.031.075c.059.135.104.27.164.42.074.195.135.404.18.63.045.165.076.315.105.48l.029.27.045.3c.016.121.031.256.031.375.014.121.014.24.014.359v.256c0 .016-.006.029-.014.045-.016.03-.031.045-.061.075-.021.015-.049.046-.08.046-.029.014-.059.014-.09.014h-.045c-.029 0-.059-.014-.09-.029-.029-.016-.061-.03-.074-.061-.016-.029-.045-.061-.061-.09s-.031-.06-.031-.09v-.359c-.014-.209-.029-.425-.059-.639-.016-.146-.045-.284-.061-.42 0-.074-.016-.146-.029-.209l-.029-.15-.038-.141-.016-.09-.045-.15c-.029-.12-.074-.24-.119-.36-.029-.091-.061-.165-.105-.239l-.029-.076-.135-.27-.031-.045c-.061-.135-.135-.27-.225-.391l-.045-.074h-.201l-.064-.091c-.055-.089-.114-.165-.18-.239l-.125-.15-.015-.016-.046-.057-.035-.045-.075-.074-.015-.03-.07-.06-.045-.046-.083-.075-.04-.037-.046-.045-.015-.016c-.016-.015-.045-.045-.075-.06l-.076-.062-.03-.015-.061-.046-.074-.06-.045-.036-.03-.016-.06-.053c0-.016-.016-.016-.031-.016l-.029-.029-.015-.016v-.013l-.03-.014-.061-.037-.044-.031-.075-.045-.06-.045-.029-.016-.032-.013h-.09l-.019-.016-.065-.035-.009-.014-.03-.016-.045-.021h-.012l-.045-.016-.025-.015-.045-.015-.01-.011-.03-.016-.053-.029-.03-.015-.09-.03-.074-.029-.137-.016-.044-.029c-.015-.01-.03-.016-.046-.016l-.029-.015c-.029-.011-.045-.016-.075-.03l-.03-.016h-.029l-.061-.029-.029-.016-.045-.015h-.092c-.008 0-.019-.005-.03-.007h-.09l-.045-.016h-.015l-.045-.016h-.041c-.025-.014-.045-.014-.07-.014l-.01-.016-.06-.015c-.03-.016-.056-.016-.084-.016l-.045-.015-.05-.016-.045-.014-.061-.016h-.061l-.179-.022h-.09l-.116-.015h-.076l-.068-.008h-.03l-.054-.016h-.285l-.01-.015h-.061c-.03 0-.064-.015-.09-.03-.03-.016-.061-.029-.081-.06l-.03-.046c-.029-.029-.029-.06-.045-.09-.014-.028-.014-.059-.014-.089s0-.06.015-.09c.016-.029.029-.06.061-.075.015-.03.044-.044.074-.06.029-.016.061-.03.09-.03h.061l.015.066zm.554 1.574l.037.003.061.006c.008 0 .018 0 .029.003.022 0 .045.004.075.006l.06.008.024.016.045.015.048.015.045.016h.03l.042.015.07.015.056.016.026.014h.073l.119.028.046.015.045.015.045.016s.015 0 .015.015l.046.015.044.016.045.016c.015 0 .03.014.046.014.007 0 .014.016.025.016l.064.03h.029l.09.03.05.029.046.03.108.045.06.015.031.031c.045.014.09.044.135.059l.048.03.048.03.049.029c.045.03.082.046.121.076l.029.014.041.031.022.015.075.045.037.03.065.043.029.015.03.015.046.03.06.046c.015.014.022.014.034.029.01.015.016.015.025.03l.033.03.036.029.03.03.046.046.029.03.016.016.09.089.016.016c0 .015.015.03.029.03l.016.013.045.046.029.045.03.03.045.06.046.046.09.119.014.029.061.076.016.029.015.031.015.029.016.03c.016.015.016.03.029.06l.043.076.016.015.029.061.031.044c.014.015.014.029.029.045l.03.045.03.061.029.059.016.046c.015.044.045.075.06.12 0 .015.015.029.015.045l.045.119.061.195c0 .016.015.045.015.061l.046.135.044.18.046.24c.014.074.014.135.029.211.016.119.03.238.03.359l.015.21v.165c0 .016 0 .029-.015.045l-.044.043c-.029.023-.045.045-.074.061-.03.015-.061.029-.09.04-.031.016-.075.016-.105.016-.029 0-.061-.016-.09-.03-.016 0-.03-.016-.045-.021-.031-.014-.061-.039-.075-.065-.03-.03-.046-.06-.046-.091l-.014-.044v-.313c0-.133-.016-.256-.031-.385-.015-.135-.044-.285-.074-.42-.029-.09-.045-.18-.075-.26l-.03-.091-.029-.075-.016-.03-.045-.12-.045-.09-.075-.149-.069-.12v-.019l-.029-.047-.03-.038-.045-.075-.046-.061-.089-.119c-.046-.061-.09-.12-.142-.178-.014-.015-.029-.029-.029-.045l-.03-.029-.017-.016-.03-.014-.03-.027v-.146l-.119-.113-.075-.068v-.014l-.03-.031-.038-.029-.015-.016c0-.015-.016-.015-.029-.015l-.046-.016-.015-.015-.061-.045-.014-.016-.016-.015c-.012-.015-.023-.015-.03-.015l-.06-.045-.016-.016-.06-.029-.011-.016-.045-.029-.03-.016-.03-.029-.029-.031h-.016c-.029-.029-.06-.044-.105-.06l-.044-.03-.03-.014-.016-.016-.045-.03-.044-.015-.06-.03-.046-.015-.015-.016-.056-.014v-.012l-.091-.03-.06-.03-.03-.015h-.06c-.03-.015-.045-.015-.075-.03H13.2l-.045-.016h-.044l-.046-.014-.029-.016h-.061l-.061-.015-.029-.016h-.165l-.069-.015H12.3l-.046-.016c-.029-.014-.06-.029-.09-.06-.014-.03-.045-.06-.06-.089-.015-.031-.03-.061-.03-.091v-.09c.006-.046.016-.075.03-.105.008-.015.015-.03.03-.045.018-.03.045-.06.075-.075.015-.015.03-.015.044-.029.031-.016.061-.016.091-.016h.06l-.014.055zm.454 1.629c.015 0 .03 0 .044.004.016 0 .031 0 .046.002l.052.005c.104.009.213.024.318.046l.104.023.026.008.114.029.059.02.046.016c.045.014.091.045.135.06l.016.015.06.03.09.046.029.014c.016.016.031.016.046.03.015.016.045.03.06.045.061.03.105.075.15.105l.105.09.09.091.061.074.029.029.03.031.044.06.091.135.075.135.06.12.046.105c.044.104.06.195.09.299.029.091.045.196.06.285l.015.15.016.136V9.8c0 .045-.016.075-.03.105-.015.029-.046.074-.075.09-.03.029-.061.045-.105.061-.029.014-.06.014-.09.014-.029 0-.06 0-.09-.014l-.104-.046c-.03-.03-.06-.045-.091-.091-.015-.029-.029-.06-.045-.104v-.166l-.015-.105-.015-.119-.016-.105-.016-.06c0-.015-.014-.045-.014-.06-.03-.121-.09-.24-.15-.36l-.061-.06-.047-.06-.045-.045-.015-.03-.075-.06-.061-.061-.059-.045c-.016-.015-.03-.015-.061-.029l-.09-.061-.061-.03-.029-.015h-.016l-.076-.031-.09-.03-.09-.015h-.075l-.044-.015-.035-.007h-.045l-.06-.016h-.255l-.015-.075h-.039c-.03-.004-.055-.015-.08-.029-.035-.021-.064-.045-.09-.08-.018-.029-.034-.061-.045-.09-.008-.029-.012-.06-.012-.09 0-.037 0-.075.015-.113.015-.039.03-.07.06-.1l.061-.045c.029-.016.061-.03.09-.03l.062-.075h.032z"/></svg> </span>
						<span class="simplesocialtxt">Viber</span>
						</button>';
					} else {

						$arrButtonsCode[] = '<button onclick="javascript:window.open(this.dataset.href, \'_self\' );return false;" class="simplesocial-viber-share" data-href="viber://forward?text=' . $permalink . '"><span class="simplesocialtxt">Share on Viber</span></button>';
					}
					break;

				case 'fblike':
					$_html = '<div class="fb-like ssb-fb-like" data-href="' . $permalink . '" data-layout="button_count" data-action="like" data-size="small" data-show-faces="false" data-share="false"></div>';

					$arrButtonsCode[] = $_html;

					break;
			}
		}

		if ( count( $arrButtonsCode ) > 0 ) {

			$ssb_buttonscode .= '<div class="simplesocialbuttons simplesocial-' . $this->selected_theme . ' ' . $extra_class . '">' . "\n";
			$ssb_buttonscode .= implode( "\n", $arrButtonsCode ) . "\n";
			$ssb_buttonscode .= '</div>' . "\n";

		}

		return $ssb_buttonscode;
	}

	/**
	 * Get the option value
	 *
	 * @param  string  $option Name of option.
	 * @param  boolean $default  Default value.
	 *
	 * @since 2.0
	 */
	function get_option( $option, $default = false ) {
		if ( isset( $this->settings[ $option ] ) ) {
			return $this->settings[ $option ];
		} else {
			return $default;
		}
	}

	function get_post_type() {

		if ( is_home() || is_front_page() ) {
			return 'home';
		} else {
			return get_post_type();
		}

	}

	/**
	 * Add Buttons on SideBar.
	 *
	 * @since 2.0
	 */
	function include_sidebar() {

		// Return Content if hide ssb.
		if ( get_post_meta( get_the_id(), $this->hideCustomMetaKey, true ) == 'true' ) {
			return;
		}

		if ( isset( $this->selected_position['sidebar'] ) && in_array( $this->get_post_type(), $this->_get_settings( 'sidebar', 'posts', array() ) ) ) {
			$show_total = false;
			$show_count = false;
			// Show Total at the end.
			if ( $this->sidebar_option['total_share'] ) {
				$show_total = true;
			}
			if ( $this->sidebar_option['share_counts'] ) {
				$show_count = true;
			}
			if ( in_array( $this->get_post_type(), $this->sidebar_option['posts'] ) ) {
				$class = 'simplesocialbuttons-float-' . $this->sidebar_option['orientation'] . '-center' . ' ' . $this->add_post_class();
				// $class = 'simplesocialbuttons-float-left-post';
				if ( $this->sidebar_option['hide_mobile'] ) {
					$class .= ' simplesocialbuttons-mobile-hidden';
					 }

					 	if ( $this->_get_settings( 'sidebar', 'share_counts' ) ) {
				$class .= ' ssb_counter-activate';
			} 

					$class            .= ' simplesocialbuttons-slide-' . $this->_get_settings( 'sidebar', 'animation', 'no-animation' );
					$_selected_network = apply_filters( 'ssb_sidebar_social_networks', $this->selected_networks );
					echo $this->generate_buttons_code( $_selected_network, $show_count, $show_total, $class );
			}
		}
	}

	function css_file() {
		include_once dirname( __FILE__ ) . '/inc/custom-css.php';
	}

	/**
	 * Update option when user click on dismiss button.
	 *
	 * @since 2.0.0
	 */
	function review_update_notice() {

		if ( ! is_admin() ||
		! current_user_can( 'manage_options' ) ||
		! isset( $_GET['_wpnonce'] ) ||
		! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'ssb-update-nonce' ) ||
		! isset( $_GET['ssb_update_2_0_dismiss'] ) ) {

			return;
		}

		if ( isset( $_GET['ssb_update_2_0_dismiss'] ) ) {
			update_option( 'ssb_update_2_0_dismiss', 'yes' );
		}

	}

	/**
	 * Show 2.0 Update Notice.
	 *
	 * @since 2.0.0
	 */
	function update_notice() {
		// delete_option( 'ssb_update_2_0_dismiss' );
		if ( get_option( 'ssb_update_2_0_dismiss' ) ) {
			return; }

		$scheme      = ( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ) ) ? '&' : '?';
		$url         = admin_url( 'admin.php?page=simple-social-buttons' ) . '&ssb_update_2_0_dismiss=yes';
		$dismiss_url = wp_nonce_url( $url, 'ssb-update-nonce' );

		?>
		<style media="screen">
		.ssb-update-notice { background: #dbf5ff; padding: 20px 20px; border: 1px solid #0085ba; border-radius: 5px; margin: 20px 20px 20px 0; }
		.ssb-update-notice:after { content: ''; display: table; clear: both; }
		.ssb-update-thumbnail { width: 114px; float: left; line-height: 80px; text-align: center; border-right: 4px solid transparent; }
		.ssb-update-thumbnail img { width: 100px; vertical-align: middle; }
		.ssb-update-text { overflow: hidden; }
		.ssb-update-text h3 { font-size: 24px; margin: 0 0 5px; font-weight: 400; line-height: 1.3; }
		.ssb-update-text p { font-size: 13px; margin: 0 0 5px; }
		.ssb_update_dismiss_button{ padding: 7px 12px; background: #0085ba; border: 1px solid #006799; border-radius: 5px; display: inline-block; color: #fff; text-decoration: none; box-shadow: 0px 2px 0px 0px rgba(0, 103, 153, 1); position: relative; margin: 15px 10px 5px 0; }
		.ssb_update_dismiss_button:hover{ top: 2px; box-shadow: 0px 0px 0px 0px rgba(0, 103, 153, 1); color: #fff; background: #006799; }
		</style>
		<div class="ssb-update-notice">
			<div class="ssb-update-thumbnail">
				<img src="<?php echo plugins_url( 'assets/images/ssb_icon.png', __FILE__ ); ?>" alt="">
			</div>
			<div class="ssb-update-text">
				<h3><?php _e( 'Simple Social Buttons 2.0 (Relaunched)', 'simple-social-buttons' ); ?></h3>
				<p><?php _e( 'Simple Social Buttons had 50,000 Active installs and It was abondoned and rarely updated since last 5 years.<br /> We at <a href="https://WPBrigade.com/?utm_source=simple-social-buttons-lite&utm_medium=link-notice-2-0" target="_blank">WPBrigade</a> adopted this plugin and rewrote it completely from scratch.<br /> <a href="https://wpbrigade.com/wordpress/plugins/simple-social-buttons-pro/?utm_source=simple-social-buttons-lite&utm_medium=link-notice-2-0&utm_campaign=pro-upgrade" target="_blank">Check out</a> What\'s new in 2.0 version.<br /> Pardon me, If there is anything broken. Please <a href="https://WPBrigade.com/contact/?utm_source=simple-social-buttons-lite" target="_blank">report</a> us any issue you see in the plugin.', 'simple-social-buttons' ); ?></p>
				<a href="<?php echo $dismiss_url; ?>" class="ssb_update_dismiss_button">Dismiss</a>
				<a href="https://wpbrigade.com/wordpress/plugins/simple-social-buttons-pro/?utm_source=simple-social-buttons-lite&utm_medium=link-learn-more&utm_campaign=pro-upgrade" target="_blank" class="ssb_update_dismiss_button">Learn more</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Add Facebook Like script.
	 *
	 * @since 2.0.4
	 */
	function fblike_script() {

		if ( ! array_key_exists( 'fblike', array_filter( $this->selected_networks ) ) ) {
			return;
		}
		?>
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.11&appId=1158761637505872';
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>
		<?php
	}


	/**
	* short code content
	*
	* @param $atts
	*
	* @since 2.0.2
	* @return string
	*/
	function short_code_content( $atts ) {

		/*

		counter = true,false
		align = left ,right,centered,
		order = googleplus,twitter,pinterest,fbshare,linkedin,reddit,whatsapp,viber,fblike

		theme
		theme1 =  sm-round
		theme2 =  simple-round
		theme3 =  round-txt
		theme4 =  round-btm-border
		Flat =  flat-button-border
		Circle =  round-icon
		Official =  simple-icons
		*/

		$selected_theme = shortcode_atts( array(
			'theme'   => '',
			'order'   => null,
			'align'   => '',
			'counter' => 'false',
		), $atts );

		$themes = array(
			'theme1' => 'sm-round',
			'theme2' => 'simple-round',
			'theme3' => 'round-txt',
			'theme4' => 'round-btm-border',
			'Flat' => 'flat-button-border',
			'Circle' => 'round-icon',
			'Official' => 'simple-icons',
		);


		if ( key_exists( $selected_theme['theme'], $themes ) ) {
			foreach ( $themes as $key => $value ) {

				if ( $selected_theme['theme'] == $key ) {

					$theme = $themes[ $key ];
				}
			}
		} else {
			$theme = $this->selected_theme;
		}
		if ( null !== $selected_theme['order'] ) {
			$selected_theme['order'] = array_flip( array_merge( array( 0 ), explode( ',', $selected_theme['order'] ) ) );

		}

		if ( isset( $this->selected_position['inline'] ) ) {
			// Show Total at the end.
			if ( $this->_get_settings( 'inline', 'total_share' ) ) {
				$show_total = true;
			} else {
				$show_total = false;
			}

			if ( empty( $selected_theme['align'] ) ) {
				$align_class = $this->_get_settings( 'inline', 'icon_alignment', 'left' );
			} else {
				$align_class = $selected_theme['align'];
			}

			$extra_class = 'simplesocialbuttons_inline simplesocialbuttons-align-' . $align_class;

			// if ( $this->inline['share_counts'] ) {
			if ( $selected_theme['counter'] == 'true' ) {
				$show_count  = true;
				$extra_class .= ' ssb_counter-activate';
			} else {
				$show_count = false;
			}


			$extra_class .= ' simplesocialbuttons-inline-' . $this->_get_settings( 'inline', 'animation', 'no-animation' );

			$ssb_buttons_code = $this->generate_buttons_short_code( $theme, $selected_theme['order'], $this->selected_networks, $show_count, $show_total, $extra_class );


		}

		return $ssb_buttons_code;

	}

	/**
	* Generate buttons html code with specified order for short code
	* @since 2.0.2
	*
	* @param mixed $order - order of social buttons
	*/
	function generate_buttons_short_code( $short_code_theme, $short_code_order, $order = null, $show_count = false, $show_total = false, $extra_class = '' ) {

		//googleplus,fbshare,twitter,pinterest,whatsapp,viber,reddit,linkedin

		// define empty buttons code to use
		$ssb_buttonscode = '';

		// get post permalink and title
		$permalink = get_permalink();
		$title     = get_the_title();

		// Sorting the buttons
		$arrButtons = array();
		if ( $short_code_order == null ) {

			foreach ( $this->arrKnownButtons as $button_name ) {
				if ( ! empty( $order[ $button_name ] ) && (int) $order[ $button_name ] != 0 ) {

					$arrButtons[ $button_name ] = $order[ $button_name ];
				}
			}

		} else {

			foreach ( $this->arrKnownButtons as $button_name ) {
				if ( ! empty( $short_code_order[ $button_name ] ) && (int) $short_code_order[ $button_name ] != 0 ) {
					$arrButtons[ $button_name ] = $short_code_order[ $button_name ];
				}
			}

		}
		// echo '<pre>'; print_r( $arrButtons ); echo '</pre>';
		@asort( $arrButtons );

		// add total share index in array.
		if ( $show_total ) {
			$arrButtons['totalshare'] = '100';
		}
		$post_id = get_the_id();

		// // Reset the cache timestamp if needed
		// // if false fetch the new share counts.
		if ( isset( $this->settings['cache'] ) && $this->settings['cache'] == 'off' ) {

			$_share_links = array();
			foreach ( $arrButtons as $social_name => $priority ) {
				if ( 'totalshare' == $social_name || 'viber' == $social_name ) {
					continue;
				}
				$_share_links[ $social_name ] = call_user_func( 'ssb_' . $social_name . '_generate_link', get_permalink() );
			}

			$result = ssb_fetch_shares_via_curl_multi( array_filter( $_share_links ) );

			$share_counts = ssb_fetch_fresh_counts( $result, $post_id );
			// update_post_meta( $post_id,'ssb_cache_timestamp',floor( ( ( date( 'U' ) / 60) / 60 ) ) );
		} else {
			$share_counts = ssb_fetch_cached_counts( array_flip( $arrButtons ), $post_id );
		}

		$arrButtonsCode = array();
		foreach ( $arrButtons as $button_name => $button_sort ) {
			switch ( $button_name ) {
				case 'googleplus':
					$googleplus_share = $share_counts['googleplus'] ? $share_counts['googleplus'] : 0;

					if ( $this->selected_theme == 'simple-icons' ) {
						$_html = '<button class="ssb_gplus-icon" data-href="https://plus.google.com/share?url=' . $permalink . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;">
						<span class="icon"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet" width="30px" height="18px" viewBox="-10 -6 60 36" class="ozWidgetRioButtonSvg_ ozWidgetRioButtonPlusOne_"><path d="M30 7h-3v4h-4v3h4v4h3v-4h4v-3h-4V7z"></path><path d="M11 9.9v4h5.4C16 16.3 14 18 11 18c-3.3 0-5.9-2.8-5.9-6S7.7 6 11 6c1.5 0 2.8.5 3.8 1.5l2.9-2.9C15.9 3 13.7 2 11 2 5.5 2 1 6.5 1 12s4.5 10 10 10c5.8 0 9.6-4.1 9.6-9.8 0-.7-.1-1.5-.2-2.2H11z"></path></svg></span>
						<span class="simplesocialtxt">Google Plus </span>';
						if ( $show_count ) {
							$_html .= '<span class="ssb_counter">'. $googleplus_share .'</span>';
						}
						$_html .= '</button>';
					} else {

						$_html = '<button class="simplesocial-gplus-share" data-href="https://plus.google.com/share?url=' . $permalink . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"><span class="simplesocialtxt">Google+</span>';
						if ( $show_count ) {
							$_html .= '<span class="ssb_counter ssb_googleplus_counter">' . $googleplus_share . '</span>';
						}
						$_html .= '</button>';
					}

					$arrButtonsCode[] = $_html;

					break;

				case 'fbshare':
					$fbshare_share = $share_counts['fbshare'] ? $share_counts['fbshare'] : 0;

					if ( $this->selected_theme == 'simple-icons' ) {
						$_html = '		<button class="ssb_fbshare-icon" target="_blank" data-href="https://www.facebook.com/sharer/sharer.php?u=' . $permalink . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;">
						<span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" class="_1pbq" color="#ffffff"><path fill="#ffffff" fill-rule="evenodd" class="icon" d="M8 14H3.667C2.733 13.9 2 13.167 2 12.233V3.667A1.65 1.65 0 0 1 3.667 2h8.666A1.65 1.65 0 0 1 14 3.667v8.566c0 .934-.733 1.667-1.667 1.767H10v-3.967h1.3l.7-2.066h-2V6.933c0-.466.167-.9.867-.9H12v-1.8c.033 0-.933-.266-1.533-.266-1.267 0-2.434.7-2.467 2.133v1.867H6v2.066h2V14z"></path></svg></span>
						<span class="simplesocialtxt">Share </span>';

						if ( $show_count ) {
							$_html .= ' <span class="ssb_counter">'. $fbshare_share .'</span>';;
						}

						$_html .= ' </button>';
					} else{

						$_html         = '<button class="simplesocial-fb-share" target="_blank" data-href="https://www.facebook.com/sharer/sharer.php?u=' . $permalink . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"><span class="simplesocialtxt">Facebook </span> ';

						if ( $show_count ) {
							$_html .= '<span class="ssb_counter ssb_fbshare_counter">' . $fbshare_share . '</span>';
						}
						$_html .= '</button>';
					}

					$arrButtonsCode[] = $_html;

					break;
				case 'twitter':
					$twitter_share = $share_counts['twitter'] ? $share_counts['twitter'] : 0;
					$via           = ! empty( $this->extra_option['twitter_handle'] ) ? '&via=' . $this->extra_option['twitter_handle'] : '';

					if ( $this->selected_theme == 'simple-icons' ) {

						$_html = '<button class="ssb_tweet-icon"  data-href="https://twitter.com/share?text=' . $title . '&url=' . $permalink . '' . $via . '" rel="nofollow" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;">
						<span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 72 72"><path fill="none" d="M0 0h72v72H0z"/><path class="icon" fill="#fff" d="M68.812 15.14c-2.348 1.04-4.87 1.744-7.52 2.06 2.704-1.62 4.78-4.186 5.757-7.243-2.53 1.5-5.33 2.592-8.314 3.176C56.35 10.59 52.948 9 49.182 9c-7.23 0-13.092 5.86-13.092 13.093 0 1.026.118 2.02.338 2.98C25.543 24.527 15.9 19.318 9.44 11.396c-1.125 1.936-1.77 4.184-1.77 6.58 0 4.543 2.312 8.552 5.824 10.9-2.146-.07-4.165-.658-5.93-1.64-.002.056-.002.11-.002.163 0 6.345 4.513 11.638 10.504 12.84-1.1.298-2.256.457-3.45.457-.845 0-1.666-.078-2.464-.23 1.667 5.2 6.5 8.985 12.23 9.09-4.482 3.51-10.13 5.605-16.26 5.605-1.055 0-2.096-.06-3.122-.184 5.794 3.717 12.676 5.882 20.067 5.882 24.083 0 37.25-19.95 37.25-37.25 0-.565-.013-1.133-.038-1.693 2.558-1.847 4.778-4.15 6.532-6.774z"/></svg></span>';

						if ( $show_count ) {
							$_html .= '<span class="simplesocialtxt">Tweet '. $twitter_share .'</span>';
						} else{
							$_html .= '<span class="simplesocialtxt">Tweet </span>';

						}

						$_html .=	'</button>';


					} else {

						$_html         = '<button class="simplesocial-twt-share" data-href="https://twitter.com/share?text=' . $title . '&url=' . $permalink . '' . $via . '" rel="nofollow" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"><span class="simplesocialtxt">Twitter</span> ';

						if ( $show_count ) {
							$_html .= '<span class="ssb_counter ssb_twitter_counter">' . $twitter_share . '</span>';
						}
						$_html .= '</button>';
					}

					$arrButtonsCode[] = $_html;

					break;
				case 'linkedin':
					$linkedin_share = $share_counts['linkedin'] ? $share_counts['linkedin'] : 0;

					if ( $this->selected_theme == 'simple-icons' ) {

						$_html = '<button class="ssb_linkedin-icon" data-href="https://www.linkedin.com/cws/share?url=' . get_permalink() . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" >
						<span class="icon">
						<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="15px" height="14.1px" viewBox="-301.4 387.5 15 14.1" enable-background="new -301.4 387.5 15 14.1" xml:space="preserve"> <g id="XMLID_398_"> <path id="XMLID_399_" fill="#FFFFFF" d="M-296.2,401.6c0-3.2,0-6.3,0-9.5h0.1c1,0,2,0,2.9,0c0.1,0,0.1,0,0.1,0.1c0,0.4,0,0.8,0,1.2 c0.1-0.1,0.2-0.3,0.3-0.4c0.5-0.7,1.2-1,2.1-1.1c0.8-0.1,1.5,0,2.2,0.3c0.7,0.4,1.2,0.8,1.5,1.4c0.4,0.8,0.6,1.7,0.6,2.5 c0,1.8,0,3.6,0,5.4v0.1c-1.1,0-2.1,0-3.2,0c0-0.1,0-0.1,0-0.2c0-1.6,0-3.2,0-4.8c0-0.4,0-0.8-0.2-1.2c-0.2-0.7-0.8-1-1.6-1 c-0.8,0.1-1.3,0.5-1.6,1.2c-0.1,0.2-0.1,0.5-0.1,0.8c0,1.7,0,3.4,0,5.1c0,0.2,0,0.2-0.2,0.2c-1,0-1.9,0-2.9,0 C-296.1,401.6-296.2,401.6-296.2,401.6z"/> <path id="XMLID_400_" fill="#FFFFFF" d="M-298,401.6L-298,401.6c-1.1,0-2.1,0-3,0c-0.1,0-0.1,0-0.1-0.1c0-3.1,0-6.1,0-9.2 c0-0.1,0-0.1,0.1-0.1c1,0,2,0,2.9,0h0.1C-298,395.3-298,398.5-298,401.6z"/> <path id="XMLID_401_" fill="#FFFFFF" d="M-299.6,390.9c-0.7-0.1-1.2-0.3-1.6-0.8c-0.5-0.8-0.2-2.1,1-2.4c0.6-0.2,1.2-0.1,1.8,0.2 c0.5,0.4,0.7,0.9,0.6,1.5c-0.1,0.7-0.5,1.1-1.1,1.3C-299.1,390.8-299.4,390.8-299.6,390.9L-299.6,390.9z"/></g></svg> </span>
						<span class="simplesocialtxt">Share</span>';

						if ( $show_count ) {
							$_html .= ' <span class="ssb_counter">'. $linkedin_share .'</span>';
						}
						$_html .= ' </button>';
					} else {

						$_html = '<button target="popup" class="simplesocial-linkedin-share" data-href="https://www.linkedin.com/cws/share?url=' . get_permalink() . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"><span class="simplesocialtxt">LinkedIn</span>';

						if ( $show_count ) {
							$_html .= '<span class="ssb_counter ssb_linkedin_counter">' . $linkedin_share . '</span>';
						}
						$_html .= '</button>';
					}

					$arrButtonsCode[] = $_html;

					break;
				case 'pinterest':
					$pinterest_share = $share_counts['pinterest'] ? $share_counts['pinterest'] : 0;

					if ( $this->selected_theme == 'simple-icons' ) {

						$_html = ' <button class="ssb_pinterest-icon" onclick="var e=document.createElement(\'script\');e.setAttribute(\'type\',\'text/javascript\');e.setAttribute(\'charset\',\'UTF-8\');e.setAttribute(\'src\',\'//assets.pinterest.com/js/pinmarklet.js?r=\'+Math.random()*99999999);document.body.appendChild(e);return false;">
						<span class="icon"> <svg xmlns="http://www.w3.org/2000/svg" height="30px" width="30px" viewBox="-1 -1 31 31"><g><path d="M29.449,14.662 C29.449,22.722 22.868,29.256 14.75,29.256 C6.632,29.256 0.051,22.722 0.051,14.662 C0.051,6.601 6.632,0.067 14.75,0.067 C22.868,0.067 29.449,6.601 29.449,14.662" fill="#fff" stroke="#fff" stroke-width="1"></path><path d="M14.733,1.686 C7.516,1.686 1.665,7.495 1.665,14.662 C1.665,20.159 5.109,24.854 9.97,26.744 C9.856,25.718 9.753,24.143 10.016,23.022 C10.253,22.01 11.548,16.572 11.548,16.572 C11.548,16.572 11.157,15.795 11.157,14.646 C11.157,12.842 12.211,11.495 13.522,11.495 C14.637,11.495 15.175,12.326 15.175,13.323 C15.175,14.436 14.462,16.1 14.093,17.643 C13.785,18.935 14.745,19.988 16.028,19.988 C18.351,19.988 20.136,17.556 20.136,14.046 C20.136,10.939 17.888,8.767 14.678,8.767 C10.959,8.767 8.777,11.536 8.777,14.398 C8.777,15.513 9.21,16.709 9.749,17.359 C9.856,17.488 9.872,17.6 9.84,17.731 C9.741,18.141 9.52,19.023 9.477,19.203 C9.42,19.44 9.288,19.491 9.04,19.376 C7.408,18.622 6.387,16.252 6.387,14.349 C6.387,10.256 9.383,6.497 15.022,6.497 C19.555,6.497 23.078,9.705 23.078,13.991 C23.078,18.463 20.239,22.062 16.297,22.062 C14.973,22.062 13.728,21.379 13.302,20.572 C13.302,20.572 12.647,23.05 12.488,23.657 C12.193,24.784 11.396,26.196 10.863,27.058 C12.086,27.434 13.386,27.637 14.733,27.637 C21.95,27.637 27.801,21.828 27.801,14.662 C27.801,7.495 21.95,1.686 14.733,1.686" fill="#bd081c"></path></g></svg> </span>
						<span class="simplesocialtxt">Save</span>';
						if ( $show_count ) {
							$_html .= '<span class="ssb_counter">'. $pinterest_share .'</span>';
						}
						$_html .= ' </button>';
					} else {

						$_html = '<button rel="nofollow" class="simplesocial-pinterest-share" onclick="var e=document.createElement(\'script\');e.setAttribute(\'type\',\'text/javascript\');e.setAttribute(\'charset\',\'UTF-8\');e.setAttribute(\'src\',\'//assets.pinterest.com/js/pinmarklet.js?r=\'+Math.random()*99999999);document.body.appendChild(e);return false;" ><span class="simplesocialtxt">Pinterest</span>';

						if ( $show_count ) {
							$_html .= '<span class="ssb_counter ssb_pinterest_counter">' . $pinterest_share . '</span>';
						}
						$_html .= '</button>';
					}

					$arrButtonsCode[] = $_html;

					break;
				case 'totalshare':
					$total_share      = $share_counts['total'] ? $share_counts['total'] : 0;
					$arrButtonsCode[] = "<span class='ssb_total_counter'>" . $total_share . '<span>Shares</span></span>';
					break;

				case 'reddit':
					$reddit_score = $share_counts['reddit'] ? $share_counts['reddit'] : 0;

					if ( $this->selected_theme == 'simple-icons' ) {
						$_html = ' <button class="ssb_reddit-icon" data-href="https://reddit.com/submit?url=' . $permalink . '&title=' . $title . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;">
						<span class="icon"> <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
						width="430.117px" height="430.117px" viewBox="0 0 430.117 430.117" style="enable-background:new 0 0 430.117 430.117;"
						xml:space="preserve"> <g> <path id="reddit" d="M307.523,231.062c1.11,2.838,1.614,5.769,1.614,8.681c0,5.862-2.025,11.556-5.423,16.204 c-3.36,4.593-8.121,8.158-13.722,9.727h0.01c-0.047,0.019-0.094,0.019-0.117,0.037c-0.023,0-0.061,0.019-0.079,0.019 c-2.623,0.896-5.312,1.316-7.98,1.316c-6.254,0-12.396-2.254-17.306-6.096c-4.872-3.826-8.56-9.324-9.717-15.845h-0.01 c0-0.019,0-0.042-0.009-0.069c0-0.019,0-0.038-0.019-0.065h0.019c-0.364-1.681-0.551-3.36-0.551-5.021 c0-5.647,1.923-11.07,5.097-15.551c3.164-4.453,7.626-7.99,12.848-9.811c0.019,0,0.038-0.01,0.038-0.01 c0.027,0,0.027-0.027,0.051-0.027c2.954-1.092,6.072-1.639,9.157-1.639c5.619,0,11.154,1.704,15.821,4.821 c4.611,3.066,8.354,7.561,10.23,13.143c0.019,0.037,0.019,0.07,0.037,0.103c0,0.037,0.019,0.057,0.037,0.084H307.523z M290.329,300.349c-2.202-1.428-4.751-2.291-7.448-2.291c-2.175,0-4.434,0.621-6.445,1.955l0,0 c-19.004,11.342-41.355,17.558-63.547,17.558c-16.65,0-33.199-3.514-48.192-10.879l-0.077-0.037l-0.075-0.028 c-2.261-0.924-4.837-2.889-7.647-4.76c-1.428-0.925-2.919-1.844-4.574-2.521c-1.633-0.695-3.447-1.181-5.386-1.181 c-1.605,0-3.292,0.359-4.957,1.115c-0.086,0.033-0.168,0.065-0.252,0.098h0.009c-2.616,0.999-4.66,2.829-5.974,4.994 c-1.372,2.23-2.046,4.826-2.046,7.411c0,2.334,0.551,4.667,1.691,6.786c1.085,2.007,2.754,3.762,4.938,4.938 c21.429,14.454,46.662,21.002,71.992,20.979c22.838,0,45.814-5.287,66.27-14.911l0.107-0.065l0.103-0.056 c2.697-1.597,6.282-3.029,9.661-5.115c1.671-1.064,3.304-2.296,4.704-3.897c1.4-1.591,2.525-3.551,3.16-5.875v-0.01 c0.266-1.026,0.392-2.025,0.392-3.024c0-1.899-0.467-3.701-1.241-5.32C294.361,303.775,292.504,301.778,290.329,300.349z M139.875,265.589c0.037,0,0.086,0.014,0.128,0.037c2.735,0.999,5.554,1.493,8.345,1.493c6.963,0,13.73-2.852,18.853-7.5 c5.115-4.662,8.618-11.257,8.618-18.775c0-0.196,0-0.392-0.009-0.625c0.019-0.336,0.028-0.705,0.028-1.083 c0-7.458-3.456-14.08-8.522-18.762c-5.085-4.686-11.836-7.551-18.825-7.551c-1.867,0-3.769,0.219-5.628,0.653 c-0.028,0-0.049,0.009-0.077,0.009c0,0-0.019,0-0.028,0c-9.252,1.937-17.373,8.803-20.37,18.248l0,0v0.01 c0,0.019-0.009,0.037-0.009,0.037c-0.861,2.586-1.262,5.255-1.262,7.896c0,5.787,1.913,11.426,5.211,16.064 c3.269,4.56,7.894,8.145,13.448,9.819C139.816,265.561,139.835,265.571,139.875,265.589z M430.033,198.094v0.038 c0.066,0.94,0.084,1.878,0.084,2.81c0,10.447-3.351,20.493-8.941,29.016c-5.218,7.976-12.414,14.649-20.703,19.177 c0.532,4.158,0.84,8.349,0.84,12.526c-0.01,22.495-7.766,44.607-21.272,62.329v0.009h-0.028 c-24.969,33.216-63.313,52.804-102.031,62.684h-0.01l-0.027,0.023c-20.647,5.013-41.938,7.574-63.223,7.574 c-31.729,0-63.433-5.722-93.018-17.585l-0.009-0.028h-0.028c-30.672-12.643-59.897-32.739-77.819-62.184 c-9.642-15.71-14.935-34.141-14.935-52.659c0-4.19,0.283-8.387,0.843-12.536c-8.072-4.545-15.063-10.99-20.255-18.687 c-5.542-8.266-9.056-17.95-9.5-28.187v-0.04v-0.037v-0.082c0.009-14.337,6.237-27.918,15.915-37.932 c9.677-10.011,22.896-16.554,37.075-16.554c0.196,0,0.392,0,0.588,0c1.487-0.101,2.987-0.159,4.488-0.159 c7.122,0,14.26,1.153,21.039,3.752l0.037,0.028l0.038,0.012c5.787,2.437,11.537,5.377,16.662,9.449 c1.661-0.871,3.472-1.851,5.504-2.625c31.064-18.395,67.171-25.491,102.358-27.538c0.306-17.431,2.448-35.68,10.949-51.65 c7.08-13.269,19.369-23.599,34-27.179l0.061-0.03l0.079-0.009c5.573-1.078,11.192-1.575,16.774-1.575 c14.869,0,29.561,3.521,43.31,9.017c6.086-9.185,14.776-16.354,24.97-20.375l0.098-0.056l0.098-0.037 c5.983-1.864,12.303-2.954,18.646-2.954c6.692,0,13.437,1.223,19.756,4.046v-0.023c0.009,0.023,0.019,0.023,0.019,0.023 c0.047,0.016,0.084,0.044,0.116,0.044c9.059,3.489,16.727,9.937,22.164,17.95c5.442,8.048,8.644,17.688,8.644,27.599 c0,1.827-0.103,3.657-0.317,5.489l-0.019,0.037c0,0.028,0,0.068-0.01,0.096c-1.063,12.809-7.551,24.047-16.736,32.063 c-9.24,8.048-21.207,12.909-33.49,12.909c-1.97,0-3.958-0.11-5.937-0.374c-12.182-0.931-23.541-6.826-31.886-15.595 c-8.373-8.755-13.768-20.453-13.768-33.08c0-0.611,0.056-1.237,0.074-1.843c-11.435-5.092-23.578-9.316-35.646-9.306 c-1.746,0-3.491,0.096-5.237,0.273h-0.019c-9.035,0.871-17.436,6.566-21.506,14.757v0.009v0.028 c-6.179,12.034-7.411,26.101-7.598,40.064c34.639,2.259,69.483,10.571,100.043,28.138h0.047l0.438,0.259 c0.579,0.343,1.652,0.931,2.623,1.449c2.101-1.704,4.322-3.456,6.856-4.966c9.264-6.17,20.241-9.238,31.223-9.238 c4.872,0,9.749,0.621,14.481,1.834h0.019l0.196,0.058c0.07,0.01,0.121,0.033,0.178,0.033v0.009 c11.183,2.845,21.3,9.267,28.917,17.927c7.612,8.674,12.731,19.648,13.73,31.561v0.025H430.033z M328.002,84.733 c0,0.469,0.01,0.95,0.057,1.44v0.028v0.056c0.224,6.018,3.065,11.619,7.383,15.756c4.34,4.14,10.1,6.702,15.942,6.725h0.08h0.079 c0.42,0.033,0.85,0.033,1.26,0.033c5.899,0.009,11.752-2.532,16.148-6.655c4.405-4.144,7.309-9.78,7.542-15.849l0.009-0.028v-0.037 c0.038-0.464,0.057-0.903,0.057-1.377c0-6.247-2.922-12.202-7.496-16.612c-4.555-4.406-10.688-7.136-16.735-7.12 c-1.951,0-3.884,0.266-5.778,0.854l-0.065,0.005l-0.056,0.023c-4.984,1.295-9.656,4.368-13.012,8.449 C330.046,74.486,328.002,79.508,328.002,84.733z M72.312,177.578c-4.63-2.156-9.418-3.696-14.15-3.676 c-0.794,0-1.597,0.047-2.39,0.133h-0.11l-0.11,0.014c-6.795,0.187-13.653,3.15-18.801,7.899 c-5.152,4.732-8.559,11.122-8.821,18.167v0.065l-0.012,0.058c-0.046,0.57-0.065,1.137-0.065,1.683 c0,4.345,1.333,8.545,3.593,12.368c1.673,2.847,3.867,5.441,6.348,7.701C45.735,204.602,58.142,189.845,72.312,177.578z M374.066,262.635c0-15.5-5.592-31.069-14.646-43.604c-18.053-25.119-46.055-41.502-75.187-50.636l-0.205-0.072 c-5.592-1.715-11.238-3.234-16.933-4.534c-17.025-3.876-34.48-5.806-51.917-5.806c-23.414,0-46.827,3.465-69.245,10.379 c-29.125,9.243-57.221,25.51-75.233,50.71v0.019c-9.129,12.587-14.475,28.208-14.475,43.763c0,5.727,0.716,11.453,2.23,17.025 l0.019,0.01c3.278,12.508,9.689,23.671,17.989,33.393c8.295,9.745,18.472,18.058,29.176,24.839c2.371,1.47,4.751,2.87,7.187,4.237 c31.094,17.356,66.898,24.964,102.445,24.964c6.012,0,12.06-0.214,18.033-0.644c35.797-2.959,71.742-13.525,100.8-35.115 l0.01-0.023c9.25-6.837,17.818-15.112,24.595-24.525c6.805-9.418,11.789-19.947,14.002-31.382V275.6l0.009-0.01 C373.627,271.32,374.066,266.985,374.066,262.635z M402.32,200.95c-0.009-3.762-0.868-7.507-2.753-11l-0.047-0.044l-0.019-0.056 c-2.521-5.19-6.479-9.11-11.248-11.782c-4.77-2.69-10.352-4.056-15.952-4.056c-5.063,0-10.1,1.132-14.57,3.379 c14.216,12.344,26.687,27.179,34.746,44.636c2.595-2.259,4.808-5.018,6.464-8.084C401.098,209.92,402.32,205.405,402.32,200.95z"/></svg></span>
						<span class="simplesocialtxt">reddit </span>';
						if ( $show_count ) {
							$_html .= '<span class="ssb_counter">'. $reddit_score .'</span>';
						}

						$_html .=	'</button>';
					} else {
						$_html        = '<button class="simplesocial-reddit-share"  data-href="https://reddit.com/submit?url=' . $permalink . '&title=' . $title . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ><span class="simplesocialtxt">Reddit</span> ';

						if ( $show_count ) {
							$_html .= '<span class="ssb_counter ssb_reddit_counter">' . $reddit_score . '</span>';
						}
						$_html .= '</button>';

					}

					$arrButtonsCode[] = $_html;
					break;
				case 'whatsapp':
					if ( $this->selected_theme == 'simple-icons' ) {
						$arrButtonsCode[] = ' <button class="ssb_whatsapp-icon" onclick="javascript:window.open(this.dataset.href, \'_blank\' );return false;" class="simplesocial-whatsapp-share" data-href="https://api.whatsapp.com/send?text=' . $permalink . '"><span class="simplesocialtxt">
									<span class="icon"> <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="512px" height="512px" viewBox="0 0 90 90" style="enable-background:new 0 0 90 90;" xml:space="preserve" class=""><g><g> <path id="WhatsApp" d="M90,43.841c0,24.213-19.779,43.841-44.182,43.841c-7.747,0-15.025-1.98-21.357-5.455L0,90l7.975-23.522   c-4.023-6.606-6.34-14.354-6.34-22.637C1.635,19.628,21.416,0,45.818,0C70.223,0,90,19.628,90,43.841z M45.818,6.982   c-20.484,0-37.146,16.535-37.146,36.859c0,8.065,2.629,15.534,7.076,21.61L11.107,79.14l14.275-4.537   c5.865,3.851,12.891,6.097,20.437,6.097c20.481,0,37.146-16.533,37.146-36.857S66.301,6.982,45.818,6.982z M68.129,53.938   c-0.273-0.447-0.994-0.717-2.076-1.254c-1.084-0.537-6.41-3.138-7.4-3.495c-0.993-0.358-1.717-0.538-2.438,0.537   c-0.721,1.076-2.797,3.495-3.43,4.212c-0.632,0.719-1.263,0.809-2.347,0.271c-1.082-0.537-4.571-1.673-8.708-5.333   c-3.219-2.848-5.393-6.364-6.025-7.441c-0.631-1.075-0.066-1.656,0.475-2.191c0.488-0.482,1.084-1.255,1.625-1.882   c0.543-0.628,0.723-1.075,1.082-1.793c0.363-0.717,0.182-1.344-0.09-1.883c-0.27-0.537-2.438-5.825-3.34-7.977   c-0.902-2.15-1.803-1.792-2.436-1.792c-0.631,0-1.354-0.09-2.076-0.09c-0.722,0-1.896,0.269-2.889,1.344   c-0.992,1.076-3.789,3.676-3.789,8.963c0,5.288,3.879,10.397,4.422,11.113c0.541,0.716,7.49,11.92,18.5,16.223   C58.2,65.771,58.2,64.336,60.186,64.156c1.984-0.179,6.406-2.599,7.312-5.107C68.398,56.537,68.398,54.386,68.129,53.938z"/> </g></g> </svg> </span>
									<span class="simplesocialtxt">Whatsapp</span>
								</button>';
					} else {

						$arrButtonsCode[] = '<button onclick="javascript:window.open(this.dataset.href, \'_blank\' );return false;" class="simplesocial-whatsapp-share" data-href="https://api.whatsapp.com/send?text=' . $permalink . '"><span class="simplesocialtxt">Share on WhatsApp</span></button>';
					}
					break;

				case 'viber':
					if ( $this->selected_theme == 'simple-icons' ) {
						$arrButtonsCode[] = '<button class="ssb_viber-icon" onclick="javascript:window.open(this.dataset.href, \'_self\' );return false;" class="simplesocial-viber-share" data-href="viber://forward?text=' . $permalink . '">
						<span class="icon"> <svg aria-labelledby="simpleicons-viber-icon" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title id="simpleicons-viber-icon">Viber icon</title><path d="M20.812 2.343c-.596-.549-3.006-2.3-8.376-2.325 0 0-6.331-.38-9.415 2.451C1.302 4.189.698 6.698.634 9.82.569 12.934.487 18.774 6.12 20.36h.005l-.005 2.416s-.034.979.609 1.178c.779.24 1.236-.504 1.98-1.303.409-.439.972-1.088 1.397-1.582 3.851.322 6.813-.416 7.149-.525.777-.254 5.176-.816 5.893-6.658.738-6.021-.357-9.83-2.338-11.547v.004zm.652 11.112c-.615 4.876-4.184 5.187-4.83 5.396-.285.092-2.895.738-6.164.525 0 0-2.445 2.941-3.195 3.705-.121.121-.271.166-.361.145-.135-.029-.164-.18-.164-.404l.015-4.006c-.015 0 0 0 0 0-4.771-1.336-4.485-6.301-4.425-8.91.044-2.596.538-4.726 1.994-6.167 2.611-2.371 7.997-2.012 7.997-2.012 4.543.016 6.721 1.385 7.223 1.846 1.674 1.432 2.529 4.865 1.904 9.893l.006-.011zM7.741 4.983c.242 0 .459.109.629.311.004.002.58.695.83 1.034.235.32.551.83.711 1.115.285.51.104 1.032-.172 1.248l-.566.45c-.285.229-.25.653-.25.653s.84 3.157 3.959 3.953c0 0 .426.039.654-.246l.451-.569c.213-.285.734-.465 1.244-.181.285.15.795.466 1.116.704.339.24 1.032.826 1.036.826.33.271.404.689.18 1.109v.016c-.23.405-.541.78-.934 1.141h-.008c-.314.27-.629.42-.944.449-.03 0-.075.016-.136 0-.135 0-.27-.029-.404-.061v-.014c-.48-.135-1.275-.48-2.596-1.216-.855-.479-1.574-.96-2.189-1.455-.315-.255-.645-.54-.976-.87l-.076-.028-.03-.03-.029-.029c-.331-.33-.615-.66-.871-.98-.48-.609-.96-1.327-1.439-2.189-.735-1.32-1.08-2.115-1.215-2.596H5.7c-.045-.134-.075-.269-.06-.404-.015-.061 0-.105 0-.141.03-.299.189-.614.458-.944h.005c.355-.39.738-.704 1.146-.933.164-.091.329-.135.479-.135h.016l-.003.012zm4.095-.683h.116l.076.002h.02l.089.005h.511l.135.015h.074l.15.016h.03l.104.015h.016l.074.015c.046 0 .076.016.105.016h.091l.075.029.06.016.06.015.03.015h.045l.046.016h.029l.074.016.045.014.046.016.06.016.03.014c.03 0 .06.016.091.016l.044.015.046.016.119.044.061.031.135.06.045.015.045.016.09.045.061.015.029.015.076.031.029.014.061.031.045.014.045.03.059.03.046.029.03.016.061.03.044.03.075.045.045.016.074.044.016.015.045.031.09.074.046.03.044.03.031.014.045.031.074.074.061.045.045.03.016.015.029.016.074.061.046.044.03.03.045.029.045.031.029.015.12.12.06.061.135.135.031.029c.016.016.045.045.061.075l.029.03.166.194.045.06c.014.016.014.031.029.031l.09.135.045.045.09.12.076.12.045.09.059.105.045.09.016.029.029.061.076.15.074.149.031.075c.059.135.104.27.164.42.074.195.135.404.18.63.045.165.076.315.105.48l.029.27.045.3c.016.121.031.256.031.375.014.121.014.24.014.359v.256c0 .016-.006.029-.014.045-.016.03-.031.045-.061.075-.021.015-.049.046-.08.046-.029.014-.059.014-.09.014h-.045c-.029 0-.059-.014-.09-.029-.029-.016-.061-.03-.074-.061-.016-.029-.045-.061-.061-.09s-.031-.06-.031-.09v-.359c-.014-.209-.029-.425-.059-.639-.016-.146-.045-.284-.061-.42 0-.074-.016-.146-.029-.209l-.029-.15-.038-.141-.016-.09-.045-.15c-.029-.12-.074-.24-.119-.36-.029-.091-.061-.165-.105-.239l-.029-.076-.135-.27-.031-.045c-.061-.135-.135-.27-.225-.391l-.045-.074h-.201l-.064-.091c-.055-.089-.114-.165-.18-.239l-.125-.15-.015-.016-.046-.057-.035-.045-.075-.074-.015-.03-.07-.06-.045-.046-.083-.075-.04-.037-.046-.045-.015-.016c-.016-.015-.045-.045-.075-.06l-.076-.062-.03-.015-.061-.046-.074-.06-.045-.036-.03-.016-.06-.053c0-.016-.016-.016-.031-.016l-.029-.029-.015-.016v-.013l-.03-.014-.061-.037-.044-.031-.075-.045-.06-.045-.029-.016-.032-.013h-.09l-.019-.016-.065-.035-.009-.014-.03-.016-.045-.021h-.012l-.045-.016-.025-.015-.045-.015-.01-.011-.03-.016-.053-.029-.03-.015-.09-.03-.074-.029-.137-.016-.044-.029c-.015-.01-.03-.016-.046-.016l-.029-.015c-.029-.011-.045-.016-.075-.03l-.03-.016h-.029l-.061-.029-.029-.016-.045-.015h-.092c-.008 0-.019-.005-.03-.007h-.09l-.045-.016h-.015l-.045-.016h-.041c-.025-.014-.045-.014-.07-.014l-.01-.016-.06-.015c-.03-.016-.056-.016-.084-.016l-.045-.015-.05-.016-.045-.014-.061-.016h-.061l-.179-.022h-.09l-.116-.015h-.076l-.068-.008h-.03l-.054-.016h-.285l-.01-.015h-.061c-.03 0-.064-.015-.09-.03-.03-.016-.061-.029-.081-.06l-.03-.046c-.029-.029-.029-.06-.045-.09-.014-.028-.014-.059-.014-.089s0-.06.015-.09c.016-.029.029-.06.061-.075.015-.03.044-.044.074-.06.029-.016.061-.03.09-.03h.061l.015.066zm.554 1.574l.037.003.061.006c.008 0 .018 0 .029.003.022 0 .045.004.075.006l.06.008.024.016.045.015.048.015.045.016h.03l.042.015.07.015.056.016.026.014h.073l.119.028.046.015.045.015.045.016s.015 0 .015.015l.046.015.044.016.045.016c.015 0 .03.014.046.014.007 0 .014.016.025.016l.064.03h.029l.09.03.05.029.046.03.108.045.06.015.031.031c.045.014.09.044.135.059l.048.03.048.03.049.029c.045.03.082.046.121.076l.029.014.041.031.022.015.075.045.037.03.065.043.029.015.03.015.046.03.06.046c.015.014.022.014.034.029.01.015.016.015.025.03l.033.03.036.029.03.03.046.046.029.03.016.016.09.089.016.016c0 .015.015.03.029.03l.016.013.045.046.029.045.03.03.045.06.046.046.09.119.014.029.061.076.016.029.015.031.015.029.016.03c.016.015.016.03.029.06l.043.076.016.015.029.061.031.044c.014.015.014.029.029.045l.03.045.03.061.029.059.016.046c.015.044.045.075.06.12 0 .015.015.029.015.045l.045.119.061.195c0 .016.015.045.015.061l.046.135.044.18.046.24c.014.074.014.135.029.211.016.119.03.238.03.359l.015.21v.165c0 .016 0 .029-.015.045l-.044.043c-.029.023-.045.045-.074.061-.03.015-.061.029-.09.04-.031.016-.075.016-.105.016-.029 0-.061-.016-.09-.03-.016 0-.03-.016-.045-.021-.031-.014-.061-.039-.075-.065-.03-.03-.046-.06-.046-.091l-.014-.044v-.313c0-.133-.016-.256-.031-.385-.015-.135-.044-.285-.074-.42-.029-.09-.045-.18-.075-.26l-.03-.091-.029-.075-.016-.03-.045-.12-.045-.09-.075-.149-.069-.12v-.019l-.029-.047-.03-.038-.045-.075-.046-.061-.089-.119c-.046-.061-.09-.12-.142-.178-.014-.015-.029-.029-.029-.045l-.03-.029-.017-.016-.03-.014-.03-.027v-.146l-.119-.113-.075-.068v-.014l-.03-.031-.038-.029-.015-.016c0-.015-.016-.015-.029-.015l-.046-.016-.015-.015-.061-.045-.014-.016-.016-.015c-.012-.015-.023-.015-.03-.015l-.06-.045-.016-.016-.06-.029-.011-.016-.045-.029-.03-.016-.03-.029-.029-.031h-.016c-.029-.029-.06-.044-.105-.06l-.044-.03-.03-.014-.016-.016-.045-.03-.044-.015-.06-.03-.046-.015-.015-.016-.056-.014v-.012l-.091-.03-.06-.03-.03-.015h-.06c-.03-.015-.045-.015-.075-.03H13.2l-.045-.016h-.044l-.046-.014-.029-.016h-.061l-.061-.015-.029-.016h-.165l-.069-.015H12.3l-.046-.016c-.029-.014-.06-.029-.09-.06-.014-.03-.045-.06-.06-.089-.015-.031-.03-.061-.03-.091v-.09c.006-.046.016-.075.03-.105.008-.015.015-.03.03-.045.018-.03.045-.06.075-.075.015-.015.03-.015.044-.029.031-.016.061-.016.091-.016h.06l-.014.055zm.454 1.629c.015 0 .03 0 .044.004.016 0 .031 0 .046.002l.052.005c.104.009.213.024.318.046l.104.023.026.008.114.029.059.02.046.016c.045.014.091.045.135.06l.016.015.06.03.09.046.029.014c.016.016.031.016.046.03.015.016.045.03.06.045.061.03.105.075.15.105l.105.09.09.091.061.074.029.029.03.031.044.06.091.135.075.135.06.12.046.105c.044.104.06.195.09.299.029.091.045.196.06.285l.015.15.016.136V9.8c0 .045-.016.075-.03.105-.015.029-.046.074-.075.09-.03.029-.061.045-.105.061-.029.014-.06.014-.09.014-.029 0-.06 0-.09-.014l-.104-.046c-.03-.03-.06-.045-.091-.091-.015-.029-.029-.06-.045-.104v-.166l-.015-.105-.015-.119-.016-.105-.016-.06c0-.015-.014-.045-.014-.06-.03-.121-.09-.24-.15-.36l-.061-.06-.047-.06-.045-.045-.015-.03-.075-.06-.061-.061-.059-.045c-.016-.015-.03-.015-.061-.029l-.09-.061-.061-.03-.029-.015h-.016l-.076-.031-.09-.03-.09-.015h-.075l-.044-.015-.035-.007h-.045l-.06-.016h-.255l-.015-.075h-.039c-.03-.004-.055-.015-.08-.029-.035-.021-.064-.045-.09-.08-.018-.029-.034-.061-.045-.09-.008-.029-.012-.06-.012-.09 0-.037 0-.075.015-.113.015-.039.03-.07.06-.1l.061-.045c.029-.016.061-.03.09-.03l.062-.075h.032z"/></svg> </span>
						<span class="simplesocialtxt">Viber</span>
						</button>';
					} else {

						$arrButtonsCode[] = '<button onclick="javascript:window.open(this.dataset.href, \'_self\' );return false;" class="simplesocial-viber-share" data-href="viber://forward?text=' . $permalink . '"><span class="simplesocialtxt">Share on Viber</span></button>';
					}
					break;

				case 'fblike':
					$_html = '<div class="fb-like ssb-fb-like" data-href="' . $permalink . '" data-layout="button_count" data-action="like" data-size="small" data-show-faces="false" data-share="false"></div>';

					$arrButtonsCode[] = $_html;

					break;
			}
		}

		if ( count( $arrButtonsCode ) > 0 ) {

			$ssb_buttonscode .= '<div class="simplesocialbuttons simplesocial-' . $short_code_theme . ' ' . $extra_class . '">' . "\n";
			$ssb_buttonscode .= implode( "\n", $arrButtonsCode ) . "\n";
			$ssb_buttonscode .= '</div>' . "\n";

		}

		return $ssb_buttonscode;
	}

} // end class


global $_ssb_pr;
if ( is_admin() ) {
	include_once dirname( __FILE__ ) . '/classes/ssb-admin.php';
	include_once dirname( __FILE__ ) . '/classes/ssb-widget.php';

	$_ssb_pr = new SimpleSocialButtonsPR_Admin();
} else {
	include_once dirname( __FILE__ ) . '/classes/ssb-widget.php';

	$_ssb_pr = new SimpleSocialButtonsPR();
}

function get_ssb( $order = null ) {
	return '<!-- use shortcode instead of this function call - [SSB theme="theme1" aign="right" counter="true" order="googleplus,twitter,pinterest,fbshare,linkedin" ] -->';
}

?>
