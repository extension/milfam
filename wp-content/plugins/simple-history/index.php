<?php
/*
Plugin Name: Simple History
Plugin URI: http://eskapism.se/code-playground/simple-history/
Description: Get a log of the changes made by users in WordPress.
Version: 0.3.7
Author: Pär Thernström
Author URI: http://eskapism.se/
License: GPL2
*/

/*  Copyright 2010  Pär Thernström (email: par.thernstrom@gmail.com)

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

define( "SIMPLE_HISTORY_VERSION", "0.3.7");
define( "SIMPLE_HISTORY_NAME", "Simple History"); 
define( "SIMPLE_HISTORY_URL", WP_PLUGIN_URL . '/simple-history/');

add_action( 'admin_head', "simple_history_admin_head" );
add_action( 'admin_init', 'simple_history_admin_init' ); // start listening to changes
add_action( 'init', 'simple_history_init' ); // start listening to changes
add_action( 'admin_menu', 'simple_history_admin_menu' );
add_action( 'wp_dashboard_setup', 'simple_history_wp_dashboard_setup' );
add_action( 'wp_ajax_simple_history_ajax', 'simple_history_ajax' );

function simple_history_ajax() {

	$type = $_POST["type"];
	if ($type == "All types") { $type = "";	}

	$user = $_POST["user"];
	if ($user == "By all users") { $user = "";	}

	$page = 0;
	if (isset($_POST["page"])) {
		$page = (int) $_POST["page"];
	}

	$args = array(
		"is_ajax" => true,
		"filter_type" => $type,
		"filter_user" => $user,
		"page" => $page
	);
	simple_history_print_history($args);
	exit;

}

function simple_history_admin_menu() {

	#define( "SIMPLE_HISTORY_PAGE_FILE", menu_page_url("simple_history_page", false)); // no need yet

	// show as page?
	if (simple_history_setting_show_as_page()) {
		add_dashboard_page(SIMPLE_HISTORY_NAME, SIMPLE_HISTORY_NAME, "edit_pages", "simple_history_page", "simple_history_management_page");
	}

}


function simple_history_wp_dashboard_setup() {
	if (simple_history_setting_show_on_dashboard()) {
		if (current_user_can("edit_pages")) {
			wp_add_dashboard_widget("simple_history_dashboard_widget", SIMPLE_HISTORY_NAME, "simple_history_dashboard");
		}
	}
}

function simple_history_dashboard() {
	simple_history_purge_db();
	simple_history_print_nav();
	simple_history_print_history();
}

function simple_history_admin_head() {
}


function simple_history_init() {

	// users and stuff
	add_action("wp_login", "simple_history_wp_login");
	add_action("wp_logout", "simple_history_wp_logout");
	add_action("delete_user", "simple_history_delete_user");
	add_action("user_register", "simple_history_user_register");
	add_action("profile_update", "simple_history_profile_update");

	// options
	#add_action("updated_option", "simple_history_updated_option", 10, 3);
	#add_action("updated_option", "simple_history_updated_option2", 10, 2);
	#add_action("updated_option", "simple_history_updated_option3", 10, 1);
	#add_action("update_option", "simple_history_update_option", 10, 3);
	
	// plugin
	add_action("activated_plugin", "simple_history_activated_plugin");
	add_action("deactivated_plugin", "simple_history_deactivated_plugin");

	// check for RSS
	// don't know if this is the right way to do this, but it seems to work!
	if (isset($_GET["simple_history_get_rss"])) {
	
		$rss_secret_option = get_option("simple_history_rss_secret");
		$rss_secret_get = $_GET["rss_secret"];

		echo '<?xml version="1.0"?>';
		$self_link = simple_history_get_rss_address();

		if ($rss_secret_option == $rss_secret_get) {
			?>
			<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
				<channel>
					<title><?php printf(__("Simple History for %s", 'simple-history'), get_bloginfo("name")) ?></title>
					<description><?php printf(__("WordPress History for %s", 'simple-history'), get_bloginfo("name")) ?></description>
					<link><?php echo get_bloginfo("siteurl") ?></link>
					<atom:link href="<?php echo $self_link; ?>" rel="self" type="application/rss+xml" />
					<?php
					$arr_items = simple_history_get_items_array("items=10");
					foreach ($arr_items as $one_item) {
						$object_type = ucwords($one_item->object_type);
						$object_name = esc_html($one_item->object_name);
						$user = get_user_by("id", $one_item->user_id);
						$user_nicename = esc_html($user->user_nicename);
						$description = "";
						if ($user_nicename) {
							$description .= sprintf(__("By %s", 'simple-history'), $user_nicename);
							$description .= "<br />";
						}
						if ($one_item->occasions) {
							$description .= sprintf(__("%d occasions", 'simple-history'), sizeof($one_item->occasions));
							$description .= "<br />";
						}
						
						$item_title = "$object_type \"{$object_name}\" {$one_item->action}";
						$item_title = html_entity_decode($item_title, ENT_COMPAT, "UTF-8");
						$item_guid = get_bloginfo("siteurl") . "?simple-history-guid=" . $one_item->id;
						?>
					      <item>
					         <title><![CDATA[<?php echo $item_title; ?>]]></title>
					         <description><![CDATA[<?php echo $description ?>]]></description>
					         <pubDate><?php echo date("D, d M Y H:i:s", $one_item->date_unix) ?> GMT</pubDate>
					         <guid isPermaLink="false"><?php echo $item_guid ?></guid>
					      </item>
						<?php
					}
					?>
				</channel>
			</rss>
			<?php
		} else {
			// not ok rss secret
			?>
			<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
				<channel>
					<title><?php printf(__("Simple History for %s", 'simple-history'), get_bloginfo("name")) ?></title>
					<description><?php printf(__("WordPress History for %s", 'simple-history'), get_bloginfo("name")) ?></description>
					<link><?php echo get_bloginfo("siteurl") ?></link>
					<atom:link href="<?php echo $self_link; ?>" rel="self" type="application/rss+xml" />
					<item>
						<title><?php _e("Wrong RSS secret", 'simple-history')?></title>
						<description><?php _e("Your RSS secret for Simple History RSS feed is wrong. Please see WordPress settings for current link to the RSS feed.", 'simple-history')?></description>
						<pubDate><?php echo date("D, d M Y H:i:s", time()) ?> GMT</pubDate>
						<guid><?php echo get_bloginfo("siteurl") . "?simple-history-guid=wrong-secret" ?></guid>
					</item>
				</channel>
			</rss>
			<?php

		}
		exit;
	}
	
}


function simple_history_admin_init() {

	load_plugin_textdomain('simple-history', false, "/simple-history/languages");

	// posts
	add_action("save_post", "simple_history_save_post");
	add_action("transition_post_status", "simple_history_transition_post_status", 10, 3);
	add_action("delete_post", "simple_history_delete_post");
	
	// attachments/media
	add_action("add_attachment", "simple_history_add_attachment");
	add_action("edit_attachment", "simple_history_edit_attachment");
	add_action("delete_attachment", "simple_history_delete_attachment");

	add_action("edit_comment", "simple_history_edit_comment");
	add_action("delete_comment", "simple_history_delete_comment");
	add_action("wp_set_comment_status", "simple_history_set_comment_status", 10, 2);
	/*
	edit_comment 
	    Runs after a comment is updated/edited in the database. Action function arguments: comment ID. 
	
	delete_comment 
	    Runs just before a comment is deleted. Action function arguments: comment ID. 

	wp_set_comment_status 
    	Runs when the status of a comment changes. Action function arguments: comment ID, status string indicating the new status ("delete", "approve", "spam", "hold").     
    */
    // comments

	add_settings_section("simple_history_settings_general", SIMPLE_HISTORY_NAME, "simple_history_settings_page", "general");
	add_settings_field("simple_history_settings_field_1", "Show Simple History", "simple_history_settings_field", "general", "simple_history_settings_general");
	add_settings_field("simple_history_settings_field_2", "RSS feed", "simple_history_settings_field_rss", "general", "simple_history_settings_general");
	register_setting("general", "simple_history_show_on_dashboard");
	register_setting("general", "simple_history_show_as_page");

	wp_enqueue_style( "simple_history_styles", SIMPLE_HISTORY_URL . "styles.css", false, SIMPLE_HISTORY_VERSION );	
	
	wp_enqueue_script("simple_history", SIMPLE_HISTORY_URL . "scripts.js", array("jquery"), SIMPLE_HISTORY_VERSION);

}
function simple_history_settings_page() {
	// leave empty. must exist.
}

