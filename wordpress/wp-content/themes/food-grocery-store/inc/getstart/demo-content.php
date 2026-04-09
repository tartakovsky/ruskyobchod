<div class="theme-offer">
	<?php
        // Check if the demo import has been completed
        $food_grocery_store_demo_import_completed = get_option('food_grocery_store_demo_import_completed', false);

        // If the demo import is completed, display the "View Site" button
        if ($food_grocery_store_demo_import_completed) {
        echo '<p class="notice-text">' . esc_html__('Your demo import has been completed successfully.', 'food-grocery-store') . '</p>';
        echo '<span><a href="' . esc_url(home_url()) . '" class="button button-primary site-btn" target="_blank">' . esc_html__('View Site', 'food-grocery-store') . '</a></span>';
        echo '<span><a href="'. esc_url(admin_url('customize.php') ) .'" class="button button-primary demo-btn" target=_blank>'. esc_html__( 'Customize Your Site', 'food-grocery-store' ) .'</a></span>';
        echo '<span><a href="'. esc_url( 'https://preview.vwthemesdemo.com/docs/free-food-grocery-store/' ) .'" class="button button-primary doc-btn" target=_blank>'. esc_html__( 'Free Theme Documentation', 'food-grocery-store' ) .'</a></span>';
    }

		//POST and update the customizer and other related data of POLITICAL CAMPAIGN
        if (isset($_POST['submit'])) {

            // Check if ibtana visual editor is installed and activated
            if (!is_plugin_active('ibtana-visual-editor/plugin.php')) {
              // Install the plugin if it doesn't exist
              $food_grocery_store_plugin_slug = 'ibtana-visual-editor';
              $food_grocery_store_plugin_file = 'ibtana-visual-editor/plugin.php';

              // Check if plugin is installed
              $food_grocery_store_installed_plugins = get_plugins();
              if (!isset($food_grocery_store_installed_plugins[$food_grocery_store_plugin_file])) {
                  include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
                  include_once(ABSPATH . 'wp-admin/includes/file.php');
                  include_once(ABSPATH . 'wp-admin/includes/misc.php');
                  include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

                  // Install the plugin
                  $food_grocery_store_upgrader = new Plugin_Upgrader();
                  $food_grocery_store_upgrader->install('https://downloads.wordpress.org/plugin/ibtana-visual-editor.latest-stable.zip');
              }
              // Activate the plugin
              activate_plugin($food_grocery_store_plugin_file);
            }

             // Check if woocommerce is installed and activated
            if (!is_plugin_active('woocommerce/woocommerce.php')) {
                // Install the plugin if it doesn't exist
                $food_grocery_store_plugin_slug = 'woocommerce';
                $food_grocery_store_plugin_file = 'woocommerce/woocommerce.php';
    
                // Check if plugin is installed
                $food_grocery_store_installed_plugins = get_plugins();
                if (!isset($food_grocery_store_installed_plugins[$food_grocery_store_plugin_file])) {
                    include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
                    include_once(ABSPATH . 'wp-admin/includes/file.php');
                    include_once(ABSPATH . 'wp-admin/includes/misc.php');
                    include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
    
                    // Install the plugin
                    $food_grocery_store_upgrader = new Plugin_Upgrader();
                    $food_grocery_store_upgrader->install('https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip');
                }
                // Activate the plugin
                activate_plugin($food_grocery_store_plugin_file);
            }
  

            // Check if  Currency Switcher for WooCommerce is installed and activated
            if (!is_plugin_active('currency-switcher-woocommerce/currency-switcher-woocommerce.php')) {
              // Install the plugin if it doesn't exist
              $food_grocery_store_plugin_slug = 'currency-switcher-woocommerce';
              $food_grocery_store_plugin_file = 'currency-switcher-woocommerce/currency-switcher-woocommerce.php';

              // Check if plugin is installed
              $food_grocery_store_installed_plugins = get_plugins();
              if (!isset($food_grocery_store_installed_plugins[$food_grocery_store_plugin_file])) {
                  include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
                  include_once(ABSPATH . 'wp-admin/includes/file.php');
                  include_once(ABSPATH . 'wp-admin/includes/misc.php');
                  include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

                  // Install the plugin
                  $food_grocery_store_upgrader = new Plugin_Upgrader();
                  $food_grocery_store_upgrader->install('https://downloads.wordpress.org/plugin/currency-switcher-woocommerce.latest-stable.zip');
              }
              // Activate the plugin
              activate_plugin($food_grocery_store_plugin_file);
            }

            // Check if  GTranslate is installed and activated
            if (!is_plugin_active('gtranslate/gtranslate.php')) {
              // Install the plugin if it doesn't exist
              $food_grocery_store_plugin_slug = 'gtranslate';
              $food_grocery_store_plugin_file = 'gtranslate/gtranslate.php';

              // Check if plugin is installed
              $food_grocery_store_installed_plugins = get_plugins();
              if (!isset($food_grocery_store_installed_plugins[$food_grocery_store_plugin_file])) {
                  include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
                  include_once(ABSPATH . 'wp-admin/includes/file.php');
                  include_once(ABSPATH . 'wp-admin/includes/misc.php');
                  include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

                  // Install the plugin
                  $food_grocery_store_upgrader = new Plugin_Upgrader();
                  $food_grocery_store_upgrader->install('https://downloads.wordpress.org/plugin/gtranslate.latest-stable.zip');
              }
              // Activate the plugin
              activate_plugin($food_grocery_store_plugin_file);
            }



            // ------- Create Nav Menu --------
            $food_grocery_store_menuname = 'Main Menus';
            $food_grocery_store_bpmenulocation = 'primary';
            $food_grocery_store_menu_exists = wp_get_nav_menu_object($food_grocery_store_menuname);

            if (!$food_grocery_store_menu_exists) {
                $food_grocery_store_menu_id = wp_create_nav_menu($food_grocery_store_menuname);

                // Create Home Page
                $food_grocery_store_home_title = 'Home';
                $food_grocery_store_home = array(
                    'post_type' => 'page',
                    'post_title' => $food_grocery_store_home_title,
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_slug' => 'home'
                );
                $food_grocery_store_home_id = wp_insert_post($food_grocery_store_home);
                // Assign Home Page Template
                add_post_meta($food_grocery_store_home_id, '_wp_page_template', 'page-template/custom-home-page.php');
                // Update options to set Home Page as the front page
                update_option('page_on_front', $food_grocery_store_home_id);
                update_option('show_on_front', 'page');
                // Add Home Page to Menu
                wp_update_nav_menu_item($food_grocery_store_menu_id, 0, array(
                    'menu-item-title' => __('Home', 'food-grocery-store'),
                    'menu-item-classes' => 'home',
                    'menu-item-url' => home_url('/'),
                    'menu-item-status' => 'publish',
                    'menu-item-object-id' => $food_grocery_store_home_id,
                    'menu-item-object' => 'page',
                    'menu-item-type' => 'post_type'
                ));
                             
                    // FRESH VEGETABLES
                   
                    $food_grocery_store_fresh_vegetables_title = 'Fresh Vegetables';
                    $food_grocery_store_fresh_vegetables_content = '
                    <p>Discover our wide range of fresh vegetables sourced daily.</p>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                    <p>Lorem Ipsum has been the industry standard dummy text ever since the 1500s.</p>
                    <p>It has survived not only five centuries but also the leap into electronic typesetting.</p>
                    <p>Remaining essentially unchanged throughout the years.</p>
                    <p>Popularised in the 1960s with the release of Letraset sheets.</p>
                    <p>Used today in modern digital publishing platforms.</p>
                    ';

                    $food_grocery_store_fresh_vegetables_page_id = wp_insert_post(array(
                        'post_type'   => 'page',
                        'post_title'  => $food_grocery_store_fresh_vegetables_title,
                        'post_content'=> $food_grocery_store_fresh_vegetables_content,
                        'post_status' => 'publish',
                        'post_author' => 1,
                        'post_name'   => 'fresh-vegetables'
                    ));

                    wp_update_nav_menu_item($food_grocery_store_food_grocery_store_menu_id, 0, array(
                        'menu-item-title' => __('Fresh Vegetables', 'food-grocery-store'),
                        'menu-item-url'   => home_url('/fresh-vegetables/'),
                        'menu-item-status'=> 'publish',
                        'menu-item-object-id' => $food_grocery_store_fresh_vegetables_page_id,
                        'menu-item-object' => 'page',
                        'menu-item-type'   => 'post_type'
                    ));

                   // ORGANIC FOODS
                   
                    $food_grocery_store_organic_foods_title = 'Organic Foods';
                    $food_grocery_store_organic_foods_content = '
                    <p>Explore our certified organic food collection.</p>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                    <p>Standard dummy text used since the 1500s.</p>
                    <p>Survived centuries without losing its original form.</p>
                    <p>Adopted widely in health and organic industries.</p>
                    <p>Ensures readability and visual balance.</p>
                    <p>Perfect for showcasing organic food products.</p>
                    ';

                    $food_grocery_store_organic_foods_page_id = wp_insert_post(array(
                        'post_type'   => 'page',
                        'post_title'  => $food_grocery_store_organic_foods_title,
                        'post_content'=> $food_grocery_store_organic_foods_content,
                        'post_status' => 'publish',
                        'post_author' => 1,
                        'post_name'   => 'organic-foods'
                    ));

                    wp_update_nav_menu_item($food_grocery_store_menu_id, 0, array(
                        'menu-item-title' => __('Organic Foods', 'food-grocery-store'),
                        'menu-item-url'   => home_url('/organic-foods/'),
                        'menu-item-status'=> 'publish',
                        'menu-item-object-id' => $food_grocery_store_organic_foods_page_id,
                        'menu-item-object' => 'page',
                        'menu-item-type'   => 'post_type'
                    ));
                   
                    // DIET READY FOODS & MORE
                   
                    $food_grocery_store_diet_ready_foods_title = 'Diet Ready Foods & More';
                    $food_grocery_store_diet_ready_foods_content = '
                    <p>Healthy and ready-to-eat diet food options.</p>
                    <p>Lorem Ipsum is simply dummy text of the printing industry.</p>
                    <p>Used to display content layout and structure.</p>
                    <p>Popular among diet and nutrition websites.</p>
                    <p>Maintains visual consistency across pages.</p>
                    <p>Helps users understand content flow.</p>
                    <p>Ideal for diet-focused product showcases.</p>
                    ';

                    $food_grocery_store_diet_ready_foods_page_id = wp_insert_post(array(
                        'post_type'   => 'page',
                        'post_title'  => $food_grocery_store_diet_ready_foods_title,
                        'post_content'=> $food_grocery_store_diet_ready_foods_content,
                        'post_status' => 'publish',
                        'post_author' => 1,
                        'post_name'   => 'diet-ready-foods'
                    ));

                    wp_update_nav_menu_item($food_grocery_store_menu_id, 0, array(
                        'menu-item-title' => __('Diet Ready Foods & More', 'food-grocery-store'),
                        'menu-item-url'   => home_url('/diet-ready-foods/'),
                        'menu-item-status'=> 'publish',
                        'menu-item-object-id' => $food_grocery_store_diet_ready_foods_page_id,
                        'menu-item-object' => 'page',
                        'menu-item-type'   => 'post_type'
                    ));
                 
                    // HERBAL JUICE
                   
                    $food_grocery_store_herbal_juice_title = 'Herbal Juice';
                    $food_grocery_store_herbal_juice_content = '
                    <p>Natural herbal juices made from selected ingredients.</p>
                    <p>Lorem Ipsum is simply dummy text of the printing industry.</p>
                    <p>Helps present product-related information clearly.</p>
                    <p>Used for layout testing and content planning.</p>
                    <p>Trusted by designers and developers worldwide.</p>
                    <p>Supports clean and readable page designs.</p>
                    <p>Perfect for health and wellness brands.</p>
                    ';

                    $food_grocery_store_herbal_juice_page_id = wp_insert_post(array(
                        'post_type'   => 'page',
                        'post_title'  => $food_grocery_store_herbal_juice_title,
                        'post_content'=> $food_grocery_store_herbal_juice_content,
                        'post_status' => 'publish',
                        'post_author' => 1,
                        'post_name'   => 'herbal-juice'
                    ));

                    wp_update_nav_menu_item($food_grocery_store_menu_id, 0, array(
                        'menu-item-title' => __('Herbal Juice', 'food-grocery-store'),
                        'menu-item-url'   => home_url('/herbal-juice/'),
                        'menu-item-status'=> 'publish',
                        'menu-item-object-id' => $food_grocery_store_herbal_juice_page_id,
                        'menu-item-object' => 'page',
                        'menu-item-type'   => 'post_type'
                    ));


                // Create About Us Page with Dummy Content
                $food_grocery_store_about_title = 'About Us';
                $food_grocery_store_about_content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam...<br>

                         Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960 with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.<br>

                            There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which dont look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isnt anything embarrassing hidden in the middle of text.<br>

                            All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.';
                $food_grocery_store_about = array(
                    'post_type' => 'page',
                    'post_title' => $food_grocery_store_about_title,
                    'post_content' => $food_grocery_store_about_content,
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_slug' => 'about-us'
                );
                $food_grocery_store_about_id = wp_insert_post($food_grocery_store_about);
                // Add About Us Page to Menu
                wp_update_nav_menu_item($food_grocery_store_menu_id, 0, array(
                    'menu-item-title' => __('About Us', 'food-grocery-store'),
                    'menu-item-classes' => 'about-us',
                    'menu-item-url' => home_url('/about-us/'),
                    'menu-item-status' => 'publish',
                    'menu-item-object-id' => $food_grocery_store_about_id,
                    'menu-item-object' => 'page',
                    'menu-item-type' => 'post_type'
                ));


                // Set the menu location if it's not already set
                if (!has_nav_menu($food_grocery_store_bpmenulocation)) {
                    $locations = get_theme_mod('nav_menu_locations'); // Use 'nav_menu_locations' to get locations array
                    if (empty($locations)) {
                        $locations = array();
                    }
                    $locations[$food_grocery_store_bpmenulocation] = $food_grocery_store_menu_id;
                    set_theme_mod('nav_menu_locations', $locations);
                }

            }


            // Set the demo import completion flag
    		update_option('food_grocery_store_demo_import_completed', true);
    		// Display success message and "View Site" button
    		echo '<p class="notice-text">' . esc_html__('Your demo import has been completed successfully.', 'food-grocery-store') . '</p>';
    		echo '<span><a href="' . esc_url(home_url()) . '" class="button button-primary site-btn" target="_blank">' . esc_html__('View Site', 'food-grocery-store') . '</a></span>';
            echo '<span><a href="'. esc_url(admin_url('customize.php') ) .'" class="button button-primary demo-btn" target=_blank>'. esc_html__( 'Customize Your Site', 'food-grocery-store' ) .'</a></span>';
            echo '<span><a href="'. esc_url( 'https://preview.vwthemesdemo.com/docs/free-vw-food-grocery-store/' ) .'" class="button button-primary doc-btn" target=_blank>'. esc_html__( 'Free Theme Documentation', 'food-grocery-store' ) .'</a></span>';
            //end

            // Top Bar //
          
            set_theme_mod( 'food_grocery_store_daily_deals_text', 'Daily Deals' );
            set_theme_mod( 'food_grocery_store_daily_deals_link', '#' );
            set_theme_mod( 'food_grocery_store_dailydeals_icon', 'fas fa-rss' );
            set_theme_mod( 'food_grocery_store_contact_text', 'Help & Contact' );
            set_theme_mod( 'food_grocery_store_contact_link', '#' );
            set_theme_mod( 'food_grocery_store_helpdesk_icon', 'fas fa-headphones' );
            set_theme_mod( 'food_grocery_store_order_icon', 'fas fa-map-marker-alt' );
            set_theme_mod( 'food_grocery_store_phone_number', '+00 123 456 7890' );
            set_theme_mod( 'food_grocery_store_myaccount_icon', 'fas fa-sign-in-alt' );
            set_theme_mod( 'food_grocery_store_shopping_icon', 'fas fa-shopping-basket' );
            set_theme_mod( 'food_grocery_store_heart_icon', 'far fa-heart' );
          
            // slider section start //
            set_theme_mod( 'food_grocery_store_slider_button_text', 'Shop Now' );
            set_theme_mod( 'food_grocery_store_top_button_url', '#' );
            

            for($food_grocery_store_i=1;$food_grocery_store_i<=3;$food_grocery_store_i++){
               $food_grocery_store_slider_title = 'Lorem Ipsum is simply dummy text of the printing';
               $food_grocery_store_slider_content = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry.';
                  // Create post object
               $my_post = array(
               'post_title'    => wp_strip_all_tags( $food_grocery_store_slider_title ),
               'post_content'  => $food_grocery_store_slider_content,
               'post_status'   => 'publish',
               'post_type'     => 'page',
               );

               // Insert the post into the database
               $food_grocery_store_post_id = wp_insert_post( $my_post );

               if ($food_grocery_store_post_id) {
                 // Set the theme mod for the slider page
                set_theme_mod('food_grocery_store_slider_page' . $food_grocery_store_i, $food_grocery_store_post_id);

                $food_grocery_store_image_url = get_template_directory_uri().'/assets/images/slider'.$food_grocery_store_i.'.png';

                $food_grocery_store_image_id = media_sideload_image($food_grocery_store_image_url, $food_grocery_store_post_id, null, 'id');

                    if (!is_wp_error($food_grocery_store_image_id)) {
                        // Set the downloaded image as the post's featured image
                        set_post_thumbnail($food_grocery_store_post_id, $food_grocery_store_image_id);
                    }
                }
            }



            // products //

            $food_grocery_store_title_array = array(
                array("Product Title Here",
                      "Product Title Here",
                      "Product Title Here",
                      "Product Title Here")
                );

            foreach ($food_grocery_store_title_array as $food_grocery_store_titles) {
                // Loop to create only 3 products
                for ($food_grocery_store_i = 0; $food_grocery_store_i < 4; $food_grocery_store_i++) {
                    // Create product content
                    $food_grocery_store_title = $food_grocery_store_titles[$food_grocery_store_i];
                    $food_grocery_store_content = 'Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.';

                    // Create product post object
                    $food_grocery_store_my_post = array(
                        'post_title'    => wp_strip_all_tags($food_grocery_store_title),
                        'post_content'  => $food_grocery_store_content,
                        'post_status'   => 'publish',
                        'post_type'     => 'product',
                    );
                    set_theme_mod('food_grocery_store_product_settings', esc_url($food_grocery_store_post_id));
                    // Insert the product into the database
                    $food_grocery_store_post_id = wp_insert_post($food_grocery_store_my_post);

                    if (is_wp_error($food_grocery_store_post_id)) {
                        error_log('Error creating product: ' . $food_grocery_store_post_id->get_error_message());
                        continue; // Skip to the next product if creation fails
                    }

                    // Add product meta (price, etc.)
                    update_post_meta($food_grocery_store_post_id, '_regular_price', '48.98'); // Regular price
                    update_post_meta($food_grocery_store_post_id, '_sale_price', '56.00'); // Sale price
                    update_post_meta($food_grocery_store_post_id, '_price', '56.00'); // Active price

                    // Handle the featured image using media_sideload_image
                    $food_grocery_store_image_url = get_template_directory_uri() . '/assets/images/product' . ($food_grocery_store_i + 1) . '.png';
                    $food_grocery_store_image_id = media_sideload_image($food_grocery_store_image_url, $food_grocery_store_post_id, null, 'id');

                    if (is_wp_error($food_grocery_store_image_id)) {
                        error_log('Error downloading image: ' . $food_grocery_store_image_id->get_error_message());
                        continue; // Skip to the next product if image download fails
                    }

                    // Assign featured image to product
                    set_post_thumbnail($food_grocery_store_post_id, $food_grocery_store_image_id);
                }
            }

            // Create track order page
            $food_grocery_store_page_query = new WP_Query(array(
                'post_type'      => 'page',
                'title'          => 'Products',
                'post_status'    => 'publish',
                'posts_per_page' => 1
            ));

            if (!$food_grocery_store_page_query->have_posts()) {
                $food_grocery_store_page_title = 'Trending Products';
                $productpage = '[products limit="4" columns="4"]';

                // Append the WooCommerce products shortcode to the content
                $food_grocery_store_content = '';
                $food_grocery_store_content .= do_shortcode($productpage);

                // Create the new page
                $food_grocery_store_page = array(
                    'post_type'    => 'page',
                    'post_title'   => $food_grocery_store_page_title,
                    'post_content' => $food_grocery_store_content,
                    'post_status'  => 'publish',
                    'post_author'  => 1,
                    'post_slug'    => 'products'
                );

                // Insert the page and get its ID
                $food_grocery_store_page_id = wp_insert_post($food_grocery_store_page);

                // Store the page ID in theme mod
                if (!is_wp_error($food_grocery_store_page_id)) {
                    set_theme_mod('food_grocery_store_product_settings', $food_grocery_store_page_id);
                }
            }

            //Copyright Text
            set_theme_mod( 'food_grocery_store_footer_text', 'By VWThemes' );

        }
    ?>

    <form action="<?php echo esc_url(home_url()); ?>/wp-admin/themes.php?page=food_grocery_store_guide" method="POST" onsubmit="return validate(this);">       
    <?php if (!get_option('food_grocery_store_demo_import_completed')) : ?>
        <form method="post">   
            <p class="run-import-text"><?php esc_html_e('Click On The Below Run Importer Button To Import Demo Content Of food grocery store', 'food-grocery-store'); ?></p>
                <p><?php esc_html_e('Please back up your website if it’s already live with data. This importer will overwrite your existing settings with the new customizer values for food grocery store', 'food-grocery-store'); ?></p>
                <input class="run-import" type="submit" name="submit" value="<?php esc_attr_e('Run Importer', 'food-grocery-store'); ?>" class="button button-primary button-large">
        </form>   
        <?php endif; ?>
        <div id="spinner" style="display:none;">         
            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/spinner.png" alt="" />
        </div>
    </form>
    <script type="text/javascript">
        function validate(form) {
            if (confirm("Do you really want to import the theme demo content?")) {
                // Show the spinner
                document.getElementById('spinner').style.display = 'block';
                // Allow the form to be submitted
                return true;
            } 
            else {
                return false;
            }
        }
    </script>
</div>
