<?php
/*
Plugin Name: Search Pages
Plugin URI: http://www.internetofficer.com/wordpress/search-pages/
Description: This makes search queries look at pages and posts instead of only posts. Based on the <a href="http://www.randomfrequency.net/wordpress/search-pages">original Search Pages plugin</a> by David B. Nagle. David was inspired by a <a href="http://www.kwebble.com/blog/2005/02/20/searching-pages-in-wordpress-15/">WordPress hack</a> created by Rob Schl&uuml;ter.
Author: InternetOfficer SPRL
Version: 2.0
Author URI: http://www.internetofficer.com/
*/ 

function add_paged_search_where($where) {
	global $wp_query;
	if (!empty($wp_query->query_vars['s'])) {
		$wordpress_version = explode ('.', get_bloginfo('version') ) ;
		if ( $wordpress_version[0] <= 1 || ($wordpress_version[0] == 2 && $wordpress_version[1] == 0 && $wordpress_version[2] <= 5) ) {
			$where = str_replace(' AND (post_status = "publish"', ' AND ((post_status = "static" or post_status = "publish")', $where);
		} else {
			$where = str_replace(" AND (post_type = 'post' AND (post_status = 'publish'", " AND ((post_type = 'post' OR post_type = 'page') AND (post_status = 'publish'", $where);
		}
	}
	return $where;
}

add_filter('posts_where', 'add_paged_search_where');
?>
