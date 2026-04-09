<?php
/**
 *  Header Image Implementation
 *
 * @package Catch_Store
 */

if ( ! function_exists( 'catch_store_featured_image' ) ) :
	/**
	 * Template for Featured Header Image from theme options
	 *
	 * To override this in a child theme
	 * simply create your own catch_store_featured_image(), and that function will be used instead.
	 *
	 * @since Audioman Pro 1.0
	 */
	function catch_store_featured_image() {
		if ( is_header_video_active() && has_header_video() ) {
			return true;
		}
		$thumbnail = 'catch-store-slider';

		if ( is_post_type_archive( 'jetpack-testimonial' ) ) {
			$jetpack_options = get_theme_mod( 'jetpack_testimonials' );

			if ( isset( $jetpack_options['featured-image'] ) && '' !== $jetpack_options['featured-image'] ) {
				$image = wp_get_attachment_image_src( (int) $jetpack_options['featured-image'], $thumbnail );
				return $image['0'];
			} else {
				return false;
			}
		} elseif ( is_post_type_archive( 'jetpack-portfolio' ) || is_post_type_archive( 'featured-content' ) || is_post_type_archive( 'ect-service' ) ) {
			$option = '';

			if ( is_post_type_archive( 'jetpack-portfolio' ) ) {
				$option = 'jetpack_portfolio_featured_image';
			} elseif ( is_post_type_archive( 'featured-content' ) ) {
				$option = 'featured_content_featured_image';
			} elseif ( is_post_type_archive( 'ect-service' ) ) {
				$option = 'ect_service_featured_image';
			}

			$featured_image = get_option( $option );

			if ( '' !== $featured_image ) {
				$image = wp_get_attachment_image_src( (int) $featured_image, $thumbnail );
				return isset( $image[0] ) ? $image[0] : false;
			} else {
				return get_header_image();
			}
		} elseif ( is_header_video_active() && has_header_video() ) {
			return true;
		} else {
			return get_header_image();
		}
	} // catch_store_featured_image
endif;

if ( ! function_exists( 'catch_store_featured_page_post_image' ) ) :
	/**
	 * Template for Featured Header Image from Post and Page
	 *
	 * To override this in a child theme
	 * simply create your own catch_store_featured_imaage_pagepost(), and that function will be used instead.
	 *
	 * @since Catch Store 1.0
	 */
	function catch_store_featured_page_post_image() {
		$thumbnail = 'catch-store-slider';

		if ( class_exists( 'WooCommerce' ) && is_shop() ) {
			if ( ! has_post_thumbnail( absint( get_option( 'woocommerce_shop_page_id' ) ) ) ) {
				return catch_store_featured_image();
			}
		} elseif ( is_home() && $blog_id = get_option('page_for_posts') ) {
			if ( has_post_thumbnail( $blog_id  ) ) {
		    	return get_the_post_thumbnail_url( $blog_id, $thumbnail );
			} else {
				return  catch_store_featured_image();
			}
		} elseif ( ! has_post_thumbnail() ) {
			return  catch_store_featured_image();
		} elseif ( is_home() && is_front_page() ) {
			return  catch_store_featured_image();
		}

		$shop_header = get_theme_mod( 'catch_store_shop_page_header_image' );
		if ( class_exists( 'WooCommerce' ) && is_shop() ) {
			return get_the_post_thumbnail_url( absint( get_option( 'woocommerce_shop_page_id' ) ), $thumbnail );
		}elseif ( class_exists( 'WooCommerce' ) && is_product () ) {
			if (  $shop_header ){
			return get_the_post_thumbnail_url( get_the_id(), $thumbnail );
			}
		}else {
			return get_the_post_thumbnail_url( get_the_id(), $thumbnail );
		}
	} // catch_store_featured_page_post_image
endif;


if ( ! function_exists( 'catch_store_featured_overall_image' ) ) :
	/**
	 * Template for Featured Header Image from theme options
	 *
	 * To override this in a child theme
	 * simply create your own catch_store_featured_pagepost_image(), and that function will be used instead.
	 *
	 * @since Audioman Pro 1.0
	 */
	function catch_store_featured_overall_image() {
		global $post;
		$enable = get_theme_mod( 'catch_store_header_media_option', 'entire-site-page-post' );

		// Check Enable/Disable header image in Page/Post Meta box
		if ( is_singular() ) {
			//Individual Page/Post Image Setting
			$individual_featured_image = get_post_meta( $post->ID, 'catch-store-header-image', true );

			if ( 'disable' === $individual_featured_image || ( 'default' === $individual_featured_image && 'disable' === $enable ) ) {
				return 'disable' ;
			} elseif ( 'enable' == $individual_featured_image && 'disable' === $enable ) {
				return catch_store_featured_page_post_image();
			}
		}

		// Check Homepage
		if ( 'homepage' === $enable ) {
			if ( is_front_page() ) {
				return catch_store_featured_image();
			}
		} elseif ( 'exclude-home' === $enable ) {
			// Check Excluding Homepage
			if ( ! is_front_page() ) {
				return catch_store_featured_image();
			}
		} elseif ( 'exclude-home-page-post' === $enable ) {
			if ( is_front_page() ) {
				return 'disable';
			} elseif ( is_singular() ) {
				return catch_store_featured_page_post_image();
			} else {
				return catch_store_featured_image();
			}
		} elseif ( 'entire-site' === $enable ) {
			// Check Entire Site
			return catch_store_featured_image();
		} elseif ( 'entire-site-page-post' === $enable ) {
			// Check Entire Site (Post/Page)
			if ( is_singular() || ( is_home() && ! is_front_page() ) ) {
				return catch_store_featured_page_post_image();
			} else {
				return catch_store_featured_image();
			}
		} elseif ( 'pages-posts' === $enable ) {
			// Check Page/Post
			if ( is_singular() ) {
				return catch_store_featured_page_post_image();
			}
		}

		return 'disable';
	} // catch_store_featured_overall_image