function simple_history_setting_show_on_dashboard() {
	return (bool) get_option("simple_history_show_on_dashboard", 1);
}
function simple_history_setting_show_as_page() {
	return (bool) get_option("simple_history_show_as_page", 1);
}


function simple_history_settings_field() {
	$show_on_dashboard = simple_history_setting_show_on_dashboard();
	$show_as_page = simple_history_setting_show_as_page();
	?>
	
	<input <?php echo $show_on_dashboard ? "checked='checked'" : "" ?> type="checkbox" value="1" name="simple_history_show_on_dashboard" id="simple_history_show_on_dashboard" />
	<label for="simple_history_show_on_dashboard"><?php _e("on the dashboard", 'simple-history') ?></label>

	<br />
	
	<input <?php echo $show_as_page ? "checked='checked'" : "" ?> type="checkbox" value="1" name="simple_history_show_as_page" id="simple_history_show_as_page" />
	<label for="simple_history_show_as_page"><?php _e("as a page under the tools menu", 'simple-history') ?></label>
	
	<?php
}

function simple_history_get_rss_address() {
	$rss_secret = get_option("simple_history_rss_secret");
	$rss_address = add_query_arg(array("simple_history_get_rss" => "1", "rss_secret" => $rss_secret), get_bloginfo("url") . "/");
	$rss_address = htmlspecialchars($rss_address, ENT_COMPAT, "UTF-8");
	return $rss_address;
}

function simple_history_update_rss_secret() {
	$rss_secret = "";
	for ($i=0; $i<20; $i++) {
		$rss_secret .= chr(rand(97,122));
	}
	update_option("simple_history_rss_secret", $rss_secret);
	return $rss_secret;
}

function simple_history_settings_field_rss() {
	?>
	<?php
	$create_new_secret = false;
	if ($rss_secret == false) {
		$create_new_secret = true;
	}
	if ($_GET["simple_history_rss_update_secret"]) {
		$create_new_secret = true;
		echo "<p class='updated'>";
		_e("Created new secret RSS adress", 'simple-history');
		echo "</p>";
	}
	
	if ($create_new_secret) {
		simple_history_update_rss_secret();
	}
	
	$rss_address = simple_history_get_rss_address();
	echo "<code><a href='$rss_address'>$rss_address</a></code>";
	echo "<br />";
	_e("This is a secret RSS feed for Simple History. Only share the link with people you trust", 'simple-history');
	echo "<br />";
	$update_link = add_query_arg("simple_history_rss_update_secret", "1");
	printf(__("You can <a href='%s'>generate a new address</a> for the RSS feed. This is useful if you think that the address has fallen into the wrong hands.", 'simple-history'), $update_link);
}

