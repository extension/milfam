<?php

if ( !empty(	$widget_title ) ) {
	echo $before_title . $widget_title . $after_title;
}
?>

<section class="ssb_followers simplesocial-simple-round">

    <?php if( $display == $show_facebook ):?>
    <a class="ssb_button simplesocial-fb-follow" rel="noopener" href="https://facebook.com/<?php echo  $facebook_id;?>"  target="_blank"><span class="simplesocialtxt"><?php echo $facebook_text;?> </span><span class="widget_counter"> <?php echo ( $display == $facebook_show_counter)? $fb_likes: '' ;?> </span></a>
	<?php endif;
	if( $display == $show_twitter ):   ?>
    <a class="ssb_button simplesocial-twt-follow" rel="noopener" href="https://twitter.com/<?php echo  $twitter_id;?>" target="_blank"><span class="simplesocialtxt"><?php echo $twitter_text;?> </span><span class="widget_counter"> <?php echo ( $display ==  $twitter_show_counter)? $twitter_follower: '';?> </span></a>
	<?php endif;
    if ( $display == $show_google_plus ):?>
    <a class="ssb_button simplesocial-gplus-follow" rel="noopener" href="https://plus.google.com/<?php echo $google_id;?>" target="_blank"><span class="simplesocialtxt"><?php echo  $google_text;?> </span><span class="widget_counter"> <?php echo ( $display == $google_show_counter )? $google_follower: '';?> </span></a>
	<?php endif;
	 if( $display == $show_youtube):
	?>
    <a class="ssb_button simplesocial-yt-follow" rel="noopener" href="https://youtube.com/<?php echo $youtube_type ?>/<?php echo $youtube_id ?>" target="_blank"><span class="simplesocialtxt"><?php echo  $youtube_text?> </span><span class="widget_counter"> <?php echo ( $display == $youtube_show_counter)?$youtube_subscriber:" ";?> </span></a>
	<?php endif;?>
    <?php if ( $display == $show_pinterest ):?>
    <a class="ssb_button simplesocial-pinterest-follow" rel="noopener" href="https://pinterest.com/<?php echo $pinterest_id;?>/" target="_blank"><span class="simplesocialtxt"><?php echo  $pinterest_text;?> </span><span class="widget_counter"> <?php echo ( $display == $pinterest_show_counter )? $pinterest_follower: '';?> </span></a>
	<?php endif;?>


</section>
<?php echo $after_widget;?>
