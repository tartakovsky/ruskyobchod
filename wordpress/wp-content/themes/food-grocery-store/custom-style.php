<?php

/*---------------------------First highlight color-------------------*/

	$food_grocery_store_color = get_theme_mod('food_grocery_store_color');

	$food_grocery_store_custom_css= "";

	if($food_grocery_store_color != false){
		$food_grocery_store_custom_css .='.search-box button, span.cart-value, .more-btn a, #slider .carousel-control-prev-icon:hover, #slider .carousel-control-next-icon:hover, .woocommerce #respond input#submit, .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce #respond input#submit.alt, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt, input[type="submit"], #footer-2, #comments a.comment-reply-link, #comments input[type="submit"], #sidebar h3, .pagination span, .pagination a, nav.woocommerce-MyAccount-navigation ul li, .toggle-nav i, .scrollup i,.widget_product_search button, span.cart-value, span.wishlist-counter, #preloader, #footer .wp-block-search .wp-block-search__button, #sidebar .wp-block-search .wp-block-search__button, #sidebar .wp-block-search .wp-block-search__label,.bradcrumbs a,.bradcrumbs span,.post-categories li a,#sidebar label.wp-block-search__label, #sidebar .wp-block-heading,.wp-block-tag-cloud a:hover,nav.navigation.posts-navigation .nav-previous a,nav.navigation.posts-navigation .nav-next a,.wp-block-button__link,.pagination span, .pagination a, .post-nav-links span, .post-nav-links a, #footer .tagcloud a:hover, .woocommerce span.onsale,a.added_to_cart.wc-forward,a.wc-block-components-checkout-return-to-cart-button{';
			$food_grocery_store_custom_css .='background: '.esc_attr($food_grocery_store_color).';';
		$food_grocery_store_custom_css .='}';

		$food_grocery_store_custom_css .='.wp-block-woocommerce-cart .wc-block-cart__submit-button, .wc-block-components-checkout-place-order-button, .wc-block-components-totals-coupon__button,.wc-block-components-order-summary-item__quantity{';
			$food_grocery_store_custom_css .='background: '.esc_attr($food_grocery_store_color).' !important;';
		$food_grocery_store_custom_css .='}';

		$food_grocery_store_custom_css .='@media screen and (max-width:720px){
				.logo{';
			$food_grocery_store_custom_css .='background: '.esc_attr($food_grocery_store_color).';';
		$food_grocery_store_custom_css .='} }';
	}
	if($food_grocery_store_color != false){
		$food_grocery_store_custom_css .='.main-navigation .current_page_ancestor > a, .main-navigation a:hover, .woocommerce ul.products li.product .price, .woocommerce div.product p.price, .woocommerce div.product span.price, .main-navigation ul.sub-menu a:hover, .post-main-box:hover h2 a, .post-main-box:hover .post-info span a, .post-info:hover a, p.phone_no strong a:hover, .account a:hover,.cart_no a:hover, .wishlist a:hover, .top-bar a:hover, #slider .inner_carousel h1 a:hover, span.woocommerce-Price-amount.amount, #content-vw a, #footer li a:hover, #comments p a{';
			$food_grocery_store_custom_css .='color: '.esc_attr($food_grocery_store_color).';';
		$food_grocery_store_custom_css .='}';
	}
	if($food_grocery_store_color != false){
		$food_grocery_store_custom_css .='.translate_lang .switcher, .switcher .selected a:hover{';
			$food_grocery_store_custom_css .='color: '.esc_attr($food_grocery_store_color).'!important;';
		$food_grocery_store_custom_css .='}';
	}	
	if($food_grocery_store_color != false){
		$food_grocery_store_custom_css .='.search-box form.woocommerce-product-search, .product-cat, .main-navigation ul ul{';
			$food_grocery_store_custom_css .='border-color: '.esc_attr($food_grocery_store_color).';';
		$food_grocery_store_custom_css .='}';

		$food_grocery_store_custom_css .='@media screen and (max-width:720px){
				button.product-btn{';
			$food_grocery_store_custom_css .='border-color: '.esc_attr($food_grocery_store_color).';';
		$food_grocery_store_custom_css .='} }';
	}
 
	/*---------------------------Width Layout -------------------*/

	$food_grocery_store_theme_lay = get_theme_mod( 'food_grocery_store_width_option','Full Width');
    if($food_grocery_store_theme_lay == 'Boxed'){
		$food_grocery_store_custom_css .='body{';
			$food_grocery_store_custom_css .='max-width: 1140px; width: 100%; padding-right: 15px; padding-left: 15px; margin-right: auto; margin-left: auto;';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='#middle-header input[type="search"]{';
			$food_grocery_store_custom_css .='width:84.5%';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='#slider{';
			$food_grocery_store_custom_css .='right: 1%;';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='.scrollup i{';
			$food_grocery_store_custom_css .='right: 100px;';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='.scrollup.left i{';
			$food_grocery_store_custom_css .='left: 100px;';
		$food_grocery_store_custom_css .='}';
	}else if($food_grocery_store_theme_lay == 'Wide Width'){
		$food_grocery_store_custom_css .='body{';
			$food_grocery_store_custom_css .='width: 100%;padding-right: 15px;padding-left: 15px;margin-right: auto;margin-left: auto;';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='.scrollup i{';
			$food_grocery_store_custom_css .='right: 30px;';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='.scrollup.left i{';
			$food_grocery_store_custom_css .='left: 30px;';
		$food_grocery_store_custom_css .='}';
	}else if($food_grocery_store_theme_lay == 'Full Width'){
		$food_grocery_store_custom_css .='body{';
			$food_grocery_store_custom_css .='max-width: 100%;';
		$food_grocery_store_custom_css .='}';
	}

	/*-----------------Slider Content Layout -------------------*/

	$food_grocery_store_theme_lay = get_theme_mod( 'food_grocery_store_slider_content_option','Center');
    if($food_grocery_store_theme_lay == 'Left'){
		$food_grocery_store_custom_css .='#slider .carousel-caption{';
			$food_grocery_store_custom_css .='text-align:left; right: 45%; left: 10%;';
		$food_grocery_store_custom_css .='}';
	}else if($food_grocery_store_theme_lay == 'Center'){
		$food_grocery_store_custom_css .='#slider .carousel-caption{';
			$food_grocery_store_custom_css .='text-align:center; right: 25%; left: 25%;';
		$food_grocery_store_custom_css .='}';
	}else if($food_grocery_store_theme_lay == 'Right'){
		$food_grocery_store_custom_css .='#slider .carousel-caption{';
			$food_grocery_store_custom_css .='text-align:right; right: 10%; left: 45%;';
		$food_grocery_store_custom_css .='}';
	}

	/*------------- Slider Content Padding Settings ------------------*/

	$food_grocery_store_slider_content_padding_top_bottom = get_theme_mod('food_grocery_store_slider_content_padding_top_bottom');
	$food_grocery_store_slider_content_padding_left_right = get_theme_mod('food_grocery_store_slider_content_padding_left_right');
	if($food_grocery_store_slider_content_padding_top_bottom != false || $food_grocery_store_slider_content_padding_left_right != false){
		$food_grocery_store_custom_css .='#slider .carousel-caption{';
			$food_grocery_store_custom_css .='top: '.esc_attr($food_grocery_store_slider_content_padding_top_bottom).'; bottom: '.esc_attr($food_grocery_store_slider_content_padding_top_bottom).';left: '.esc_attr($food_grocery_store_slider_content_padding_left_right).'!important;right: '.esc_attr($food_grocery_store_slider_content_padding_left_right).'!important;';
		$food_grocery_store_custom_css .='}';
	}

	/*---------------------------Slider Height ------------*/

	$food_grocery_store_slider_height = get_theme_mod('food_grocery_store_slider_height');
	if($food_grocery_store_slider_height != false){
		$food_grocery_store_custom_css .='#slider img{';
			$food_grocery_store_custom_css .='height: '.esc_attr($food_grocery_store_slider_height).';';
		$food_grocery_store_custom_css .='}';
	}

	/*---------------------- Slider Image Overlay ------------------------*/

	$food_grocery_store_slider_image_overlay = get_theme_mod('food_grocery_store_slider_image_overlay', true);
	if($food_grocery_store_slider_image_overlay == false){
		$food_grocery_store_custom_css .='#slider img{';
			$food_grocery_store_custom_css .='opacity:0.7;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_slider_image_overlay_color = get_theme_mod('food_grocery_store_slider_image_overlay_color', true);
	if($food_grocery_store_slider_image_overlay_color != false){
		$food_grocery_store_custom_css .='#slider{';
			$food_grocery_store_custom_css .='background-color: '.esc_attr($food_grocery_store_slider_image_overlay_color).';';
		$food_grocery_store_custom_css .='}';
	}

	/*---------------------------Blog Layout -------------------*/

	$food_grocery_store_theme_lay = get_theme_mod( 'food_grocery_store_blog_layout_option','Default');
    if($food_grocery_store_theme_lay == 'Default'){
		$food_grocery_store_custom_css .='.post-main-box{';
			$food_grocery_store_custom_css .='';
		$food_grocery_store_custom_css .='}';
	}else if($food_grocery_store_theme_lay == 'Center'){
		$food_grocery_store_custom_css .='.post-main-box, .post-main-box h2, .post-info, .new-text p{';
			$food_grocery_store_custom_css .='text-align:center;';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='.post-info{';
			$food_grocery_store_custom_css .='margin-top:10px;';
		$food_grocery_store_custom_css .='}';
	}else if($food_grocery_store_theme_lay == 'Left'){
		$food_grocery_store_custom_css .='.post-main-box, .post-main-box h2, .post-info, .new-text p, #our-services p{';
			$food_grocery_store_custom_css .='text-align:Left;';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='.post-main-box h2{';
			$food_grocery_store_custom_css .='margin-top:10px;';
		$food_grocery_store_custom_css .='}';
	}

	// featured image dimention
	$food_grocery_store_blog_post_featured_image_dimension = get_theme_mod('food_grocery_store_blog_post_featured_image_dimension', 'default');
	$food_grocery_store_blog_post_featured_image_custom_width = get_theme_mod('food_grocery_store_blog_post_featured_image_custom_width',250);
	$food_grocery_store_blog_post_featured_image_custom_height = get_theme_mod('food_grocery_store_blog_post_featured_image_custom_height',250);
	if($food_grocery_store_blog_post_featured_image_dimension == 'custom'){
		$food_grocery_store_custom_css .='.post-main-box img{';
			$food_grocery_store_custom_css .='width: '.esc_attr($food_grocery_store_blog_post_featured_image_custom_width).'; height: '.esc_attr($food_grocery_store_blog_post_featured_image_custom_height).';';
		$food_grocery_store_custom_css .='}';
	}

	/*--------------------- Blog Page Posts -------------------*/

	$food_grocery_store_blog_page_posts_settings = get_theme_mod( 'food_grocery_store_blog_page_posts_settings','Into Blocks');
    if($food_grocery_store_blog_page_posts_settings == 'Without Blocks'){
		$food_grocery_store_custom_css .='.post-main-box{';
			$food_grocery_store_custom_css .='box-shadow: none; border: none; margin:30px 0;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_related_image_box_shadow = get_theme_mod('food_grocery_store_related_image_box_shadow',0);
	if($food_grocery_store_related_image_box_shadow != false){
		$food_grocery_store_custom_css .='.related-post .box-image img{';
			$food_grocery_store_custom_css .='box-shadow: '.esc_attr($food_grocery_store_related_image_box_shadow).'px '.esc_attr($food_grocery_store_related_image_box_shadow).'px '.esc_attr($food_grocery_store_related_image_box_shadow).'px #cccccc;';
		$food_grocery_store_custom_css .='}';
	}


	/*---------------- Single Post Settings ------------------*/

	$food_grocery_store_single_blog_comment_title = get_theme_mod('food_grocery_store_single_blog_comment_title', 'Leave a Reply');
	if($food_grocery_store_single_blog_comment_title == ''){
		$food_grocery_store_custom_css .='#comments h2#reply-title {';
			$food_grocery_store_custom_css .='display: none;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_single_blog_comment_button_text = get_theme_mod('food_grocery_store_single_blog_comment_button_text', 'Post Comment');
	if($food_grocery_store_single_blog_comment_button_text == ''){
		$food_grocery_store_custom_css .='#comments p.form-submit {';
			$food_grocery_store_custom_css .='display: none;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_comment_width = get_theme_mod('food_grocery_store_single_blog_comment_width');
	if($food_grocery_store_comment_width != false){
		$food_grocery_store_custom_css .='#comments textarea{';
			$food_grocery_store_custom_css .='width: '.esc_attr($food_grocery_store_comment_width).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_singlepost_image_box_shadow = get_theme_mod('food_grocery_store_singlepost_image_box_shadow',0);
	if($food_grocery_store_singlepost_image_box_shadow != false){
		$food_grocery_store_custom_css .='.feature-box img{';
			$food_grocery_store_custom_css .='box-shadow: '.esc_attr($food_grocery_store_singlepost_image_box_shadow).'px '.esc_attr($food_grocery_store_singlepost_image_box_shadow).'px '.esc_attr($food_grocery_store_singlepost_image_box_shadow).'px #cccccc;';
		$food_grocery_store_custom_css .='}';
	}

	/*----------------Responsive Media -----------------------*/

	$food_grocery_store_resp_slider = get_theme_mod( 'food_grocery_store_resp_slider_hide_show',true);
	if($food_grocery_store_resp_slider == true && get_theme_mod( 'food_grocery_store_slider_arrows', true) == false){
    	$food_grocery_store_custom_css .='#slider{';
			$food_grocery_store_custom_css .='display:none;';
		$food_grocery_store_custom_css .='} ';
	}
    if($food_grocery_store_resp_slider == true){
    	$food_grocery_store_custom_css .='@media screen and (max-width:575px) {';
		$food_grocery_store_custom_css .='#slider{';
			$food_grocery_store_custom_css .='display:block;';
		$food_grocery_store_custom_css .='} }';
	}else if($food_grocery_store_resp_slider == false){
		$food_grocery_store_custom_css .='@media screen and (max-width:575px) {';
		$food_grocery_store_custom_css .='#slider{';
			$food_grocery_store_custom_css .='display:none;';
		$food_grocery_store_custom_css .='} }';
	}

	$food_grocery_store_resp_sidebar = get_theme_mod( 'food_grocery_store_sidebar_hide_show',true);
    if($food_grocery_store_resp_sidebar == true){
    	$food_grocery_store_custom_css .='@media screen and (max-width:575px) {';
		$food_grocery_store_custom_css .='#sidebar{';
			$food_grocery_store_custom_css .='display:block;';
		$food_grocery_store_custom_css .='} }';
	}else if($food_grocery_store_resp_sidebar == false){
		$food_grocery_store_custom_css .='@media screen and (max-width:575px) {';
		$food_grocery_store_custom_css .='#sidebar{';
			$food_grocery_store_custom_css .='display:none;';
		$food_grocery_store_custom_css .='} }';
	}

	$food_grocery_store_resp_scroll_top = get_theme_mod( 'food_grocery_store_resp_scroll_top_hide_show',true);
	if($food_grocery_store_resp_scroll_top == true && get_theme_mod( 'food_grocery_store_footer_scroll',true) != true){
    	$food_grocery_store_custom_css .='.scrollup i{';
			$food_grocery_store_custom_css .='visibility:hidden !important;';
		$food_grocery_store_custom_css .='} ';
	}
    if($food_grocery_store_resp_scroll_top == true){
    	$food_grocery_store_custom_css .='@media screen and (max-width:575px) {';
		$food_grocery_store_custom_css .='.scrollup i{';
			$food_grocery_store_custom_css .='visibility:visible !important;';
		$food_grocery_store_custom_css .='} }';
	}else if($food_grocery_store_resp_scroll_top == false){
		$food_grocery_store_custom_css .='@media screen and (max-width:575px){';
		$food_grocery_store_custom_css .='.scrollup i{';
			$food_grocery_store_custom_css .='visibility:hidden !important;';
		$food_grocery_store_custom_css .='} }';
	}

	$food_grocery_store_resp_menu_toggle_btn_bg_color = get_theme_mod('food_grocery_store_resp_menu_toggle_btn_bg_color');
	if($food_grocery_store_resp_menu_toggle_btn_bg_color != false){
		$food_grocery_store_custom_css .='.toggle-nav i,.sidenav .closebtn{';
			$food_grocery_store_custom_css .='background: '.esc_attr($food_grocery_store_resp_menu_toggle_btn_bg_color).';';
		$food_grocery_store_custom_css .='}';
	}

	/*---------------- Menus Settings ------------------*/

	$food_grocery_store_navigation_menu_font_size = get_theme_mod('food_grocery_store_navigation_menu_font_size');
	if($food_grocery_store_navigation_menu_font_size != false){
		$food_grocery_store_custom_css .='.main-navigation a{';
			$food_grocery_store_custom_css .='font-size: '.esc_attr($food_grocery_store_navigation_menu_font_size).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_nav_menus_font_weight = get_theme_mod( 'food_grocery_store_navigation_menu_font_weight','Default');
    if($food_grocery_store_nav_menus_font_weight == 'Default'){
		$food_grocery_store_custom_css .='.main-navigation a{';
			$food_grocery_store_custom_css .='';
		$food_grocery_store_custom_css .='}';
	}else if($food_grocery_store_nav_menus_font_weight == 'Normal'){
		$food_grocery_store_custom_css .='.main-navigation a{';
			$food_grocery_store_custom_css .='font-weight: normal;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_header_menus_color = get_theme_mod('food_grocery_store_header_menus_color');
	if($food_grocery_store_header_menus_color != false){
		$food_grocery_store_custom_css .='.main-navigation a{';
			$food_grocery_store_custom_css .='color: '.esc_attr($food_grocery_store_header_menus_color).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_header_menus_hover_color = get_theme_mod('food_grocery_store_header_menus_hover_color');
	if($food_grocery_store_header_menus_hover_color != false){
		$food_grocery_store_custom_css .='.main-navigation a:hover, .main-navigation .current_page_item > a, .main-navigation .current-menu-item > a, .main-navigation .current_page_ancestor > a{';
			$food_grocery_store_custom_css .='color: '.esc_attr($food_grocery_store_header_menus_hover_color).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_header_submenus_color = get_theme_mod('food_grocery_store_header_submenus_color');
	if($food_grocery_store_header_submenus_color != false){
		$food_grocery_store_custom_css .='.main-navigation ul ul a{';
			$food_grocery_store_custom_css .='color: '.esc_attr($food_grocery_store_header_submenus_color).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_header_submenus_hover_color = get_theme_mod('food_grocery_store_header_submenus_hover_color');
	if($food_grocery_store_header_submenus_hover_color != false){
		$food_grocery_store_custom_css .='.main-navigation ul.sub-menu a:hover{';
			$food_grocery_store_custom_css .='color: '.esc_attr($food_grocery_store_header_submenus_hover_color).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_menus_item = get_theme_mod( 'food_grocery_store_menus_item_style','None');
    if($food_grocery_store_menus_item == 'None'){
		$food_grocery_store_custom_css .='.main-navigation a{';
			$food_grocery_store_custom_css .='';
		$food_grocery_store_custom_css .='}';
	}else if($food_grocery_store_menus_item == 'Zoom In'){
		$food_grocery_store_custom_css .='.main-navigation a:hover{';
			$food_grocery_store_custom_css .='transition: all 0.3s ease-in-out !important; transform: scale(1.2) !important; color:#d51c22;';
		$food_grocery_store_custom_css .='}'; 
	}

	$food_grocery_store_navigation_menu_font_weight = get_theme_mod('food_grocery_store_navigation_menu_font_weight','600');
	if($food_grocery_store_navigation_menu_font_weight != false){
		$food_grocery_store_custom_css .='.main-navigation a{';
			$food_grocery_store_custom_css .='font-weight: '.esc_attr($food_grocery_store_navigation_menu_font_weight).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_theme_lay = get_theme_mod( 'food_grocery_store_menu_text_transform','Capitalize');
	if($food_grocery_store_theme_lay == 'Capitalize'){
		$food_grocery_store_custom_css .='.main-navigation a{';
			$food_grocery_store_custom_css .='text-transform:Capitalize;';
		$food_grocery_store_custom_css .='}';
	}
	if($food_grocery_store_theme_lay == 'Lowercase'){
		$food_grocery_store_custom_css .='.main-navigation a{';
			$food_grocery_store_custom_css .='text-transform:Lowercase;';
		$food_grocery_store_custom_css .='}';
	}
	if($food_grocery_store_theme_lay == 'Uppercase'){
		$food_grocery_store_custom_css .='.main-navigation a{';
			$food_grocery_store_custom_css .='text-transform:Uppercase;';
		$food_grocery_store_custom_css .='}';
	}

	/*---------------- Post Settings ------------------*/

	$food_grocery_store_featured_image_border_radius = get_theme_mod('food_grocery_store_featured_image_border_radius', 0);
	if($food_grocery_store_featured_image_border_radius != false){
		$food_grocery_store_custom_css .='.box-image img, .feature-box img{';
			$food_grocery_store_custom_css .='border-radius: '.esc_attr($food_grocery_store_featured_image_border_radius).'px;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_featured_image_box_shadow = get_theme_mod('food_grocery_store_featured_image_box_shadow',0);
	if($food_grocery_store_featured_image_box_shadow != false){
		$food_grocery_store_custom_css .='.box-image img, #content-vw img{';
			$food_grocery_store_custom_css .='box-shadow: '.esc_attr($food_grocery_store_featured_image_box_shadow).'px '.esc_attr($food_grocery_store_featured_image_box_shadow).'px '.esc_attr($food_grocery_store_featured_image_box_shadow).'px #cccccc;';
		$food_grocery_store_custom_css .='}';
	}


	$food_grocery_store_single_blog_post_navigation_show_hide = get_theme_mod('food_grocery_store_single_blog_post_navigation_show_hide',true);
	if($food_grocery_store_single_blog_post_navigation_show_hide != true){
		$food_grocery_store_custom_css .='.post-navigation{';
			$food_grocery_store_custom_css .='display: none;';
		$food_grocery_store_custom_css .='}';
	}

	/*---------------- Button Settings ------------------*/

	$food_grocery_store_button_padding_top_bottom = get_theme_mod('food_grocery_store_button_padding_top_bottom');
	$food_grocery_store_button_padding_left_right = get_theme_mod('food_grocery_store_button_padding_left_right');
	if($food_grocery_store_button_padding_top_bottom != false || $food_grocery_store_button_padding_left_right != false){
		$food_grocery_store_custom_css .='.post-main-box .more-btn a{';
			$food_grocery_store_custom_css .='padding-top: '.esc_attr($food_grocery_store_button_padding_top_bottom).'!important; 
			padding-bottom: '.esc_attr($food_grocery_store_button_padding_top_bottom).'!important;
			padding-left: '.esc_attr($food_grocery_store_button_padding_left_right).'!important;
			padding-right: '.esc_attr($food_grocery_store_button_padding_left_right).'!important;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_button_border_radius = get_theme_mod('food_grocery_store_button_border_radius');
	if($food_grocery_store_button_border_radius != false){
		$food_grocery_store_custom_css .='.post-main-box .more-btn a{';
			$food_grocery_store_custom_css .='border-radius: '.esc_attr($food_grocery_store_button_border_radius).'px;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_button_font_size = get_theme_mod('food_grocery_store_button_font_size',14);
	$food_grocery_store_custom_css .='.post-main-box .more-btn a{';
		$food_grocery_store_custom_css .='font-size: '.esc_attr($food_grocery_store_button_font_size).';';
	$food_grocery_store_custom_css .='}';

	$food_grocery_store_theme_lay = get_theme_mod( 'food_grocery_store_button_text_transform','Uppercase');
	if($food_grocery_store_theme_lay == 'Capitalize'){
		$food_grocery_store_custom_css .='.post-main-box .more-btn a{';
			$food_grocery_store_custom_css .='text-transform:Capitalize;';
		$food_grocery_store_custom_css .='}';
	}
	if($food_grocery_store_theme_lay == 'Lowercase'){
		$food_grocery_store_custom_css .='.post-main-box .more-btn a{';
			$food_grocery_store_custom_css .='text-transform:Lowercase;';
		$food_grocery_store_custom_css .='}';
	}
	if($food_grocery_store_theme_lay == 'Uppercase'){ 
		$food_grocery_store_custom_css .='.post-main-box .more-btn a{';
			$food_grocery_store_custom_css .='text-transform:Uppercase;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_button_letter_spacing = get_theme_mod('food_grocery_store_button_letter_spacing');
	$food_grocery_store_custom_css .='.post-main-box .more-btn a{';
		$food_grocery_store_custom_css .='letter-spacing: '.esc_attr($food_grocery_store_button_letter_spacing).';';
	$food_grocery_store_custom_css .='}';

	/*-------------- Copyright Alignment ----------------*/

	$food_grocery_store_copyright_background_color = get_theme_mod('food_grocery_store_copyright_background_color');
	if($food_grocery_store_copyright_background_color != false){
		$food_grocery_store_custom_css .='#footer-2{';
			$food_grocery_store_custom_css .='background-color: '.esc_attr($food_grocery_store_copyright_background_color).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_footer_padding = get_theme_mod('food_grocery_store_footer_padding');
	if($food_grocery_store_footer_padding != false){
		$food_grocery_store_custom_css .='#footer{';
			$food_grocery_store_custom_css .='padding: '.esc_attr($food_grocery_store_footer_padding).' 0;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_footer_widgets_heading = get_theme_mod( 'food_grocery_store_footer_widgets_heading','Left');
    if($food_grocery_store_footer_widgets_heading == 'Left'){
		$food_grocery_store_custom_css .='#footer h3, .footer .wp-block-search .wp-block-search__label{';
		$food_grocery_store_custom_css .='text-align: left; position:relative;';
		$food_grocery_store_custom_css .='}';
	}else if($food_grocery_store_footer_widgets_heading == 'Center'){
		$food_grocery_store_custom_css .='#footer h3, .footer .wp-block-search .wp-block-search__label{';
			$food_grocery_store_custom_css .='text-align: center; position:relative;';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='#footer h3:after, #footer .wp-block-search .wp-block-search__label:after{';
		$food_grocery_store_custom_css .='position: absolute; right:50%;';
		$food_grocery_store_custom_css .='}';
	}else if($food_grocery_store_footer_widgets_heading == 'Right'){
		$food_grocery_store_custom_css .='#footer h3, .footer .wp-block-search .wp-block-search__label{';
			$food_grocery_store_custom_css .='text-align: right; position:relative;';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='#footer h3:after, #footer .wp-block-search .wp-block-search__label:after{';
		$food_grocery_store_custom_css .='position: absolute; right:0%;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_footer_widgets_content = get_theme_mod( 'food_grocery_store_footer_widgets_content','Left');
    if($food_grocery_store_footer_widgets_content == 'Left'){
		$food_grocery_store_custom_css .='#footer .widget{';
		$food_grocery_store_custom_css .='text-align: left;';
		$food_grocery_store_custom_css .='}';
	}else if($food_grocery_store_footer_widgets_content == 'Center'){
		$food_grocery_store_custom_css .='#footer .widget{';
			$food_grocery_store_custom_css .='text-align: center;';
		$food_grocery_store_custom_css .='}';
	}else if($food_grocery_store_footer_widgets_content == 'Right'){
		$food_grocery_store_custom_css .='#footer .widget{';
			$food_grocery_store_custom_css .='text-align: right;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_footer_background_color = get_theme_mod('food_grocery_store_footer_background_color');
	if($food_grocery_store_footer_background_color != false){
		$food_grocery_store_custom_css .='#footer{';
			$food_grocery_store_custom_css .='background-color: '.esc_attr($food_grocery_store_footer_background_color).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_copyright_font_size = get_theme_mod('food_grocery_store_copyright_font_size');
	if($food_grocery_store_copyright_font_size != false){
		$food_grocery_store_custom_css .='.copyright p{';
			$food_grocery_store_custom_css .='font-size: '.esc_attr($food_grocery_store_copyright_font_size).';';
		$food_grocery_store_custom_css .='}';
	}

		
	/*-------------- Copyright Alignment ----------------*/
	$food_grocery_store_copyright_alignment = get_theme_mod('food_grocery_store_copyright_alignment');
	if($food_grocery_store_copyright_alignment != false){
		$food_grocery_store_custom_css .='.copyright p,#footer-2 p{';
			$food_grocery_store_custom_css .='text-align: '.esc_attr($food_grocery_store_copyright_alignment).';';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='
		@media screen and (max-width:720px) {
			.copyright p,#footer-2 p{';
			$food_grocery_store_custom_css .='text-align: center;} }';
	}

	$food_grocery_store_align_footer_social_icon = get_theme_mod('food_grocery_store_align_footer_social_icon');
	if($food_grocery_store_align_footer_social_icon != false){
		$food_grocery_store_custom_css .='.copyright .widget{';
			$food_grocery_store_custom_css .='text-align: '.esc_attr($food_grocery_store_align_footer_social_icon).';';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='
		@media screen and (max-width:720px) {
			.copyright .widget{';
			$food_grocery_store_custom_css .='text-align: center;} }';
	}

	$food_grocery_store_resp_stickycopyright = get_theme_mod( 'food_grocery_store_stickycopyright_hide_show',false);
	if($food_grocery_store_resp_stickycopyright == true && get_theme_mod( 'food_grocery_store_copyright_sticky',false) != true){
    	$food_grocery_store_custom_css .='.copyright-sticky{';
			$food_grocery_store_custom_css .='position:static;';
		$food_grocery_store_custom_css .='} ';
	}

	$food_grocery_store_footer_social_icons_font_size = get_theme_mod('food_grocery_store_footer_social_icons_font_size','16');
	$food_grocery_store_custom_css .='.copyright .widget i{';
		$food_grocery_store_custom_css .='font-size: '.esc_attr($food_grocery_store_footer_social_icons_font_size).'px;';
	$food_grocery_store_custom_css .='}';

	$food_grocery_store_footer_background_image = get_theme_mod('food_grocery_store_footer_background_image');
	if($food_grocery_store_footer_background_image != false){
		$food_grocery_store_custom_css .='#footer{';
			$food_grocery_store_custom_css .='background: url('.esc_attr($food_grocery_store_footer_background_image).');background-size:cover;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_theme_lay = get_theme_mod( 'food_grocery_store_img_footer','scroll');
	if($food_grocery_store_theme_lay == 'fixed'){
		$food_grocery_store_custom_css .='#footer{';
			$food_grocery_store_custom_css .='background-attachment: fixed !important;';
		$food_grocery_store_custom_css .='}';
	}elseif ($food_grocery_store_theme_lay == 'scroll'){
		$food_grocery_store_custom_css .='#footer{';
			$food_grocery_store_custom_css .='background-attachment: scroll !important;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_footer_img_position = get_theme_mod('food_grocery_store_footer_img_position','center center');
	if($food_grocery_store_footer_img_position != false){
		$food_grocery_store_custom_css .='#footer{';
			$food_grocery_store_custom_css .='background-position: '.esc_attr($food_grocery_store_footer_img_position).'!important;';
		$food_grocery_store_custom_css .='}';
	} 

	$food_grocery_store_copyright_font_weight = get_theme_mod('food_grocery_store_copyright_font_weight');
	if($food_grocery_store_copyright_font_weight != false){
		$food_grocery_store_custom_css .='.copyright p, .copyright a{';
			$food_grocery_store_custom_css .='font-weight: '.esc_attr($food_grocery_store_copyright_font_weight).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_copyright_text_color = get_theme_mod('food_grocery_store_copyright_text_color');
	if($food_grocery_store_copyright_text_color != false){
		$food_grocery_store_custom_css .='.copyright p, .copyright a{';
			$food_grocery_store_custom_css .='color: '.esc_attr($food_grocery_store_copyright_text_color).';';
		$food_grocery_store_custom_css .='}';
	} 


	/*-------------- Wooommerce Settings ----------------*/

	$food_grocery_store_related_product_show_hide = get_theme_mod('food_grocery_store_related_product_show_hide',true);
	if($food_grocery_store_related_product_show_hide != true){
		$food_grocery_store_custom_css .='.related.products{';
			$food_grocery_store_custom_css .='display: none;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_products_btn_padding_top_bottom = get_theme_mod('food_grocery_store_products_btn_padding_top_bottom');
	if($food_grocery_store_products_btn_padding_top_bottom != false){
		$food_grocery_store_custom_css .='.woocommerce a.button{';
			$food_grocery_store_custom_css .='padding-top: '.esc_attr($food_grocery_store_products_btn_padding_top_bottom).' !important; padding-bottom: '.esc_attr($food_grocery_store_products_btn_padding_top_bottom).' !important;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_products_btn_padding_left_right = get_theme_mod('food_grocery_store_products_btn_padding_left_right');
	if($food_grocery_store_products_btn_padding_left_right != false){
		$food_grocery_store_custom_css .='.woocommerce a.button{';
			$food_grocery_store_custom_css .='padding-left: '.esc_attr($food_grocery_store_products_btn_padding_left_right).' !important; padding-right: '.esc_attr($food_grocery_store_products_btn_padding_left_right).' !important;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_woocommerce_sale_position = get_theme_mod( 'food_grocery_store_woocommerce_sale_position','right');
    if($food_grocery_store_woocommerce_sale_position == 'left'){
		$food_grocery_store_custom_css .='.woocommerce ul.products li.product .onsale{';
			$food_grocery_store_custom_css .='left: 10px; right: auto !important;';
		$food_grocery_store_custom_css .='}';
	}else if($food_grocery_store_woocommerce_sale_position == 'right'){
		$food_grocery_store_custom_css .='.woocommerce ul.products li.product .onsale{';
			$food_grocery_store_custom_css .='left: auto; right: 0;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_woocommerce_sale_font_size = get_theme_mod('food_grocery_store_woocommerce_sale_font_size');
	if($food_grocery_store_woocommerce_sale_font_size != false){
		$food_grocery_store_custom_css .='.woocommerce span.onsale{';
			$food_grocery_store_custom_css .='font-size: '.esc_attr($food_grocery_store_woocommerce_sale_font_size).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_woocommerce_sale_padding_top_bottom = get_theme_mod('food_grocery_store_woocommerce_sale_padding_top_bottom');
	if($food_grocery_store_woocommerce_sale_padding_top_bottom != false){
		$food_grocery_store_custom_css .='.woocommerce span.onsale{';
			$food_grocery_store_custom_css .='padding-top: '.esc_attr($food_grocery_store_woocommerce_sale_padding_top_bottom).'; padding-bottom: '.esc_attr($food_grocery_store_woocommerce_sale_padding_top_bottom).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_woocommerce_sale_padding_left_right = get_theme_mod('food_grocery_store_woocommerce_sale_padding_left_right');
	if($food_grocery_store_woocommerce_sale_padding_left_right != false){
		$food_grocery_store_custom_css .='.woocommerce span.onsale{';
			$food_grocery_store_custom_css .='padding-left: '.esc_attr($food_grocery_store_woocommerce_sale_padding_left_right).'; padding-right: '.esc_attr($food_grocery_store_woocommerce_sale_padding_left_right).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_woocommerce_sale_border_radius = get_theme_mod('food_grocery_store_woocommerce_sale_border_radius', 0);
	if($food_grocery_store_woocommerce_sale_border_radius != false){
		$food_grocery_store_custom_css .='.woocommerce span.onsale{';
			$food_grocery_store_custom_css .='border-radius: '.esc_attr($food_grocery_store_woocommerce_sale_border_radius).'px;';
		$food_grocery_store_custom_css .='}';
	}

	/*------------------ Logo  -------------------*/

	$food_grocery_store_logo_padding = get_theme_mod('food_grocery_store_logo_padding');
	if($food_grocery_store_logo_padding != false){
		$food_grocery_store_custom_css .='.logo{';
			$food_grocery_store_custom_css .='padding: '.esc_attr($food_grocery_store_logo_padding).'!important;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_logo_margin = get_theme_mod('food_grocery_store_logo_margin');
	if($food_grocery_store_logo_margin != false){
		$food_grocery_store_custom_css .='.logo{';
			$food_grocery_store_custom_css .='margin: '.esc_attr($food_grocery_store_logo_margin).';';
		$food_grocery_store_custom_css .='}';
	}

	// Site title Font Size
	$food_grocery_store_site_title_font_size = get_theme_mod('food_grocery_store_site_title_font_size');
	if($food_grocery_store_site_title_font_size != false){
		$food_grocery_store_custom_css .='.logo h1, .logo p.site-title{';
			$food_grocery_store_custom_css .='font-size: '.esc_attr($food_grocery_store_site_title_font_size).';';
		$food_grocery_store_custom_css .='}';
	}

	// Site tagline Font Size
	$food_grocery_store_site_tagline_font_size = get_theme_mod('food_grocery_store_site_tagline_font_size');
	if($food_grocery_store_site_tagline_font_size != false){
		$food_grocery_store_custom_css .='.logo p.site-description{';
			$food_grocery_store_custom_css .='font-size: '.esc_attr($food_grocery_store_site_tagline_font_size).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_site_title_color = get_theme_mod('food_grocery_store_site_title_color');
	if($food_grocery_store_site_title_color != false){
		$food_grocery_store_custom_css .='p.site-title a{';
			$food_grocery_store_custom_css .='color: '.esc_attr($food_grocery_store_site_title_color).'!important;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_site_tagline_color = get_theme_mod('food_grocery_store_site_tagline_color');
	if($food_grocery_store_site_tagline_color != false){
		$food_grocery_store_custom_css .='.logo p.site-description{';
			$food_grocery_store_custom_css .='color: '.esc_attr($food_grocery_store_site_tagline_color).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_logo_width = get_theme_mod('food_grocery_store_logo_width');
	if($food_grocery_store_logo_width != false){
		$food_grocery_store_custom_css .='.logo img{';
			$food_grocery_store_custom_css .='width: '.esc_attr($food_grocery_store_logo_width).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_logo_height = get_theme_mod('food_grocery_store_logo_height');
	if($food_grocery_store_logo_height != false){
		$food_grocery_store_custom_css .='.logo img{';
			$food_grocery_store_custom_css .='height: '.esc_attr($food_grocery_store_logo_height).';';
		$food_grocery_store_custom_css .='}';
	}

	// Woocommerce img

	$food_grocery_store_shop_featured_image_border_radius = get_theme_mod('food_grocery_store_shop_featured_image_border_radius', 0);
	if($food_grocery_store_shop_featured_image_border_radius != false){
		$food_grocery_store_custom_css .='.woocommerce ul.products li.product a img{';
			$food_grocery_store_custom_css .='border-radius: '.esc_attr($food_grocery_store_shop_featured_image_border_radius).'px;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_shop_featured_image_box_shadow = get_theme_mod('food_grocery_store_shop_featured_image_box_shadow');
	if($food_grocery_store_shop_featured_image_box_shadow != false){
		$food_grocery_store_custom_css .='.woocommerce ul.products li.product a img{';
				$food_grocery_store_custom_css .='box-shadow: '.esc_attr($food_grocery_store_shop_featured_image_box_shadow).'px '.esc_attr($food_grocery_store_shop_featured_image_box_shadow).'px '.esc_attr($food_grocery_store_shop_featured_image_box_shadow).'px #ddd;';
		$food_grocery_store_custom_css .='}';
	}

	/*------------------ Preloader Background Color  -------------------*/

	$food_grocery_store_preloader_bg_color = get_theme_mod('food_grocery_store_preloader_bg_color');
	if($food_grocery_store_preloader_bg_color != false){
		$food_grocery_store_custom_css .='#preloader{';
			$food_grocery_store_custom_css .='background-color: '.esc_attr($food_grocery_store_preloader_bg_color).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_preloader_border_color = get_theme_mod('food_grocery_store_preloader_border_color');
	if($food_grocery_store_preloader_border_color != false){
		$food_grocery_store_custom_css .='.loader-line{';
			$food_grocery_store_custom_css .='border-color: '.esc_attr($food_grocery_store_preloader_border_color).'!important;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_preloader_bg_img = get_theme_mod('food_grocery_store_preloader_bg_img');
	if($food_grocery_store_preloader_bg_img != false){
		$food_grocery_store_custom_css .='#preloader{';
			$food_grocery_store_custom_css .='background: url('.esc_attr($food_grocery_store_preloader_bg_img).');-webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover;';
		$food_grocery_store_custom_css .='}';
	}

	// Header Background Color
	$food_grocery_store_header_background_color = get_theme_mod('food_grocery_store_header_background_color');
	if($food_grocery_store_header_background_color != false){
		$food_grocery_store_custom_css .='.home-page-header{';
			$food_grocery_store_custom_css .='background-color: '.esc_attr($food_grocery_store_header_background_color).';';
		$food_grocery_store_custom_css .='}';
	}

	// Header Background position
	$food_grocery_store_header_img_position = get_theme_mod('food_grocery_store_header_img_position','center top');
	if($food_grocery_store_header_img_position != false){
		$food_grocery_store_custom_css .='.home-page-header{';
			$food_grocery_store_custom_css .='background-position: '.esc_attr($food_grocery_store_header_img_position).'!important;';
		$food_grocery_store_custom_css .='}';
	}

	/*----------------Sroll to top Settings ------------------*/

	$food_grocery_store_scroll_to_top_font_size = get_theme_mod('food_grocery_store_scroll_to_top_font_size');
	if($food_grocery_store_scroll_to_top_font_size != false){
		$food_grocery_store_custom_css .='.scrollup i{';
			$food_grocery_store_custom_css .='font-size: '.esc_attr($food_grocery_store_scroll_to_top_font_size).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_scroll_to_top_padding = get_theme_mod('food_grocery_store_scroll_to_top_padding');
	$food_grocery_store_scroll_to_top_padding = get_theme_mod('food_grocery_store_scroll_to_top_padding');
	if($food_grocery_store_scroll_to_top_padding != false){
		$food_grocery_store_custom_css .='.scrollup i{';
			$food_grocery_store_custom_css .='padding-top: '.esc_attr($food_grocery_store_scroll_to_top_padding).';padding-bottom: '.esc_attr($food_grocery_store_scroll_to_top_padding).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_scroll_to_top_width = get_theme_mod('food_grocery_store_scroll_to_top_width');
	if($food_grocery_store_scroll_to_top_width != false){
		$food_grocery_store_custom_css .='.scrollup i{';
			$food_grocery_store_custom_css .='width: '.esc_attr($food_grocery_store_scroll_to_top_width).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_scroll_to_top_height = get_theme_mod('food_grocery_store_scroll_to_top_height');
	if($food_grocery_store_scroll_to_top_height != false){
		$food_grocery_store_custom_css .='.scrollup i{';
			$food_grocery_store_custom_css .='height: '.esc_attr($food_grocery_store_scroll_to_top_height).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_scroll_to_top_border_radius = get_theme_mod('food_grocery_store_scroll_to_top_border_radius');
	if($food_grocery_store_scroll_to_top_border_radius != false){
		$food_grocery_store_custom_css .='.scrollup i{';
			$food_grocery_store_custom_css .='border-radius: '.esc_attr($food_grocery_store_scroll_to_top_border_radius).'px;';
		$food_grocery_store_custom_css .='}';
	}

	/*----------------Social Icons Settings ------------------*/

	$food_grocery_store_social_icon_font_size = get_theme_mod('food_grocery_store_social_icon_font_size');
	if($food_grocery_store_social_icon_font_size != false){
		$food_grocery_store_custom_css .='#sidebar .custom-social-icons i, #footer-2 .custom-social-icons i{';
			$food_grocery_store_custom_css .='font-size: '.esc_attr($food_grocery_store_social_icon_font_size).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_social_icon_padding = get_theme_mod('food_grocery_store_social_icon_padding');
	if($food_grocery_store_social_icon_padding != false){
		$food_grocery_store_custom_css .='#sidebar .custom-social-icons i, #footer-2 .custom-social-icons i{';
			$food_grocery_store_custom_css .='padding: '.esc_attr($food_grocery_store_social_icon_padding).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_social_icon_width = get_theme_mod('food_grocery_store_social_icon_width');
	if($food_grocery_store_social_icon_width != false){
		$food_grocery_store_custom_css .='#sidebar .custom-social-icons i, #footer-2 .custom-social-icons i{';
			$food_grocery_store_custom_css .='width: '.esc_attr($food_grocery_store_social_icon_width).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_social_icon_height = get_theme_mod('food_grocery_store_social_icon_height');
	if($food_grocery_store_social_icon_height != false){
		$food_grocery_store_custom_css .='#sidebar .custom-social-icons i, #footer-2 .custom-social-icons i{';
			$food_grocery_store_custom_css .='height: '.esc_attr($food_grocery_store_social_icon_height).';';
		$food_grocery_store_custom_css .='}';
	}

	/*--------------------- Grid Posts Posts -------------------*/

	$food_grocery_store_display_grid_posts_settings = get_theme_mod( 'food_grocery_store_display_grid_posts_settings','Into Blocks');
    if($food_grocery_store_display_grid_posts_settings == 'Without Blocks'){
		$food_grocery_store_custom_css .='.grid-post-main-box{';
			$food_grocery_store_custom_css .='box-shadow: none; border: none; margin:30px 0;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_grid_featured_image_box_shadow = get_theme_mod('food_grocery_store_grid_featured_image_box_shadow',0);
	if($food_grocery_store_grid_featured_image_box_shadow != false){
		$food_grocery_store_custom_css .='.grid-post-main-box .box-image img, .grid-post-main-box .feature-box img, #content-vw img{';
			$food_grocery_store_custom_css .='box-shadow: '.esc_attr($food_grocery_store_grid_featured_image_box_shadow).'px '.esc_attr($food_grocery_store_grid_featured_image_box_shadow).'px '.esc_attr($food_grocery_store_grid_featured_image_box_shadow).'px #cccccc;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_grid_featured_image_border_radius = get_theme_mod('food_grocery_store_grid_featured_image_border_radius', 0);
	if($food_grocery_store_grid_featured_image_border_radius != false){
		$food_grocery_store_custom_css .='.grid-post-main-box .box-image img, .grid-post-main-box .feature-box img, #content-vw img{';
			$food_grocery_store_custom_css .='border-radius: '.esc_attr($food_grocery_store_grid_featured_image_border_radius).'px;';
		$food_grocery_store_custom_css .='}';
	}
	/*---------------------------Footer Style -------------------*/

	$food_grocery_store_theme_lay = get_theme_mod( 'food_grocery_store_footer_template','food_grocery_store-footer-one');
    if($food_grocery_store_theme_lay == 'food_grocery_store-footer-one'){
		$food_grocery_store_custom_css .='#footer{';
			$food_grocery_store_custom_css .='';
		$food_grocery_store_custom_css .='}';

	}else if($food_grocery_store_theme_lay == 'food_grocery_store-footer-two'){
		$food_grocery_store_custom_css .='#footer{';
			$food_grocery_store_custom_css .='background: linear-gradient(to right, #f9f8ff, #dedafa);';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='#footer p, #footer li a, #footer, #footer h3, #footer a.rsswidget, #footer #wp-calendar a, .copyright a, #footer .custom_details, #footer ins span, #footer .tagcloud a, .main-inner-box span.entry-date a, nav.woocommerce-MyAccount-navigation ul li:hover a, #footer ul li a, #footer table, #footer th, #footer td, #footer caption, #sidebar caption,#footer nav.wp-calendar-nav a,#footer .search-form .search-field,#footer h3{';
			$food_grocery_store_custom_css .='color:#000;';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='#footer ul li::before{';
			$food_grocery_store_custom_css .='background:#000;';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='#footer table, #footer th, #footer td,#footer .search-form .search-field,#footer .tagcloud a{';
			$food_grocery_store_custom_css .='border: 1px solid #000;';
		$food_grocery_store_custom_css .='}';

	}else if($food_grocery_store_theme_lay == 'food_grocery_store-footer-three'){
		$food_grocery_store_custom_css .='#footer{';
			$food_grocery_store_custom_css .='background: #232524;';
		$food_grocery_store_custom_css .='}';
	}
	else if($food_grocery_store_theme_lay == 'food_grocery_store-footer-four'){
		$food_grocery_store_custom_css .='#footer{';
			$food_grocery_store_custom_css .='background: #f7f7f7;';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='#footer p, #footer li a, #footer, #footer h3, #footer a.rsswidget, #footer #wp-calendar a, .copyright a, #footer .custom_details, #footer ins span, #footer .tagcloud a, .main-inner-box span.entry-date a, nav.woocommerce-MyAccount-navigation ul li:hover a, #footer ul li a, #footer table, #footer th, #footer td, #footer caption, #sidebar caption,#footer nav.wp-calendar-nav a,#footer .search-form .search-field{';
			$food_grocery_store_custom_css .='color:#000;';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='#footer ul li::before{';
			$food_grocery_store_custom_css .='background:#000;';
		$food_grocery_store_custom_css .='}';
		$food_grocery_store_custom_css .='#footer table, #footer th, #footer td,#footer .search-form .search-field,#footer .tagcloud a{';
			$food_grocery_store_custom_css .='border: 1px solid #000;';
		$food_grocery_store_custom_css .='}';
	}
	else if($food_grocery_store_theme_lay == 'food_grocery_store-footer-five'){
		$food_grocery_store_custom_css .='#footer{';
			$food_grocery_store_custom_css .='background: linear-gradient(to right, #01093a, #2d0b00);';
		$food_grocery_store_custom_css .='}';
	}

 	/*---------------- Footer Settings ------------------*/

	$food_grocery_store_button_footer_heading_letter_spacing = get_theme_mod('food_grocery_store_button_footer_heading_letter_spacing',1);
	$food_grocery_store_custom_css .='#footer h3, a.rsswidget.rss-widget-title{';
		$food_grocery_store_custom_css .='letter-spacing: '.esc_attr($food_grocery_store_button_footer_heading_letter_spacing).'px;';
	$food_grocery_store_custom_css .='}';

	$food_grocery_store_button_footer_font_size = get_theme_mod('food_grocery_store_button_footer_font_size','30');
	$food_grocery_store_custom_css .='#footer h3, a.rsswidget.rss-widget-title{';
		$food_grocery_store_custom_css .='font-size: '.esc_attr($food_grocery_store_button_footer_font_size).'px;';
	$food_grocery_store_custom_css .='}';

	$food_grocery_store_theme_lay = get_theme_mod( 'food_grocery_store_button_footer_text_transform','Capitalize');
	if($food_grocery_store_theme_lay == 'Capitalize'){
		$food_grocery_store_custom_css .='#footer h3{';
			$food_grocery_store_custom_css .='text-transform:Capitalize;';
		$food_grocery_store_custom_css .='}';
	}
	if($food_grocery_store_theme_lay == 'Lowercase'){
		$food_grocery_store_custom_css .='#footer h3, a.rsswidget.rss-widget-title{';
			$food_grocery_store_custom_css .='text-transform:Lowercase;';
		$food_grocery_store_custom_css .='}';
	}
	if($food_grocery_store_theme_lay == 'Uppercase'){
		$food_grocery_store_custom_css .='#footer h3, a.rsswidget.rss-widget-title{';
			$food_grocery_store_custom_css .='text-transform:Uppercase;';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_footer_heading_weight = get_theme_mod('food_grocery_store_footer_heading_weight','600');
	if($food_grocery_store_footer_heading_weight != false){
		$food_grocery_store_custom_css .='#footer h3, a.rsswidget.rss-widget-title{';
			$food_grocery_store_custom_css .='font-weight: '.esc_attr($food_grocery_store_footer_heading_weight).';';
		$food_grocery_store_custom_css .='}';
	}

	$food_grocery_store_resp_stickyheader = get_theme_mod( 'food_grocery_store_stickyheader_hide_show',false);
	if($food_grocery_store_resp_stickyheader == true && get_theme_mod( 'food_grocery_store_sticky_header',false) != true){
    	$food_grocery_store_custom_css .='.header-fixed{';
			$food_grocery_store_custom_css .='position:static;';
		$food_grocery_store_custom_css .='} ';
	}
    if($food_grocery_store_resp_stickyheader == true){
    	$food_grocery_store_custom_css .='@media screen and (max-width:575px) {';
		$food_grocery_store_custom_css .='.header-fixed{';
			$food_grocery_store_custom_css .='position:fixed;';
		$food_grocery_store_custom_css .='} }';
	}else if($food_grocery_store_resp_stickyheader == false){
		$food_grocery_store_custom_css .='@media screen and (max-width:575px){';
		$food_grocery_store_custom_css .='.header-fixed{';
			$food_grocery_store_custom_css .='position:static;';
		$food_grocery_store_custom_css .='} }';
	}

	$food_grocery_store_responsive_preloader_hide = get_theme_mod('food_grocery_store_responsive_preloader_hide',false);
	if($food_grocery_store_responsive_preloader_hide == true && get_theme_mod('food_grocery_store_loader_enable',false) == false){
		$food_grocery_store_custom_css .='@media screen and (min-width:575px){
			#preloader{';
			$food_grocery_store_custom_css .='display:none !important;';
		$food_grocery_store_custom_css .='} }';
	}

	if($food_grocery_store_responsive_preloader_hide == false){
		$food_grocery_store_custom_css .='@media screen and (max-width:575px){
			#preloader{';
			$food_grocery_store_custom_css .='display:none !important;';
		$food_grocery_store_custom_css .='} }';
	}

	$food_grocery_store_resp_topbar = get_theme_mod( 'food_grocery_store_resp_topbar_hide_show',true);
	if($food_grocery_store_resp_topbar == true && get_theme_mod( 'food_grocery_store_topbar_arrows', true) == false){
    	$food_grocery_store_custom_css .='.top-bar{';
			$food_grocery_store_custom_css .='display:none;';
		$food_grocery_store_custom_css .='} ';
	}
    if($food_grocery_store_resp_topbar == true){
    	$food_grocery_store_custom_css .='@media screen and (max-width:575px) {';
		$food_grocery_store_custom_css .='.top-bar{';
			$food_grocery_store_custom_css .='display:block;';
		$food_grocery_store_custom_css .='} }';
	}else if($food_grocery_store_resp_topbar == false){
		$food_grocery_store_custom_css .='@media screen and (max-width:575px) {';
		$food_grocery_store_custom_css .='.top-bar{';
			$food_grocery_store_custom_css .='display:none;';
		$food_grocery_store_custom_css .='} }';
	}


	$food_grocery_store_bradcrumbs_alignment = get_theme_mod( 'food_grocery_store_bradcrumbs_alignment','Left');
    if($food_grocery_store_bradcrumbs_alignment == 'Left'){
    	$food_grocery_store_custom_css .='@media screen and (min-width:768px) {';
		$food_grocery_store_custom_css .='.bradcrumbs{';
			$food_grocery_store_custom_css .='text-align:start;';
		$food_grocery_store_custom_css .='}}';
	}else if($food_grocery_store_bradcrumbs_alignment == 'Center'){
		$food_grocery_store_custom_css .='@media screen and (min-width:768px) {';
		$food_grocery_store_custom_css .='.bradcrumbs{';
			$food_grocery_store_custom_css .='text-align:center;';
		$food_grocery_store_custom_css .='}}';
	}else if($food_grocery_store_bradcrumbs_alignment == 'Right'){
		$food_grocery_store_custom_css .='@media screen and (min-width:768px) {';
		$food_grocery_store_custom_css .='.bradcrumbs{';
			$food_grocery_store_custom_css .='text-align:end;';
		$food_grocery_store_custom_css .='}}';
	}

	// sticky sidebar
		$food_grocery_store_resp_stickysidebar = get_theme_mod( 'food_grocery_store_stickysidebar_hide_show',false);
	if($food_grocery_store_resp_stickysidebar == true && get_theme_mod( 'food_grocery_store_sticky_sidebar',false) != true){
		$food_grocery_store_custom_css .= '@media (min-width: 768px) {';
    	$food_grocery_store_custom_css .='#sidebar{';
			$food_grocery_store_custom_css .='position:static;';
		$food_grocery_store_custom_css .='} ';
		$food_grocery_store_custom_css .= '}';
	}
    // First Letter Capital
	$food_grocery_store_show_first_caps = get_theme_mod('food_grocery_store_show_first_caps', false);
	if ($food_grocery_store_show_first_caps ) {
	    $food_grocery_store_custom_css .= '.post-main-box .new-text p:nth-of-type(1)::first-letter {';
	    $food_grocery_store_custom_css .=' font-size: 55px;font-weight: 600;margin-right: 5px;';
	    $food_grocery_store_custom_css .='}';
	} else {
		$food_grocery_store_custom_css .= '.post-main-box .new-text p:nth-of-type(1)::first-letter {';
	    $food_grocery_store_custom_css .= 'display: none;';
	    $food_grocery_store_custom_css .='}';
	}
	// Hover on featured image //
	$food_grocery_store_show_featured = get_theme_mod( 'food_grocery_store_featured_image_hide_show', 1 );
	$food_grocery_store_hover_effect  = get_theme_mod( 'food_grocery_store_featured_image_hover', 'none' );

	if ( $food_grocery_store_show_featured && $food_grocery_store_hover_effect !== 'none' ) {

		$food_grocery_store_custom_css .= '
		.food-grocery-store-featured-image img{
			transition: all 0.4s ease;
		}';

		if ( $food_grocery_store_hover_effect === 'zoom-in' ) {
			$food_grocery_store_custom_css .= '
			.food-grocery-store-featured-image:hover img{
				transform: scale(1.2);
			}';
		}

		if ( $food_grocery_store_hover_effect === 'zoom-out' ) {
			$food_grocery_store_custom_css .= '
			.food-grocery-store-featured-image img{
				transform: scale(1.2);
			}
			.food-grocery-store-featured-image:hover img{
				transform: scale(1);
			}';
		}

		if ( $food_grocery_store_hover_effect === 'grayscale' ) {
			$food_grocery_store_custom_css .= '
			.food-grocery-store-featured-image img{
				filter: grayscale(100%);
			}
			.food-grocery-store-featured-image:hover img{
				filter: grayscale(0);
			}';
		}

		if ( $food_grocery_store_hover_effect === 'sepia' ) {
			$food_grocery_store_custom_css .= '
			.food-grocery-store-featured-image:hover img{
				filter: sepia(100%);
			}';
		}

		if ( $food_grocery_store_hover_effect === 'blur' ) {
			$food_grocery_store_custom_css .= '
			.food-grocery-store-featured-image:hover img{
				filter: blur(3px);
			}';
		}

		if ( $food_grocery_store_hover_effect === 'bright' ) {
			$food_grocery_store_custom_css .= '
			.food-grocery-store-featured-image:hover img{
				filter: brightness(1.3);
			}';
		}

		if ( $food_grocery_store_hover_effect === 'translate' ) {
			$food_grocery_store_custom_css .= '
			.food-grocery-store-featured-image:hover img{
				transform: translateY(-10px);
			}';
		}
	}
