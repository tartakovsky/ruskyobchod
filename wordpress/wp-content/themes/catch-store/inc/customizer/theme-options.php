<?php
/**
 * Theme Options
 *
 * @package Catch_Store
 */

/**
 * Add theme options
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function catch_store_theme_options( $wp_customize ) {
	$wp_customize->add_panel( 'catch_store_theme_options', array(
		'title'    => esc_html__( 'Theme Options', 'catch-store' ),
		'priority' => 130,
	) );

	// Breadcrumb Option.
	$wp_customize->add_section( 'catch_store_breadcrumb_options', array(
		'description'   => esc_html__( 'Breadcrumbs are a great way of letting your visitors find out where they are on your site with just a glance.', 'catch-store' ),
		'panel'         => 'catch_store_theme_options',
		'title'         => esc_html__( 'Breadcrumb', 'catch-store' ),
	) );

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_breadcrumb_option',
			'default'           => 1,
			'sanitize_callback' => 'catch_store_sanitize_checkbox',
			'label'             => esc_html__( 'Check to enable Breadcrumb', 'catch-store' ),
			'section'           => 'catch_store_breadcrumb_options',
			'type'              => 'checkbox',
		)
	);

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_breadcrumb_on_homepage',
			'sanitize_callback' => 'catch_store_sanitize_checkbox',
			'label'             => esc_html__( 'Check to enable Breadcrumb on Homepage', 'catch-store' ),
			'section'           => 'catch_store_breadcrumb_options',
			'type'              => 'checkbox',
		)
	);

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_latest_posts_title',
			'default'           => esc_html__( 'News', 'catch-store' ),
			'sanitize_callback' => 'wp_kses_post',
			'label'             => esc_html__( 'Latest Posts Title', 'catch-store' ),
			'section'           => 'catch_store_theme_options',
		)
	);

	// Layout Options
	$wp_customize->add_section( 'catch_store_layout_options', array(
		'title' => esc_html__( 'Layout Options', 'catch-store' ),
		'panel' => 'catch_store_theme_options',
		)
	);

	/* Default Layout */
	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_default_layout',
			'default'           => 'right-sidebar',
			'sanitize_callback' => 'catch_store_sanitize_select',
			'label'             => esc_html__( 'Default Layout', 'catch-store' ),
			'section'           => 'catch_store_layout_options',
			'type'              => 'radio',
			'choices'           => array(
				'right-sidebar'         => esc_html__( 'Right Sidebar ( Content, Primary Sidebar )', 'catch-store' ),
				'no-sidebar-full-width' => esc_html__( 'No Sidebar: Full Width', 'catch-store' ),
			),
		)
	);

	/* Homepage/Archive Layout */
	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_homepage_archive_layout',
			'default'           => 'right-sidebar',
			'sanitize_callback' => 'catch_store_sanitize_select',
			'label'             => esc_html__( 'Homepage/Archive Layout', 'catch-store' ),
			'section'           => 'catch_store_layout_options',
			'type'              => 'radio',
			'choices'           => array(
				'right-sidebar'         => esc_html__( 'Right Sidebar ( Content, Primary Sidebar )', 'catch-store' ),
				'no-sidebar-full-width' => esc_html__( 'No Sidebar: Full Width', 'catch-store' ),
			),
		)
	);

	// Excerpt Options.
	$wp_customize->add_section( 'catch_store_excerpt_options', array(
		'panel'     => 'catch_store_theme_options',
		'title'     => esc_html__( 'Excerpt Options', 'catch-store' ),
	) );

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_excerpt_length',
			'default'           => '20',
			'sanitize_callback' => 'absint',
			'description' => esc_html__( 'Excerpt length. Default is 20 words', 'catch-store' ),
			'input_attrs' => array(
				'min'   => 10,
				'max'   => 200,
				'step'  => 5,
				'style' => 'width: 60px;',
			),
			'label'    => esc_html__( 'Excerpt Length (words)', 'catch-store' ),
			'section'  => 'catch_store_excerpt_options',
			'type'     => 'number',
		)
	);

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_excerpt_more_text',
			'default'           => esc_html__( 'Continue Reading', 'catch-store' ),
			'sanitize_callback' => 'sanitize_text_field',
			'label'             => esc_html__( 'Read More Text', 'catch-store' ),
			'section'           => 'catch_store_excerpt_options',
			'type'              => 'text',
		)
	);

	// Excerpt Options.
	$wp_customize->add_section( 'catch_store_search_options', array(
		'panel'     => 'catch_store_theme_options',
		'title'     => esc_html__( 'Search Options', 'catch-store' ),
	) );

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_search_text',
			'default'           => esc_html__( 'Search', 'catch-store' ),
			'sanitize_callback' => 'sanitize_text_field',
			'label'             => esc_html__( 'Search Text', 'catch-store' ),
			'section'           => 'catch_store_search_options',
			'type'              => 'text',
		)
	);

	// Homepage / Frontpage Options.
	$wp_customize->add_section( 'catch_store_homepage_options', array(
		'description' => esc_html__( 'Only posts that belong to the categories selected here will be displayed on the front page', 'catch-store' ),
		'panel'       => 'catch_store_theme_options',
		'title'       => esc_html__( 'Homepage / Frontpage Options', 'catch-store' ),
	) );

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_front_page_category',
			'sanitize_callback' => 'catch_store_sanitize_category_list',
			'custom_control'    => 'catch_store_Multi_Categories_Control',
			'label'             => esc_html__( 'Categories', 'catch-store' ),
			'section'           => 'catch_store_homepage_options',
			'type'              => 'dropdown-categories',
		)
	);

	// Pagination Options.
	$pagination_type = get_theme_mod( 'catch_store_pagination_type', 'default' );

	$nav_desc = '';

	/**
	* Check if navigation type is Jetpack Infinite Scroll and if it is enabled
	*/
	$nav_desc = sprintf(
		wp_kses(
			__( 'For infinite scrolling, use %1$sCatch Infinite Scroll Plugin%2$s with Infinite Scroll module Enabled.', 'catch-store' ),
			array(
				'a' => array(
					'href' => array(),
					'target' => array(),
				),
				'br'=> array()
			)
		),
		'<a target="_blank" href="https://wordpress.org/plugins/catch-infinite-scroll/">',
		'</a>'
	);

	$wp_customize->add_section( 'catch_store_pagination_options', array(
		'description' => $nav_desc,
		'panel'       => 'catch_store_theme_options',
		'title'       => esc_html__( 'Pagination Options', 'catch-store' ),
	) );

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_pagination_type',
			'default'           => 'default',
			'sanitize_callback' => 'catch_store_sanitize_select',
			'choices'           => catch_store_get_pagination_types(),
			'label'             => esc_html__( 'Pagination type', 'catch-store' ),
			'section'           => 'catch_store_pagination_options',
			'type'              => 'select',
		)
	);

	// For WooCommerce layout: catch_store_woocommerce_layout, check woocommerce-options.php.
	/* Scrollup Options */
	$wp_customize->add_section( 'catch_store_scrollup', array(
		'panel'    => 'catch_store_theme_options',
		'title'    => esc_html__( 'Scrollup Options', 'catch-store' ),
	) );

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_disable_scrollup',
			'sanitize_callback' => 'catch_store_sanitize_checkbox',
			'label'             => esc_html__( 'Disable Scroll Up', 'catch-store' ),
			'section'           => 'catch_store_scrollup',
			'type'              => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'catch_store_theme_options' );

