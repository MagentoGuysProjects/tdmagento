jQuery(window).load(function(){
	function viewport() {
	    var e = window, a = 'inner';
	    if (!('innerWidth' in window )) {
	        a = 'client';
	        e = document.documentElement || document.body;
	    }
	    return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
	}
	
	if( jQuery('.stickynote-top').length ){
		if(jQuery('body.boxed-layout').length){
			jQuery('.stickynote-top').css({'width': jQuery('#sns_wrapper').outerWidth(), 'left':jQuery('#sns_wrapper').offset().left});
		}
		if(!jQuery('.stickynote-top').hasClass('loaded')) jQuery('.stickynote-top').addClass('loaded');
		if(getCookie(TMPL_NAME + '_prstatus')!='close'){
			jQuery('body#bd').addClass('open-stickynote-top', {duration:200});
			jQuery('.stickynote-top').stop().animate({top:'0px'}, {duration:200});
			jQuery('#sns_wrapper').stop().animate({paddingTop:jQuery('.stickynote-top').outerHeight()}, {duration:200});
		}else{
			jQuery('.stickynote-top').stop().animate({top: - jQuery('.stickynote-top').outerHeight()}, {duration:0});
			jQuery('#sns_wrapper').stop().animate({paddingTop:'0px'}, {duration:0});
		}
		jQuery('.stickynote-top .btn-close').bind('click', function(){
			if(jQuery('body#bd').hasClass('open-stickynote-top')){
				jQuery('body#bd').removeClass('open-stickynote-top', {duration:200});
				jQuery('.stickynote-top').stop().animate({top: - jQuery('.stickynote-top').outerHeight()}, {duration:200});
				jQuery('#sns_wrapper').stop().animate({paddingTop:'0px'}, {duration:200});
				if(jQuery('#sns_menu').hasClass('keep-menu')){
					jQuery('#sns_menu.keep-menu').stop().animate({top:'0px'}, {duration:200});
				}
				createCookie(TMPL_NAME + '_prstatus', 'close', 1);
			}else{
				jQuery('body#bd').addClass('open-stickynote-top', {duration:200});
				jQuery('.stickynote-top').stop().animate({top:'0px'}, {duration:200});
				jQuery('#sns_wrapper').stop().animate({paddingTop:jQuery('.stickynote-top').outerHeight()}, {duration:200});
				if(jQuery('#sns_menu').hasClass('keep-menu')){
					jQuery('#sns_menu.keep-menu').stop().animate({top:jQuery('.stickynote-top').outerHeight()}, {duration:200});
				}
				createCookie(TMPL_NAME + '_prstatus', 'close', -1);
			}
		});
	}
	jQuery(window).resize(function(){
		if( jQuery('.stickynote-top').length ){
			if(jQuery('body.boxed-layout').length){
				jQuery('.stickynote-top').css({'width': jQuery('#sns_wrapper').outerWidth(), 'left':jQuery('#sns_wrapper').offset().left});
			}
			if(getCookie(TMPL_NAME + '_prstatus')!='close'){
				jQuery('#sns_wrapper').stop().animate({paddingTop:jQuery('.stickynote-top').outerHeight()}, {duration:200});
			}else{
				jQuery('.stickynote-top').stop().animate({top: - jQuery('.stickynote-top').outerHeight()}, {duration:0});
			}
		}
	});
});


