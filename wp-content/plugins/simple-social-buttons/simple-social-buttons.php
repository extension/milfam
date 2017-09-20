<?php
/**
 * Plugin Name: Simple Social Buttons
 * Plugin URI: http://www.WPBrigade.com/wordpress/plugins/simple-social-buttons/
 * Description: Insert social buttons into posts, pages and archives: <code>Facebook</code> "Like it", "Share", <code>Google</code> Plus One "+1", <code>Twitter</code> share, <code>LinkedIn</code> and <code>Pinterest</code>.
 * Version: 1.9.0
 * Author: WPBrigade
 * Author URI: http://www.WPBrigade.com/
 * Text Domain: simplesocialbuttons
 * Domain Path: /lang
 */

/*  Copyright 2011, Muhammad Adnan (WPBrigade)  (email : captain@wpbrigade.com)

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

/**
 * HACK: Converted to class, added buttons ordering, improve saving settings
 * @author Fabian Wolf
 * @link http://usability-idealist.de/
 * @since 1.3
 * @requires PHP 5
 */


class SimpleSocialButtonsPR {
	var $pluginName = 'Simple Social Buttons';
	var $pluginVersion = '1.9.0';
	var $pluginPrefix = 'ssb_pr_';
	var $hideCustomMetaKey = '_ssb_hide';

	// plugin default settings
	var $pluginDefaultSettings = array(
		'googleplus' => '1',
		'fblike' => '2',
		'twitter' => '3',
		'pinterest' => '0',
		'beforepost' => '1',
		'afterpost' => '0',
		'beforepage' => '1',
		'afterpage' => '0',
		'beforearchive' => '0',
		'afterarchive' => '0',
    'fbshare' => '0',
    'linkedin' => '0'
	);

	// defined buttons
	var $arrKnownButtons = array('fblike', 'googleplus', 'twitter', 'pinterest', 'fbshare', 'linkedin');

	// an array to store current settings, to avoid passing them between functions
	var $settings = array();


	/**
	 * Constructor
	 */
	function __construct() {
		register_activation_hook( __FILE__, array(&$this, 'plugin_install') );
		register_deactivation_hook( __FILE__, array(&$this, 'plugin_uninstall') );


		$this->constants();
		/**
		 * Action hooks
		 */
		add_action( 'create_ssb', array(&$this, 'direct_display'), 10 , 1);

		/**
		 * basic init
		 */
		add_action( 'init', array(&$this, 'plugin_init') );

		// get settings
		$this->settings = $this->get_settings();

		// social JS + CSS data
		add_action( 'wp_footer', array(&$this, 'include_social_js') );
		if ( !isset($this->settings['override_css']) || $this->settings['override_css'] != 1) {
			add_action( 'wp_head', array(&$this, 'include_css') );
		}

		/**
		 * Filter hooks
		 */
		add_filter( 'the_content', array(&$this, 'insert_buttons') );
		add_filter( 'the_excerpt', array(&$this, 'insert_buttons') );

		add_filter( 'plugin_row_meta',        array( $this, '_row_meta'), 10, 2 );
	}

	function plugin_init() {
   		load_plugin_textdomain( 'simplesocialbuttons', '', dirname( plugin_basename( __FILE__ ) ).'/lang' );
	}

