// IIFE - Immediately Invoked Function Expression
(function($, window, document) {


  // Listen for the jQuery ready event on the document
  $(function() {

    // The DOM is ready!
    $("#ssb_active_icons").sortable({
      connectWith: "#ssb_inactive_icons",
      cursor: 'move',
      update: function(event, ui) {
        var order = $("#ssb_active_icons").sortable("toArray", {attribute: 'data-id' } );
        $('#ssb_icons_order').val( order.join(','));
        $('#ssb_networks\\[icon_selection\\]').val( order.join(','));
      },
    });

    $("#ssb_inactive_icons").sortable({
      connectWith: "#ssb_active_icons",
      cursor: 'move'
    });

    $('.ssb_settings_color_picker').wpColorPicker();

    // sidebar extra space.
    if (!$('#ssb_sidebar\\[icon_space\\]').is(':checked')) {
      $('.container-ssb_sidebar\\[icon_space_value\\]').css('display', 'none');
    }
    $('#ssb_sidebar\\[icon_space\\]').on('change', function(event) {
      if($(this).is(':checked')){
        $('.container-ssb_sidebar\\[icon_space_value\\]').css('display', 'block');
      }else{
        $('.container-ssb_sidebar\\[icon_space_value\\]').css('display', 'none');
      }
    });

    if (!$('#ssb_inline\\[icon_space\\]').is(':checked')) {
      $('.container-ssb_inline\\[icon_space_value\\]').css('display', 'none');
    }
    $('#ssb_inline\\[icon_space\\]').on('change', function(event) {
      if($(this).is(':checked')){
        $('.container-ssb_inline\\[icon_space_value\\]').css('display', 'block');
      }else{
        $('.container-ssb_inline\\[icon_space_value\\]').css('display', 'none');
      }
    });

    if (!$('#ssb_media\\[icon_space\\]').is(':checked')) {
      $('.container-ssb_media\\[icon_space_value\\]').css('display', 'none');
    }
    $('#ssb_media\\[icon_space\\]').on('change', function(event) {
      if($(this).is(':checked')){
        $('.container-ssb_media\\[icon_space_value\\]').css('display', 'block');
      }else{
        $('.container-ssb_media\\[icon_space_value\\]').css('display', 'none');
      }
    });

    if (!$('#ssb_flyin\\[icon_space\\]').is(':checked')) {
      $('.container-ssb_flyin\\[icon_space_value\\]').css('display', 'none');
    }
    $('#ssb_flyin\\[icon_space\\]').on('change', function(event) {
      if($(this).is(':checked')){
        $('.container-ssb_flyin\\[icon_space_value\\]').css('display', 'block');
      }else{
        $('.container-ssb_flyin\\[icon_space_value\\]').css('display', 'none');
      }
    });

    if (!$('#ssb_popup\\[icon_space\\]').is(':checked')) {
      $('.container-ssb_popup\\[icon_space_value\\]').css('display', 'none');
    }
    $('#ssb_popup\\[icon_space\\]').on('change', function(event) {
      if($(this).is(':checked')){
        $('.container-ssb_popup\\[icon_space_value\\]').css('display', 'block');
      }else{
        $('.container-ssb_popup\\[icon_space_value\\]').css('display', 'none');
      }
    });

    if (!$('#ssb_popup\\[trigger_after_scrolling\\]').is(':checked')) {
      $('.container-ssb_popup\\[trigger_after_scrolling_value\\]').css('display', 'none');
    }
    $('#ssb_popup\\[trigger_after_scrolling\\]').on('change', function(event) {
      if($(this).is(':checked')){
        $('.container-ssb_popup\\[trigger_after_scrolling_value\\]').css('display', 'block');
      }else{
        $('.container-ssb_popup\\[trigger_after_scrolling_value\\]').css('display', 'none');
      }
    });


    $( '.simple-social-buttons-log-file' ).on( 'click', function( event ) {

      event.preventDefault();

      $.ajax({

        url: ajaxurl,
        type: 'POST',
        data: {
          action : 'ssb_help',
        },
        beforeSend: function() {
          $('.ssb-log-file-sniper').show();
        },
        success: function( response ) {

          $('.ssb-log-file-sniper').hide();
          $('.ssb-log-file-text').show();

          if ( ! window.navigator.msSaveOrOpenBlob ) { // If msSaveOrOpenBlob() is supported, then so is msSaveBlob().
            $('<a />', {
              "download" : 'simple-social-buttons-log.txt',
              "href" : 'data:text/plain;charset=utf-8,' + encodeURIComponent( response ),
            }).appendTo( "body" )
            .click(function() {
              $(this).remove()
            })[0].click()
          } else {
            var blobObject = new Blob( [response] );
            window.navigator.msSaveBlob( blobObject, 'simple-social-buttons-log.txt' );
          }

          setTimeout(function() {
            $(".ssb-log-file-text").fadeOut()
          }, 3000 );
        }
      });

    });


    //widget  js
    $(document).on('click', '.get_fb_token', function () {
      var fb_content = $(this).parent().parent().parent().parent();
      console.log(fb_content);
      var client_id = $(fb_content).find('.fb_app_id').val().trim();
      var secret_key = $(fb_content).find('.fb_secret_key').val().trim();

      if ( 0 == client_id.length ) {
        $(fb_content).find('.fb-error').text('Fb App id :  required');
        return false;
      }
      if ( 0 == secret_key.length ) {
        $(fb_content).find('.fb-error').text('Fb Security key  :  required');
        return false;
      }

      $(fb_content).find('#token_loader').show();
      $.ajax({
        url: 'https://graph.facebook.com/oauth/access_token',
        data: {
          client_id: client_id,
          client_secret: secret_key,
          grant_type: 'client_credentials'
        },
        dataType: 'json',

      }).done(function (data, textStatus, jqXHR) {
        //facebook_access_token.val( data.replace( 'access_token=' , '' ) );
        $(fb_content).find('.fb_access_token').val(data.access_token);
        $(fb_content).find('.fb-error').text('');
      }).fail(function (jqXHR, textStatus, errorThrown) {
        $(fb_content).find('.fb-error').text('Incorrect data, please check each field.' + '\n\n' + 'Info Message: ' + jqXHR.responseJSON.error.message);
      }).always(function (jqXHR, textStatus, errorThrown) {
        $(fb_content).find('#token_loader').hide();
      });

    });

    $(document).on('click', '.fb_count_check', function () {
      var fb_content = $(this).parent().parent();

      if ($(this).is(':checked')) {
        $(fb_content).find('.fb_api_key').css('display', 'block');
        // $('.fb_api_key').css('display', 'block');
      } else {
        $(fb_content).find('.fb_api_key').css('display', 'none');
      }
    });
    $(document).on('click', '.show_fb_check', function () {
      var widget_content = $(this).parent().parent();

      if ($(this).is(':checked')) {
        $(widget_content).find('.show_fb').css('display', 'block');
      } else {
        $(widget_content).find('.show_fb').css('display', 'none');
      }

    });

    $(document).on('click', '.show_twitter_check', function () {
      var widget_content = $(this).parent().parent();
      if ($(this).is(':checked')) {
        $(widget_content).find('.show_twitter').css('display', 'block');
      } else {
        $(widget_content).find('.show_twitter').css('display', 'none');
      }

    });
    $(document).on('click', '.twitter_count_check', function () {

      var twitter_content = $(this).parent().parent();
      if ($(this).is(':checked')) {
        $(twitter_content).find('.twitter_api_key').css('display', 'block');

        // $('.twitter_api_key').css('display', 'block');
      } else {
        $(twitter_content).find('.twitter_api_key').css('display', 'none');
      }
    });

    $(document).on('click', '.show_google_check', function () {
      var widget_content = $(this).parent().parent();
      if ($(this).is(':checked')) {
        // $('.show_google_plus').css('display', 'block');
        $(widget_content).find('.show_google_plus').css('display', 'block');
      } else {
        $(widget_content).find('.show_google_plus').css('display', 'none');
      }

    });


    $(document).on('click', '.show_youtube_check', function () {
      var widget_content = $(this).parent().parent();
      if ($(this).is(':checked')) {

        $(widget_content).find('.show_youtube').css('display', 'block');
      } else {
        $(widget_content).find('.show_youtube').css('display', 'none');
      }

    });

      $(document).on('click', '.show_pinterest_check', function () {
          var widget_content = $(this).parent().parent();
          if ($(this).is(':checked')) {
      
              $(widget_content).find('.show_pinterest').css('display', 'block');
          } else {
              $(widget_content).find('.show_pinterest').css('display', 'none');
          }

      });

      $(document).on('click', '.pinterest_count_check', function () {

          var pinterest_content = $(this).parent().parent();
          if ($(this).is(':checked')) {
              $(pinterest_content).find('.pinterest_api_key').css('display', 'block');
          } else {
              $(pinterest_content).find('.pinterest_api_key').css('display', 'none');
          }
      });

      $(document).on('click', '.show_instagram_check', function () {
        var widget_content = $(this).parent().parent();
        if ($(this).is(':checked')) {
  
          $(widget_content).find('.show_instagram').css('display', 'block');
        } else {
          $(widget_content).find('.show_instagram').css('display', 'none');
        }

      });

      $(document).on('click', '.instagram_count_check', function () {

        var instagram_content = $(this).parent().parent();
        if ($(this).is(':checked')) {
          $(instagram_content).find('.instagram_api_key').css('display', 'block');
        } else {
          $(instagram_content).find('.instagram_api_key').css('display', 'none');
        }
      });

      $(document).on('click', '.show_whatsapp_check', function () {
          var widget_content = $(this).parent().parent();
          if ($(this).is(':checked')) {
              $(widget_content).find('.show_whatsapp').css('display', 'block');
          } else {
              $(widget_content).find('.show_whatsapp').css('display', 'none');
          }

      });

      //end widget js;


  });

  // The rest of the code goes here!

}(window.jQuery, window, document));
