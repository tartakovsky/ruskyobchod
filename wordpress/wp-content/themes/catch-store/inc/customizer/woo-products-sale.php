<?php
/**
 * Adding support for WooCommerce Products Showcase Option
 */

if ( ! class_exists( 'WooCommerce' ) ) {
    // Bail if WooCommerce is not installed
    return;
}

/**
 * Add WooCommerce Product Showcase Options to customizer
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function catch_store_sale_products_showcase( $wp_customize ) {
   $wp_customize->add_section( 'catch_store_sale_products_showcase', array(
        'title' => esc_html__( 'Sale Products', 'catch-store' ),
        'panel' => 'catch_store_theme_options',
    ) );

    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_sale_products_showcase_option',
            'default'           => 'disabled',
            'sanitize_callback' => 'catch_store_sanitize_select',
            'choices'           => catch_store_section_visibility_options(),
            'label'             => esc_html__( 'Enable on', 'catch-store' ),
            'section'           => 'catch_store_sale_products_showcase',
            'type'              => 'select',
        )
    );

    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_sale_products_showcase_headline',
            'default'           => esc_html__( 'On Sale Products', 'catch-store' ),
            'sanitize_callback' => 'wp_kses_post',
            'label'             => esc_html__( 'Headline', 'catch-store' ),
            'active_callback'   => 'catch_store_is_sale_products_showcase_active',
            'section'           => 'catch_store_sale_products_showcase',
            'type'              => 'text',
        )
    );

    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_sale_products_showcase_subheadline',
            'default'           => esc_html__( 'Trending Fashion in this Season', 'catch-store' ),
            'sanitize_callback' => 'wp_kses_post',
            'label'             => esc_html__( 'Sub headline', 'catch-store' ),
            'active_callback'   => 'catch_store_is_sale_products_showcase_active',
            'section'           => 'catch_store_sale_products_showcase',
            'type'              => 'text',
        )
    );

    catch_store_register_option( $wp_customize, array(
            'name'              => 'catch_store_sale_products_showcase_number',
            'default'           => 4,
            'sanitize_callback' => 'catch_store_sanitize_number_range',
            'active_callback'   => 'catch_store_is_sale_products_showcase_active',
            'description'       => esc_html__( 'Save and refresh the page if No. of Products is changed. Set -1 to display all', 'catch-store' ),
            'input_attrs'       => array(
                'style' => 'width: 50px;',
                'min'   => -1,
            ),
            'label'             => esc_html__( 'No of Products', 'catch-store' ),
            'section'           => 'catch_store_sale_products_showcase',
            'type'              => 'number',
        )
    );
}
add_action( 'customize_register', 'catch_store_sale_products_showcase', 10 );

/** Active Callback Functions **/
if( ! function_exists( 'catch_store_is_sale_products_showcase_active' ) ) :
    /**
    * Return true if featured content is active
    *
    * @since Catch_Store Pro 1.0
    */
    function catch_store_is_sale_products_showcase_active( $control ) {
        $enable = $control->manager->get_setting( 'catch_store_sale_products_showcase_option' )->value();

        return ( catch_store_check_section( $enable ) );
    }
endif;