/**
 * Returns an array of avaliable fonts registered for Catch Store
 *
 * @since Catch Store 1.0
 */
function catch_store_avaliable_fonts() {
	$avaliable_fonts = array(
		'arial-black' => array(
			'value' => 'arial-black',
			'label' => '"Arial Black", Gadget, sans-serif',
		),
		'allan' => array(
			'value' => 'allan',
			'label' => '"Allan", sans-serif',
		),
		'allerta' => array(
			'value' => 'allerta',
			'label' => '"Allerta", sans-serif',
		),
		'amaranth' => array(
			'value' => 'amaranth',
			'label' => '"Amaranth", sans-serif',
		),
		'amatic-sc' => array(
			'value' => 'amatic-sc',
			'label' => '"Amatic SC", cursive',
		),
		'arial' => array(
			'value' => 'arial',
			'label' => 'Arial, Helvetica, sans-serif',
		),
		'bitter' => array(
			'value' => 'bitter',
			'label' => '"Bitter", sans-serif',
		),
		'cabin' => array(
			'value' => 'cabin',
			'label' => '"Cabin", sans-serif',
		),
		'cantarell' => array(
			'value' => 'cantarell',
			'label' => '"Cantarell", sans-serif',
		),
		'century-gothic' => array(
			'value' => 'century-gothic',
			'label' => '"Century Gothic", sans-serif',
		),
		'courier-new' => array(
			'value' => 'courier-new',
			'label' => '"Courier New", Courier, monospace',
		),
		'crimson-text' => array(
			'value' => 'crimson-text',
			'label' => '"Crimson Text", sans-serif',
		),
		'cuprum' => array(
			'value' => 'cuprum',
			'label' => '"Cuprum", sans-serif',
		),
		'dancing-script' => array(
			'value' => 'dancing-script',
			'label' => '"Dancing Script", sans-serif',
		),
		'droid-sans' => array(
			'value' => 'droid-sans',
			'label' => '"Droid Sans", sans-serif',
		),
		'droid-serif' => array(
			'value' => 'droid-serif',
			'label' => '"Droid Serif", sans-serif',
		),
		'exo' => array(
			'value' => 'exo',
			'label' => '"Exo", sans-serif',
		),
		'exo-2' => array(
			'value' => 'exo-2',
			'label' => '"Exo 2", sans-serif',
		),
		'georgia' => array(
			'value' => 'georgia',
			'label' => 'Georgia, "Times New Roman", Times, serif',
		),
		'helvetica' => array(
			'value' => 'helvetica',
			'label' => 'Helvetica, "Helvetica Neue", Arial, sans-serif',
		),
		'helvetica-neue' => array(
			'value' => 'helvetica-neue',
			'label' => '"Helvetica Neue", Helvetica, Arial,sans-serif',
		),
		'istok-web' => array(
			'value' => 'istok-web',
			'label' => '"Istok Web", sans-serif',
		),
		'impact' => array(
			'value' => 'impact',
			'label' => 'Impact, Charcoal, sans-serif',
		),
		'josefin-sans' => array(
			'value' => 'josefin-sans',
			'label' => '"Josefin Sans", sans-serif',
		),
		'lato' => array(
			'value' => 'lato',
			'label' => '"Lato", sans-serif',
		),
		'lucida-sans-unicode' => array(
			'value' => 'lucida-sans-unicode',
			'label' => '"Lucida Sans Unicode", "Lucida Grande", sans-serif',
		),
		'lucida-grande' => array(
			'value' => 'lucida-grande',
			'label' => '"Lucida Grande", "Lucida Sans Unicode", sans-serif',
		),
		'lobster' => array(
			'value' => 'lobster',
			'label' => '"Lobster", sans-serif',
		),
		'lora' => array(
			'value' => 'lora',
			'label' => '"Lora", serif',
		),
		'monaco' => array(
			'value' => 'monaco',
			'label' => 'Monaco, Consolas, "Lucida Console", monospace, sans-serif',
		),
		'montserrat' => array(
			'value' => 'montserrat',
			'label' => '"Montserrat", sans-serif',
		),
		'nobile' => array(
			'value' => 'nobile',
			'label' => '"Nobile", sans-serif',
		),
		'noto-serif' => array(
			'value' => 'noto-serif',
			'label' => '"Noto Serif", serif',
		),
		'neuton' => array(
			'value' => 'neuton',
			'label' => '"Neuton", serif',
		),
		'open-sans' => array(
			'value' => 'open-sans',
			'label' => '"Open Sans", sans-serif',
		),
		'oswald' => array(
			'value' => 'oswald',
			'label' => '"Oswald", sans-serif',
		),
		'palatino' => array(
			'value' => 'palatino',
			'label' => 'Palatino, "Palatino Linotype", "Book Antiqua", serif',
		),
		'patua-one' => array(
			'value' => 'patua-one',
			'label' => '"Patua One", sans-serif',
		),
		'playfair-display' => array(
			'value' => 'playfair-display',
			'label' => '"Playfair Display", sans-serif',
		),
		'pt-sans' => array(
			'value' => 'pt-sans',
			'label' => '"PT Sans", sans-serif',
		),
		'pt-serif' => array(
			'value' => 'pt-serif',
			'label' => '"PT Serif", serif',
		),
		'quattrocento-sans' => array(
			'value' => 'quattrocento-sans',
			'label' => '"Quattrocento Sans", sans-serif',
		),
		'roboto' => array(
			'value' => 'roboto',
			'label' => '"Roboto", sans-serif',
		),
		'roboto-slab' => array(
			'value' => 'roboto-slab',
			'label' => '"Roboto Slab", serif',
		),
		'rubik' => array(
			'value' => 'rubik',
			'label' => '"Rubik", serif',
		),
		'sans-serif' => array(
			'value' => 'sans-serif',
			'label' => 'Sans Serif, Arial',
		),
		'source-sans-pro' => array(
			'value' => 'source-sans-pro',
			'label' => '"Source Sans Pro", sans-serif',
		),
		'tahoma' => array(
			'value' => 'tahoma',
			'label' => 'Tahoma, Geneva, sans-serif',
		),
		'trebuchet-ms' => array(
			'value' => 'trebuchet-ms',
			'label' => '"Trebuchet MS", "Helvetica", sans-serif',
		),
		'times-new-roman' => array(
			'value' => 'times-new-roman',
			'label' => '"Times New Roman", Times, serif',
		),
		'ubuntu' => array(
			'value' => 'ubuntu',
			'label' => '"Ubuntu", sans-serif',
		),
		'varela' => array(
			'value' => 'varela',
			'label' => '"Varela", sans-serif',
		),
		'verdana' => array(
			'value' => 'verdana',
			'label' => 'Verdana, Geneva, sans-serif',
		),
		'yanone-kaffeesatz' => array(
			'value' => 'yanone-kaffeesatz',
			'label' => '"Yanone Kaffeesatz", sans-serif',
		),
	);

	return apply_filters( 'catch_store_avaliable_fonts', $avaliable_fonts );
}