// @todo: move all add-related stuff to own file? there are so many of them.. kinda confusing, ey.

function simple_history_activated_plugin($plugin_name) {
	$plugin_name = urlencode($plugin_name);
	simple_history_add("action=activated&object_type=plugin&object_name=$plugin_name");
}
function simple_history_deactivated_plugin($plugin_name) {
	$plugin_name = urlencode($plugin_name);
	simple_history_add("action=deactivated&object_type=plugin&object_name=$plugin_name");
}

function simple_history_edit_comment($comment_id) {
	
	$comment_data = get_commentdata($comment_id, 0, true);
	$comment_post_ID = $comment_data["comment_post_ID"];
	$post = get_post($comment_post_ID);
	$post_title = get_the_title($comment_post_ID);
	$excerpt = get_comment_excerpt($comment_id);
	$author = get_comment_author($comment_id);

	$str = sprintf( "$excerpt [" . __('From %1$s on %2$s') . "]", $author, $post_title );
	$str = urlencode($str);

	simple_history_add("action=edited&object_type=comment&object_name=$str&object_id=$comment_id");
}

function simple_history_delete_comment($comment_id) {
	
	$comment_data = get_commentdata($comment_id, 0, true);
	$comment_post_ID = $comment_data["comment_post_ID"];
	$post = get_post($comment_post_ID);
	$post_title = get_the_title($comment_post_ID);
	$excerpt = get_comment_excerpt($comment_id);
	$author = get_comment_author($comment_id);

	$str = sprintf( "$excerpt [" . __('From %1$s on %2$s') . "]", $author, $post_title );
	$str = urlencode($str);

	simple_history_add("action=deleted&object_type=comment&object_name=$str&object_id=$comment_id");
}

function simple_history_set_comment_status($comment_id, $new_status) {
	#echo "<br>new status: $new_status<br>"; // 0
	// $new_status hold (unapproved), approve, spam, trash
	$comment_data = get_commentdata($comment_id, 0, true);
	$comment_post_ID = $comment_data["comment_post_ID"];
	$post = get_post($comment_post_ID);
	$post_title = get_the_title($comment_post_ID);
	$excerpt = get_comment_excerpt($comment_id);
	$author = get_comment_author($comment_id);

	$action = "";
	if ("approve" == $new_status) {
		$action = "approved";
	} elseif ("hold" == $new_status) {
		$action = "unapproved";
	} elseif ("spam" == $new_status) {
		$action = "marked as spam";
	} elseif ("trash" == $new_status) {
		$action = "trashed";
	} elseif ("0" == $new_status) {
		$action = "untrashed";
	}

	$action = urlencode($action);

	$str = sprintf( "$excerpt [" . __('From %1$s on %2$s') . "]", $author, $post_title );
	$str = urlencode($str);

	simple_history_add("action=$action&object_type=comment&object_name=$str&object_id=$comment_id");
}

function simple_history_update_option($option, $oldval, $newval) {
	/*
	echo "<br><br>simple_history_update_option()";
		echo "<br>Updated option $option";
		echo "<br>oldval: ";
		bonny_d($oldval);
		echo "<br>newval:";
		bonny_d($newval);
	*/

	if ($option == "active_plugins") {
	
		$debug = "\n";
		$debug .= "\nsimple_history_update_option()";
		$debug .= "\noption: $option";
		$debug .= "\noldval: " . print_r($oldval, true);
		$debug .= "\nnewval: " . print_r($newval, true);
	
		//  Returns an array containing all the entries from array1 that are not present in any of the other arrays. 
		// alltså:
		//	om newval är array1 och innehåller en rad så är den tillagd
		// 	om oldval är array1 och innhåller en rad så är den bortagen
		$diff_added = array_diff((array) $newval, (array) $oldval);
		$diff_removed = array_diff((array) $oldval, (array) $newval);
		$debug .= "\ndiff_added: " . print_r($diff_added, true);
		$debug .= "\ndiff_removed: " . print_r($diff_removed, true);
		#b_fd($debug);
	}
}

function simple_history_updated_option($option, $oldval, $newval) {
/*
	echo "<br><br>simple_history_updated_option()";
	echo "<br>Updated option $option";
	echo "<br>oldval: ";
	bonny_d($oldval);
	echo "<br>newval:";
	bonny_d($newval);
*/

}

// debug to file
// short for "bonny_file_debug" :)
function b_fd($str) {
	$file = "/Users/bonny/Dropbox/localhost/wordpress3/wp-content/plugins/simple-history/debug.txt";
	#$f = fopen($file, "+a");
	file_put_contents($file, $str, FILE_APPEND);
}

/*
function simple_history_updated_option2($option, $oldval) {
	echo "<br><br>xxx_simple_history_updated_option2";
	bonny_d($option);
	bonny_d($oldval);
}
function simple_history_updated_option3($option) {
	echo "<br><br>xxx_simple_history_updated_option3";
	echo "<br>option: $option";
}
*/


function simple_history_add_attachment($attachment_id) {
	$post = get_post($attachment_id);
	$post_title = urlencode(get_the_title($post->ID));
	simple_history_add("action=added&object_type=attachment&object_id=$attachment_id&object_name=$post_title");
}
function simple_history_edit_attachment($attachment_id) {
	// is this only being called if the title of the attachment is changed?!
	$post = get_post($attachment_id);
	$post_title = urlencode(get_the_title($post->ID));
	simple_history_add("action=updated&object_type=attachment&object_id=$attachment_id&object_name=$post_title");
}
function simple_history_delete_attachment($attachment_id) {
	$post = get_post($attachment_id);
	$post_title = urlencode(get_the_title($post->ID));
	simple_history_add("action=deleted&object_type=attachment&object_id=$attachment_id&object_name=$post_title");
}