	function constants() {
		define( 'SSB_FEEDBACK_SERVER', 'https://wpbrigade.com/' );
		define( 'SSB_VERSION', $this->pluginVersion );
		define( 'SSB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}
	/**
	 * Both avoids time-wasting https calls AND provides better SSL-protection if the current server is accessed using HTTPS
	 */
	public function get_current_http( $echo = true ) {
		$return = 'http' . (strtolower(@$_SERVER['HTTPS']) == 'on' ? 's' : '') . '://';

		if($echo != false) {
			echo $return;
			return;
		}

		return $return;
	}

	function include_social_js($force_include = false) {
		$lang = get_bloginfo('language');
		$lang_g = strtolower(substr($lang, 0, 2));
		$lang_fb = str_replace('-', '_', $lang);

      // most common problem with incorrect WPLANG in /wp-config.php
      if($lang_fb == "en" || empty($lang_fb)) {
         $lang_fb = "en_US";
      }

		/**
		 * Disable loading of social network JS if disabled for specific post type
		 *
		 * NOTE: Conditional tags seem to work only AFTER the page has loaded, thus the code has been added here instead of at the plugin init
		 * @author Fabian Wolf
		 * @link http://usability-idealist.de/
		 * @date Di 20. Dez 17:50:01 CET 2011
		 */
		if($this->where_to_insert() != false || $force_include == true) {
?>

<!-- Simple Social Buttons plugin -->
<script type="text/javascript">
//<![CDATA[
<?php if ((int)$this->settings['googleplus'] != 0):?>
// google plus
window.___gcfg = {lang: '<?php echo $lang_g; ?>'};
(function() {
   var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
   po.src = 'https://apis.google.com/js/plusone.js';
   var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
<?php endif;?>
<?php if ((int)$this->settings['fblike'] != 0 || (int)$this->settings['fbshare'] != 0):?>
// facebook
(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/<?php echo $lang_fb; ?>/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
<?php endif;?>
<?php if ((int)$this->settings['twitter'] != 0):?>
// twitter
!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
<?php endif;?>
// ]]>
</script>
<?php if ((int)$this->settings['pinterest'] != 0):?>
<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>
<?php endif;?>
<!-- linkedin -->
<?php if ( isset( $this->settings['linkedin'] ) && (int)$this->settings['linkedin'] != 0): ?>
<script src="//platform.linkedin.com/in.js" type="text/javascript"> lang: en_US</script>
<?php endif; ?>
<!-- /End of Simple Social Buttons -->

<?php
		}
	}


	function include_css() {
?>

<!-- Simple Social Buttons style sheet -->
<style type="text/css">
   div.simplesocialbuttons { height: auto; margin: 5px auto 5px -5px; text-align: left; clear: left; }
   div.simplesocialbutton { float: left; }
   div.ssb-button-googleplus { width: 100px; }
   div.ssb-button-fblike { width: 140px; line-height: 1; }
   div.ssb-button-fbshare { width: 140px; line-height: 1; }
   div.ssb-button-twitter { width: 130px; }
   div.ssb-button-pinterest { width: 100px; }
   .fb-like iframe { max-width: none !important; }
	 .simplesocialbuttons .simplesocialbutton { display: inline-block; vertical-align: top; width: auto;float: none; margin: 0 5px 5px;}
	 .simplesocialbuttons .simplesocialbutton iframe { margin: 0; vertical-align: inherit; }

</style>
<!-- End of Simple Social Buttons -->

<?php
	}

	/**
	 * Called when installing = activating the plugin
	 */
	function plugin_install() {
		$defaultSettings = $this->check_old_settings();

		/**
		 * @see http://codex.wordpress.org/Function_Reference/add_option
		 * @param string $name 			Name of the option to be added. Use underscores to separate words, and do not use uppercase - this is going to be placed into the database.
		 * @param mixed $value			Value for this option name. Limited to 2^32 bytes of data.
		 * @param string $deprecated	Deprecated in WordPress Version 2.3.
		 * @param string $autoload		Should this option be automatically loaded by the function wp_load_alloptions() (puts options into object cache on each page load)? Valid values: yes or no. Default: yes
		 */
		add_option( $this->pluginPrefix . 'settings', $defaultSettings, '', 'yes' );
		add_option( $this->pluginPrefix . 'version', $this->pluginVersion, '', 'yes' ); // for backward-compatiblity checks

	}

	/**
	 * Backward compatiblity for newer versions
	 */
	function check_old_settings() {
		$return = $this->pluginDefaultSettings;

		$oldSettings = get_option( $this->pluginPrefix . 'settings', array() );

		if( !empty($oldSettings) && is_array($oldSettings) != false) {
			$return = wp_parse_args( $oldSettings, $this->pluginDefaultSettings );
		}

		return $return;
	}

   /**
    * Plugin unistall and database clean up
    */
	function plugin_uninstall() {
		if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ) {
			exit();
		}

		delete_option( $this->pluginPrefix . 'settings' );
		delete_option( $this->pluginPrefix . 'version' );

	}


	/**
	 * Get settings from database
	 */
	public function get_settings() {
		$return = get_option($this->pluginPrefix . 'settings' );
		if(empty($return) != false) {
			$return = $this->pluginDefaultSettings;
		}

		return $return;
	}

	/**
	 * Update settings
	 */
	function update_settings( $newSettings = array() ) {
		$return = false;

		// compile settings
		$currentSettings = $this->get_settings();

		/**
		 * Compile settings array
		 * @see http://codex.wordpress.org/Function_Reference/wp_parse_args
		 * @param mixed $args
		 * @param mixed $defaults
		 */
		$updatedSettings = wp_parse_args( $newSettings, $currentSettings );

		if($currentSettings != $updatedSettings ) {
			$return = update_option( $this->pluginPrefix . 'settings', $newSettings );
		}

		return $return;
	}

   /**
    * Returns true on pages where buttons should be shown
    */
	function where_to_insert() {
		$return = false;

		// display on single post?
		if(is_single() && ($this->settings['beforepost'] || $this->settings['afterpost']) && @array_shift(get_post_meta(get_the_ID(), $this->hideCustomMetaKey)) != 'true') {
			$return = true;
		}

		// display on single page?
		if(is_page() && ($this->settings['beforepage'] || $this->settings['afterpage']) && @array_shift(get_post_meta(get_the_ID(), $this->hideCustomMetaKey)) != 'true') {
			$return = true;
		}

		// display on frontpage?
		if((is_front_page() || is_home()) && $this->settings['showfront']) {
			$return = true;
		}

      	// display on category archive?
		if(is_category() && $this->settings['showcategory']) {
			$return = true;
		}

      	// display on date archive?
		if(is_date() && $this->settings['showarchive'])
		{
			$return = true;
		}

      	// display on tag archive?
		if(is_tag() && $this->settings['showtag']) {
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
		$new_links = array('<a href="https://wordpress.org/support/plugin/simple-social-buttons/reviews/?filter=5" target="_blank"><span class="dashicons dashicons-thumbs-up"></span> ' . __( 'Vote!', 'simplesocialbuttons' ) . '</a>',
		);

		$links = array_merge( $links, $new_links );
	}

	return $links;
}


	/**
	 * Insert the buttons to the content
	 */
	function insert_buttons($content) {
		// Insert or  not?
		if(!$this->where_to_insert() ) {
			return $content;
		}

		// creating order
		$order = array();
		foreach ($this->arrKnownButtons as $button_name) {
      if ( isset( $this->settings[$button_name] ) ) {
        $order[$button_name] = $this->settings[$button_name];
      }
		}
		$ssb_buttonscode = $this->generate_buttons_code($order);

		if(is_single()) {
			if($this->settings['beforepost']) {
				$content = $ssb_buttonscode.$content;
			}
			if($this->settings['afterpost']) {
				$content = $content.$ssb_buttonscode;
			}
		} else if(is_page()) {
			if($this->settings['beforepage']) {
				$content = $ssb_buttonscode.$content;
			}
			if($this->settings['afterpage']) {
				$content = $content.$ssb_buttonscode;
			}
		} else {
			if($this->settings['beforearchive']) {
				$content = $ssb_buttonscode.$content;
			}
			if($this->settings['afterarchive']) {
				$content = $content.$ssb_buttonscode;
			}
		}

		return $content;

	}

	function direct_display($order = null)
	{
		// Return false if hide SSB for this page/post is disabled
		if (is_single() && array_shift(get_post_meta(get_the_ID(), $this->hideCustomMetaKey)) == 'true') return false;

		// Display buttons and scripts
		$buttons_code = $this->generate_buttons_code($order);
		echo $buttons_code;
		$this->include_social_js(true);
	}

	/**
	 * Generate buttons html code with specified order
	 *
	 * @param mixed $order - order of social buttons
	 */
	function generate_buttons_code($order = null)
	{
		foreach ($this->arrKnownButtons as $button_name) {
			$defaultOrder[$button_name] = $this->pluginDefaultSettings[$button_name];
		}

		$order = wp_parse_args($order, $defaultOrder);

		// define empty buttons code to use
		$ssb_buttonscode = '';

		// get post permalink and title
		$permalink = get_permalink();
		$title = get_the_title();

		//Sorting the buttons
		$arrButtons = array();
		foreach($this->arrKnownButtons as $button_name) {
			if(!empty($order[$button_name]) && (int)$order[$button_name] != 0) {
				$arrButtons[$button_name] = $order[$button_name];
			}
		}
		@asort($arrButtons);

		$arrButtonsCode = array();
		foreach($arrButtons as $button_name => $button_sort) {
			switch($button_name) {
				case 'googleplus':
					$arrButtonsCode[] = '<div class="simplesocialbutton ssb-button-googleplus"><!-- Google Plus One--><div class="g-plusone" data-size="medium" data-href="'.$permalink.'"></div></div>';
					break;
				case 'fblike':
					$arrButtonsCode[] = '<div class="simplesocialbutton ssb-button-fblike"><!-- Facebook like--><div id="fb-root"></div><div class="fb-like" data-href="'.$permalink.'" data-send="false" data-layout="button_count" data-show-faces="false"></div></div>';
					break;
				case 'fbshare':
					$arrButtonsCode[] = '<div class="simplesocialbutton ssb-button-fblike"><!-- Facebook Share--><div id="fb-root"></div><div class="fb-share-button" data-href="'.$permalink.'" data-send="false" data-layout="button_count"  ></div></div>';
					break;
				case 'twitter':
					$arrButtonsCode[] = '<div class="simplesocialbutton ssb-button-twitter"><!-- Twitter--><a href="https://twitter.com/share" class="twitter-share-button" data-text="'.$title.'" data-url="'.$permalink.'" ' . ((!empty($this->settings['twitterusername'])) ? 'data-via="'.$this->settings['twitterusername'].'" ' : '') . 'rel="nofollow"></a></div>';
					break;
					case 'linkedin':
						$arrButtonsCode[] = '<div class="simplesocialbutton ssb-button-linkdin"><!-- LinkdIn--><script type="IN/Share" data-counter="right"></script></div>';
						break;
				case 'pinterest':
					global $post;
					$thumb_id = get_post_thumbnail_id($post->ID);

					// Don't show 'Pin It' button, if post doesn't have thumbnail
					if (empty($thumb_id)) break;

					// Getting thumbnail url
					$thumb = wp_get_attachment_image_src($thumb_id, 'thumbnail_size' );
					$thumb_src = (isset($thumb[0])) ? $thumb[0] : null;
					$thumb_alt = get_post_meta($thumb_id , '_wp_attachment_image_alt', true);

					// if there isn't thumbnail alt, take a post title as a description
					$description = (!empty($thumb_alt)) ? $thumb_alt : $title ;

					$arrButtonsCode[] = '<div class="simplesocialbutton ssb-button-pinterest"><!-- Pinterest--><a href="http://pinterest.com/pin/create/button/?url='.urlencode($permalink).'&media='.urlencode($thumb_src).'&description='.urlencode($description).'" data-pin-do="buttonPin" data-pin-config="beside" rel="nofollow"><img border="0" src="<a href="//www.pinterest.com/pin/create/button/?url=http%3A%2F%2Fwww.flickr.com%2Fphotos%2Fkentbrew%2F6851755809%2F&media=http%3A%2F%2Ffarm8.staticflickr.com%2F7027%2F6851755809_df5b2051c9_z.jpg&description=Next%20stop%3A%20Pinterest" data-pin-do="buttonPin" data-pin-config="beside"><img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_gray_20.png" title="Pin It" /></a></div>';
					break;
			}
		}

		if(count($arrButtonsCode) > 0) {
			$ssb_buttonscode = '<div class="simplesocialbuttons">'."\n";
			$ssb_buttonscode .= implode("\n", $arrButtonsCode) . "\n";
			$ssb_buttonscode .= '</div>'."\n";
		}

		return $ssb_buttonscode;
	}
} // end class


/**
 * Admin class
 *
 * Gets only initiated if this plugin is called inside the admin section ;)
 */
class SimpleSocialButtonsPR_Admin extends SimpleSocialButtonsPR {

	function __construct() {
		parent::__construct();


		add_action('admin_menu', array(&$this, 'admin_actions') );
		add_action('add_meta_boxes', array(&$this, 'ssb_meta_box'));
		add_action('save_post', array(&$this, 'ssb_save_meta'), 10, 2);

		add_filter('plugin_action_links', array(&$this, 'plugin_action_links'), 10, 2 );

		add_action( 'admin_footer',	array( $this, 'add_deactive_modal' ) );
		add_action( 'wp_ajax_ssb_deactivate', array( $this, 'ssb_deactivate' ) );
		add_action( 'admin_init', array( $this, 'review_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'in_admin_header', array( $this, 'skip_notices' ), 100000 );

	}

	public function admin_actions() {
		if (current_user_can('activate_plugins'))
				add_menu_page( 'Simple Social Buttons ', 'Social Buttons ', "activate_plugins", 'simple-social-buttons', array(&$this, 'admin_page'), 'dashicons-share', 100 );
	}

	public function admin_page() {
		global $wpdb;

		include dirname( __FILE__  ).'/ssb-admin.php';
	}



	public function plugin_action_links($links, $file) {
		static $this_plugin;

		if (!$this_plugin) {
			$this_plugin = plugin_basename(__FILE__);
		}

		if ($file == $this_plugin) {
			$settings_link = '<a href="' . admin_url( 'admin.php?page=simple-social-buttons' ) . '">'.__('Settings', 'simplesocialbuttons').'</a>';
			array_unshift($links, $settings_link);
		}

		return $links;
	}

	function admin_enqueue_scripts( $page ) {

		if ( 'toplevel_page_simple-social-buttons' == $page ) {
			wp_enqueue_script( 'ssb-admin-js', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-sortable' ), time(), $in_footer = false );
		}

	}

	/**
	 * Register meta box to hide/show SSB plugin on single post or page
	 */
	public function ssb_meta_box()
	{
		$postId = $_GET['post'];
		$postType = get_post_type($postId);

		if ($postType != 'page' && $postType != 'post') return false;

		$currentSsbHide = get_post_custom_values($this->hideCustomMetaKey, $postId);

		if ($currentSsbHide[0] == 'true') {
			$checked = true;
		} else {
			$checked = false;
		}

		// Rendering meta box
		if (!function_exists('add_meta_box')) include('includes/template.php');
		add_meta_box('ssb_meta_box', __('SSB Settings', 'simplesocialbuttons'), array(&$this, 'render_ssb_meta_box'), $postType, 'side', 'default', array('type' => $postType, 'checked' => $checked));
	}

	/**
	 * Showing custom meta field
	 */
	public function render_ssb_meta_box($post, $metabox)
	{
		wp_nonce_field( plugin_basename( __FILE__ ), 'ssb_noncename' );
?>

<label for="<?php echo $this->hideCustomMetaKey;?>"><input type="checkbox" id="<?php echo $this->hideCustomMetaKey;?>" name="<?php echo $this->hideCustomMetaKey;?>" value="true" <?php if ($metabox['args']['checked']):?>checked="checked"<?php endif;?>/>&nbsp;<?php echo __('Hide Simple Social Buttons', 'simplesocialbuttons');?></label>

<?php
	}


	/**
	 * Saving custom meta value
	 */
	public function ssb_save_meta($post_id, $post)
	{
		$postId = (int)$post_id;

		// Verify if this is an auto save routine.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		// Verify this came from the our screen and with proper authorization
		if ( !wp_verify_nonce( $_POST['ssb_noncename'], plugin_basename( __FILE__ ) ) )
			return;

		// Check permissions
		if ( 'page' == $_POST['post_type'] ) {
			if ( !current_user_can( 'edit_page', $post_id ) )
	       		return;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ) )
			return;
		}

		// Saving data
		$newValue = (isset($_POST[$this->hideCustomMetaKey])) ? $_POST[$this->hideCustomMetaKey] : 'false';

		update_post_meta($postId, $this->hideCustomMetaKey, $newValue);
	}


	/**
	 * Show the popup on pluing deactivate
	 *
	 * @since 1.9.0
	 */
	function add_deactive_modal() {
		global $pagenow;

		if ( 'plugins.php' !== $pagenow ) {
			return;
		}

		include SSB_PLUGIN_DIR . 'inc/ssb-deactivate-form.php';
	}

	/**
	 * Send the user responce to api.
	 *
	 * @since 1.9.0
	 */
	function ssb_deactivate() {
		$email         = get_option( 'admin_email' );
		$_reason       = sanitize_text_field( wp_unslash( $_POST['reason'] ) );
		$reason_detail = sanitize_text_field( wp_unslash( $_POST['reason_detail'] ) );
		$reason        = '';

		if ( $_reason == '1' ) {
			$reason = 'I only needed the plugin for a short period';
		} elseif ( $_reason == '2' ) {
			$reason = 'I found a better plugin';
		} elseif ( $_reason == '3' ) {
			$reason = 'The plugin broke my site';
		} elseif ( $_reason == '4' ) {
			$reason = 'The plugin suddenly stopped working';
		} elseif ( $_reason == '5' ) {
			$reason = 'I no longer need the plugin';
		} elseif ( $_reason == '6' ) {
			$reason = 'It\'s a temporary deactivation. I\'m just debugging an issue.';
		} elseif ( $_reason == '7' ) {
			$reason = 'Other';
		}
		$fields = array(
			'email' 		        => $email,
			'website' 			    => get_site_url(),
			'action'            => 'Deactivate',
			'reason'            => $reason,
			'reason_detail'     => $reason_detail,
			'blog_language'     => get_bloginfo( 'language' ),
			'wordpress_version' => get_bloginfo( 'version' ),
			'plugin_version'    => SSB_VERSION,
			'plugin_name' 			=> 'Simple Social Buttons',
		);

		$response = wp_remote_post( SSB_FEEDBACK_SERVER, array(
			'method'      => 'POST',
			'timeout'     => 5,
			'httpversion' => '1.0',
			'blocking'    => false,
			'headers'     => array(),
			'body'        => $fields,
		) );

		wp_die();
	}

/**
 * Check either to show notice or not.
 *
 * @since 1.9.0
 */
	public function review_notice() {

	$this->review_dismissal();
	$this->review_prending();

	$review_dismissal	= get_site_option( 'ssb_review_dismiss' );
	if ( 'yes' == $review_dismissal ) {
		return;
	}

	$activation_time 	= get_site_option( 'ssb_active_time' );
	if ( ! $activation_time ) {

		$activation_time = time();
		add_site_option( 'ssb_active_time', $activation_time );
	}

	// 1296000 = 15 Days in seconds.
	if ( time() - $activation_time > 1296000 ) {
		add_action( 'admin_notices' , array( $this, 'review_notice_message' ) );
	}

}

	/**
	 * Show review Message After 15 days.
	 *
	 * @since 1.9.0
	 */
	public function review_notice_message() {

	$scheme      = ( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ) )  ? '&' : '?';
	$url         = $_SERVER['REQUEST_URI'] . $scheme . 'ssb_review_dismiss=yes';
	$dismiss_url = wp_nonce_url( $url, 'ssb-review-nonce' );

	$_later_link = $_SERVER['REQUEST_URI'] . $scheme . 'ssb_review_later=yes';
	$later_url   = wp_nonce_url( $_later_link, 'ssb-review-nonce' );

?>
<style media="screen">
	.ssb-review-notice { padding: 15px 15px 15px 0; background-color: #fff; border-radius: 3px; margin: 20px 20px 0 0; border-left: 4px solid transparent; } .ssb-review-notice:after { content: ''; display: table; clear: both; }
	.ssb-review-thumbnail { width: 114px; float: left; line-height: 80px; text-align: center; border-right: 4px solid transparent; }
	.ssb-review-thumbnail img { width: 74px; vertical-align: middle; }
	.ssb-review-text { overflow: hidden; }
	.ssb-review-text h3 { font-size: 24px; margin: 0 0 5px; font-weight: 400; line-height: 1.3; }
	.ssb-review-text p { font-size: 13px; margin: 0 0 5px; }
	.ssb-review-ul { margin: 0; padding: 0; }
	.ssb-review-ul li { display: inline-block; margin-right: 15px; }
	.ssb-review-ul li a { display: inline-block; color: #10738B; text-decoration: none; padding-left: 26px; position: relative; }
	.ssb-review-ul li a span { position: absolute; left: 0; top: -2px; }
</style>
	<div class="ssb-review-notice">
		<div class="ssb-review-thumbnail">
			<img src="<?php echo plugins_url( 'assets/images/ssb_grey_logi.png', __FILE__ ) ?>" alt="">
		</div>
		<div class="ssb-review-text">
			<h3><?php _e( 'Leave A Review?', 'simplesocialbuttons' ) ?></h3>
			<p><?php _e( 'We hope you\'ve enjoyed using Simple Social Buttons! Would you consider leaving us a review on WordPress.org?', 'simplesocialbuttons' ) ?></p>
			<ul class="ssb-review-ul"><li><a href="https://wordpress.org/support/plugin/simple-social-buttons/reviews/?filter=5" target="_blank"><span class="dashicons dashicons-external"></span><?php _e( 'Sure! I\'d love to!', 'simplesocialbuttons' ) ?></a></li>
				 <li><a href="<?php echo $dismiss_url ?>"><span class="dashicons dashicons-smiley"></span><?php _e( 'I\'ve already left a review', 'simplesocialbuttons' ) ?></a></li>
				 <li><a href="<?php echo $later_url ?>"><span class="dashicons dashicons-calendar-alt"></span><?php _e( 'Maybe Later', 'simplesocialbuttons' ) ?></a></li>
				 <li><a href="<?php echo $dismiss_url ?>"><span class="dashicons dashicons-dismiss"></span><?php _e( 'Never show again', 'simplesocialbuttons' ) ?></a></li></ul>
		</div>
	</div>
<?php
}

/**
 * Set time to current so review notice will popup after 15 days
 *
 * @since 1.9.0
 */
function review_prending() {

	// delete_site_option( 'ssb_review_dismiss' );
	if ( ! is_admin() ||
		! current_user_can( 'manage_options' ) ||
		! isset( $_GET['_wpnonce'] ) ||
		! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'ssb-review-nonce' ) ||
		! isset( $_GET['ssb_review_later'] ) ) {

		return;
	}

	// Reset Time to current time.
	update_site_option( 'ssb_active_time', time() );

}

/**
 *	Check and Dismiss review message.
 *
 *	@since 1.9.0
 */
private function review_dismissal() {

	//delete_site_option( 'ssb_review_dismiss' );
	if ( ! is_admin() ||
		! current_user_can( 'manage_options' ) ||
		! isset( $_GET['_wpnonce'] ) ||
		! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'ssb-review-nonce' ) ||
		! isset( $_GET['ssb_review_dismiss'] ) ) {

		return;
	}

	add_site_option( 'ssb_review_dismiss', 'yes' );
}


/**
 * Skip the all the notice from settings page.
 *
 * @since 1.9.0
 */
function skip_notices() {

	if ( 'toplevel_page_simple-social-buttons' === get_current_screen()->id ) {

		global $wp_filter;

		if ( is_network_admin() and isset( $wp_filter['network_admin_notices'] ) ) {
			unset( $wp_filter['network_admin_notices'] );
		} elseif ( is_user_admin() and isset( $wp_filter['user_admin_notices'] ) ) {
			unset( $wp_filter['user_admin_notices'] );
		} else {
			if ( isset( $wp_filter['admin_notices'] ) ) {
				unset( $wp_filter['admin_notices'] );
			}
		}

		if ( isset( $wp_filter['all_admin_notices'] ) ) {
			unset( $wp_filter['all_admin_notices'] );
		}
	}

}

} // end SimpleSocialButtonsPR_Admin

if(is_admin() ) {
	$_ssb_pr = new SimpleSocialButtonsPR_Admin();
} else {
	$_ssb_pr = new SimpleSocialButtonsPR();
}

/**
 * Function to insert Simple Social Buttons directly in template.
 *
 * @param mixed $order - order of the buttons in array or string (parsed by wp_parse_args())
 *
 * @example 1 - use in template with default order
 * get_ssb();
 *
 * @example 2 - use in template with specified order
 * get_ssb('googleplus=3&fblike=2&twitter=1');
 *
 * @example 3 - hiding button by setting order to 0. By using code below googleplus button won't be displayed
 * get_ssb('googleplus=0&fblike=1&twitter=2');
 *
 *
 */
function get_ssb($order = null)
{
	do_action('create_ssb', $order);
}

?>
