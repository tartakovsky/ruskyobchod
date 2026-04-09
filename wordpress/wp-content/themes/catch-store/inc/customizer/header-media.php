<?php
/**
 * Header Media Options
 *
 * @package Catch_Store
 */

function catch_store_header_media_options( $wp_customize ) {
	$wp_customize->get_section( 'header_image' )->description = esc_html__( 'If you add video, it will only show up on Homepage/FrontPage. Other Pages will use Header/Post/Page Image depending on your selection of option. Header Image will be used as a fallback while the video loads ', 'catch-store' );

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_header_media_option',
			'default'           => 'entire-site-page-post',
			'sanitize_callback' => 'catch_store_sanitize_select',
			'choices'           => array(
				'homepage'               => esc_html__( 'Homepage / Frontpage', 'catch-store' ),
				'exclude-home'           => esc_html__( 'Excluding Homepage', 'catch-store' ),
				'exclude-home-page-post' => esc_html__( 'Excluding Homepage, Page/Post Featured Image', 'catch-store' ),
				'entire-site'            => esc_html__( 'Entire Site', 'catch-store' ),
				'entire-site-page-post'  => esc_html__( 'Entire Site, Page/Post Featured Image', 'catch-store' ),
				'pages-posts'            => esc_html__( 'Pages and Posts', 'catch-store' ),
				'disable'                => esc_html__( 'Disabled', 'catch-store' ),
			),
			'label'             => esc_html__( 'Enable on ', 'catch-store' ),
			'section'           => 'header_image',
			'type'              => 'select',
			'priority'          => 1,
		)
	);

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_header_media_title',
			'default'           => esc_html__( 'The Autumn Collection', 'catch-store' ),
			'sanitize_callback' => 'wp_kses_post',
			'label'             => esc_html__( 'Header Media Title', 'catch-store' ),
			'section'           => 'header_image',
			'type'              => 'text',
		)
	);

    catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_header_media_text',
			'default'           => esc_html__( 'Hypnotic Time of Autumn Falls', 'catch-store' ),
			'sanitize_callback' => 'wp_kses_post',
			'label'             => esc_html__( 'Header Media Text', 'catch-store' ),
			'section'           => 'header_image',
			'type'              => 'textarea',
		)
	);

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_header_media_url',
			'default'           => '#',
			'sanitize_callback' => 'esc_url_raw',
			'label'             => esc_html__( 'Header Media Url', 'catch-store' ),
			'section'           => 'header_image',
		)
	);

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_header_media_url_text',
			'default'           => esc_html__( 'Continue Reading', 'catch-store' ),
			'sanitize_callback' => 'sanitize_text_field',
			'label'             => esc_html__( 'Header Media Url Text', 'catch-store' ),
			'section'           => 'header_image',
		)
	);

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_header_url_target',
			'sanitize_callback' => 'catch_store_sanitize_checkbox',
			'label'             => esc_html__( 'Check to Open Link in New Window/Tab', 'catch-store' ),
			'section'           => 'header_image',
			'type'              => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'catch_store_header_media_options' );

