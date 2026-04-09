<?php
/**
 * Adding support for WooCommerce Plugin
 */

if ( ! class_exists( 'WooCommerce' ) ) {
    // Bail if WooCommerce is not installed
    return;
}


if ( ! function_exists( 'catch_store_woocommerce_setup' ) ) :
    /**
     * Sets up support for various WooCommerce features.
     */
    function catch_store_woocommerce_setup() {
        add_theme_support( 'woocommerce', array(
            'thumbnail_image_width'  => 640,
            'thumbnail_image_height' => 800,
        ) );

        if ( ! get_theme_mod( 'catch_store_product_gallery_zoom' ) ) {
            add_theme_support('wc-product-gallery-zoom');
        }

        if ( ! get_theme_mod( 'catch_store_product_gallery_lightbox' ) ) {
            add_theme_support('wc-product-gallery-lightbox');
        }

        if ( ! get_theme_mod( 'catch_store_product_gallery_slider' ) ) {
            add_theme_support('wc-product-gallery-slider');
        }
    }
endif; //catch-catch_store_woocommerce_setup
add_action( 'after_setup_theme', 'catch_store_woocommerce_setup' );


/**
 * Add WooCommerce Options to customizer
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function catch_store_woocommerce_options( $wp_customize ) {
    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_woocommerce_layout',
            'default'           => 'no-sidebar-full-width',
            'sanitize_callback' => 'catch_store_sanitize_select',
            'description'       => esc_html__( 'Layout for WooCommerce Pages', 'catch-store' ),
            'label'             => esc_html__( 'WooCommerce Layout', 'catch-store' ),
            'section'           => 'catch_store_layout_options',
            'type'              => 'radio',
            'choices'           => array(
                'right-sidebar'         => esc_html__( 'Right Sidebar ( Content, Primary Sidebar )', 'catch-store' ),
                'no-sidebar-full-width' => esc_html__( 'No Sidebar: Full Width', 'catch-store' ),
            ),
        )
    );

    // WooCommerce Options
    $wp_customize->add_section( 'catch_store_woocommerce_options', array(
        'title'       => esc_html__( 'WooCommerce Options', 'catch-store' ),
        'panel'       => 'catch_store_theme_options',
        'description' => esc_html__( 'Since these options are added via theme support, you will need to save and refresh the customizer to view the full effect.', 'catch-store' ),
    ) );

    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_product_gallery_zoom',
            'sanitize_callback' => 'catch_store_sanitize_checkbox',
            'label'             => esc_html__( 'Check to disable Product Gallery Zoom', 'catch-store' ),
            'section'           => 'catch_store_woocommerce_options',
            'type'              => 'checkbox',
        )
    );

    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_product_gallery_lightbox',
            'sanitize_callback' => 'catch_store_sanitize_checkbox',
            'label'             => esc_html__( 'Check to disable Product Gallery Lightbox', 'catch-store' ),
            'section'           => 'catch_store_woocommerce_options',
            'type'              => 'checkbox',
        )
    );

    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_product_gallery_slider',
            'sanitize_callback' => 'catch_store_sanitize_checkbox',
            'label'             => esc_html__( 'Check to disable Product Gallery Slider', 'catch-store' ),
            'section'           => 'catch_store_woocommerce_options',
            'type'              => 'checkbox',
        )
    );

        // WooCommerce Excerpt Options.
    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_woo_excerpt_length',
            'default'           => '10',
            'sanitize_callback' => 'absint',
            'input_attrs' => array(
                'min'   => 5,
                'max'   => 200,
                'step'  => 5,
                'style' => 'width: 60px;',
            ),
            'label'    => esc_html__( 'WooCommerce Product Excerpt Length (words)', 'catch-store' ),
            'section'  => 'catch_store_excerpt_options',
            'type'     => 'number',
        )
    );

    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_woo_excerpt_more_text',
            'default'           => esc_html__( 'Buy Now', 'catch-store' ),
            'sanitize_callback' => 'sanitize_text_field',
            'label'             => esc_html__( 'WooCommerce Product Read More Text', 'catch-store' ),
            'section'           => 'catch_store_excerpt_options',
            'type'              => 'text',
        )
    );
}
add_action( 'customize_register', 'catch_store_woocommerce_options' );


/**
 * uses remove_action to remove the WooCommerce Wrapper and add_action to add Main Wrapper
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );


if ( ! function_exists( 'catch_store_woocommerce_start' ) ) :
    function catch_store_woocommerce_start() {
    	echo '<div id="primary" class="content-area"><main role="main" class="site-main woocommerce" id="main"><div class="woocommerce-posts-wrapper">';
    }
endif; //catch-catch_store_woocommerce_start
add_action( 'woocommerce_before_main_content', 'catch_store_woocommerce_start', 15 );


if ( ! function_exists( 'catch_store_woocommerce_end' ) ) :
    function catch_store_woocommerce_end() {
    	echo '</div><!-- .woocommerce-posts-wrapper --></main><!-- #main --></div><!-- #primary -->';
    }
endif; //catch-catch_store_woocommerce_end
add_action( 'woocommerce_after_main_content', 'catch_store_woocommerce_end', 15 );


function catch_store_woocommerce_shorting_start() {
	echo '<div class="woocommerce-shorting-wrapper">';
}
add_action( 'woocommerce_before_shop_loop', 'catch_store_woocommerce_shorting_start', 10 );


function catch_store_woocommerce_shorting_end() {
	echo '</div><!-- .woocommerce-shorting-wrapper -->';
}
add_action( 'woocommerce_before_shop_loop', 'catch_store_woocommerce_shorting_end', 40 );


function catch_store_woocommerce_product_container_start() {
	echo '<div class="product-container">';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'catch_store_woocommerce_product_container_start', 20 );


function catch_store_woocommerce_product_container_end() {
	echo '</div><!-- .product-container -->';
}
add_action( 'woocommerce_after_shop_loop_item', 'catch_store_woocommerce_product_container_end', 20 );

/**
 * Disable the default WooCommerce stylesheet.
 *
 * Removing the default WooCommerce stylesheet and enqueing your own will
 * protect you during WooCommerce core updates.
 *
 * @link https://docs.woocommerce.com/document/disable-the-default-stylesheet/
 */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * Add 'woocommerce-active' class to the body tag.
 *
 * @param  array $classes CSS classes applied to the body tag.
 * @return array $classes modified to include 'woocommerce-active' class.
 */