// user is updated
function simple_history_profile_update($user_id) {
	$user = get_user_by("id", $user_id);
	$user_nicename = urlencode($user->user_nicename);
	simple_history_add("action=updated&object_type=user&object_id=$user_id&object_name=$user_nicename");
}

// user is created
function simple_history_user_register($user_id) {
	$user = get_user_by("id", $user_id);
	$user_nicename = urlencode($user->user_nicename);
	simple_history_add("action=created&object_type=user&object_id=$user_id&object_name=$user_nicename");
}

// user is deleted
function simple_history_delete_user($user_id) {
	$user = get_user_by("id", $user_id);
	$user_nicename = urlencode($user->user_nicename);
	simple_history_add("action=deleted&object_type=user&object_id=$user_id&object_name=$user_nicename");
}

// user logs in
function simple_history_wp_login($user) {
	$current_user = wp_get_current_user();
	$user = get_user_by("login", $user);
	$user_nicename = urlencode($user->user_nicename);
	// if user id = null then it's because we are logged out and then no one is acutally loggin in.. like a.. ghost-user!
	if ($current_user->ID == 0) {
		$user_id = $user->ID;
	} else {
		$user_id = $current_user->ID;
	}
	simple_history_add("action=logged_in&object_type=user&object_id=".$user->ID."&user_id=$user_id&object_name=$user_nicename");
}
// user logs out
function simple_history_wp_logout() {
	$current_user = wp_get_current_user();
	$current_user_id = $current_user->ID;
	$user_nicename = urlencode($current_user->user_nicename);
	simple_history_add("action=logged_out&object_type=user&object_id=$current_user_id&object_name=$user_nicename");
}

function simple_history_delete_post($post_id) {
	if (wp_is_post_revision($post_id) == false) {
		$post = get_post($post_id);
		if ($post->post_status != "auto-draft" && $post->post_status != "inherit") {
			$post_title = urlencode(get_the_title($post->ID));
			simple_history_add("action=deleted&object_type=post&object_subtype=" . $post->post_type . "&object_id=$post_id&object_name=$post_title");
		}
	}
}

function simple_history_save_post($post_id) {

	if (wp_is_post_revision($post_id) == false) {
		// not a revision
		// it should also not be of type auto draft
		$post = get_post($post_id);
		if ($post->post_status != "auto-draft") {
			// bonny_d($post);
			#echo "save";
			// [post_title] => neu
			// [post_type] => page
		}
		
	}
}

// post has changed status
function simple_history_transition_post_status($new_status, $old_status, $post) {

	#echo "<br>From $old_status to $new_status";

	// From new to auto-draft <- ignore
	// From new to inherit <- ignore
	// From auto-draft to draft <- page/post created
	// From draft to draft
	// From draft to pending
	// From pending to publish
	# From pending to trash
	// if not from & to = same, then user has changed something
	//bonny_d($post); // regular post object
	if ($old_status == "auto-draft" && ($new_status != "auto-draft" && $new_status != "inherit")) {
		// page created
		$action = "created";
	} elseif ($new_status == "auto-draft" || ($old_status == "new" && $new_status == "inherit")) {
		// page...eh.. just leave it.
		return;
	} elseif ($new_status == "trash") {
		$action = "deleted";
	} else {
		// page updated. i guess.
		$action = "updated";
	}
	$object_type = "post";
	$object_subtype = $post->post_type;
	if ($object_subtype == "revision") {
		// don't log revisions
		return;
	}
	
	if (wp_is_post_revision($post->ID) === false) {
		// ok, no revision
		$object_id = $post->ID;
	} else {
		return; 
	}
	
	$post_title = get_the_title($post->ID);
	$post_title = urlencode($post_title);
	
	simple_history_add("action=$action&object_type=$object_type&object_subtype=$object_subtype&object_id=$object_id&object_name=$post_title");
}


/**
 * add event to history table
 */
function simple_history_add($args) {

	$defaults = array(
		"action" => null,
		"object_type" => null,
		"object_subtype" => null,
		"object_id" => null,
		"object_name" => null,
		"user_id" => null,
	);

	$args = wp_parse_args( $args, $defaults );

	$action = $args["action"];
	$object_type = $args["object_type"];
	$object_subtype = $args["object_subtype"];
	$object_id = $args["object_id"];
	$object_name = mysql_real_escape_string($args["object_name"]);
	$user_id = $args["user_id"];

	global $wpdb;
	$tableprefix = $wpdb->prefix;
	if ($user_id) {
		$current_user_id = $user_id;
	} else {
		$current_user = wp_get_current_user();
		$current_user_id = (int) $current_user->ID;
	}
	$sql = "INSERT INTO {$tableprefix}simple_history SET date = now(), action = '$action', object_type = '$object_type', object_subtype = '$object_subtype', user_id = '$current_user_id', object_id = '$object_id', object_name = '$object_name'";
	$wpdb->query($sql);
}