function setHeight(array, status){
	var h=0;
	if(jQuery.isArray(array)){
		for($i=0;$i<array.length; $i++){
			jQuery(array[$i]).css('height', "");
		}
		for($i=0;$i<array.length; $i++){
			if( (h < jQuery(array[$i]).height()) &&  (jQuery(array[$i]).css('display')!="none")){
				h = jQuery(array[$i]).height();
			}
		}
		if(status){
			for($i=0;$i<array.length; $i++){
				jQuery(array[$i]).css('height', h);
			}
		} else {
			for($i=0;$i<array.length; $i++){
				jQuery(array[$i]).css('height', '');
			}
		}
	} else {
		jQuery(array).each(function(){
			if( (h < jQuery(this).height()) &&  (jQuery(this).css('display')!="none")){
				h = jQuery(this).height();
			}
		});
		if(status) jQuery(array).css('height', h);
		else jQuery(array).css('height', '');
	}
}
// fix dropdown bootstrap on magento
(function() {
    var isBootstrapEvent = false;
    if (window.jQuery) {
        var all = jQuery('*');
        jQuery.each(['hide.bs.dropdown', 
            'hide.bs.collapse', 
            'hide.bs.modal', 
            'hide.bs.tooltip',
            'hide.bs.popover'], function(index, eventName) {
            all.on(eventName, function( event ) {
                isBootstrapEvent = true;
            });
        });
    }
    var originalHide = Element.hide;
    Element.addMethods({
        hide: function(element) {
            if(isBootstrapEvent) {
                isBootstrapEvent = false;
                return element;
            }
            return originalHide(element);
        }
    });
})(jQuery);
// end fix dropdown bootstrap on magento
function dump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }
    alert(out);
    var pre = document.createElement('pre');
    pre.innerHTML = out;
    document.body.appendChild(pre)
}
(function($){
	$.fn.SnsAccordion = function(options) {
		var $el    = $(this);
		var defaults = {
			active: 'open',
			el_wrap: 'li',
			el_content: 'ul',
			accordion: true,
			expand: true,
			btn_open: '<i class="fa fa-plus-square-o"></i>',
			btn_close: '<i class="fa fa-minus-square-o"></i>'
		};
		var options = $.extend({}, defaults, options);
		
		
		$(document).ready(function() {
			$el.find(options.el_wrap).each(function(){
				$(this).find('> a, > span, > h4').wrap('<div class="accr_header"></div>');
				if(($(this).find(options.el_content)).length){
					$(this).find('> .accr_header').append('<span class="btn_accor">' + options.btn_open + '</span>');
					$(this).find('> '+options.el_content+':not(".accr_header")').wrap('<div class="accr_content"></div>');
				}
			});
			if(options.accordion){
				$('.accr_content').hide();
				$el.find(options.el_wrap).each(function(){
					if($(this).hasClass(options.active)) {
						$(this).find('> .accr_content')
							   .addClass(options.active)
							   .slideDown();
						$(this).find('> .accr_header')
							   .addClass(options.active);
					}
				});
			} else {
				$el.find(options.el_wrap).each(function(){
					if(!options.expand){
						$('.accr_content').hide();
					} else {
						$(this).find('> .accr_content').addClass(options.active);
						$(this).find('> .accr_header').addClass(options.active);
						$(this).find('> .accr_header .btn_accor').html(options.btn_close);
					}
				});
			}
	   });
	    $(window).load(function() {
			$el.find(options.el_wrap).each(function(){
				var $wrap = $(this);
				var $accrhead = $wrap.find('> .accr_header');
				var btn_accor = '.btn_accor';
				
				$accrhead.find(btn_accor).on('click', function(event) {
					event.preventDefault();
					var obj = $(this);
					var slide = true;
					if($accrhead.hasClass(options.active)) {
						slide = false;
					}
					if(options.accordion){
						$wrap.siblings(options.el_wrap).find('> .accr_content').slideUp().removeClass(options.active);
						$wrap.siblings(options.el_wrap).find('> .accr_header').removeClass(options.active);
						$wrap.siblings(options.el_wrap).find('> .accr_header ' + btn_accor).html(options.btn_open);
					}
					if(slide) {
						$accrhead.addClass(options.active);
						obj.html(options.btn_close);
						$accrhead.siblings('.accr_content').addClass(options.active).stop(true, true).slideDown();
					} else {
						$accrhead.removeClass(options.active);
						obj.html(options.btn_open);
						$accrhead.siblings('.accr_content').removeClass(options.active).stop(true, true).slideUp();
					}
					return false;
				});
			});
		});
	};
})(jQuery);