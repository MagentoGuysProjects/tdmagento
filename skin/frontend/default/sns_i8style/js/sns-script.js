jQuery(document).ready(function($){
	
//	if($(window).width() >= 992) setHeight('#sns_footer_top .column', true);
//	$(window).resize(function(){
//		if($(window).width() >= 992){
//			setHeight('#sns_footer_top .column', true);
//		} else {
//			setHeight('#sns_footer_top .column', false);
//		}
//	});
	
	if($('#sns_menu') && KEEP_MENU == 1){
		$('#sns_menu').stick_in_parent({
			sticky_class: 'keep-menu'
		});
	}
});