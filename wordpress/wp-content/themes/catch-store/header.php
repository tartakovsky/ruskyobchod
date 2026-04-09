<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * @package Catch_Store
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif; ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php do_action( 'wp_body_open' );  ?>

<?php
/**
 * Moving default Store Notice from bottom to this position.
 * For removal script, check inc/customizer/woocomerce.php function catch_store_remove_default_woo_store_notice.
 */
if ( function_exists( 'woocommerce_demo_store' ) ) {
	woocommerce_demo_store();
}
?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'catch-store' ); ?></a>

	<header id="masthead" class="site-header" role="banner">
		<?php get_template_part( 'template-parts/header/site', 'branding' ); ?>

		<?php get_template_part( 'template-parts/navigation/navigation', 'primary' ); ?>
	</header><!-- .site-header -->

	<?php catch_store_sections(); ?>

	<div id="content" class="site-content">
		<div class="wrapper">
