function food_grocery_store_menu_open_nav() {
	window.food_grocery_store_responsiveMenu=true;
	jQuery(".sidenav").addClass('show');
}
function food_grocery_store_menu_close_nav() {
	window.food_grocery_store_responsiveMenu=false;
 	jQuery(".sidenav").removeClass('show');
}

jQuery(function($){
 	"use strict";
   	jQuery('.main-menu > ul').superfish({
		delay:       500,
		animation:   {opacity:'show',height:'show'},  
		speed:       'fast'
   	});
});

jQuery(document).ready(function () {
	window.food_grocery_store_currentfocus=null;
  	food_grocery_store_checkfocusdElement();
	var food_grocery_store_body = document.querySelector('body');
	food_grocery_store_body.addEventListener('keyup', food_grocery_store_check_tab_press);
	var food_grocery_store_gotoHome = false;
	var food_grocery_store_gotoClose = false;
	window.food_grocery_store_responsiveMenu=false;
 	function food_grocery_store_checkfocusdElement(){
	 	if(window.food_grocery_store_currentfocus=document.activeElement.className){
		 	window.food_grocery_store_currentfocus=document.activeElement.className;
	 	}
 	}
 	function food_grocery_store_check_tab_press(e) {
		"use strict";
		// pick passed event or global event object if passed one is empty
		e = e || event;
		var activeElement;

		if(window.innerWidth < 999){
		if (e.keyCode == 9) {
			if(window.food_grocery_store_responsiveMenu){
			if (!e.shiftKey) {
				if(food_grocery_store_gotoHome) {
					jQuery( ".main-menu ul:first li:first a:first-child" ).focus();
				}
			}
			if (jQuery("a.closebtn.mobile-menu").is(":focus")) {
				food_grocery_store_gotoHome = true;
			} else {
				food_grocery_store_gotoHome = false;
			}

		}else{

			if(window.food_grocery_store_currentfocus=="responsivetoggle"){
				jQuery( "" ).focus();
			}}}
		}
		if (e.shiftKey && e.keyCode == 9) {
		if(window.innerWidth < 999){
			if(window.food_grocery_store_currentfocus=="header-search"){
				jQuery(".responsivetoggle").focus();
			}else{
				if(window.food_grocery_store_responsiveMenu){
				if(food_grocery_store_gotoClose){
					jQuery("a.closebtn.mobile-menu").focus();
				}
				if (jQuery( ".main-menu ul:first li:first a:first-child" ).is(":focus")) {
					food_grocery_store_gotoClose = true;
				} else {
					food_grocery_store_gotoClose = false;
				}
			
			}else{

			if(window.food_grocery_store_responsiveMenu){
			}}}}
		}
	 	food_grocery_store_checkfocusdElement();
	}
});

jQuery('document').ready(function($){
    setTimeout(function () {
		jQuery("#preloader").fadeOut("slow");
    },1000);
    // Sticky Header
  	$(window).scroll(function(){
		var sticky = $('.header-sticky'),
			scroll = $(window).scrollTop();

		if (scroll >= 100) sticky.addClass('header-fixed');
		else sticky.removeClass('header-fixed');
	});
});
 /*sticky sidebar*/
window.addEventListener('scroll', function() {
  var sticky = document.querySelector('.sidebar-sticky');
  if (!sticky) return;

  var scrollTop = window.scrollY || document.documentElement.scrollTop;
  var windowHeight = window.innerHeight;
  var documentHeight = document.documentElement.scrollHeight;

  var isBottom = scrollTop + windowHeight >= documentHeight-100;

  if (scrollTop >= 100 && !isBottom) {
    sticky.classList.add('sidebar-fixed');
  } else {
    sticky.classList.remove('sidebar-fixed');
  }
});
/*sticky copyright*/
window.addEventListener('scroll', function() {
  var sticky = document.querySelector('.copyright-sticky');
  if (!sticky) return;

  var scrollTop = window.scrollY || document.documentElement.scrollTop;
  var windowHeight = window.innerHeight;
  var documentHeight = document.documentElement.scrollHeight;

  var isBottom = scrollTop + windowHeight >= documentHeight-100;

  if (scrollTop >= 100 && !isBottom) {
    sticky.classList.add('copyright-fixed');
  } else {
    sticky.classList.remove('copyright-fixed');
  }
});

jQuery(document).ready(function(){
	jQuery(".product-cat").hide();
    jQuery("button.product-btn").click(function(){
        jQuery(".product-cat").toggle();
    });
});

jQuery(document).ready(function () {
	jQuery(window).scroll(function () {
	    if (jQuery(this).scrollTop() > 100) {
	        jQuery('.scrollup i').fadeIn();
	    } else {
	        jQuery('.scrollup i').fadeOut();
	    }
	});
	jQuery('.scrollup').click(function () {
	    jQuery("html, body").animate({
	        scrollTop: 0
	    }, 600);
	    return false;
	});
});