<?php
/**
 * Featured Content options
 *
 * @package Catch_Store
 */

/**
 * Add featured content options to theme options
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function catch_store_featured_content_options( $wp_customize ) {
	// Add note to ECT Featured Content Section
    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_featured_content_jetpack_note',
            'sanitize_callback' => 'sanitize_text_field',
            'custom_control'    => 'catch_store_Note_Control',
            'label'             => sprintf( esc_html__( 'For all Featured Content Options for Catch Store Theme, go %1$shere%2$s', 'catch-store' ),
                '<a href="javascript:wp.customize.section( \'catch_store_featured_content\' ).focus();">',
                 '</a>'
            ),
           'section'            => 'featured_content',
            'type'              => 'description',
            'priority'          => 1,
        )
    );

    $wp_customize->add_section( 'catch_store_featured_content', array(
			'title' => esc_html__( 'Featured Content', 'catch-store' ),
			'panel' => 'catch_store_theme_options',
		)
	);

	// Add color scheme setting and control.
	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_featured_content_option',
			'default'           => 'disabled',
			'sanitize_callback' => 'catch_store_sanitize_select',
			'choices'           => catch_store_section_visibility_options(),
			'label'             => esc_html__( 'Enable on', 'catch-store' ),
			'section'           => 'catch_store_featured_content',
			'type'              => 'select',
		)
	);

    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_featured_content_cpt_note',
            'sanitize_callback' => 'sanitize_text_field',
            'custom_control'    => 'catch_store_Note_Control',
            'active_callback'   => 'catch_store_is_featured_content_active',
            /* translators: 1: <a>/link tag start, 2: </a>/link tag close. */
			'label'             => sprintf( esc_html__( 'For CPT heading and sub-heading, go %1$shere%2$s', 'catch-store' ),
                 '<a href="javascript:wp.customize.control( \'featured_content_title\' ).focus();">',
                 '</a>'
            ),
            'section'           => 'catch_store_featured_content',
            'type'              => 'description',
        )
    );

	catch_store_register_option( $wp_customize, array(
			'name'              => 'catch_store_featured_content_number',
			'default'           => 4,
			'sanitize_callback' => 'catch_store_sanitize_number_range',
			'active_callback'   => 'catch_store_is_featured_content_active',
			'description'       => esc_html__( 'Save and refresh the page if No. of Featured Content is changed (Max no of Featured Content is 20)', 'catch-store' ),
			'input_attrs'       => array(
				'style' => 'width: 100px;',
				'min'   => 0,
			),
			'label'             => esc_html__( 'No of Featured Content', 'catch-store' ),
			'section'           => 'catch_store_featured_content',
			'type'              => 'number',
		)
	);

	$number = get_theme_mod( 'catch_store_featured_content_number', 4 );

	//loop for featured CTP content
	for ( $i = 1; $i <= $number ; $i++ ) {
		catch_store_register_option( $wp_customize, array(
				'name'              => 'catch_store_featured_content_cpt_' . $i,
				'sanitize_callback' => 'catch_store_sanitize_post',
				'active_callback'   => 'catch_store_is_featured_content_active',
				'label'             => esc_html__( 'Featured Content', 'catch-store' ) . ' ' . $i ,
				'section'           => 'catch_store_featured_content',
				'type'              => 'select',
                'choices'           => catch_store_generate_post_array( 'featured-content' ),
			)
		);
	} // End for().
}
add_action( 'customize_register', 'catch_store_featured_content_options', 10 );

/** Active Callback Functions **/
if( ! function_exists( 'catch_store_is_featured_content_active' ) ) :
	/**
	* Return true if featured content is active
	*
	* @since Catch Store 1.0
	*/
	function catch_store_is_featured_content_active( $control ) {
		global $wp_query;

		$page_id = $wp_query->get_queried_object_id();

		// Front page display in Reading Settings
		$page_for_posts = get_option('page_for_posts');

		$enable = $control->manager->get_setting( 'catch_store_featured_content_option' )->value();

		//return true only if previwed page on customizer matches the type of content option selected
		return ( 'entire-site' == $enable || ( ( is_front_page() || ( is_home() && $page_for_posts != $page_id ) ) && 'homepage' == $enable )
	);
	}
endif;

if( ! function_exists( 'catch_store_is_featured_cpt_content_active' ) ) :
	/**
	* Return true if page content is active
	*
	* @since Catch Store 1.0
	*/
	function catch_store_is_featured_cpt_content_active( $control ) {
		$type = $control->manager->get_setting( 'catch_store_featured_content_type' )->value();

		//return true only if previwed page on customizer matches the type of content option selected and is or is not selected type
		return ( catch_store_is_featured_content_active( $control ) && ( 'featured-content' === $type )
	);
	}
endif;