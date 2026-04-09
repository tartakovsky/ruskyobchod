<?php
/**
 * Add Testimonial Settings in Customizer
 *
 * @package Catch_Store
*/

/**
 * Add testimonial options to theme options
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function catch_store_testimonial_options( $wp_customize ) {
    // Add note to Jetpack Testimonial Section
    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_jetpack_testimonial_cpt_note',
            'sanitize_callback' => 'sanitize_text_field',
            'custom_control'    => 'catch_store_Note_Control',
            'label'             => sprintf( esc_html__( 'For Testimonial Options for Catch Store Theme, go %1$shere%2$s', 'catch-store' ),
                '<a href="javascript:wp.customize.section( \'catch_store_testimonials\' ).focus();">',
                 '</a>'
            ),
           'section'            => 'jetpack_testimonials',
            'type'              => 'description',
            'priority'          => 1,
        )
    );

    $wp_customize->add_section( 'catch_store_testimonials', array(
            'panel'    => 'catch_store_theme_options',
            'title'    => esc_html__( 'Testimonials', 'catch-store' ),
        )
    );

    $action = 'install-plugin';
    $slug   = 'essential-content-types';

    $install_url = wp_nonce_url(
        add_query_arg(
            array(
                'action' => $action,
                'plugin' => $slug
            ),
            admin_url( 'update.php' )
        ),
        $action . '_' . $slug
    );

    // Add note to ECT Featured Content Section
    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_testimonial_note_1',
            'sanitize_callback' => 'sanitize_text_field',
            'custom_control'    => 'Catch_Store_Note_Control',
            'active_callback'   => 'catch_store_is_ect_testimonial_inactive',
            /* translators: 1: <a>/link tag start, 2: </a>/link tag close. */
            'label'             => sprintf( esc_html__( 'For Testimonial, install %1$sEssential Content Types%2$s Plugin with Testimonial Content Type Enabled', 'catch-store' ),
                '<a target="_blank" href="' . esc_url( $install_url ) . '">',
                '</a>'

            ),
           'section'            => 'catch_store_testimonials',
            'type'              => 'description',
            'priority'          => 1,
        )
    );

    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_testimonial_option',
            'default'           => 'disabled',
            'sanitize_callback' => 'catch_store_sanitize_select',
            'active_callback'   => 'catch_store_is_ect_testimonial_active',
            'choices'           => catch_store_section_visibility_options(),
            'label'             => esc_html__( 'Enable on', 'catch-store' ),
            'section'           => 'catch_store_testimonials',
            'type'              => 'select',
            'priority'          => 1,
        )
    );

    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_testimonial_cpt_note',
            'sanitize_callback' => 'sanitize_text_field',
            'custom_control'    => 'catch_store_Note_Control',
            'active_callback'   => 'catch_store_is_testimonial_active',
            /* translators: 1: <a>/link tag start, 2: </a>/link tag close. */
			'label'             => sprintf( esc_html__( 'For CPT heading and sub-heading, go %1$shere%2$s', 'catch-store' ),
                '<a href="javascript:wp.customize.section( \'jetpack_testimonials\' ).focus();">',
                '</a>'
            ),
            'section'           => 'catch_store_testimonials',
            'type'              => 'description',
        )
    );

    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_testimonial_number',
            'default'           => '4',
            'sanitize_callback' => 'catch_store_sanitize_number_range',
            'active_callback'   => 'catch_store_is_testimonial_active',
            'label'             => esc_html__( 'Number of items to show', 'catch-store' ),
            'section'           => 'catch_store_testimonials',
            'type'              => 'number',
            'input_attrs'       => array(
                'style'             => 'width: 100px;',
                'min'               => 0,
            ),
        )
    );

    $number = get_theme_mod( 'catch_store_testimonial_number', 4 );

    for ( $i = 1; $i <= $number ; $i++ ) {
        //for CPT
        catch_store_register_option( $wp_customize, array(
                'name'              => 'catch_store_testimonial_cpt_' . $i,
                'sanitize_callback' => 'catch_store_sanitize_post',
                'active_callback'   => 'catch_store_is_testimonial_active',
                'label'             => esc_html__( 'Testimonial', 'catch-store' ) . ' ' . $i ,
                'section'           => 'catch_store_testimonials',
                'type'              => 'select',
                'choices'           => catch_store_generate_post_array( 'jetpack-testimonial' ),
            )
        );
    } // End for().
}
add_action( 'customize_register', 'catch_store_testimonial_options' );

/**
 * Active Callback Functions
 */
if ( ! function_exists( 'catch_store_is_testimonial_active' ) ) :
    /**
    * Return true if testimonial is active
    *
    * @since Catch Store 1.0
    */
    function catch_store_is_testimonial_active( $control ) {
        $enable = $control->manager->get_setting( 'catch_store_testimonial_option' )->value();

        //return true only if previwed page on customizer matches the type of content option selected
        return ( catch_store_check_section( $enable ) );
    }
endif;

if ( ! function_exists( 'catch_store_is_ect_testimonial_inactive' ) ) :
    /**
    *
    * @since Catch Store 1.0
    */
    function catch_store_is_ect_testimonial_inactive( $control ) {
        return ! ( class_exists( 'Essential_Content_Jetpack_Testimonial' ) || class_exists( 'Essential_Content_Pro_Jetpack_Testimonial' ) );
    }
endif;

if ( ! function_exists( 'catch_store_is_ect_testimonial_active' ) ) :
    /**
    *
    * @since Catch Store 1.0
    */
    function catch_store_is_ect_testimonial_active( $control ) {
        return ( class_exists( 'Essential_Content_Jetpack_Testimonial' ) || class_exists( 'Essential_Content_Pro_Jetpack_Testimonial' ) );
    }
endif;