<?php
/**
 * The template for displaying portfolio items
 *
 * @package Catch_Store
 */
?>

<?php
$enable = get_theme_mod( 'catch_store_portfolio_option', 'disabled' );

if ( ! catch_store_check_section( $enable ) ) {
	// Bail if portfolio section is disabled.
	return;
}

	$headline   = get_option( 'jetpack_portfolio_title', esc_html__( 'Projects', 'catch-store' ) );
	$subheadline = get_option( 'jetpack_portfolio_content' );


$classes[] = 'section';

$classes[] = 'layout-three';
?>
<div id="portfolio-content-section" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<div class="wrapper">
		<?php if ( $headline || $subheadline ) : ?>
			<div class="section-heading-wrapper portfolio-section-headline">
			<?php if ( $headline ) : ?>
				<div class="section-title-wrapper">
					<h2 class="section-title"><?php echo wp_kses_post( $headline ); ?></h2>
				</div><!-- .section-title-wrapper -->
			<?php endif; ?>

			<?php if ( $subheadline ) : ?>
				<div class="taxonomy-description-wrapper section-subtitle">
					<?php
	                $subheadline = apply_filters( 'the_content', $subheadline );
	                echo str_replace( ']]>', ']]&gt;', $subheadline );
	                ?>
				</div><!-- .taxonomy-description-wrapper -->
			<?php endif; ?>
			</div><!-- .section-heading-wrapper -->
		<?php endif; ?>

		<div class="section-content-wrapper portfolio-content-wrapper layout-three">
			<?php
				get_template_part( 'template-parts/portfolio/post-types', 'portfolio' ); 
			?>
		</div><!-- .portfolio-wrapper -->
	</div><!-- .wrapper -->
</div><!-- #portfolio-content-section -->
