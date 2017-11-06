<?php
/**
 * Plugin Name: Simple Social Buttons
 * Plugin URI: http://www.WPBrigade.com/wordpress/plugins/simple-social-buttons/
 * Description: Simple Social Buttons adds an advanced set of social media sharing buttons to your WordPress sites, such as: Google +1, Facebook, WhatsApp, Viber, Twitter, Reddit, LinkedIn and Pinterest. This makes it the most <code>Flexible Social Sharing Plugin ever for Everyone.</code>
 * Version: 2.0.2
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
	public $pluginName = 'Simple Social Buttons';
	public $pluginVersion = '2.0.2';
	public $pluginPrefix = 'ssb_pr_';
	public $hideCustomMetaKey = '_ssb_hide';

	// plugin default settings
	public $pluginDefaultSettings = array(
		'googleplus' => '1',
		'twitter' => '3',
		'pinterest' => '0',
		'beforepost' => '1',
		'afterpost' => '0',
		'beforepage' => '1',
		'afterpage' => '0',
		'beforearchive' => '0',
		'afterarchive' => '0',
		'fbshare' => '0',
		'linkedin' => '0',
		'cache' => 'on',
	);

	// defined buttons
	public $arrKnownButtons = array( 'googleplus', 'twitter', 'pinterest', 'fbshare', 'linkedin', 'reddit', 'whatsapp', 'viber' );

	// an array to store current settings, to avoid passing them between functions
	public $settings = array();

	public $selected_networks = array();
	public $selected_theme = '';
	public $selected_position = '';
	public $inline_option = '';
	public $sidebar_option = '';
	public $extra_option = '';

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
		add_action( 'wp_footer' , array( $this, 'ssb_footer_functions' ), 99 );

		add_filter( 'ssb_footer_scripts', array( $this, 'ssb_output_cache_trigger' ) );

		add_action( 'wp_ajax_ssb_fetch_data', array( $this, 'ajax_fetch_fresh_data' ) );
		add_action( 'wp_ajax_nopriv_ssb_fetch_data', array( $this, 'ajax_fetch_fresh_data' ) );

		add_action( 'wp_footer', array( $this, 'include_sidebar' ) );
		add_action( 'wp_head', array( $this, 'css_file' ) );

		add_action( 'admin_notices', array( $this, 'update_notice' ) );
		add_action( 'admin_init', array( $this, 'review_update_notice' ) );

	}

	function set_selected_networks() {
		$networks = get_option( 'ssb_networks' );
		$this->selected_networks = array_flip( array_merge( array( 0 ), explode( ',', $networks['icon_selection'] ) ) );

	}

	function set_selected_theme() {
		$theme = get_option( 'ssb_themes' );
		$this->selected_theme = $theme['icon_style'];

	}

	function set_selected_position() {
		$theme = get_option( 'ssb_positions' );
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

		$order = array();
		$post_id = $_POST['postID'];
		foreach ( $this->arrKnownButtons as $button_name ) {

			if ( isset( $this->settings[ $button_name ] ) && $this->settings[ $button_name ] > 0 ) {
				$order[ $button_name ] = $this->settings[ $button_name ];
			}
		}

		$_share_links = array();
		foreach ( $order as $social_name => $priority ) {
			if ( 'totalshare' == $social_name || 'viber' == $social_name ) {
				continue; }
			$_share_links[ $social_name ] = call_user_func( 'ssb_' . $social_name . '_generate_link', get_permalink( $post_id ) );
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
			$share_counts = ssb_fetch_fresh_counts( $result , $post_id );
			update_post_meta( $post_id, 'ssb_cache_timestamp',floor( ( ( date( 'U' ) / 60) / 60 ) ) );
			  echo json_encode( $share_counts );
			wp_die();
	}

	function ssb_output_cache_trigger( $info ) {

		// Return early if we're not on a single page or we have fresh cache.
		if ( ( ! is_singular() || ssb_is_cache_fresh( $info['postID'], true )) && empty( $_GET['ssb_cache'] ) ) {
			return $info;
		}

		// Return if we're on a WooCommerce account page.
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return $info;
		}
 
		// Return if caching is off.
		// if (  'on' != $this->settings['cache'] ) {
		// 	return $info;
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
				console.log(array);

				jQuery.each( array, function( index, value ){
					console.log(index);
					console.log(value);
					jQuery('.ssb_'+ index +'_counter').html(value);
				});


			});
		}

		<?php
		$info['footer_output'] .= ob_get_clean();

		return $info;
	}


	function ssb_footer_functions() {

		// Fetch a few variables.
		$info['postID']           = get_the_ID();
		$info['footer_output']    = '';

		// Pass the array through our custom filters.
		$info = apply_filters( 'ssb_footer_scripts' , $info );

		// If we have output, output it.
		if ( $info['footer_output'] ) {
			echo '<script type="text/javascript">';
			echo $info['footer_output'];
			echo '</script>';
		}
	}



	function front_enqueue_scripts() {

		wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css' );
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
				'icon_selection' => 'fbshare,twitter,googleplus,linkedin',
			);
			update_option( 'ssb_networks', $_default );
		}

		if ( ! get_option( 'ssb_themes' ) ) {
			$_default = array(
				'icon_style' => 'sm-round',
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
				'posts' => array(
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
		$_arr = $this->$section;
		return  isset( $_arr[ $value ] ) && ! empty( $_arr[ $value ] ) ? $_arr[ $value ] : $default;
	}

	/**
	 * Returns true on pages where buttons should be shown
	 */
	function where_to_insert() {

		$return = false;

		// Single Page/Post
		if ( isset( $this->selected_position['inline'] ) && 'false' == get_post_meta( get_the_ID(), $this->hideCustomMetaKey , true ) ) {
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
	 * Add Inline Buttons.
	 *
	 * @since 1.0
	 */
	function insert_buttons( $content ) {

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

			$extra_class = 'simplesocialbuttons_inline simplesocialbuttons-align-' . $this->_get_settings( 'inline', 'icon_alignment', 'left' );

			// if ( $this->inline['share_counts'] ) {
			if ( $this->_get_settings( 'inline', 'share_counts' ) ) {
				$show_count = true;
				$extra_class .= ' ssb_counter-activate';
			} else {
				$show_count = false;
			}

			if ( $this->_get_settings( 'inline', 'hide_mobile' ) ) {
				$extra_class .= ' simplesocialbuttons-mobile-hidden'; }
			$extra_class .= ' simplesocialbuttons-inline-' .  $this->_get_settings( 'inline', 'animation', 'no-animation' );

			$ssb_buttonscode = $this->generate_buttons_code( $this->selected_networks, $show_count, $show_total, $extra_class );

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
		$title = get_the_title();

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
				if ( 'totalshare' == $social_name || 'viber' == $social_name ) {
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
			$share_counts = ssb_fetch_fresh_counts( $result , $post_id );
			// update_post_meta( $post_id,'ssb_cache_timestamp',floor( ( ( date( 'U' ) / 60) / 60 ) ) );
		} else {
			$share_counts = ssb_fetch_cached_counts( array_flip( $arrButtons ), $post_id );
		}

		$arrButtonsCode = array();
		foreach ( $arrButtons as $button_name => $button_sort ) {
			switch ( $button_name ) {
				case 'googleplus':
					$googleplus_share = $share_counts['googleplus'] ? $share_counts['googleplus'] : 0;

					$_html = '<button class="simplesocial-gplus-share" data-href="https://plus.google.com/share?url=' . $permalink . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"><span class="simplesocialtxt">Google+</span>';
					if ( $show_count ) {
						$_html .= '<span class="ssb_counter ssb_googleplus_counter">' . $googleplus_share . '</span>';
					}
					 $_html .= '</button>';

					$arrButtonsCode[] = $_html;

					break;

				case 'fbshare':
					$fbshare_share = $share_counts['fbshare'] ? $share_counts['fbshare'] : 0;
					$_html = '<button class="simplesocial-fb-share" target="_blank" data-href="https://www.facebook.com/sharer/sharer.php?u=' . $permalink . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"><span class="simplesocialtxt">Facebook </span> ';

					if ( $show_count ) {
						$_html .= '<span class="ssb_counter ssb_fbshare_counter">' . $fbshare_share . '</span>';
					}
					 $_html .= '</button>';

					$arrButtonsCode[] = $_html;

					break;
				case 'twitter':
					$twitter_share = $share_counts['twitter'] ? $share_counts['twitter'] : 0;
					$via = ! empty( $this->extra_option['twitter_handle'] ) ? '&via=' . $this->extra_option['twitter_handle'] : '' ;
					$_html = '<button class="simplesocial-twt-share" data-href="https://twitter.com/share?text=' . $title . '&url=' . $permalink . '' . $via . '" rel="nofollow" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"><span class="simplesocialtxt">Twitter</span> ';

					if ( $show_count ) {
						$_html .= '<span class="ssb_counter ssb_twitter_counter">' . $twitter_share . '</span>';
					}
					$_html .= '</button>';

					$arrButtonsCode[] = $_html;

					break;
				case 'linkedin':
					$linkedin_share = $share_counts['linkedin'] ? $share_counts['linkedin'] : 0;

					$_html = '<button target="popup" class="simplesocial-linkedin-share" data-href="https://www.linkedin.com/cws/share?url=' . get_permalink() . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"><span class="simplesocialtxt">LinkedIn</span>';

					if ( $show_count ) {
						$_html .= '<span class="ssb_counter ssb_linkedin_counter">' . $linkedin_share . '</span>';
					}
					$_html .= '</button>';

					$arrButtonsCode[] = $_html;

					break;
				case 'pinterest':
					$pinterest_share = $share_counts['pinterest'] ?  $share_counts['pinterest'] : 0;

					$_html = '<button rel="nofollow" class="simplesocial-pinterest-share" onclick="var e=document.createElement(\'script\');e.setAttribute(\'type\',\'text/javascript\');e.setAttribute(\'charset\',\'UTF-8\');e.setAttribute(\'src\',\'//assets.pinterest.com/js/pinmarklet.js?r=\'+Math.random()*99999999);document.body.appendChild(e);return false;" ><span class="simplesocialtxt">Pinterest</span>';

					if ( $show_count ) {
						$_html .= '<span class="ssb_counter ssb_pinterest_counter">' . $pinterest_share . '</span>';
					}
					$_html .= '</button>';

					$arrButtonsCode[] = $_html;

					break;
				case 'totalshare':
 					$total_share = $share_counts['total'] ? $share_counts['total'] : 0;
					$arrButtonsCode[] = "<span class='share-counter'>" . $total_share . '<span>Shares</span></span>';
					break;

				case 'reddit':
					$reddit_score = $share_counts['reddit'] ? $share_counts['reddit'] : 0;
					$_html = '<button class="simplesocial-reddit-share"  data-href="https://reddit.com/submit?url=' . $permalink . '&title=' . $title . '" onclick="javascript:window.open(this.dataset.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ><span class="simplesocialtxt">Reddit</span> ';

					if ( $show_count ) {
						$_html .= '<span class="ssb_counter ssb_reddit_counter">' . $reddit_score . '</span>';
					}
					$_html .= '</button>';

					$arrButtonsCode[] = $_html;
					break;
				case 'whatsapp':
					$arrButtonsCode[] = '<button onclick="javascript:window.open(this.dataset.href, \'_self\' );return false;" class="simplesocial-whatsapp-share" data-href="whatsapp://send?text=' . $permalink . '"><span class="simplesocialtxt">Share on WhatsApp</span></button>';
					break;

				case 'viber':
						$arrButtonsCode[] = '<button onclick="javascript:window.open(this.dataset.href, \'_self\' );return false;" class="simplesocial-viber-share" data-href="viber://forward?text=' . $permalink . '"><span class="simplesocialtxt">Share on Viber</span></button>';
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
			return  $this->settings[ $option ];
		} else {
			return $default;
		}
	}

	function get_post_type(){

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
		if ( isset( $this->selected_position['sidebar'] ) && in_array( $this->get_post_type(),  $this->_get_settings( 'sidebar', 'posts', array() ) ) ) {
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
				$class = 'simplesocialbuttons-float-' . $this->sidebar_option['orientation'] . '-center';
				if ( $this->sidebar_option['hide_mobile'] ) {
					$class .= ' simplesocialbuttons-mobile-hidden'; }
					$class .= ' simplesocialbuttons-slide-' . $this->_get_settings( 'sidebar', 'animation', 'no-animation' );
				echo $this->generate_buttons_code( $this->selected_networks, $show_count, $show_total, $class );
			}
		}
	}

	function css_file() {
		include_once  dirname( __FILE__ ) . '/inc/custom-css.php';
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
		//delete_option( 'ssb_update_2_0_dismiss' );
		if ( get_option( 'ssb_update_2_0_dismiss' ) ) { return; }

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
				<a href="<?php echo $dismiss_url ?>" class="ssb_update_dismiss_button">Dismiss</a>
				<a href="https://wpbrigade.com/wordpress/plugins/simple-social-buttons-pro/?utm_source=simple-social-buttons-lite&utm_medium=link-learn-more&utm_campaign=pro-upgrade" target="_blank" class="ssb_update_dismiss_button">Learn more</a>
			</div>
		</div>
		<?php
	}

} // end class


global $_ssb_pr;
if ( is_admin() ) {
	include_once  dirname( __FILE__ ) . '/classes/ssb-admin.php';
	$_ssb_pr = new SimpleSocialButtonsPR_Admin();
} else {
	$_ssb_pr = new SimpleSocialButtonsPR();
}

function get_ssb( $order = null ) {
	return '<!-- Shortcode Removed -->';
}

?>
