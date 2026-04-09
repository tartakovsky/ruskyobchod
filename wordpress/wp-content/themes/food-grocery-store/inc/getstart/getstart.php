<?php
/* Add to Dashboard main menu */
function food_grocery_store_gettingstarted() {
    add_menu_page(
        esc_html__( 'Food Grocery Store', 'food-grocery-store' ), // Page title
        esc_html__( 'Food Grocery Store', 'food-grocery-store' ), // Menu title
        'manage_options',                            // Capability
        'food_grocery_store_guide',                        // Menu slug
        'food_grocery_store_mostrar_guide',                // Callback
        get_template_directory_uri() . '/inc/getstart/images/menu-icon.svg', // Image icon
        59                                           // Position
    );
}
add_action( 'admin_menu', 'food_grocery_store_gettingstarted' );

// Add a Custom CSS file to WP Admin Area
function food_grocery_store_admin_theme_style() {
   wp_enqueue_style('food-grocery-store-custom-admin-style', esc_url(get_template_directory_uri()) . '/inc/getstart/getstart.css');
   wp_enqueue_script('food-grocery-store-tabs', esc_url(get_template_directory_uri()) . '/inc/getstart/js/tab.js');
	
   // Admin notice code START
	wp_register_script('food-grocery-store-notice', esc_url(get_template_directory_uri()) . '/inc/getstart/js/notice.js', array('jquery'), time(), true);
	wp_enqueue_script('food-grocery-store-notice');
	// Admin notice code END
}
add_action('admin_enqueue_scripts', 'food_grocery_store_admin_theme_style');

