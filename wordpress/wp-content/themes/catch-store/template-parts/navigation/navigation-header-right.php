<?php
/**
 * Displays Header Right Navigation
 *
 * @package Catch_Store
 */
?>

<div id="site-header-secondary-cart-wrapper" class="site-header-cart-wrapper">
	<ul class="site-header-cart menu">
		<li class="menu-inline site-cart">
			<?php
				if( function_exists( 'catch_store_cart_link' ) ) {
					catch_store_cart_link();
				}
			?>
		</li>
	</ul>
</div><!-- .site-header-cart-wrapper -->
