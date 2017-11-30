<style>
.wp-ssb-hidden{
  overflow: hidden;
}
.wp-ssb-popup-overlay .wp-ssb-internal-message{
  margin: 3px 0 3px 22px;
  display: none;
}
.wp-ssb-reason-input{
  margin: 3px 0 3px 22px;
  display: none;
}
.wp-ssb-reason-input input[type="text"]{
  width: 100%;
  display: block;
}
.wp-ssb-popup-overlay{
  background: rgba(0,0,0, .8);
  position: fixed;
  top:0;
  left: 0;
  height: 100%;
  width: 100%;
  z-index: 1000;
  overflow: auto;
  visibility: hidden;
  opacity: 0;
  transition: opacity 0.3s ease-in-out:
}
.wp-ssb-popup-overlay.wp-ssb-active{
  opacity: 1;
  visibility: visible;
}
.wp-ssb-serveypanel{
  width: 600px;
  background: #fff;
  margin: 65px auto 0;
}
.wp-ssb-popup-header{
  background: #f1f1f1;
  padding: 20px;
  border-bottom: 1px solid #ccc;
}
.wp-ssb-popup-header h2{
  margin: 0;
}
.wp-ssb-popup-body{
  padding: 10px 20px;
}
.wp-ssb-popup-footer{
  background: #f9f3f3;
  padding: 10px 20px;
  border-top: 1px solid #ccc;
}
.wp-ssb-popup-footer:after{
  content:"";
  display: table;
  clear: both;
}
.action-btns{
  float: right;
}
.wp-ssb-anonymous{
  display: none;
}
.attention, .error-message {
  color: red;
  font-weight: 600;
  display: none;
}
.wp-ssb-spinner{
  display: none;
}
.wp-ssb-spinner img{
  margin-top: 3px;
}

</style>

<div class="wp-ssb-popup-overlay">
  <div class="wp-ssb-serveypanel">
    <form action="#" method="post" id="wp-ssb-deactivate-form">
      <div class="wp-ssb-popup-header">
        <h2><?php _e( 'Quick feedback about Simple Social Buttons', 'wp-ssb' ); ?></h2>
      </div>
      <div class="wp-ssb-popup-body">
        <h3><?php _e( 'If you have a moment, please let us know why you are deactivating:', 'wp-ssb' ); ?></h3>
        <ul id="wp-ssb-reason-list">
          <li class="wp-ssb-reason" data-input-type="" data-input-placeholder="">
            <label>
              <span>
                <input type="radio" name="wp-ssb-selected-reason" value="1">
              </span>
              <span><?php _e( 'I only needed the plugin for a short period', 'wp-ssb' ); ?></span>
            </label>
            <div class="wp-ssb-internal-message"></div>
          </li>
          <li class="wp-ssb-reason has-input" data-input-type="textfield">
            <label>
              <span>
                <input type="radio" name="wp-ssb-selected-reason" value="2">
              </span>
              <span><?php _e( 'I found a better plugin', 'wp-ssb' ); ?></span>
            </label>
            <div class="wp-ssb-internal-message"></div>
            <div class="wp-ssb-reason-input"><span class="message error-message"><?php _e( 'Kindly tell us the name of plugin', 'wp-ssb' ); ?></span><input type="text" name="better_plugin" placeholder="What's the plugin's name?"></div>
          </li>
          <li class="wp-ssb-reason" data-input-type="" data-input-placeholder="">
            <label>
              <span>
                <input type="radio" name="wp-ssb-selected-reason" value="3">
              </span>
              <span><?php _e( 'The plugin broke my site', 'wp-ssb' ); ?></span>
            </label>
            <div class="wp-ssb-internal-message"></div>
          </li>
          <li class="wp-ssb-reason" data-input-type="" data-input-placeholder="">
            <label>
              <span>
                <input type="radio" name="wp-ssb-selected-reason" value="4">
              </span>
              <span><?php _e( 'The plugin suddenly stopped working', 'wp-ssb' ); ?></span>
            </label>
            <div class="wp-ssb-internal-message"></div>
          </li>
          <li class="wp-ssb-reason" data-input-type="" data-input-placeholder="">
            <label>
              <span>
                <input type="radio" name="wp-ssb-selected-reason" value="5">
              </span>
              <span><?php _e( 'I no longer need the plugin', 'wp-ssb' ); ?></span>
            </label>
            <div class="wp-ssb-internal-message"></div>
          </li>
          <li class="wp-ssb-reason" data-input-type="" data-input-placeholder="">
            <label>
              <span>
                <input type="radio" name="wp-ssb-selected-reason" value="6">
              </span>
              <span><?php _e( "It's a temporary deactivation. I'm just debugging an issue.", 'wp-ssb' ); ?></span>
            </label>
            <div class="wp-ssb-internal-message"></div>
          </li>
          <li class="wp-ssb-reason has-input" data-input-type="textfield" >
            <label>
              <span>
                <input type="radio" name="wp-ssb-selected-reason" value="7">
              </span>
              <span><?php _e( 'Other', 'wp-ssb' ); ?></span>
            </label>
            <div class="wp-ssb-internal-message"></div>
            <div class="wp-ssb-reason-input"><span class="message error-message "><?php _e( 'Kindly tell us the reason so we can improve.', 'wp-ssb' ); ?></span><input type="text" name="other_reason" placeholder="Would you like to share what's other reason ?"></div>
          </li>
        </ul>
      </div>
      <div class="wp-ssb-popup-footer">
        <label class="wp-ssb-anonymous"><input type="checkbox" /><?php _e( 'Anonymous feedback', 'wp-ssb' ); ?></label>
        <input type="button" class="button button-secondary button-skip wp-ssb-popup-skip-feedback" value="Skip &amp; Deactivate" >
        <div class="action-btns">
          <span class="wp-ssb-spinner"><img src="<?php echo admin_url( '/images/spinner.gif' ); ?>" alt=""></span>
          <input type="submit" class="button button-secondary button-deactivate wp-ssb-popup-allow-deactivate" value="Submit &amp; Deactivate" disabled="disabled">
          <a href="#" class="button button-primary wp-ssb-popup-button-close"><?php _e( 'Cancel', 'wp-ssb' ); ?></a>

        </div>
      </div>
    </form>
  </div>
