<div class="wrap">

<style type="text/css">
div.inside ul li {
  line-height: 16px;
  list-style-type: square;
  margin-left: 15px;
}
.ssb_right_sidebar{

  width: 255px;
  float: right;
  min-width: inherit;
  box-sizing: border-box;
}
.ssb_right_sidebar #poststuff {
  width: 255px;
  float: right;
  min-width: inherit;
  box-sizing: border-box;
  max-width: 100%;
  min-width: 100%;
}
.ssb_right_sidebar  .ssb_social_links_wrapper{
  padding: 10px;
  min-width: 100%;
  box-sizing: border-box;
}
.ssb_right_sidebar  .ssb_social_links li a {
  width: 100%;
  display: block;
  position: relative;
}
.ssb_right_sidebar  .ssb_social_links .dashicons {
  position: absolute;
  right: 10px;
  margin-top: 3px;
}
.ssb_social_links_wrapper .postbox{
  min-width: 100%;
  border:0;
}
.ssb_right_sidebar  .ssb_social_links li .twitter .dashicons {
  color: #45b0e3;
}
.ssb_right_sidebar  .ssb_social_links li .facebook .dashicons {
  color: #3b5998;
}
.ssb_right_sidebar  .ssb_social_links li .wordpress .dashicons {
  color: #21759b;
}
.ssb_right_sidebar .plugins_lists li {
  padding-bottom: 12px;
  line-height: 1.4;
}
.ssb_right_sidebar #poststuff .stuffbox>h3,.ssb_right_sidebar  #poststuff h2,.ssb_right_sidebar  #poststuff h3.hndle{
  border-bottom: 1px solid #ccc;
  font-size: 1.3em;
  padding: 10px;
}
.ssb_settings_container {
  float: left;
  width: calc(100% - 275px);
}
.ssb_settings_container .postbox .inside,.ssb_settings_container  .stuffbox .inside{
  padding: 12px;
  box-sizing: border-box;
}
#ssb_subscribe_btn {
  display: block;
  margin: 20px auto 0;
}
.ssb_settings_container .postbox .inside h3{
  margin: 0 -12px 10px;
  padding: 0 12px 15px;
  border-bottom: 1px solid #ccc;
}
.ssb_settings_container #poststuff{
  min-width: 100%;
}
.ssb_settings_container #poststuff h2{
  border-bottom: 1px solid #ccc;
  font-size: 1.3em;
}
.ssb_settings_container #poststuff .ssb_settings_box{
  border:1px solid #ccc;
  overflow: hidden;
  display: table;
  background: #f6f6f6;
}
.ssb_settings_container #poststuff .ssb_settings_box + .ssb_settings_box{
  border-top: 0;
}
.ssb_settings_container #poststuff .ssb_settings_box h2{
  width: 180px;
  float: left;
  text-align: center;
  background: #f6f6f6;
  border:0;
  display: table-cell;
  padding: 10px;
}
.ssb_settings_container #poststuff .ssb_settings_box ul{
  overflow: hidden;
  margin:0;
  padding: 10px 0;
  list-style: none;
  font-size: 0;
  border-left: 1px solid #ccc;
  display: table-cell;
  vertical-align: top;
  width: 100%;
  background: #fff;
}
.ssb_settings_container #poststuff .ssb_settings_box ul li{
  display: inline-block;
  margin-bottom: 0;
  list-style: none;
  font-size: 16px;
}
.ssb_settings_container #poststuff .ssb_settings_box ul li:hover{
  cursor: pointer;
  
}
.ssb-top-bar{
  background: #f3fbff;
  overflow: hidden;
  padding: 12px 20px;

}
.ssb-top-bar img  {
  float: left;
  margin-right: 20px;
  width: 100px;
}
.ssb-top-bar-content{
  overflow: hidden;
}
.ssb-top-bar-content h2{
  margin: 10px 0;
}
@media only screen and (max-width: 850px){

  .ssb_settings_container{
    width:100%;
  }
  .ssb_right_sidebar{
    float: left;
  }
  .ssb_settings_container #poststuff .ssb_settings_box h2{
    width: 120px;
  }
}
</style>

