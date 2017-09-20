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
      },
    });

    $("#ssb_inactive_icons").sortable({
      connectWith: "#ssb_active_icons",
      cursor: 'move'
    });


  });

  // The rest of the code goes here!

}(window.jQuery, window, document));
