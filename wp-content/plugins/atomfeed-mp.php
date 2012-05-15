<?php
/*
Plugin Name: AtomFeed Fix
Plugin URI: http://wordpress.org/#
Description: blah
Author: Mark Pilgrim
Version: 0.0
Author URI: http://diveintomark.org
*/

remove_action('do_feed_rss2', 'do_feed_rss2', 10, 1);
add_action('do_feed_rss2', 'do_feed_atom', 10, 1);

?>