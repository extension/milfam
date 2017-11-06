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
		$('.simplesocialbuttons_inline').addClass('simplesocialbuttons-inline-in');
		var sidebarwidth = $('div[class*="simplesocialbuttons-float"]>a:first-child').outerWidth(true);
		$('div[class*="simplesocialbuttons-float"]').css('width', sidebarwidth + 'px');
	});

}(window.jQuery, window, document));
