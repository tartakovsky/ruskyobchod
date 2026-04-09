<?php
/**
 * WooCommerce Compatibility File
 * See: https://wordpress.org/plugins/woocommerce/
 *
 * @package Catch Store 1.0
 */

if ( ! class_exists( 'WooCommerce' ) ) {
    // Bail if WooCommerce is not installed
    return;
}

/**
 * Query WooCommerce activation
 */
if ( ! function_exists( 'catch_store_is_woocommerce_activated' ) ) {
	function catch_store_is_woocommerce_activated() {
		return class_exists( 'WooCommerce' ) ? true : false;
	}
}

if ( ! function_exists( 'catch_store_header_mini_cart_refresh_number' ) ) {
	/**
	 * Update Header Cart items number on add to cart
	 */
	function catch_store_header_mini_cart_refresh_number( $fragments ){
	    ob_start();
	    ?>
	    <span class="count"><?php echo wp_kses_data( sprintf( _n( '%d item', '%d items', WC()->cart->get_cart_contents_count(), 'catch-store' ), WC()->cart->get_cart_contents_count() ) );?></span>
	    <?php
	        $fragments['.site-cart-contents .count'] = ob_get_clean();
	    return $fragments;
	}
}
add_filter( 'woocommerce_add_to_cart_fragments', 'catch_store_header_mini_cart_refresh_number' );

if ( ! function_exists( 'catch_store_header_mini_cart_refresh_amount' ) ) {
	/**
	 * Update Header Cart amount on add to cart
	 */
	function catch_store_header_mini_cart_refresh_amount( $fragments ){
	    ob_start();
	    ?>
	    <span class="amount"><?php echo wp_kses_data( WC()->cart->get_cart_subtotal() ); ?></span>
	    <?php
	        $fragments['.site-cart-contents .amount'] = ob_get_clean();
	    return $fragments;
	}
}
add_filter( 'woocommerce_add_to_cart_fragments', 'catch_store_header_mini_cart_refresh_amount' );

if ( ! function_exists( 'catch_store_header_cart' ) ) {
    /**
     * Display Header Cart
     *
     * @since  1.0.0
     * @uses  catch_store_is_woocommerce_activated() check if WooCommerce is activated
     * @return void
     */
    function catch_store_header_cart() {
        //account class
        if ( is_account_page() ) {
            $accountclass = 'menu-inline current-menu-item';
        } else {
            $accountclass = 'menu-inline';
        }
        //cart class
        if ( is_cart() ) {
            $cartclass = 'menu-inline site-cart current-menu-item';
        } else {
            $cartclass = 'menu-inline site-cart';
        }
        ?>

        <ul id="site-header-cart" class="site-header-cart menu">
            <li class="<?php echo esc_attr( $accountclass ); ?>">
                <?php catch_store_myaccount_icon_link(); ?>
            </li>
                <li class="<?php echo esc_attr( $cartclass ); ?>">
                    <?php catch_store_cart_link(); ?>

                    <ul id="site-cart-contents-items">
                        <?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>
                    </ul>
                </li>
        </ul>

        <?php
    }
}

if ( ! function_exists( 'catch_store_myaccount_icon_link' ) ) {
    /**
     * The account callback function
     */
    function catch_store_myaccount_icon_link() {
        echo '<a class="my-account" href="' . esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) . '" title="' . esc_attr__( 'Go to My Account', 'catch-store' ) . '"><span class="screen-reader-text">' . esc_attr__( 'My Account', 'catch-store' ) . '</span>' . catch_store_get_svg( array( 'icon' => 'user' ) ) . '</a>';
    }
}

if ( ! function_exists( 'catch_store_cart_link' ) ) {
    /**
     * Cart Link
     * Displayed a link to the cart including the number of items present and the cart total
     *
     * @return void
     * @since  1.0.0
     */
    function catch_store_cart_link() {
        ?>
            <a class="site-cart-contents" href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php esc_attr_e( 'View your shopping cart', 'catch-store' ); ?>">
                <?php echo catch_store_get_svg( array( 'icon' => 'shopping-bag', 'title' => esc_html__( 'View your shopping cart', 'catch-store' ) ) ); ?>
                <?php /* translators: number of items in the mini cart. */ ?>
                <span class="count"><?php echo wp_kses_data( sprintf( _n( '%d item', '%d items', WC()->cart->get_cart_contents_count(), 'catch-store' ), WC()->cart->get_cart_contents_count() ) );?></span><span class="sep"> / </span><span class="amount"><?php echo wp_kses_data( WC()->cart->get_cart_subtotal() ); ?></span>
            </a>
        <?php
    }
}