// Returns an English representation of a past date within the last month
// Graciously stolen from http://ejohn.org/files/pretty.js
// ..and simple_history stole it even more graciously from simple-php-framework http://github.com/tylerhall/simple-php-framework/
function simple_history_time2str($ts) {
    #if(!ctype_digit($ts))
    #   $ts = strtotime($ts);

    $diff = time() - $ts;
    if($diff == 0)
        return 'now';
    elseif($diff > 0)
    {
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 60) return 'just now';
            if($diff < 120) return '1 minute ago';
            if($diff < 3600) return floor($diff / 60) . ' minutes ago';
            if($diff < 7200) return '1 hour ago';
            if($diff < 86400) return floor($diff / 3600) . ' hours ago';
        }
        if($day_diff == 1) return 'Yesterday';
        if($day_diff < 7) return $day_diff . ' days ago';
        if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
        if($day_diff < 60) return 'last month';
        return date('F Y', $ts);
    }
    else
    {
        $diff = abs($diff);
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 120) return 'in a minute';
            if($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
            if($diff < 7200) return 'in an hour';
            if($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
        }
        if($day_diff == 1) return 'Tomorrow';
        if($day_diff < 4) return date('l', $ts);
        if($day_diff < 7 + (7 - date('w'))) return 'next week';
        if(ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
        if(date('n', $ts) == date('n') + 1) return 'next month';
        #return date('F Y', $ts);
        return $ts; // return back and let us do something else with it
    }
}

function simple_history_purge_db() {
	global $wpdb;
	$tableprefix = $wpdb->prefix;
	$sql = "DELETE FROM {$tableprefix}simple_history WHERE DATE_ADD(date, INTERVAL 60 DAY) < now()";
	$wpdb->query($sql);
}

function simple_history_management_page() {

	simple_history_purge_db();

	?>

	<div class="wrap">
		<h2><?php echo SIMPLE_HISTORY_NAME ?></h2>
		<?php	
		simple_history_print_nav();
		simple_history_print_history();
		?>
	</div>

	<?php

}

if (!function_exists("bonny_d")) {
	function bonny_d($var) {
		echo "<pre>";
		print_r($var);
		echo "</pre>";
	}
}

// when activating plugin: create tables
#register_activation_hook( __FILE__, 'simple_history_install' );
#echo "<br>" . WP_PLUGIN_DIR . "/simple-history/index.php";
#echo plugin_basename(__FILE__);
// __FILE__ doesnt work for me because of soft linkes directories
register_activation_hook( WP_PLUGIN_DIR . "/simple-history/index.php" , 'simple_history_install' );

function simple_history_install() {

	global $wpdb;

	$table_name = $wpdb->prefix . "simple_history";
	#if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
		  id int(10) NOT NULL AUTO_INCREMENT,
		  date datetime NOT NULL,
		  action varchar(255) NOT NULL,
		  object_type varchar(255) NOT NULL,
		  object_subtype VARCHAR(255) NOT NULL,
		  user_id int(10) NOT NULL,
		  object_id int(10) NOT NULL,
		  object_name varchar(255) NOT NULL,
		  PRIMARY KEY (id)
		);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		// add ourself as a history item.
		$plugin_name = urlencode(SIMPLE_HISTORY_NAME);
	
	#}

	simple_history_add("action=activated&object_type=plugin&object_name=$plugin_name");
	
	// also generate a rss secret, if it does not exist
	if (!get_option("simple_history_rss_secret")) {
		simple_history_update_rss_secret();
	}

}

function simple_history_print_nav() {

	global $wpdb;
	$tableprefix = $wpdb->prefix;
	
	// fetch all types that are in the log
	if (isset($_GET["simple_history_type_to_show"])) {
		$simple_history_type_to_show = $_GET["simple_history_type_to_show"];
	} else {
		$simple_history_type_to_show = "";
	}
	$sql = "SELECT DISTINCT object_type, object_subtype FROM {$tableprefix}simple_history ORDER BY object_type, object_subtype";
	$arr_types = $wpdb->get_results($sql);
	#echo "<p>View:</p>";
	$str_types = "";
	$str_types .= "<ul class='simple-history-filter simple-history-filter-type'>";
	$css = "";
	if (empty($simple_history_type_to_show)) {
		$css = "class='selected'";
	}

	// add_query_arg(
	$link = esc_html(add_query_arg("simple_history_type_to_show", ""));
	#echo "<li>Filter by type: </li>";
	$str_types .= "<li $css><a href='$link'>All types</a> | </li>";
	foreach ($arr_types as $one_type) {
		$css = "";
		if ($one_type->object_subtype && $simple_history_type_to_show == ($one_type->object_type."/".$one_type->object_subtype)) {
			$css = "class='selected'";
		} elseif (!$one_type->object_subtype && $simple_history_type_to_show == $one_type->object_type) {
			$css = "class='selected'";
		}
		$str_types .= "<li $css>";
		$arg = "";
		if ($one_type->object_subtype) {
			$arg = $one_type->object_type."/".$one_type->object_subtype;
		} else {
			$arg = $one_type->object_type;
		}
		$link = esc_html(add_query_arg("simple_history_type_to_show", $arg));
		$str_types .= "<a href='$link'>";
		$str_types .= $one_type->object_type;
		if ($one_type->object_subtype) {
			$str_types .= "/".$one_type->object_subtype;
		}
		$str_types .= "</a> | ";
		$str_types .= "</li>";
	}
	$str_types .= "</ul>";
	$str_types = str_replace("| </li></ul>", "</li></ul>", $str_types);
	if (!empty($arr_types)) {
		echo $str_types;
	}

	// fetch all users that are in the log
	$sql = "SELECT DISTINCT user_id FROM {$tableprefix}simple_history WHERE user_id <> 0";
	$arr_users_regular = $wpdb->get_results($sql);
	foreach ($arr_users_regular as $one_user) {
		$arr_users[$one_user->user_id] = array("user_id" => $one_user->user_id);
	}
	if (!empty($arr_users)) {
		foreach ($arr_users as $user_id => $one_user) {
			$user = get_user_by("id", $user_id);
			if ($user) {
				$arr_users[$user_id]["user_login"] = $user->user_login;
				$arr_users[$user_id]["user_nicename"] = $user->user_nicename;
				if (isset($user->first_name)) {
					$arr_users[$user_id]["first_name"] = $user->first_name;
				}
				if (isset($user->last_name)) {
					$arr_users[$user_id]["last_name"] = $user->last_name;
				}
			}
		}
	}

	if ($arr_users) {
		if (isset($_GET["simple_history_user_to_show"])) {
			$simple_history_user_to_show = $_GET["simple_history_user_to_show"];
		} else {
			$simple_history_user_to_show = "";
		}
		$str_users = "";
		$str_users .= "<ul class='simple-history-filter simple-history-filter-user'>";
		$css = "";
		if (empty($simple_history_user_to_show)) {
			$css = " class='selected' ";
		}
		$link = esc_html(add_query_arg("simple_history_user_to_show", ""));
		#echo "<li>Filter by user: </li>";
		$str_users .= "<li $css><a href='$link'>" . __("By all users", 'simple-history') ."</a> | </li>";
		foreach ($arr_users as $user_id => $user_info) {
			$link = esc_html(add_query_arg("simple_history_user_to_show", $user_id));
			$css = "";
			if ($user_id == $simple_history_user_to_show) {
				$css = " class='selected' ";
			}
			$str_users .= "<li $css>";
			$str_users .= "<a href='$link'>";
			$str_users .= $user_info["user_nicename"];
			$str_users .= "</a> | ";
			$str_users .= "</li>";
		}
		$str_users .= "</ul>";
		$str_users = str_replace("| </li></ul>", "</li></ul>", $str_users);
		echo $str_users;
	}


}


