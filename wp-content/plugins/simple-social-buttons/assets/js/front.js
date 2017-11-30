(function($) {

  /**
   * Copyright 2012, Digital Fusion
   * Licensed under the MIT license.
   * http://teamdf.com/jquery-plugins/license/
   *
   * @author Sam Sehnert
   * @desc A small plugin that checks whether elements are within
   *     the user visible viewport of a web browser.
   *     only accounts for vertical position, not horizontal.
   */

  $.fn.visible = function(partial) {

      var $t            = $(this),
          $w            = $(window),
          viewTop       = $w.scrollTop(),
          viewBottom    = viewTop + $w.height(),
          _top          = $t.offset().top,
          _bottom       = _top + $t.height(),
          compareTop    = partial === true ? _bottom : _top,
          compareBottom = partial === true ? _top : _bottom;

    return ((compareBottom <= viewBottom) && (compareTop >= viewTop));

  };

})(jQuery);
// IIFE - Immediately Invoked Function Expression
(function($, window, document) {

	// Listen for the jQuery ready event on the document
	$(function() {

		// The DOM is ready!
		if($('div[class*="simplesocialbuttons-float"]').length>0){
			$('body').addClass('body_has_simplesocialbuttons');
		}

	});

	$(window).load(function(){
	var allMods = $(".simplesocialbuttons_inline");

	  // Already visible modules
	  allMods.each(function(i, el) {
	    var el = $(el);
	    if (el.visible(true)) {
	      el.addClass('simplesocialbuttons-inline-in');
	    }
	  });

	  $(window).scroll(function(event) {

	    allMods.each(function(i, el) {
	      var el = $(el);
	      if (el.visible(true)) {
	        el.addClass('simplesocialbuttons-inline-in');
	      }
	    });

	  });
		// $('.simplesocialbuttons_inline').addClass('simplesocialbuttons-inline-in');
		var sidebarwidth = $('div[class*="simplesocialbuttons-float"]>a:first-child').outerWidth(true);
		$('div[class*="simplesocialbuttons-float"]').css('width', sidebarwidth + 'px');
		// var sidebaroffset = $(window).width() -  1100;
		// if($('.simplesocialbuttons-float-left-post').length>0){
		// 	$('.simplesocialbuttons-float-left-post').css('left', sidebaroffset/2+'px');
		//  	$('.simplesocialbuttons-float-left-post').removeClass('float-touched-sidebar');
		// }
		// if($('.simplesocialbuttons-float-right-post').length>0){
		// 	$('.simplesocialbuttons-float-right-post').css('right', sidebaroffset/2+'px');
		//  	$('.simplesocialbuttons-float-left-post').removeClass('float-touched-sidebar');
		//  	$('.simplesocialbuttons-float-right-post').removeClass('float-touched-sidebar');
		// }
		// if(sidebaroffset/2 <= 50){
		//  	$('.simplesocialbuttons-float-left-post').addClass('float-touched-sidebar');
		//  	$('.simplesocialbuttons-float-right-post').addClass('float-touched-sidebar');
		// }
	});
	// $(window).on('resize',function(){
	// 	var sidebaroffset = $(window).width() -  1100;
	// 	if($('.simplesocialbuttons-float-left-post').length>0){
	// 		$('.simplesocialbuttons-float-left-post').css('left', sidebaroffset/2+'px');
	// 	 	$('.simplesocialbuttons-float-left-post').removeClass('float-touched-sidebar');
	// 	}
	// 	if($('.simplesocialbuttons-float-right-post').length>0){
	// 		$('.simplesocialbuttons-float-right-post').css('right', sidebaroffset/2+'px');
	// 	 	$('.simplesocialbuttons-float-left-post').removeClass('float-touched-sidebar');
	// 	}
	// 	if(sidebaroffset/2 <= 50){
	// 	 	$('.simplesocialbuttons-float-left-post').addClass('float-touched-sidebar');
	// 	}
	// });


	// $(window).on('scroll', function(){
	// 	if($('.simplesocialbuttons-float-left-post').length>0){
	// 		if($(window).scrollTop() >= 500){
	// 			$('.simplesocialbuttons-float-left-post').addClass('float-in')
	// 		}else{
	// 			$('.simplesocialbuttons-float-left-post').removeClass('float-in')
	// 		}
	// 	}
	// 	if($('.simplesocialbuttons-float-right-post').length>0){
	// 		if($(window).scrollTop() >= 500){
	// 			$('.simplesocialbuttons-float-right-post').addClass('float-in')
	// 		}else{
	// 			$('.simplesocialbuttons-float-right-post').removeClass('float-in')
	// 		}
	// 	}
	// })

}(window.jQuery, window, document));
