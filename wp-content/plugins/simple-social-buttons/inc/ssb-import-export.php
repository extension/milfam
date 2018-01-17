<?php
/**
* SSB Import Export Page Content.
*
* @since 2.0.4
*/
?>
<div class="ssb-import-export-page">
  <h2><?php esc_html_e( 'Import/Export Simple Social Share Buttons Settings', 'simple-social-buttons' ); ?></h2>
  <div class=""><?php esc_html_e( "Import/Export your Social share button Settings for/from other sites.", 'simple-social-buttons' ); ?></div>
  <table class="form-table">
    <tbody>
      <tr class="import_setting">
        <th scope="row">
          <label for="ssb_press_import"><?php esc_html_e( 'Import Settings:', 'simple-social-buttons' ); ?></label>
        </th>
        <td>
          <input type="file" name="ssb_press_import" id="ssb_press_import">
          <input type="button" class="button ssb-import" value="<?php esc_html_e( 'Import', 'simple-social-buttons' ); ?>" disabled="disabled">
          <span class="import-sniper">
            <img src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>">
          </span>
          <span class="import-text"><?php esc_html_e( 'Simple Social Buttons Share Settings Imported Successfully.', 'simple-social-buttons' ); ?></span>
          <span class="wrong-import"></span>
          <p class="description"><?php esc_html_e( 'Select a file and click on Import to start processing.', 'simple-social-buttons' ); ?></p>
        </td>
      </tr>
      <tr class="export_setting">
        <th scope="row">
          <label for="ssb_configure[export_setting]"><?php esc_html_e( 'Export Settings:', 'simple-social-buttons' ); ?></label>
        </th>
        <td>
          <input type="button" class="button ssb-export" value="<?php esc_html_e( 'Export', 'simple-social-buttons' ); ?>">
          <span class="export-sniper">
            <img src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>">
          </span>
          <span class="export-text"><?php esc_html_e( 'Simple Social Button Settings Exported Successfully!', 'simple-social-buttons' ); ?></span>
          <p class="description"><?php esc_html_e( 'Export Simple Social Button Settings.', 'simple-social-buttons' ) ?></p>
        </td>
      </tr>
    </tbody>
  </table>
</div>


<script>
(function($) {
  'use strict';
  $(".import-sniper").hide();
  $(".import-text").hide();
  $(".export-sniper").hide();
  $(".export-text").hide();
  // Remove Disabled attribute from Import Button.
  $( '#ssb_press_import' ).on( 'change', function( event ) {

    event.preventDefault();

    var ssbFileImp = $( '#ssb_press_import' ).val();
    var ssbpressFileExt = ssbFileImp.substr( ssbFileImp.lastIndexOf('.') + 1 );

    $( '.ssb-import' ).attr( "disabled", "disabled" );

    if ( 'json' == ssbpressFileExt ) {
      $(".import_setting .wrong-import").html("");
      $( '.ssb-import' ).removeAttr( "disabled" );
    } else {
      $(".import_setting .wrong-import").html("Invalid File.");
    }
  });
  $('.ssb-export').on('click',  function(event) {

    event.preventDefault();

    var dateObj = new Date();
    var month   = dateObj.getUTCMonth() + 1; //months from 1-12
    var day     = dateObj.getUTCDate();
    var year    = dateObj.getUTCFullYear();
    var newdate = year + "-" + month + "-" + day;

    $.ajax({

      url: ajaxurl,
      type: 'POST',
      data: {
        action : 'ssb_export',
      },
      beforeSend: function() {
        $(".export_setting .export-sniper").show();
      },
      success: function( response ) {

        $(".export_setting .export-sniper").hide();
        $(".export_setting .export-text").show();

        if ( ! window.navigator.msSaveOrOpenBlob ) { // If msSaveOrOpenBlob() is supported, then so is msSaveBlob().
          $("<a />", {
            "download" : "ssb-export-"+newdate+".json",
            "href" : "data:application/json;charset=utf-8," + encodeURIComponent( response ),
          }).appendTo( "body" )
          .click(function() {
            $(this).remove()
          })[0].click()
        } else {
          var blobObject = new Blob( [response] );
          window.navigator.msSaveBlob( blobObject, "ssb-export-"+newdate+".json" );
        }

        setTimeout(function() {
          $(".export_setting .export-text").fadeOut()
        }, 3000 );
      }
    });
  });

  $('.ssb-import').on( 'click', function(event) {
    event.preventDefault();

    var file    = $('#ssb_press_import');
    var fileObj = new FormData();
    var content = file[0].files[0];

    fileObj.append( 'file', content );
    fileObj.append( 'action', 'ssb_import' );

    $.ajax({

      processData: false,
      contentType: false,
      url: ajaxurl,
      type: 'POST',
      data: fileObj, // file and action append into variable fileObj.
      beforeSend: function() {
        $(".import_setting .import-sniper").show();
        $(".import_setting .wrong-import").html("");
        $( '.ssb-import' ).attr( "disabled", "disabled" );
      },
      success: function(response) {

        $(".import_setting .import-sniper").hide();
        // $(".import_setting .import-text").fadeIn();
        if ( 'error' == response ) {
          $(".import_setting .wrong-import").html("JSON File is not Valid.");
        } else {
          $(".import_setting .import-text").show();
          setTimeout( function() {
            $(".import_setting .import-text").fadeOut();
            // $(".import_setting .wrong-import").html("");
            file.val('');
          }, 3000 );
        }

      }
    }); //!ajax.
  });
})(jQuery); // This invokes the function above and allows us to use '$' in place of 'jQuery' in our code.


</script>