// return an array with all events and occasions
function simple_history_get_items_array($args) {

	global $wpdb;
	
	$defaults = array(
		"page" => 0,
		"items" => 5,
		"filter_type" => "",
		"filter_user" => "",
		"is_ajax" => false
	);
	$args = wp_parse_args( $args, $defaults );

	$simple_history_type_to_show = $args["filter_type"];
	$simple_history_user_to_show = $args["filter_user"];
	
	$where = " WHERE 1=1 ";
	if ($simple_history_type_to_show) {
		$filter_type = "";
		$filter_subtype = "";
		if (strpos($simple_history_type_to_show, "/") !== false) {
			// split it up
			$arr_args = explode("/", $simple_history_type_to_show);
			$filter_type = $arr_args[0];
			$filter_subtype = $arr_args[1];
		} else {
			$filter_type = $simple_history_type_to_show;
		}
		$where .= " AND object_type = '$filter_type' ";
		$where .= " AND object_subtype = '$filter_subtype' ";
	}
	if ($simple_history_user_to_show) {
		$userinfo = get_user_by("slug", $simple_history_user_to_show);
		$where .= " AND user_id = '" . $userinfo->ID . "'";
	}

	$tableprefix = $wpdb->prefix;
	$limit_page = $args["page"] * $args["items"];
	$limit_items = $args["items"];
	$sql_limit = " LIMIT $limit_page, $args[items]";

	$sql = "SELECT *, UNIX_TIMESTAMP(date) as date_unix FROM {$tableprefix}simple_history $where ORDER BY date DESC ";
	$rows = $wpdb->get_results($sql);
	
	$loopNum = 0;
	$real_loop_num = -1;
	
	$arr_events = array();
	if ($rows) {
		$prev_row = null;
		foreach ($rows as $one_row) {
			if (
				$prev_row
				&& $one_row->action == $prev_row->action
				&& $one_row->object_type == $prev_row->object_type
				&& $one_row->object_type == $prev_row->object_type
				&& $one_row->object_subtype == $prev_row->object_subtype
				&& $one_row->user_id == $prev_row->user_id
				&& $one_row->object_id == $prev_row->object_id
				&& $one_row->object_name == $prev_row->object_name
			) {
				// this event is like the previous event, but only with a different date
				// so add it to the last element in arr_events
				$arr_events[$prev_row->id]->occasions[] = $one_row;
			} else {
		
				$real_loop_num++;
		
				#echo "<br>real_loop_num: $real_loop_num";
				#echo "<br>loop_num: $loopNum";
		
				if ($args["page"] > 0 && ($args["page"] * $args["items"] > $real_loop_num)) {
					#echo "<br>CONTINUE";
					continue;
				}
				
				if ($loopNum >= $args["items"]) {
					#echo "<br>BREAK";
					break;
				}
			
				// new event, not as previous one
				#echo "<br><br>adding one";
				$arr_events[$one_row->id] = $one_row;
				$arr_events[$one_row->id]->occasions = array();
				
				$prev_row = $one_row;
				
				$loopNum++;
			}
		}
	}
	return $arr_events;
}

