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

  });

  // The rest of the code goes here!

}(window.jQuery, window, document));
