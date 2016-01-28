<?php
/*
Plugin Name: RSS Includes Pages
Version: 3.4
Plugin URI: http://infolific.com/technology/software-worth-using/include-pages-in-wordpress-rss-feeds/
Description: Include pages and custom post types in RSS feeds. Particularly useful to those that use WordPress as a CMS. The <a href="http://infolific.com/technology/software-worth-using/include-pages-in-wordpress-rss-feeds/#pro-version" target="_blank">pro version</a> (less than $10) gives you more control and support for custom post types.
Author: Marios Alexandrou
Author URI: http://infolific.com/technology/
License: GPLv2 or later
Text Domain: rss-includes-pages
*/

/*
Copyright 2015 Marios Alexandrou

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

//Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'rssip_add_pages' );
//add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'rssip_action_links' );
add_filter( 'plugin_row_meta', 'rssip_plugin_meta', 10, 2 );

/*
function rssip_action_links( $links ) {
   $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=rss-includes-pages') ) .'">Settings</a>';
   //$links[] = '<a href="http://infolific.com/technology/software-worth-using/include-pages-in-wordpress-rss-feeds/#pro-version" target="_blank">Pro Version</a>';
   return $links;
}
*/

function rssip_plugin_meta( $links, $file ) { // add some links to plugin meta row
	if ( strpos( $file, 'rss-includes-pages.php' ) !== false ) {
		$links = array_merge( $links, array( '<a href="' . esc_url( get_admin_url(null, 'options-general.php?page=rss-includes-pages') ) . '">Settings</a>' ) );
		$links = array_merge( $links, array( '<a href="http://infolific.com/technology/software-worth-using/include-pages-in-wordpress-rss-feeds/#pro-version" target="_blank">Pro Version (less than $10)</a>' ) );
	}
	return $links;
}

/*
* Add a submenu under Tools
*/
function rssip_add_pages() {
	$page = add_submenu_page( 'options-general.php', 'RSS Includes Pages', 'RSS Includes Pages', 'activate_plugins', 'rss-includes-pages', 'rssip_options_page' );
	add_action( "admin_print_scripts-$page", "rssip_admin_scripts" );
}

/*
* Scripts needed for the admin side
*/
function rssip_admin_scripts() {
	wp_enqueue_style( 'rssip_styles', plugins_url() . '/rss-includes-pages/css/rssip.css' );
}