// output the log
// take filtrering into consideration
function simple_history_print_history($args = null) {
	
	$arr_events = simple_history_get_items_array($args);

	$defaults = array(
		"page" => 0,
		"items" => 5,
		"filter_type" => "",
		"filter_user" => "",
		"is_ajax" => false
	);

	$args = wp_parse_args( $args, $defaults );

	if ($arr_events) {
		if (!$args["is_ajax"]) {
			// if not ajax, print the div
			echo "<div id='simple-history-ol-wrapper'><ol class='simple-history'>";
		}
	
		$loopNum = 0;
		$real_loop_num = -1;
		foreach ($arr_events as $one_row) {

			$real_loop_num++;

			#if ($args["page"] > 0 && ($args["page"] * $args["items"] > $real_loop_num)) {
			#	continue;
			#}
			
			#if ($loopNum >= $args["items"]) {
			#	break;
			#}
			/*
				stdClass Object
				(
				    [id] => 94
				    [date] => 2010-07-03 21:08:43
				    [action] => update
				    [object_type] => post
				    [object_subtype] => page
				    [user_id] => 1
				    [object_id] => 732
				    [date_unix] => 1278184123
				    [occasions] => array
				)				
			*/
			#bonny_d($one_row);
							
			$object_type = $one_row->object_type;
			$object_subtype = $one_row->object_subtype;
			$object_id = $one_row->object_id;
			$object_name = $one_row->object_name;
			$user_id = $one_row->user_id;
			$action = $one_row->action;
			$occasions = $one_row->occasions;
			$num_occasions = sizeof($occasions);

			$css = "";
			if ("attachment" == $object_type) {
				if (wp_get_attachment_image_src($object_id, array(50,50), true)) {
					// yep, it's an attachment and it has an icon/thumbnail
					$css .= ' simple-history-has-attachment-thumnbail ';
				}
			}
			if ("user" == $object_type) {
				$css .= ' simple-history-has-attachment-thumnbail ';
			}

			if ($num_occasions > 0) {
				$css .= ' simple-history-has-occasions ';
			}
			
			echo "<li class='$css'>";

			echo "<div class='first'>";
			
			// who performed the action
			$who = "";
			$user = get_user_by("id", $user_id);

			$who .= "<span class='who'>";
			if ($user) {
				// http://localhost/wordpress3/wp-admin/user-edit.php?user_id=6
				$user_link = "user-edit.php?user_id={$user->ID}";
				$who .= "<a href='$user_link'>";
				$who .= $user->user_nicename;
				$who .= "</a>";
				if (isset($user->first_name) && isset($user->last_name)) {
					if ($user->first_name || $user->last_name) {
						$who .= " (";
						if ($user->first_name && $user->last_name) {
							$who .= $user->first_name . " " . $user->last_name;
						} else {
							$who .= $user->first_name . $user->last_name; // just one of them, no space necessary
						}
						$who .= ")";
					}
				}
			} else {
				$who .= "&lt;" . __("Unknown or deleted user", 'simple-history') ."&gt;";
			}
			$who .= "</span>";
			
			// what and object
			if ("post" == $object_type) {
				
				$post_out = "";
				$post_out .= $object_subtype;
				$post = get_post($object_id);
				if (null == $post) {
					// post does not exist, probably deleted
					// check if object_name exists
					if ($object_name) {
						$post_out .= " <span class='simple-history-title'>\"" . esc_html($object_name) . "\"</span>";
					} else {
						$post_out .= " <span class='simple-history-title'>&lt;unknown name&gt;</span>";
					}
				} else {
					#$title = esc_html($post->post_title);
					$title = get_the_title($post->ID);
					$edit_link = get_edit_post_link($object_id, 'display');
					$post_out .= " <a href='$edit_link'>";
					$post_out .= "<span class='simple-history-title'>{$title}</span>";
					$post_out .= "</a>";
				}
				if ("created" == $action) {
					$post_out .= " " . __("created", 'simple-history') . " ";
				} elseif ("updated" == $action) {
					$post_out .= " " . __("updated", 'simple-history') . " ";
				} elseif ("deleted" == $action) {
					$post_out .= " " . __("deleted", 'simple-history') . " ";
				} else {
					$post_out .= " $action";
				}
				
				$post_out = ucfirst($post_out);
				echo $post_out;

				
			} elseif ("attachment" == $object_type) {
			
				$attachment_out = "";
				$attachment_out .= "attachment ";
				$post = get_post($object_id);
				
				if ($post) {
					#$title = $post->post_title;
					$title = urlencode(get_the_title($post->ID));					
					$edit_link = get_edit_post_link($object_id, 'display');
					$attachment_image_src = wp_get_attachment_image_src($object_id, array(50,50), true);
					$attachment_image = "";
					if ($attachment_image_src) {
						$attachment_image = "<a class='simple-history-attachment-thumbnail' href='$edit_link'><img src='{$attachment_image_src[0]}' alt='Attachment icon' width='{$attachment_image_src[1]}' height='{$attachment_image_src[2]}' /></a>";
					}
					$attachment_out .= $attachment_image;
					$attachment_out .= " <a href='$edit_link'>";
					$attachment_out .= "<span class='simple-history-title'>{$title}</span>";
					$attachment_out .= "</a>";
					
					#echo " (".get_post_mime_type($object_id).")";
				} else {
					if ($object_name) {
						$attachment_out .= "<span class='simple-history-title'>\"" . esc_html($object_name) . "\"</span>";
					} else {
						$attachment_out .= " <span class='simple-history-title'>&lt;deleted&gt;</span>";
					}
				}

				if ("added" == $action) {
					$attachment_out .= " added ";
				} elseif ("updated" == $action) {
					$attachment_out .= " updated ";
				} elseif ("deleted" == $action) {
					$attachment_out .= " deleted ";
				} else {
					$attachment_out .= " $action ";
				}
				
				$attachment_out = ucfirst($attachment_out);
				echo $attachment_out;
				#echo " <span class='simple-history-discrete'>(".get_post_mime_type($object_id).")</span>";


			} elseif ("user" == $object_type) {
				$user_out = "";
				$user_out .= "user";
				$user = get_user_by("id", $object_id);
				if ($user) {
					$user_link = "user-edit.php?user_id={$user->ID}";
					$user_out .= "<span class='simple-history-title'>";
					$user_out .= " <a href='$user_link'>";
					$user_out .= $user->user_nicename;
					$user_out .= "</a>";
					if (isset($user->first_name) && isset($user->last_name)) {
						if ($user->first_name || $user->last_name) {
							$user_out .= " (";
							if ($user->first_name && $user->last_name) {
								$user_out .= $user->first_name . " " . $user->last_name;
							} else {
								$user_out .= $user->first_name . $user->last_name; // just one of them, no space necessary
							}
							$user_out .= ")";
						}
					}
					$user_out .= "</span>";
				} else {
					// most likely deleted user
					$user_link = "";
					$user_out .= " \"$object_name\"";
				}

				$user_avatar = get_avatar($user->user_email, "50"); 
				if ($user_link) {
					$user_out .= "<a class='simple-history-attachment-thumbnail' href='$user_link'>$user_avatar</a>";
				} else {
					$user_out .= "<span class='simple-history-attachment-thumbnail' href='$user_link'>$user_avatar</span>";
				}

				if ("created" == $action) {
					$user_out .=  " " . __("added", 'simple-history') . " ";
				} elseif ("updated" == $action) {
					$user_out .= " " . __("updated", 'simple-history') . " " ;
				} elseif ("deleted" == $action) {
					$user_out .= " " . __("deleted", 'simple-history') . " ";
				} elseif ("logged_in" == $action) {
					$user_out .= " " . __("logged in", 'simple-history') . " ";
				} elseif ("logged_out" == $action) {
					$user_out .= " " . __("logged out", 'simple-history') . " ";
				} else {
					$user_out .= " $action";
				}
				
				$user_out = ucfirst($user_out);
				echo $user_out;

			} elseif ("comment" == $object_type) {
				
				$comment_link = get_edit_comment_link($object_id);
				echo ucwords($object_type) . " $object_subtype <a href='$comment_link'><span class='simple-history-title'>$object_name\"</span></a> $action";

			} else {

				// unknown/general type
				echo ucwords($object_type) . " $object_subtype <span class='simple-history-title'>\"$object_name\"</span> $action";

			}
			echo "</div>";
			
			echo "<div class='second'>";
			// when
			$date_i18n_date = date_i18n(get_option('date_format'), strtotime($one_row->date), $gmt=false);
			$date_i18n_time = date_i18n(get_option('time_format'), strtotime($one_row->date), $gmt=false);		
			echo "By $who, ";
			echo "<span class='when'>".simple_history_time2str($one_row->date_unix)."</span>";
			echo "<span class='when_detail'>$date_i18n_date at $date_i18n_time</span>";
			echo "</div>";

			// occasions
			if ($num_occasions > 0) {
				echo "<div class='third'>";
				if ($num_occasions == 1) {
					$one_occasion = __("+ 1 occasion", 'simple-history');
					echo "<a class='simple-history-occasion-show' href='#'>$one_occasion</a>";
				} else {
					$many_occasion = sprintf(__("+ %d occasions", 'simple-history'), $num_occasions);
					echo "<a class='simple-history-occasion-show' href='#'>$many_occasion</a>";
				}
				echo "<ul class='simple-history-occasions hidden'>";
				foreach ($occasions as $one_occasion) {
					echo "<li>";
					$date_i18n_date = date_i18n(get_option('date_format'), strtotime($one_occasion->date), $gmt=false);
					$date_i18n_time = date_i18n(get_option('time_format'), strtotime($one_occasion->date), $gmt=false);		
					echo simple_history_time2str($one_occasion->date_unix) . " ($date_i18n_date at $date_i18n_time)";

					echo "</li>";
				}
				echo "</ul>";
				echo "</div>";
			}
			

			echo "</li>";

			$loopNum++;
		}
		
		// if $loopNum == 0 no items where found for this page
		if ($loopNum == 0) {
			echo "simpleHistoryNoMoreItems";
		}
		
		if (!$args["is_ajax"]) {
			// if not ajax, print the divs and stuff we need
			$show_more = sprintf(__("Show %d more", 'simple-history'), $args["items"]);
			$loading = __("Loading...", 'simple-history');
			$no_more_found = __("No more history items found.", 'simple-history');
			$view_rss = __("Simple History RSS feed", 'simple-history');
			$view_rss_link = simple_history_get_rss_address();
			
			echo "</ol>
			</div>
			<p id='simple-history-load-more'><a href='#'>$show_more</a></p>
			<p class='hidden' id='simple-history-load-more-loading'>$loading</p>
			<p class='hidden' id='simple-history-no-more-items'>$no_more_found</p>
			<p id='simple-history-rss-feed-dashboard'><a title='$view_rss' href='$view_rss_link'>$view_rss</a></p>
			<p id='simple-history-rss-feed-page'><a title='$view_rss' href='$view_rss_link'><span></span>$view_rss</a></p>
			";
		}
	} else {
		if ($args["is_ajax"]) {
			echo "simpleHistoryNoMoreItems";
		} else {
			$no_found = __("No history items found.", 'simple-history');
			$please_note = __("Please note that Simple History only records things that happen after this plugin have been installed.", 'simple-history');
			echo "<p>$no_found</p>";
			echo "<p>$please_note</p>";
		}
		
	}
}

?>