<div class="ssb-top-bar">
  <a href="https://wpbrigade.com/"><img src="<?php echo plugins_url( 'assets/images/ssb_icon.png', __FILE__ ) ?>" alt="Simple Social Buttons"></a>
  <div class="ssb-top-bar-content">
    <h2>Simple Social Buttons - <?php _e('Settings'); ?>:</h2>
    <p><?php _e('<strong>Simple Social Buttons</strong> by <strong><a href="https://wpbrigade.com/">WPBrigade</a></strong>. This plugin adds a social media buttons, such as: <strong>Google +1</strong>, <strong>Facebook Like it</strong>, <strong>Facebook share</strong>, <strong>Twitter share</strong>, <strong>LinkedIn share</strong> and <strong>Pinterest</strong>. The most flexible social buttons plugin ever.', 'simplesocialbuttons'); ?></p>
  </div>

</div>

<?php

$all_icons = array(
    'fblike'     => 0,
    'twitter'    => 0,
    'googleplus' => 0,
    'pinterest'  => 0,
    'fbshare'    => 0,
    'linkedin'   => 0
);

if(strtolower(@$_POST['hiddenconfirm']) == 'y') {

	/**
	 * Compile settings array
	 * @see http://codex.wordpress.org/Function_Reference/wp_parse_args
	 */

  //  image_oder return string thats why we explode it.
  //  add empty array to start form 1 index.
  update_option( 'ssb_icons_order', sanitize_text_field( wp_unslash( $_POST['ssb_icons_order'] ) ) );
  $ssb_icons_order = array_merge( array(0),  explode( ',', sanitize_text_field( wp_unslash( $_POST['ssb_icons_order'] ) ) ) );

	$updateSettings = array(
		'googleplus'    => isset( $_POST['ssb_googleplus'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_googleplus'] ) ) : '',
		'fblike'        => isset( $_POST['ssb_fblike'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_fblike'] ) ) : '',
		'twitter'       => isset( $_POST['ssb_twitter'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_twitter'] ) ) : '',
		'pinterest'     => isset( $_POST['ssb_pinterest'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_pinterest'] ) ) : '',

		'beforepost'    => isset( $_POST['ssb_beforepost'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_beforepost'] ) ) : '',
		'afterpost'     => isset( $_POST['ssb_afterpost'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_afterpost'] ) ) : '',
		'beforepage'    => isset( $_POST['ssb_beforepage'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_beforepage'] ) ) : '',
		'afterpage'     => isset( $_POST['ssb_afterpage'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_afterpage'] ) ) : '',
		'beforearchive' => isset( $_POST['ssb_beforearchive'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_beforearchive'] ) ) : '',
		'afterarchive'  => isset( $_POST['ssb_afterarchive'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_afterarchive'] ) ) : '',

		'showfront'     => isset( $_POST['ssb_showfront'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_showfront'] ) ) : '',
		'showcategory'  => isset( $_POST['ssb_showcategory'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_showcategory'] ) ) : '',
		'showarchive'   => isset( $_POST['ssb_showarchive'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_showarchive'] ) ) : '',
		'showtag'       => isset( $_POST['ssb_showtag'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_showtag'] ) ) : '',

		'override_css'  => isset( $_POST['ssb_override_css'] ) ? sanitize_text_field( wp_unslash( $_POST['ssb_override_css'] ) ) : '',

		'twitterusername' => isset( $_POST['ssb_twitterusername'] ) ? str_replace(array("@", " "), "", sanitize_text_field( wp_unslash( $_POST['ssb_twitterusername'] ) ) ) : '',
	);

	$this->update_settings( array_merge(  $updateSettings, array_merge( $all_icons, array_flip( $ssb_icons_order )  ) )  );

}

/**
 * HACK: Use one big array instead of a bunchload of single options
 * @author Fabian Wolf
 * @link http://usability-idealist.de/
 * @since 1.2.1
 */

// get settings from database
$settings = $this->get_settings();

extract( $settings, EXTR_PREFIX_ALL, 'ssb' );


?>


<div class="postbox-container ssb_settings_container">
   <div id="poststuff">
      <form name="ssb_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

      <div class="postbox">
        <h2>Select buttons</h2>
         <div class="inside">
           <p>Drag & Drop to activate and order your share buttons:</p>
           <div class="ssb_settings_box">


           <h2>Active</h2>
           <ul id="ssb_active_icons" class="items" style="min-height:35px">
             <!-- <li id="facebook" class="list">FaceBook</li>
             <li id="twitter" class="list">Twiiter</li> -->

             <?php
             $ssb_icons_order = array();
             foreach ($this->arrKnownButtons as $button_name) {
                 $ssb_icons_order[$button_name] = isset( $settings[$button_name] ) ? $settings[$button_name] : '' ;
             }
             asort( $ssb_icons_order );
              ?>
             <?php foreach ($ssb_icons_order as $key => $value): ?>
               <?php if ( $value != 0): ?>
                 <li data-id="<?php echo $key ?>" class="list"><img src="<?php echo plugins_url( 'assets/images/'.$key.'.jpg', __FILE__ ) ?>" /></li>
               <?php endif; ?>
             <?php endforeach; ?>


           </ul>
           </div>
           <div class="ssb_settings_box">
           <h2>InActive</h2>

           <ul id="ssb_inactive_icons" class="items" style="min-height:35px">
             <?php foreach ( $ssb_icons_order as $key => $value): ?>
               <?php if ( $value == 0): ?>
                 <li data-id="<?php echo $key ?>" class="list" ><img src="<?php echo plugins_url( 'assets/images/'.$key.'.jpg', __FILE__ ) ?>" /></li>
               <?php endif; ?>
             <?php endforeach; ?>
           </ul>
         </div>

           <input type="hidden" id="ssb_icons_order" name="ssb_icons_order" value="<?php echo get_option( 'ssb_icons_order', join( ',', array_flip( array_filter( $ssb_icons_order ) ) ) ) ?>" />


			<p><label for="ssb_override_css"><input type="checkbox" name="ssb_override_css" id="ssb_override_css" value="1" <?php if(!empty($ssb_override_css)) { echo 'checked="checked"'; } ?>/> <?php _e('Disable plugin CSS (only advanced users)', 'simplesocialbuttons'); ?></label></p>
         </div>
      </div>

      <div class="postbox">
         <div class="inside">
           <h3><?php _e('Single posts - display settings', 'simplesocialbuttons'); ?></h3>
            <h4><?php _e('Place buttons on single post:', 'simplesocialbuttons'); ?></h4>
            <p><input type="checkbox" name="ssb_beforepost" id="ssb_beforepost" value="1" <?php if(!empty($ssb_beforepost)) { ?>checked="checked"<?php } ?> /> <label for="ssb_beforepost"><?php _e('Before the content', 'simplesocialbuttons'); ?></label></p>
            <p><input type="checkbox" name="ssb_afterpost" id="ssb_afterpost" value="1" <?php if(!empty($ssb_afterpost)) { ?>checked="checked"<?php } ?> /> <label for="ssb_afterpost"><?php _e('After the content', 'simplesocialbuttons'); ?></label></p>
         </div>
      </div>

      <div class="postbox">
         <div class="inside">
           <h3><?php _e('Single pages - display settings', 'simplesocialbuttons'); ?></h3>
            <h4><?php _e('Place buttons on single pages:', 'simplesocialbuttons'); ?></h4>
            <p><input type="checkbox" name="ssb_beforepage" id="ssb_beforepage" value="1" <?php if(!empty($ssb_beforepage)) { ?>checked="checked"<?php } ?> /> <label for="ssb_beforepage"><?php _e('Before the page content', 'simplesocialbuttons'); ?></label></p>
            <p><input type="checkbox" name="ssb_afterpage" id="ssb_afterpage" value="1" <?php if(!empty($ssb_afterpage)) { ?>checked="checked"<?php } ?> /> <label for="ssb_afterpage"><?php _e('After the page content', 'simplesocialbuttons'); ?></label></p>
         </div>
      </div>

      <div class="postbox">
         <div class="inside">
           <h3><?php _e('Archives - display settings', 'simplesocialbuttons'); ?></h3>
            <h4><?php _e('Select additional places to display buttons:', 'simplesocialbuttons'); ?></h4>
            <p><input type="checkbox" name="ssb_showfront" id="ssb_showfront" value="1" <?php if(!empty($ssb_showfront)) { ?>checked="checked"<?php } ?> /> <label for="ssb_showfront"><?php _e('Show at frontpage', 'simplesocialbuttons'); ?></label></p>
            <p><input type="checkbox" name="ssb_showcategory" id="ssb_showcategory" value="1" <?php if(!empty($ssb_showcategory)) { ?>checked="checked"<?php } ?> /> <label for="ssb_showcategory"><?php _e('Show at category pages', 'simplesocialbuttons'); ?></label></p>
            <p><input type="checkbox" name="ssb_showarchive" id="ssb_showarchive" value="1" <?php if(!empty($ssb_showarchive)) { ?>checked="checked"<?php } ?> /> <label for="ssb_showarchive"><?php _e('Show at archive pages', 'simplesocialbuttons'); ?></label></p>
            <p><input type="checkbox" name="ssb_showtag" id="ssb_showtag" value="1" <?php if(!empty($ssb_showtag)) { ?>checked="checked"<?php } ?> /> <label for="ssb_showtag"><?php _e('Show at tag pages', 'simplesocialbuttons'); ?></label></p>

            <h4><?php _e('Place buttons on archives:', 'simplesocialbuttons'); ?></h4>
            <p><input type="checkbox" name="ssb_beforearchive" id="ssb_beforearchive" value="1" <?php if(!empty($ssb_beforearchive)) { ?>checked="checked"<?php } ?> /> <label for="ssb_beforearchive"><?php _e('Before the content', 'simplesocialbuttons'); ?></label></p>
            <p><input type="checkbox" name="ssb_afterarchive" id="ssb_afterarchive" value="1" <?php if(!empty($ssb_afterarchive)) { ?>checked="checked"<?php } ?> /> <label for="ssb_afterarchive"><?php _e('After the content', 'simplesocialbuttons'); ?></label></p>
         </div>
      </div>

      <div class="postbox">
         <div class="inside">
           <h3><?php _e('Additional features'); ?></h3>
            <p><label for="ssb_twitterusername"><?php _e('Twitter @username', 'simplesocialbuttons'); ?>: <input type="text" name="ssb_twitterusername" id="ssb_twitterusername" value="<?php echo (isset($ssb_twitterusername)) ? $ssb_twitterusername : "";?>" /></label></p>
         </div>
      </div>

      <div class="submit">
         <input type="hidden" name="hiddenconfirm" value="Y" />
         <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
      </div>

   </form>
</div>
</div>

<div class="postbox-container ssb_right_sidebar">
   <div id="poststuff">
      <div class="postbox ssb_social_links_wrapper">
        <div class="sidebar postbox">
          <h2>Spread the Word</h2>
          <ul class="ssb_social_links">
            <li>
              <a href="http://twitter.com/share?text=Check out this (FREE) Amazing Social Share Plugin for WordPress&amp;url=https://wordpress.org/plugins/simple-social-buttons/" data-count="none" class="button twitter" target="_blank" title="Post to Twitter Now">Share on Twitter<span class="dashicons dashicons-twitter"></span></a>
            </li>

            <li>
              <a href="https://www.facebook.com/sharer/sharer.php?u=https://wordpress.org/plugins/simple-social-buttons/" class="button facebook" target="_blank" title="Check out this (FREE) Amazing Social Share Plugin for WordPress">Share on Facebook<span class="dashicons dashicons-facebook"></span>
              </a>
            </li>

            <li>
              <a href="https://wordpress.org/plugins/simple-social-buttons/?filter=5" class="button wordpress" target="_blank" title="Rate on WordPress.org">Rate on WordPress.org<span class="dashicons dashicons-wordpress"></span>
              </a>
            </li>
          </ul>
        </div>
      </div>

      <div class="postbox ssb_social_links_wrapper">
        <div class="sidebar postbox">

          <h2>Subscribe Newsletter</h2>
          <ul>
            <li>
              <label for="">Email</label>
              <input type="email" name="subscriber_mail" value="<?php echo get_option( 'admin_email' ) ?>" id="ssb_subscribe_mail">
              <p class="ssb_subscribe_warning"></p>
            </li>
            <li>
              <label for="">Name</label>
              <input type="text" name="subscriber_name" id="ssb_subscribe_name" value="<?php echo wp_get_current_user()->display_name ?>">
            </li>
            <li>
              <input type="submit" value="Subscribe Now" class="button button-primary button-big" id="ssb_subscribe_btn">
              <img src="<?php echo admin_url( 'images/spinner.gif' ) ?>" class="ssb_subscribe_loader" style="display:none">
            </li>
            <li>
              <p class="ssb_return_message"></p>
            </li>
          </ul>
        </div>
      </div>

      <div class="postbox ssb_social_links_wrapper">
        <div class="sidebar postbox">
          <h2>Recommended Plugins</h2>
          <!-- <p>Following are the plugins highly recommend by Team WPBrigade.com</p> -->
          <ul class="plugins_lists">
            <li>
              <a href="https://wpbrigade.com/wordpress/plugins/loginpress-pro/?utm_source=ssb-lite&amp;utm_medium=sidebar&amp;utm_campaign=pro-upgrade" target="_blank" title="Post to Twitter Now">LoginPress - Login Customizer</a>
            </li>

            <li>
              <a href="https://analytify.io/ref/73/?utm_source=ssb-lite&amp;utm_medium=sidebar&amp;utm_campaign=pro-upgrade" target="_blank" title="Share with your facebook friends about this awesome plugin.">Google Analytics by Analytify
              </a>
            </li>

            <li>
              <a href="https://wpbrigade.com/wordpress/plugins/related-posts/?utm_source=ssb-lite&amp;utm_medium=sidebar&amp;utm_campaign=pro-upgrade" target="_blank" title="Releated Posts Thumbnails">Releated Posts Thumbnails</a>
            </li>

            <li>
              <a href="http://wpbrigade.com/recommend/maintenance-mode" target="_blank" title="Under Construction &amp; Maintenance mode">Under Construction &amp; Maintenance mode
              </a>
            </li>
          </ul>
        </div>
      </div>

   </div>
</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
  $('#ssb_subscribe_btn').on('click', function(event) {
    event.preventDefault();
    var subscriber_mail = $('#ssb_subscribe_mail').val();
    var name = $('#ssb_subscribe_name').val();
    if (!subscriber_mail) {
      $('.ssb_subscribe_warning').html('Please Enter Email');
      return;
    }
    $.ajax({
      url: 'https://wpbrigade.com/wp-json/wpbrigade/v1/subsribe-to-mailchimp',
      type: 'POST',
      data: {
        subscriber_mail : subscriber_mail,
        name : name,
        plugin_name : 'ssb'
      },
      beforeSend : function() {
        $('.ssb_subscribe_loader').show();
        $('#ssb_subscribe_btn').attr('disabled', 'disabled');
      }
    })
    .done(function(res) {
      $('.ssb_return_message').html(res);
      $('.ssb_subscribe_loader').hide();
    });
  });
});
</script>