</div>


<script>
(function( $ ) {

  $(function() {

    var pluginSlug = 'simple-social-buttons';
    // Code to fire when the DOM is ready.

    $(document).on('click', 'tr[data-slug="' + pluginSlug + '"] .deactivate', function(e){
      e.preventDefault();

      $('.wp-ssb-popup-overlay').addClass('wp-ssb-active');
      $('body').addClass('wp-ssb-hidden');
    });
    $(document).on('click', '.wp-ssb-popup-button-close', function () {
      close_popup();
    });
    $(document).on('click', ".wp-ssb-serveypanel,tr[data-slug='" + pluginSlug + "'] .deactivate",function(e){
      e.stopPropagation();
    });

    $(document).click(function(){
      close_popup();
    });
    $('.wp-ssb-reason label').on('click', function(){
      if($(this).find('input[type="radio"]').is(':checked')){
        //$('.wp-ssb-anonymous').show();
        $(this).next().next('.wp-ssb-reason-input').show().end().end().parent().siblings().find('.wp-ssb-reason-input').hide();
      }
    });
    $('input[type="radio"][name="wp-ssb-selected-reason"]').on('click', function(event) {
      $(".wp-ssb-popup-allow-deactivate").removeAttr('disabled');
    });
    $(document).on('submit', '#wp-ssb-deactivate-form', function(event) {
      event.preventDefault();

      var _reason =  $(this).find('input[type="radio"][name="wp-ssb-selected-reason"]:checked').val();
      var _reason_details = '';
      if ( _reason == 2 ) {
        _reason_details = $(this).find("input[type='text'][name='better_plugin']").val();
      } else if ( _reason == 7 ) {
        _reason_details = $(this).find("input[type='text'][name='other_reason']").val();
      }

      if ( ( _reason == 7 || _reason == 2 ) && _reason_details == '' ) {
        $('.message.error-message').show();
        return ;
      }

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action        : 'ssb_deactivate',
          reason        : _reason,
          reason_detail : _reason_details,
        },
        beforeSend: function(){
          $(".wp-ssb-spinner").show();
          $(".wp-ssb-popup-allow-deactivate").attr("disabled", "disabled");
        }
      })
      .done(function() {
        // $(".wp-ssb-spinner").hide();
        // $(".wp-ssb-popup-allow-deactivate").removeAttr("disabled");
        window.location.href =  $("tr[data-slug='"+ pluginSlug +"'] .deactivate a").attr('href');
      });

    });

    $('.wp-ssb-popup-skip-feedback').on('click', function(e){
      window.location.href =  $("tr[data-slug='"+ pluginSlug +"'] .deactivate a").attr('href');
    })

    function close_popup() {
      $('.wp-ssb-popup-overlay').removeClass('wp-ssb-active');
      $('#wp-ssb-deactivate-form').trigger("reset");
      $(".wp-ssb-popup-allow-deactivate").attr('disabled', 'disabled');
      $(".wp-ssb-reason-input").hide();
      $('body').removeClass('wp-ssb-hidden');
      $('.message.error-message').hide();
    }
  });

})( jQuery ); // This invokes the function above and allows us to use '$' in place of 'jQuery' in our code.
</script>
