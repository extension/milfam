<?php
/*
Plugin Name: RSS Includes Pages
Version: 1.4.3
Plugin URI: http://infolific.com/technology/software-worth-using/include-pages-in-wordpress-rss-feeds/
Description: Include pages (not just posts) in RSS feeds. Particularly useful to those that use WordPress as a CMS. 
Author: Marios Alexandrou
Author URI: http://infolific.com/technology/
*/
add_filter('posts_where', 'ma_posts_where');

function ma_posts_where($var){
	if (!is_feed()){ // check if this is a feed
		return $var; // if not, return an unmodified variable
	} else {
		global $table_prefix; // get the table prefix
		$find = $table_prefix . 'posts.post_type = \'post\''; // find where the query filters by post_type
		$replace = '(' . $find . ' OR ' . $table_prefix . 'posts.post_type = \'page\')'; // add OR post_type 'page' to the query
		$var = str_replace($find, $replace, $var); // change the query
	}
	return $var; // return the variable
}

/*
* Deal with Last Post Modified so feeds will validate.  WP default just checks for posts, not pages.
*/

add_filter('get_lastpostmodified', 'ma_get_lastpostmodified',10,2);

// We do this because is_feed is not set when calling get_lastpostmodified.
add_action('rss2_ns', 'ma_feed_true');
add_action('atom_ns', 'ma_feed_true');
add_action('rdf_ns', 'ma_feed_true');
// We won't mess with comment feeds.
add_action ('rss2_comments_ns', 'ma_feed_false');
add_action ('atom_comments_ns', 'ma_feed_false');

function ma_get_lastpostmodified($lastpostmodified, $timezone){
	global $ma_feed, $wpdb;;
	if (!($ma_feed)){
		return $lastpostmodified;
	}
	
	//queires taken from wp-includes/post.php  modified to include pages
	$lastpostmodified = $wpdb->get_var("SELECT post_modified_gmt FROM $wpdb->posts WHERE post_status = 'publish' AND (post_type = 'post' OR post_type = 'page') ORDER BY post_modified_gmt DESC LIMIT 1");
	$lastpostdate = $wpdb->get_var("SELECT post_date_gmt FROM $wpdb->posts WHERE post_status = 'publish' AND (post_type = 'post' OR post_type = 'page') ORDER BY post_date_gmt DESC LIMIT 1");
	if ( $lastpostdate > $lastpostmodified ) {
			$lastpostmodified = $lastpostdate;
	}
	return $lastpostmodified;
}

function ma_feed_true(){
	global $ma_feed;
	$ma_feed = true;
}
function ma_feed_false(){
	global $ma_feed;
	$ma_feed = false;
}
?>
