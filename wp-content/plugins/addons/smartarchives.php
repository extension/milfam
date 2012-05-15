<?php
/*
Plugin Name: Smart Archives
Version: 1.01
Plugin URI: http://justinblanton.com/projects/smartarchives/
Description: A simple, clean, and future-proof way to present your archives.
Author: Justin Blanton
Author URI: http://justinblanton.com
*/

function smartArchives(){

global $tableposts, $PHP_SELF;

// set the URI of your archives; this might not need to be changed (currently, it's http://yoursite.com/archives/)
//$archive_uri = get_settings('home')."/archives/";
$archive_uri = get_settings('home')."/";
$now = gmdate('Y-m-d H:i:s'); // get the current GMT date
$year = date('Y');
$qy = mysql_query("SELECT distinct year(post_date) as year, post_status FROM $tableposts WHERE post_status='publish' AND post_date <= CURDATE() ORDER BY year desc");

// loop to create the small archive block with year/month links
while($years = mysql_fetch_array($qy)) {
   echo('<strong><a href="'.$archive_uri.$years[year].'/">'.$years[year].'</a>:</strong> ');
	$qm = mysql_query("SELECT distinct month(post_date) as month, monthname(post_date) as monthn FROM $tableposts ORDER BY month asc") or die(mysql_error());
	while($date = mysql_fetch_array($qm)) {
		$q = mysql_query("SELECT *, year(post_date) as year, month(post_date) as month FROM $tableposts WHERE year(post_date)='$year' AND month(post_date)='$date[month]' AND post_status='publish' AND post_date <= CURDATE() ORDER BY id desc") or die(mysql_error()); 
		if(mysql_num_rows($q)) {
			$sm = date("M", strtotime("$date[month]/01/2001")); // get the shortened month name
			$pd = sprintf("%02s", $date[month]); // pad the month with a zero if needed 
			echo('<a href="'.$archive_uri.$year.'/'.$pd.'/">'.$sm.'</a> ');
		}
	}
	$year--; // move to previous year
	echo('<br />'."\n");
}

echo ('<br /><br />'."\n");

$year = date('Y'); // reset the year
$qy = mysql_query("SELECT distinct year(post_date) as year, post_status FROM $tableposts WHERE post_status='publish' AND post_date <= CURDATE() ORDER BY year desc");

// loop to display links to all posts, sorted by descending month and day
while($years = mysql_fetch_array($qy)) {
	$qm = mysql_query("SELECT distinct month(post_date) as month, monthname(post_date) as monthn FROM $tableposts ORDER BY month desc") or die(mysql_error());
	while($date = mysql_fetch_array($qm)) {
		$q = mysql_query("SELECT *, year(post_date) as year, month(post_date) as month FROM $tableposts WHERE year(post_date)='$year' AND month(post_date)='$date[month]' AND post_status='publish' AND post_date <= CURDATE() ORDER BY id desc") or die(mysql_error());
		if(mysql_num_rows($q)) {
			$lm = date("F", strtotime("$date[month]/01/2001")); // get the full month name
			$pd = sprintf("%02s", $date[month]); // pad the month with a zero if needed 
			echo('<h2><a href="'.$archive_uri.$year.'/'.$pd.'/">'.$lm.' '.$years[year].'</a></h2>'."\n");
		    echo('<ul>'."\n");
		    $q = mysql_query("SELECT *, year(post_date) as year, month(post_date) as month FROM $tableposts WHERE year(post_date)='$year' AND month(post_date)='$date[month]' AND post_status='publish' ORDER BY id desc") or die(mysql_error());
		    while($post = mysql_fetch_array($q)){
				if ($post[post_date_gmt] <= $now) {
					echo('<li><a href="'.get_permalink($post[ID]).'">'.$post[post_title].'</a></li>'."\n");
				}
			}
			echo ('</ul>'."\n".'<br />'."\n");
		}
	}
	$year--; // move to previous year
}

}