function rssip_options_page() {
	//Do nothing in the free version of this plugin.
?>
<div class="wrap" style="padding-bottom: 5em;">
	<h2>RSS Includes Pages</h2>
	<p>By default posts and pages are set to be included in feeds. The <a href="http://infolific.com/technology/software-worth-using/include-pages-in-wordpress-rss-feeds/#pro-version" target="_blank">pro version</a> (less than $10) allows you to change these options as well as add custom post types to your feed.</p>
	<div id="rssip-items">
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
			<ul id="rssip_itemlist">
				<li>
				<?php
					echo "<div>";
						echo "Include These Post Types:";
						echo "<br />";

						echo "<label class='side-label' for='rssip_posts'>&bull; Posts:</label>";
						echo "<input disabled='disabled' class='checkbox' type='checkbox' name='rssip_posts' id='rssip_posts' CHECKED />";
						echo "<br />";

						echo "<label class='side-label' for='rssip_pages'>&bull; Pages:</label>";
						echo "<input disabled='disabled' class='checkbox' type='checkbox' name='rssip_pages' id='rssip_pages' CHECKED />";
						echo "<br />";
						
						$rssip_args = array(
							'public'   => true
						);
						$rssip_output = 'names'; // names or objects, note names is the default
						$rssip_operator = 'and'; // 'and' or 'or'
						$rssip_post_types = get_post_types( $rssip_args, $rssip_output, $rssip_operator ); 
						foreach ( $rssip_post_types as $rssip_post_type ) {
							if ( strcasecmp( $rssip_post_type, 'post' ) != 0 && strcasecmp( $rssip_post_type, 'page' ) != 0 ) {
								echo "<label class='side-label' for='rssip_pages'>&bull; " . ucfirst( $rssip_post_type ) . ":</label>";
								echo "<input disabled='disabled' class='checkbox' type='checkbox' name='rssip_" . $rssip_post_type . "' id='rssip_" . $rssip_post_type . "'";
								echo " />";
								echo "<br />";
							}
						}

						echo "<br />";
												
						echo "<label class='side-label' for='rssip_exclude'>Exclude IDs:</label>";
						echo "<input disabled='disabled' class='textbox-long' type='text' name='rssip_exclude' id='rssip_exclude' value='pro version only' />";
						echo "<br />";

						echo "<label class='side-label' for='rssip_include'>Include IDs:</label>";
						echo "<input disabled='disabled' class='textbox-long' type='text' name='rssip_include' id='rssip_include' value='pro version only' />";
						echo "<br />";
					echo "</div>";
				?>
				</li>
			</ul>
			<div id="divTxt"></div>
		    <div class="clearpad"></div>
			<input disabled="disabled" type="submit" class="button left" value="Update Settings" />
			<input type="hidden" name="setup-update" />
		</form>
	</div>
	<div id="rssip-sb">
		<div class="postbox" id="rssip-sbone">
			<h3 class='hndle'><span>Documentation</span></h3>
			<div class="inside">
				<strong>Instructions</strong>
				<p>This plugin allows you to include pages in your RSS feed. The <a href="http://infolific.com/technology/software-worth-using/include-pages-in-wordpress-rss-feeds/#pro-version" target="_blank">pro version</a> (less than $10) gives you a little more control.</p>
				<ol>
					<li>Select the options to the left to indicate what should be included in your RSS feeds.</li>
					<li>To exclude items, list the IDs separated by commas. Or to include items, list the IDs separated by commas. Note that excluding and including are mutually exclusive. If you specify both, exclude will win.</li>
					<li>Note that entries in your RSS feed are still sorted by date with the most recently published items at the top.</li>
				</ol>
				<strong>Tips</strong>
				<ol>
					<li>If just posts are selected, you've specified WordPress' default behavior.</li>
					<li>If you cache your feeds, be sure to flush the cache when testing.</li>
					<li>Note that third-party services that distribute your feed may not update your feed immediately so you may not see the effect of your options to the left for some time.</li>
				</ol>
			</div>
		</div>
		<div class="postbox"  id="rssip-sbtwo">
			<h3 class='hndle'><span>Support</span></h3>
			<div class="inside">
				<p>Your best bet is to post on the <a href="https://wordpress.org/plugins/rss-includes-pages/">plugin support page</a>.</p>
				<p>Please consider supporting me by <a href="https://wordpress.org/support/view/plugin-reviews/rss-includes-pages">rating this plugin</a>. Thanks!</p>
			</div>
		</div>
		<div class="postbox" id="rssip-sbthree">
			<h3 class='hndle'><span>Other Plugins</span></h3>
			<div class="inside">
				<ul>
					<li><a href="https://wordpress.org/plugins/real-time-find-and-replace/">Real-Time Find and Replace</a>: Set up find and replace rules that are executed AFTER a page is generated by WordPress, but BEFORE it is sent to a user's browser.</li>
					<li><a href="https://wordpress.org/plugins/republish-old-posts/">Republish Old Posts</a>: Republish old posts automatically by resetting the date to the current date. Puts your evergreen posts back in front of your users.</li>
					<li><a href="https://wordpress.org/extend/plugins/rss-includes-pages/">RSS Includes Pages</a>: Modifies RSS feeds so that they include pages and custom post types. My most popular plugin!</li>
					<li><a href="https://wordpress.org/extend/plugins/enhanced-plugin-admin">Enhanced Plugin Admin</a>: At-a-glance info (rating, review count, last update date) on your site's plugin page about the plugins you have installed (both active and inactive).</li>
					<li><a href="https://wordpress.org/extend/plugins/add-any-extension-to-pages/">Add Any Extention to Pages</a>: Add any extension of your choosing (e.g. .html, .htm, .jsp, .aspx, .cfm) to WordPress pages.</li>
					<li><a href="https://wordpress.org/extend/plugins/social-media-email-alerts/">Social Media E-Mail Alerts</a>: Receive e-mail alerts when your site gets traffic from social media sites of your choosing. You can also set up alerts for when certain parameters appear in URLs.</li>				</ul>
				</ul>
			</div>
		</div>
	</div>
</div>
<?php } ?>
<?php
function rssip_posts_where( $var ) {
	if ( !is_feed() ) { // check if this is a feed
		return $var; // if not, return an unmodified variable

	} else {
		global $table_prefix; // get the table prefix
		$find = $table_prefix . "posts.post_type = 'post'"; // find where the query filters by post_type

		//Includes posts and pages in feed
		$replace = "(" . $find . " OR " . $table_prefix . "posts.post_type = 'page')"; // add OR post_type 'page' to the query
		$var = str_replace( $find, $replace, $var ); // change the query

	}

	return $var; // return the variable
}

function rssip_get_lastpostmodified( $lastpostmodified, $timezone ) {
	global $rssip_feed, $wpdb;

	if ( !( $rssip_feed ) ) {
		return $lastpostmodified;
	}

	//queries taken from wp-includes/post.php  modified to include pages
	$lastpostmodified = $wpdb->get_var( "SELECT post_modified_gmt FROM $wpdb->posts WHERE post_status = 'publish' AND (post_type = 'post' OR post_type = 'page') ORDER BY post_modified_gmt DESC LIMIT 1" );
	$lastpostdate = $wpdb->get_var( "SELECT post_date_gmt FROM $wpdb->posts WHERE post_status = 'publish' AND (post_type = 'post' OR post_type = 'page') ORDER BY post_date_gmt DESC LIMIT 1" );
	
	if ( $lastpostdate > $lastpostmodified ) {
		$lastpostmodified = $lastpostdate;
	}

	return $lastpostmodified;
}

function rssip_feed_true() {
	global $rssip_feed;
	$rssip_feed = true;
}

function rssip_feed_false() {
	global $rssip_feed;
	$rssip_feed = false;
}

add_filter( 'posts_where', 'rssip_posts_where' );

/*
* Deal with Last Post Modified so feeds will validate. WordPress default just checks for posts, not pages.
*/
add_filter( 'get_lastpostmodified', 'rssip_get_lastpostmodified', 10, 2 );

// We do this because is_feed is not set when calling get_lastpostmodified.
add_action( 'rss2_ns', 'rssip_feed_true' );
add_action( 'atom_ns', 'rssip_feed_true' );
add_action( 'rdf_ns', 'rssip_feed_true' );

// We won't mess with comment feeds.
add_action ( 'rss2_comments_ns', 'rssip_feed_false' );
add_action ( 'atom_comments_ns', 'rssip_feed_false' );
?>