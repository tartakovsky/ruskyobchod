<?php
/**
 * Displays Primary Navigation
 *
 * @package Catch_Store
 */
?>

<div id="header-navigation-area">
	<div class="wrapper">
		<button id="primary-menu-toggle" class="menu-primary-toggle menu-toggle" aria-controls="primary-menu" aria-expanded="false">
			<?php
			echo catch_store_get_svg( array( 'icon' => 'bars' ) );
			echo catch_store_get_svg( array( 'icon' => 'close' ) );
			echo '<span class="menu-label-prefix">'. esc_attr__( 'Primary ', 'catch-store' ) . '</span>'; ?>
				<span class="menu-label">Menu</span>
		</button>

		<div id="site-header-menu" class="site-primary-menu">
			<?php if ( has_nav_menu( 'menu-1' ) ) : ?>
				<nav id="site-primary-navigation" class="main-navigation site-navigation custom-primary-menu" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'catch-store' ); ?>">
					<?php wp_nav_menu( array(
						'theme_location'	=> 'menu-1',
						'container_class'	=> 'primary-menu-container',
						'menu_class'		=> 'primary-menu',
					) ); ?>
				</nav><!-- #site-primary-navigation.custom-primary-menu -->
			<?php else : ?>
				<nav id="site-primary-navigation" class="main-navigation site-navigation default-page-menu" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'catch-store' ); ?>">
					<?php wp_page_menu(
						array(
							'menu_class' => 'primary-menu-container',
							'before'     => '<ul id="primary-page-menu" class="primary-menu">',
							'after'      => '</ul>',
						)
					); ?>
				</nav><!-- #site-primary-navigation.default-page-menu -->
			<?php endif; ?>
			
			<div id="site-header-primary-cart-wrapper" class="site-header-cart-wrapper">	
				<?php
					if( function_exists( 'catch_store_header_cart' ) ) {
						catch_store_header_cart();
					}
				?>
			</div>
		</div><!-- .site-primary-menu -->
	</div><!-- .wrapper -->
</div><!-- #header-navigation-area -->