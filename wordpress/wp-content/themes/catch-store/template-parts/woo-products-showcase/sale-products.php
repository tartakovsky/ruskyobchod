<?php
/**
 * The template for displaying Woo Products Showcase
 *
 * @package Audioman
 */

if ( ! class_exists( 'WooCommerce' ) ) {
    // Bail if WooCommerce is not installed
    return;
}

$enable_content = get_theme_mod( 'catch_store_sale_products_showcase_option', 'disabled' );

if ( ! catch_store_check_section( $enable_content ) ) {
	// Bail if featured content is disabled.
	return;
}

$number         = get_theme_mod( 'catch_store_sale_products_showcase_number', 4 );
$columns        = 4;
$paginate       = false;
$product_filter = 'on_sale';


$shortcode = '[products';

if ( $number ) {
	$shortcode .= ' limit="' . esc_attr( $number ) . '"';
}

if ( $columns ) {
	$shortcode .= ' columns="' . absint( $columns ) . '"';
}

if ( $paginate ) {
	$shortcode .= ' paginate="' . esc_attr( $paginate ) . '"';
}

if ( $product_filter && 'none' !== $product_filter ) {
	$shortcode .= ' ' . esc_attr( $product_filter ) . '="true"';
}

$shortcode .= ']';

$title     = get_theme_mod( 'catch_store_sale_products_showcase_headline', esc_html__( 'On Sale Products', 'catch-store' ) );
$sub_title = get_theme_mod( 'catch_store_sale_products_showcase_subheadline', esc_html__( 'Trending Fashion in this Season', 'catch-store' ) );
?>

<div id="product-content-section" class="section">
	<div class="wrapper">
		<?php if ( $title || $sub_title ) : ?>
			<div class="section-heading-wrapper portfolio-section-headline">
				<?php if ( '' != $title ) : ?>
					<div class="section-title-wrapper">
						<h2 class="section-title"><?php echo wp_kses_post( $title ); ?></h2>
					</div><!-- .section-title-wrapper -->
				<?php endif; ?>

				<?php if ( $sub_title ) : ?>
					<div class="taxonomy-description-wrapper">
						<p class="section-subtitle">
							<?php echo wp_kses_post( $sub_title ); ?>
						</p>
					</div><!-- .taxonomy-description-wrapper -->
				<?php endif; ?>
			</div><!-- .section-heading-wrapper -->
		<?php endif; ?>

		<div class="section-content-wrapper product-content-wrapper">
			<?php echo do_shortcode( $shortcode ); ?>
		</div><!-- .section-content-wrapper -->
	</div><!-- .wrapper -->
</div><!-- .sectionr -->
