<?php
/*
*
* Plugin Name: Simple Social Buttons
* Plugin URI: http://www.WPBrigade.com/wordpress/plugins/simple-social-buttons/
* Description: Insert social buttons into posts and archives: Facebook "Like it", Google Plus One "+1", Twitter share and Pinterest.
* Version: 1.8.0
* Author: WPBrigade
* Author URI: http://adnan.pk/
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
	var $pluginVersion = '1.7.11';
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
		'afterarchive' => '0'
	);

	// defined buttons
	var $arrKnownButtons = array('fblike', 'googleplus', 'twitter', 'pinterest');

	// an array to store current settings, to avoid passing them between functions
	var $settings = array();


	/**
	 * Constructor
	 */
	function __construct() {
		register_activation_hook( __FILE__, array(&$this, 'plugin_install') );
		register_deactivation_hook( __FILE__, array(&$this, 'plugin_uninstall') );

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
		if(!empty($this->settings['override_css'])) {
			add_action( 'wp_head', array(&$this, 'include_css') );
		}

		/**
		 * Filter hooks
		 */
		add_filter( 'the_content', array(&$this, 'insert_buttons') );
		add_filter( 'the_excerpt', array(&$this, 'insert_buttons') );
	}

	function plugin_init() {
   		load_plugin_textdomain( 'simplesocialbuttons', '', dirname( plugin_basename( __FILE__ ) ).'/lang' );
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
<?php if ((int)$this->settings['fblike'] != 0):?>
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
<!-- /End of Simple Social Buttons -->

<?php
		}
	}


	function include_css() {
?>

<!-- Simple Social Buttons style sheet -->
<style type="text/css">
   div.simplesocialbuttons { height: 20px; margin: 10px auto 10px 0; text-align: left; clear: left; }
   div.simplesocialbutton { float: left; }
   div.ssb-button-googleplus { width: 100px; }
   div.ssb-button-fblike { width: 140px; line-height: 1; }
   div.ssb-button-twitter { width: 130px; }
   div.ssb-button-pinterest { width: 100px; }
   .fb-like iframe { max-width: none !important; }
	 .simplesocialbuttons .simplesocialbutton { display: inline-block; vertical-align: top; width: auto; }
	 .simplesocialbuttons .simplesocialbutton iframe { margin: 0; }
	 .simplesocialbuttons .simplesocialbutton + .simplesocialbutton { margin-left: 20px; }
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
			$order[$button_name] = $this->settings[$button_name];
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
				case 'twitter':
					$arrButtonsCode[] = '<div class="simplesocialbutton ssb-button-twitter"><!-- Twitter--><a href="https://twitter.com/share" class="twitter-share-button" data-text="'.$title.'" data-url="'.$permalink.'" ' . ((!empty($this->settings['twitterusername'])) ? 'data-via="'.$this->settings['twitterusername'].'" ' : '') . 'rel="nofollow"></a></div>';
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

		add_action( 'wp_ajax_ssb_subscriber', array( $this, 'ssb_subscriber' ) );
	}

	public function admin_actions() {
		if (current_user_can('activate_plugins'))
    		add_options_page('Simple Social Buttons ', 'Social Buttons ', "activate_plugins", 'simple-social-buttons', array(&$this, 'admin_page') );
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
			$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=simple-social-buttons">'.__('Settings', 'simplesocialbuttons').'</a>';
			array_unshift($links, $settings_link);
		}

		return $links;
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

	function ssb_subscriber() {

		$email = sanitize_email( wp_unslash( $_POST['subscriber_mail'] ) );
		$display_name = sanitize_text_field( wp_unslash( $_POST['name'] ) );


		$data = array(
			'email_address' => $email,
			"status_if_new" => "pending",
			// 'status'        => 'pending', // "subscribed","unsubscribed","cleaned","pending"
			'merge_fields'  => array(
				'FNAME'       => $display_name
			),
			'interests' => array(
				'7aec774503' => true, // add interest ? loginPress => d91a783713 || Related Posts Thumbnails => 1c85722a4b || Newsletter Subscribers => 3eefb18f62
			)
		);


		$apiKey = '7f32f907a2e6ceeaf8a97fc00b962b2b-us15';
		$listId = '30d28e0fc3';

		$memberId = md5( strtolower( $data['email_address'] ) );
		$dataCenter = substr( $apiKey , strpos( $apiKey,'-' ) + 1 );


		$url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId .'/members/' . $memberId;

		// 2d13665c08ff83f22de7cc7da216db12
		$json = json_encode( $data );

		$ch = curl_init( $url );

		curl_setopt( $ch, CURLOPT_USERPWD, 'user:' . $apiKey );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'PUT' );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $json );

		curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)' );

		$result = curl_exec( $ch );

		if( $request_type == 'GET' ){
			$result_array = json_decode ( $result , true );
			$status       = $result_array['status'];
			return $status;
		}
		// var_dump($result);
		// var_dump( curl_getinfo( $ch ) );

		$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

		curl_close( $ch );
		if ( 200 == $httpCode ) {
			echo "Thank you!";
		} else {
			echo "oops! Please try again later.";
		}
		wp_die();
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