//guidline for about theme
function food_grocery_store_mostrar_guide() { 
	//custom function about theme customizer
	$return = add_query_arg( array()) ;
	$food_grocery_store_theme = wp_get_theme( 'food-grocery-store' );
?>

<div class="wrapper-info">
	<div class="tab-sec">
    	
    	<div class="tab">
    		<button class="tablinks" onclick="food_grocery_store_open_tab(event, 'theme_offer')"><?php esc_html_e( 'Demo Import', 'food-grocery-store' ); ?></button>
			<button class="tablinks" onclick="food_grocery_store_open_tab(event, 'lite_theme')"><?php esc_html_e( 'Setup With Customizer', 'food-grocery-store' ); ?></button>
			<button class="tablinks" onclick="food_grocery_store_open_tab(event, 'theme_pro')"><?php esc_html_e( 'Get Premium', 'food-grocery-store' ); ?></button>
  			<button class="tablinks" onclick="food_grocery_store_open_tab(event, 'free_pro')"><?php esc_html_e( 'Free VS Premium', 'food-grocery-store' ); ?></button>
  			<button class="tablinks" onclick="food_grocery_store_open_tab(event, 'get_bundle')"><?php esc_html_e( 'WP Theme Bundle', 'food-grocery-store' ); ?></button>
		</div>

		<?php 
			$food_grocery_store_plugin_custom_css = '';
			if(class_exists('Ibtana_Visual_Editor_Menu_Class')){
				$food_grocery_store_plugin_custom_css ='display: block';
			}
		?>

		<div id="theme_offer" class="tabcontent open">
			<div class="demo-content">
				<div class="demo-text">
					<?php 
					/* Get Started. */ 
					require get_theme_file_path( '/inc/getstart/demo-content.php' );
				 	?>
				</div>
				
			 	<img src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/getstart/images/responsive.png" alt="" class="resp-img" />
			</div> 	
		</div>
		<div id="lite_theme" class="tabcontent">
			<div class="lite-theme-tab" style="<?php echo esc_attr($food_grocery_store_plugin_custom_css); ?>">
				<h3><?php esc_html_e( 'Food Grocery Store', 'food-grocery-store' ); ?></h3>
				<hr class="h3hr">
				<p><?php esc_html_e('Food Grocery Store is an expertly crafted theme for online grocery stores, organic food stores, supermarket, food company, food market, food product, food shop, online vegetables, digital Storefront , shopkeeper, retail Store, E bazar, organic tea, juice shop, merchant, sausages, Bakery Products, Pet Food Delivery, environmental naturo eco, medicines, naturopathy, natural and organic medicinal herbs, soft drinks, hand tools store, Daily Household Items, Organic Foods, Dairy Products, Seafood & Meat Delivery, protective kits retailer,Retail, Grocery, E-commerce, Market, Food, e shoppay, boutiques, food packaging industry, farming products, agricultural products, digital marketplace, Electronics Store, online marketplace, vegetables vendor, organic farm, grocery markets, and supermarkets. Using the theme customization options, ready-to-use, responsive, and innovative you can tweak the layout and modify it for making it suitable to be used for food delivery websites as well. It establishes a sophisticated online website for taking your business to the next level as you can optimize the available resources to generate more profit. This theme is excellent to create mega store for beverages, vegetables, fruits, ice creams, herbs, juice, meat, cold drinks etc. All the products are clearly displayed on the website as it is retina ready and are presented in a categorized manner as this sophisticated theme has everything well organized. This modern theme has secure and optimized codes that work well and make your site deliver a stunning performance. The layout looks extremely professional and offers a wonderful slider for displaying product-related images. The interactive elements such as Call To Action (CTA) buttons, Custom Header, threaded comments, Full Width Template, will keep the shoppers and viewers engaged and guide them. It is able to handle large amounts of traffic easily as it has a robust design built using the Bootstrap framework. This user-friendly theme has an SEO friendly design that gets you more traffic. The WPML and RTL compatibility of this theme makes it translation ready.','food-grocery-store'); ?></p>
				<div class="lite-info">
					<div class="col-left-inner">
				  		<h4><?php esc_html_e( 'Theme Documentation', 'food-grocery-store' ); ?></h4>
						<p><?php esc_html_e( 'If you need any assistance regarding setting up and configuring the Theme, our documentation is there.', 'food-grocery-store' ); ?></p>
						<div class="info-link">
							<a href="<?php echo esc_url( FOOD_GROCERY_STORE_FREE_THEME_DOC ); ?>" target="_blank"> <?php esc_html_e( 'Documentation', 'food-grocery-store' ); ?></a>
						</div>
						<hr>
						<h4><?php esc_html_e('Theme Customizer', 'food-grocery-store'); ?></h4>
						<p> <?php esc_html_e('To begin customizing your website, start by clicking "Customize".', 'food-grocery-store'); ?></p>
						<div class="info-link">
							<a target="_blank" href="<?php echo esc_url( admin_url('customize.php') ); ?>"><?php esc_html_e('Customizing', 'food-grocery-store'); ?></a>
						</div>
						<hr>
						<h4><?php esc_html_e('Having Trouble, Need Support?', 'food-grocery-store'); ?></h4>
						<p> <?php esc_html_e('Our dedicated team is well prepared to help you out in case of queries and doubts regarding our theme.', 'food-grocery-store'); ?></p>
						<div class="info-link">
							<a href="<?php echo esc_url( FOOD_GROCERY_STORE_SUPPORT ); ?>" target="_blank"><?php esc_html_e('Support Forum', 'food-grocery-store'); ?></a>
						</div>
						<hr>
						<h4><?php esc_html_e('Reviews & Testimonials', 'food-grocery-store'); ?></h4>
						<p> <?php esc_html_e('All the features and aspects of this WordPress Theme are phenomenal. I\'d recommend this theme to all.', 'food-grocery-store'); ?></p>
						<div class="info-link">
							<a href="<?php echo esc_url( FOOD_GROCERY_STORE_REVIEW ); ?>" target="_blank"><?php esc_html_e('Reviews', 'food-grocery-store'); ?></a>
						</div>

						<div class="link-customizer">
							<h4><?php esc_html_e( 'Link to customizer', 'food-grocery-store' ); ?></h4>
							<div class="first-row">
								<div class="row-box">
									<div class="row-box1">
										<span class="dashicons dashicons-buddicons-buddypress-logo"></span><a href="<?php echo esc_url( admin_url('customize.php?autofocus[control]=custom_logo') ); ?>" target="_blank"><?php esc_html_e('Upload your logo','food-grocery-store'); ?></a>
									</div>
									<div class="row-box2">
										<span class="dashicons dashicons-category"></span><a href="<?php echo esc_url( admin_url('customize.php?autofocus[section]=food_grocery_store_top_bar') ); ?>" target="_blank"><?php esc_html_e('Header','food-grocery-store'); ?></a>
									</div>
								</div>

								<div class="row-box">
									<div class="row-box1">
										<span class="dashicons dashicons-slides"></span><a href="<?php echo esc_url( admin_url('customize.php?autofocus[section]=food_grocery_store_slider_section') ); ?>" target="_blank"><?php esc_html_e('Slider Settings','food-grocery-store'); ?></a>
									</div>
									<div class="row-box2">
										<span class="dashicons dashicons-category"></span><a href="<?php echo esc_url( admin_url('customize.php?autofocus[section]=food_grocery_store_top_charts_section') ); ?>" target="_blank"><?php esc_html_e('Top Charts Section','food-grocery-store'); ?></a>
									</div>
								</div>
							
								<div class="row-box">
									<div class="row-box1">
										<span class="dashicons dashicons-category"></span><a href="<?php echo esc_url( admin_url('customize.php?autofocus[section]=food_grocery_store_right_sidebar_section') ); ?>" target="_blank"><?php esc_html_e('Right Sidebar Section','food-grocery-store'); ?></a>
									</div>
									<div class="row-box2">
										<span class="dashicons dashicons-menu"></span><a href="<?php echo esc_url( admin_url('customize.php?autofocus[panel]=nav_menus') ); ?>" target="_blank"><?php esc_html_e('Menus','food-grocery-store'); ?></a>
									</div>
								</div>
								
								<div class="row-box">
									<div class="row-box1">
										<span class="dashicons dashicons-admin-generic"></span><a href="<?php echo esc_url( admin_url('customize.php?autofocus[section]=food_grocery_store_left_right') ); ?>" target="_blank"><?php esc_html_e('General Settings','food-grocery-store'); ?></a>
									</div>
									<div class="row-box2">
										<span class="dashicons dashicons-format-gallery"></span><a href="<?php echo esc_url( admin_url('customize.php?autofocus[section]=food_grocery_store_post_settings') ); ?>" target="_blank"><?php esc_html_e('Post settings','food-grocery-store'); ?></a>
									</div>
								</div>

								<div class="row-box">
									<div class="row-box1">
										<span class="dashicons dashicons-screenoptions"></span><a href="<?php echo esc_url( admin_url('customize.php?autofocus[panel]=widgets') ); ?>" target="_blank"><?php esc_html_e('Footer Widget','food-grocery-store'); ?></a>
									</div>
									<div class="row-box2">
										<span class="dashicons dashicons-text-page"></span><a href="<?php echo esc_url( admin_url('customize.php?autofocus[section]=food_grocery_store_footer') ); ?>" target="_blank"><?php esc_html_e('Footer Text','food-grocery-store'); ?></a>
									</div>
								</div>
							</div>
						</div>
				  	</div>
					<div class="col-right-inner">
						<h4 class="page-template"><?php esc_html_e('How to set up Home Page Template','food-grocery-store'); ?></h4>
						<p><?php esc_html_e('Follow these instructions to setup Home page.','food-grocery-store'); ?></p>
	                  	<p><span class="strong"><?php esc_html_e('1. Create a new page :','food-grocery-store'); ?></span><?php esc_html_e(' Go to ','food-grocery-store'); ?>
						  	<b><?php esc_html_e(' Dashboard >> Pages >> Add New Page','food-grocery-store'); ?></b></p>
	                  	<p><?php esc_html_e('Name it as "Home" then select the template "Custom Home Page".','food-grocery-store'); ?></p>
	                  	<img src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/getstart/images/home-page-template.png" alt="" />
	                  	<p><span class="strong"><?php esc_html_e('2. Set the front page:','food-grocery-store'); ?></span><?php esc_html_e(' Go to ','food-grocery-store'); ?>
						  	<b><?php esc_html_e(' Settings >> Reading ','food-grocery-store'); ?></b></p>
					  	<p><?php esc_html_e('Select the option of Static Page, now select the page you created to be the homepage, while another page to be your default page.','food-grocery-store'); ?></p>
	                  	<img src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/getstart/images/set-front-page.png" alt="" />
	                  	<p><?php esc_html_e(' Once you are done with setup, then follow the','food-grocery-store'); ?> <a class="doc-links" href="<?php echo esc_url( FOOD_GROCERY_STORE_FREE_THEME_DOC ); ?>" target="_blank"><?php esc_html_e('Documentation','food-grocery-store'); ?></a></p>
				  	</div>

				</div>
			  	
			</div>
		</div>

		<div id="theme_pro" class="tabcontent">		  	
			<div class="pro-info">
				<div class="col-left-pro">
					<h3><?php esc_html_e( 'Premium Theme Information', 'food-grocery-store' ); ?></h3>
					<hr class="h3hr">
			    	<p><?php esc_html_e('Build a fabulous website for your grocery supermarket using this Grocery Store WordPress Theme. It is suitable for establishing a prominent website for selling groceries online as it provides a good opportunity for fruit and vegetable dealers as well as food store owners to build a big customer base for getting more sales and profit. The theme is so well crafted for creating an ideal website that will draw the attention of the audience and potential customers. The homepage is perfect for displaying all the products as you get properly designed sections for projecting all the relevant information. Its full-width slider gives a splendid display of images related to your grocery store. This WP Grocery Store WordPress Theme also provides slider settings. There are plenty of layout choices for you along with the different arrangements of sidebars. The footer is also editable and can be widgetized as you get a number of widgets included in the theme.','food-grocery-store'); ?></p>
			    	<div class="pro-links">
				    	<a href="<?php echo esc_url( FOOD_GROCERY_STORE_LIVE_DEMO ); ?>" target="_blank" class="demo-btn"><?php esc_html_e('Live Demo', 'food-grocery-store'); ?></a>
						<a href="<?php echo esc_url( FOOD_GROCERY_STORE_BUY_NOW ); ?>" target="_blank" class="prem-btn"><?php esc_html_e('Buy Premium', 'food-grocery-store'); ?></a>
						<a href="<?php echo esc_url( FOOD_GROCERY_STORE_PRO_DOC ); ?>" target="_blank" class="doc-btn"><?php esc_html_e('Documentation', 'food-grocery-store'); ?></a>
					</div>
			    </div>
			    <div class="col-right-pro scroll-image-wrapper">
			    	<img src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/getstart/images/pro-theme.jpg" alt="" class="pro-img" />		    	
			    </div>
			</div>		    
		</div>

		<div id="free_pro" class="tabcontent">
		  	<div class="featurebox">
			    <h3><?php esc_html_e( 'Theme Features', 'food-grocery-store' ); ?></h3>
				<hr class="h3hr">
				<div class="table-image">
					<table class="tablebox">
						<thead>
							<tr>
								<th> <?php esc_html_e('Features', 'food-grocery-store'); ?></th>
								<th><?php esc_html_e('Free Themes', 'food-grocery-store'); ?></th>
								<th><?php esc_html_e('Premium Themes', 'food-grocery-store'); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?php esc_html_e('Theme Customization', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Responsive Design', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Logo Upload', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Social Media Links', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Banner Settings', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Template Pages', 'food-grocery-store'); ?></td>
								<td class="table-img"><?php esc_html_e('3', 'food-grocery-store'); ?></td>
								<td class="table-img"><?php esc_html_e('10', 'food-grocery-store'); ?></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Home Page Template', 'food-grocery-store'); ?></td>
								<td class="table-img"><?php esc_html_e('1', 'food-grocery-store'); ?></td>
								<td class="table-img"><?php esc_html_e('1', 'food-grocery-store'); ?></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Theme sections', 'food-grocery-store'); ?></td>
								<td class="table-img"><?php esc_html_e('2', 'food-grocery-store'); ?></td>
								<td class="table-img"><?php esc_html_e('13', 'food-grocery-store'); ?></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Contact us Page Template / Support Templates', 'food-grocery-store'); ?></td>
								<td class="table-img">0</td>
								<td class="table-img"><?php esc_html_e('1', 'food-grocery-store'); ?></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Blog Templates & Layout', 'food-grocery-store'); ?></td>
								<td class="table-img">0</td>
								<td class="table-img"><?php esc_html_e('3(Full width/Left/Right Sidebar)', 'food-grocery-store'); ?></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Page Templates & Layout', 'food-grocery-store'); ?></td>
								<td class="table-img">0</td>
								<td class="table-img"><?php esc_html_e('3(Left/Right Sidebar)', 'food-grocery-store'); ?></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Color Pallete For Particular Sections', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Global Color Option', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Section Reordering', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Demo Importer', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Allow To Set Site Title, Tagline, Logo', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Enable Disable Options On All Sections, Logo', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Full Documentation', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Latest WordPress Compatibility', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Support 3rd Party Plugins', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Secure and Optimized Code', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Exclusive Functionalities', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Section Enable / Disable', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Section Google Font Choices', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Video Gallery', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Simple & Mega Menu Option', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Support to add custom CSS / JS ', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Shortcodes', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Custom Background, Colors, Header, Logo & Menu', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Premium Membership', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Budget Friendly Value', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('Priority Error Fixing', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Custom Feature Addition', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr class="odd">
								<td><?php esc_html_e('All Access Theme Pass', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Seamless Customer Support', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Food Grocery Store ', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Detail Services', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('About Business Page', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Team Member Page', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Project Description Page', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e('Support Page', 'food-grocery-store'); ?></td>
								<td class="table-img"><span class="dashicons dashicons-no"></span></td>
								<td class="table-img"><span class="dashicons dashicons-saved"></span></td>
							</tr>
							<tr>
								<td></td>
								<td class="table-img"></td>
								<td class="update-link"><a href="<?php echo esc_url( FOOD_GROCERY_STORE_BUY_NOW ); ?>" target="_blank"><?php esc_html_e('Upgrade to Pro', 'food-grocery-store'); ?></a></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div id="get_bundle" class="tabcontent">	
			<div class="bundle-info">
				<div class="col-left-pro">
			   		<h3><?php esc_html_e( 'WP Theme Bundle', 'food-grocery-store' ); ?></h3>
			   		<hr class="h3hr">
			    	<p><?php esc_html_e('Enhance your website effortlessly with our WP Theme Bundle. Get access to 400+ premium WordPress themes and 5+ powerful plugins, all designed to meet diverse business needs. Enjoy seamless integration with any plugins, ultimate customization flexibility, and regular updates to keep your site current and secure. Plus, benefit from our dedicated customer support, ensuring a smooth and professional web experience.','food-grocery-store'); ?></p>
			    	<div class="feature">
			    		<h4><?php esc_html_e( 'Features:', 'food-grocery-store' ); ?></h4>
			    		<p><img src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/getstart/images/tick.png" alt="" /><?php esc_html_e('400+ Premium Themes & 5+ Plugins.', 'food-grocery-store'); ?></p>
			    		<p><img src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/getstart/images/tick.png" alt="" /><?php esc_html_e('Seamless Integration.', 'food-grocery-store'); ?></p>
			    		<p><img src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/getstart/images/tick.png" alt="" /><?php esc_html_e('Customization Flexibility.', 'food-grocery-store'); ?></p>
			    		<p><img src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/getstart/images/tick.png" alt="" /><?php esc_html_e('Regular Updates.', 'food-grocery-store'); ?></p>
			    		<p><img src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/getstart/images/tick.png" alt="" /><?php esc_html_e('Dedicated Support.', 'food-grocery-store'); ?></p>
			    	</div>
			    	<p><?php esc_html_e('Upgrade now and give your website the professional edge it deserves, all at an unbeatable price of $99!', 'food-grocery-store'); ?></p>
			    	<div class="pro-links">
						<a href="<?php echo esc_url( FOOD_GROCERY_STORE_THEME_BUNDLE_BUY_NOW ); ?>" target="_blank" class="bundle-buy"><?php esc_html_e('Get Bundle', 'food-grocery-store'); ?></a>
						<a href="<?php echo esc_url( FOOD_GROCERY_STORE_THEME_BUNDLE_DOC ); ?>" target="_blank" class="bundle-doc"><?php esc_html_e('Documentation', 'food-grocery-store'); ?></a>
					</div>
			   	</div>
			   	<div class="col-right-pro scroll-image-wrapper">
			    	<img src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/getstart/images/bundle.jpg" alt="" />
			   	</div>
			</div>	  	
		   			    
		</div>
	</div>
	<div class="coupen-code-section">
		<div class="sshot-section">
			<div class="sshot-inner">
				<h2><?php esc_html_e( 'Welcome To Food Grocery Store,', 'food-grocery-store' ); ?> </h2>
				<div class="on-pro">
					<span class="version"><?php esc_html_e( 'Version', 'food-grocery-store' ); ?>: <?php echo esc_html($food_grocery_store_theme['Version']);?></span>
					<span class="coupon-code"><?php esc_html_e('Get 20% Of On Pro Theme-Use Code: ','food-grocery-store'); ?><span class="code-highlight"><?php esc_html_e('VWPRO20','food-grocery-store'); ?></span>
				</div>
		    	<p><?php esc_html_e('All Our Wordpress Themes Are Modern, Minimalist, 100% Responsive, Seo-Friendly,Feature-Rich, And Multipurpose That Best Suit Designers, Bloggers And Other Professionals Who Are Working In The Creative Fields.','food-grocery-store'); ?></p>
		    	<div class="btn-section">
			    	<div class="proo-links">
				    	<a href="<?php echo esc_url( FOOD_GROCERY_STORE_LIVE_DEMO ); ?>" target="_blank" class="demo-btn"><?php esc_html_e('Live Demo', 'food-grocery-store'); ?></a>
						<a href="<?php echo esc_url( FOOD_GROCERY_STORE_BUY_NOW ); ?>" target="_blank" class="prem-btn"><?php esc_html_e('Buy Premium', 'food-grocery-store'); ?></a>
						<a href="<?php echo esc_url( FOOD_GROCERY_STORE_PRO_DOC ); ?>" target="_blank" class="doc-btn"><?php esc_html_e('Documentation', 'food-grocery-store'); ?></a>
						
					</div>
			    	
			    </div>
			</div>
	    	<div class="bundle-banner">
	    		<div class="bundle-img">
	    			<img src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/getstart/images/bundle-notice.png" alt="" />
	    		</div>
	    		<div class="bundle-text">
		  			<h2><?php esc_html_e('WP THEME BUNDLE','food-grocery-store'); ?></h2>
					<h4><?php esc_html_e('Get Access to 400+ Premium WordPress Themes At Just $99','food-grocery-store'); ?></h4>
					<div class="bundle-button">
			  			<a href="<?php echo esc_url( 'https://www.vwthemes.com/discount/FREEBREF?redirect=/products/wp-theme-bundle'); ?>" target="_blank"><?php esc_html_e('Get 10% OFF On Bundle', 'food-grocery-store'); ?></a>
			  		</div>
		  		</div>
		  		
	    	</div>
	    </div>
	    <div class="coupen-section">
	    	<div class="logo-section">
			  	<img src="<?php echo esc_url(get_template_directory_uri()); ?>/screenshot.png" alt="" />
		  	</div>
		  	<div class="logo-right">	
		  		<div class="logo-text">
		  			<h2><?php esc_html_e('GET PRO','food-grocery-store'); ?></h2>
					<h4><?php esc_html_e('20% Off','food-grocery-store'); ?></h4>
		  		</div>						
			</div>
	    </div>
	</div>
      
</div>
<?php } ?>