endif;

if ( ! function_exists( 'catch_store_header_media_text' ) ):
	/**
	 * Display Header Media Text
	 *
	 * @since Audioman Pro 1.0
	 */
	function catch_store_header_media_text() {

		if ( ! catch_store_has_header_media_text() ) {
			// Bail early if header media text is disabled on front page
			return false;
		}
		?>
		<div class="custom-header-content sections header-media-section">
			<?php catch_store_header_title( '<h2 class="entry-title">', '</h2>' ); ?>

			<?php catch_store_header_description(); ?>

			<?php if ( is_front_page() ) :
				$header_media_url      = get_theme_mod( 'catch_store_header_media_url', '#' );
				$header_media_url_text = get_theme_mod( 'catch_store_header_media_url_text', esc_html__( 'Go Shopping', 'catch-store' ) );
			?>

				<?php if ( $header_media_url_text ) : ?>
					<a class="more-link" href="<?php echo esc_url( $header_media_url ); ?>" target="<?php echo esc_url( get_theme_mod( 'catch_store_header_url_target' ) ) ? '_blank' : '_self'; ?>">

						<span class="more-button"><?php echo esc_html( $header_media_url_text ); ?><span class="screen-reader-text"><?php echo wp_kses_post( $header_media_url_text ); ?></span></span>
					</a>
				<?php endif; ?>
			<?php endif; ?>
		</div><!-- .custom-header-content -->
		<?php
	} // catch_store_header_media_text.
endif;

if ( ! function_exists( 'catch_store_has_header_media_text' ) ):
	/**
	 * Return Header Media Text fro front page
	 *
	 * @since Audioman Pro 1.0
	 */
	function catch_store_has_header_media_text() {
		$header_image = catch_store_featured_overall_image();

		if ( is_front_page() ) {
			$header_media_title    = get_theme_mod( 'catch_store_header_media_title',esc_html__( 'The Autumn Collection', 'catch-store' ) );
			$header_media_text     = get_theme_mod( 'catch_store_header_media_text', esc_html__( 'Hypnotic Time of Autumn Falls', 'catch-store' ) );
			$header_media_url      = get_theme_mod( 'catch_store_header_media_url', '#' );
			$header_media_url_text = get_theme_mod( 'catch_store_header_media_url_text', esc_html__( 'More', 'catch-store' ) );

			if ( ! $header_media_title && ! $header_media_text && ! $header_media_url && ! $header_media_url_text ) {
				// Bail early if header media text is disabled
				return false;
			}
		} elseif ( 'disable' === $header_image ) {
			return false;
		}

		return true;
	} // catch_store_has_header_media_text.
endif;

if ( ! function_exists( 'catch_store_header_title' ) ) :
	/**
	 * Display header media text
	 */
	function catch_store_header_title( $before = '', $after = '' ) {
		if ( is_front_page() ) {
			$header_media_title = get_theme_mod( 'catch_store_header_media_title', esc_html__( 'The Autumn Collection', 'catch-store' ) );
			if ( $header_media_title ) {
				echo $before . wp_kses_post( $header_media_title ) . $after;
			}
		}  elseif ( is_singular() ) {
			if ( is_page() ) {
				if( ! get_theme_mod( 'catch_store_single_page_title' ) ) {
					the_title( $before, $after );
				}
			} else {
				the_title( $before, $after );
			}
		} elseif ( is_404() ) {
			echo $before . esc_html__( 'Nothing Found', 'catch-store' ) . $after;
		} elseif ( is_search() ) {
			/* translators: %s: search query. */
			echo $before . sprintf( esc_html__( 'Search Results for: %s', 'catch-store' ), '<span>' . get_search_query() . '</span>' ) . $after;
		} else {
			the_archive_title( $before, $after );
		}
	}
endif;

if ( ! function_exists( 'catch_store_header_description' ) ) :
	/**
	 * Display header media description
	 */
	function catch_store_header_description( $before = '', $after = '' ) {
		if ( is_front_page() ) {
			echo $before . '<p class="site-header-text">' . wp_kses_post( get_theme_mod( 'catch_store_header_media_text', esc_html__( 'Hypnotic Time of Autumn Falls', 'catch-store' ) ) ) . '</p>' . $after;
		} elseif ( is_singular() && ! is_page() ) {
			echo $before . '<div class="entry-header"><div class="entry-meta">';
				catch_store_entry_posted_on();
			echo '</div><!-- .entry-meta --></div>' . $after;
		} elseif ( is_404() ) {
			echo $before . '<p>' . esc_html__( 'Oops! That page can&rsquo;t be found', 'catch-store' ) . '</p>' . $after;
		} else {
			the_archive_description( $before, $after );
		}
	}
endif;