function catch_store_woocommerce_active_body_class( $classes ) {
    $classes[] = 'woocommerce-active';

    return $classes;
}
add_filter( 'body_class', 'catch_store_woocommerce_active_body_class' );

/**
 * WooCommerce specific scripts & stylesheets.
 *
 * @return void
 */
function catch_store_woocommerce_scripts() {
    $font_path   = WC()->plugin_url() . '/assets/fonts/';
    $inline_font = '@font-face {
            font-family: "star";
            src: url("' . $font_path . 'star.eot");
            src: url("' . $font_path . 'star.eot?#iefix") format("embedded-opentype"),
                url("' . $font_path . 'star.woff") format("woff"),
                url("' . $font_path . 'star.ttf") format("truetype"),
                url("' . $font_path . 'star.svg#star") format("svg");
            font-weight: normal;
            font-style: normal;
        }';

    wp_add_inline_style( 'catch-store-style', $inline_font );
}
add_action( 'wp_enqueue_scripts', 'catch_store_woocommerce_scripts' );


if ( ! function_exists( 'catch_store_woocommerce_product_columns_wrapper' ) ) {
    /**
     * Product columns wrapper.
     *
     * @return  void
     */
    function catch_store_woocommerce_product_columns_wrapper() {
        // Get option from Customizer=> WooCommerce=> Product Catlog=> Products per row.
        echo '<div class="columns-' . absint( get_option( 'woocommerce_catalog_columns', 3 ) ) . '">';
    }
}
add_action( 'woocommerce_before_shop_loop', 'catch_store_woocommerce_product_columns_wrapper', 40 );

if ( ! function_exists( 'catch_store_woocommerce_product_columns_wrapper_close' ) ) {
    /**
     * Product columns wrapper close.
     *
     * @return  void
     */
    function catch_store_woocommerce_product_columns_wrapper_close() {
        echo '</div>';
    }
}
add_action( 'woocommerce_after_shop_loop', 'catch_store_woocommerce_product_columns_wrapper_close', 40 );

if ( ! function_exists( 'catch_store_remove_default_woo_store_notice' ) ) {
    /**
     * Remove default Store Notice from footer, added in header.php
     *
     * @return  void
     */
    function catch_store_remove_default_woo_store_notice() {
        remove_action( 'wp_footer', 'woocommerce_demo_store' );
    }
}
add_action( 'after_setup_theme', 'catch_store_remove_default_woo_store_notice', 40 );
