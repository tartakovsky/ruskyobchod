<?php
/**
 * Template for displaying search forms in Catch Store
 *
 * @package Catch_Store
 */
?>

<?php $search_text = get_theme_mod( 'catch_store_search_text', esc_html__( 'Search', 'catch-store' ) ); ?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php echo _x( 'Search for:', 'label', 'catch-store' ); ?></span>
		<input type="search" class="search-field" placeholder="<?php echo esc_attr( $search_text ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
	</label>
	<button type="submit" class="search-submit"><?php echo catch_store_get_svg( array( 'icon' => 'search' ) ); ?><span class="screen-reader-text"><?php echo _x( 'Search', 'submit button', 'catch-store' ); ?></span></button>
</form>
