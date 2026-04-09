<?php
/**
 * Food Grocery Store functions and definitions
 *
 * @package Food Grocery Store
 */
/* Breadcrumb Begin */
function food_grocery_store_the_breadcrumb() {
	if (!is_home()) {
		echo '<a href="';
			echo esc_url( home_url() );
		echo '">';
			bloginfo('name');
		echo "</a> ";
		if (is_category() || is_single()) {
			the_category(',');
			if (is_single()) {
				echo "<span> ";
					the_title();
				echo "</span> ";
			}
		} elseif (is_page()) {
			echo "<span> ";
				the_title();
		}
	}
}
/* Theme Setup */
if ( ! function_exists( 'food_grocery_store_setup' ) ) :
 
function food_grocery_store_setup() {

	$GLOBALS['content_width'] = apply_filters( 'food_grocery_store_content_width', 640 );
	
	load_theme_textdomain( 'food-grocery-store', get_template_directory() . '/languages' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-slider' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'comment-list', 'search-form', 'comment-form', ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 240,
		'width'       => 240,
		'flex-height' => true,
	) );
	add_image_size('food-grocery-store-homepage-thumb',240,145,true);
	
    register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'food-grocery-store' ),
	) );

	add_theme_support( 'custom-background', array(
		'default-color' => 'ffffff'
	) );

	//selective refresh for sidebar and widgets
	add_theme_support( 'customize-selective-refresh-widgets' );

	/*
	 * Enable support for Post Formats.
	 *
	 * See: https://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array('image','video','gallery','audio',) );

	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, icons, and column width.
	 */
	add_editor_style( array( 'css/editor-style.css', food_grocery_store_font_url() ) );

	// Theme Activation Notice
	global $pagenow;

	if (
		is_admin()
		&&
		('themes.php' == $pagenow)
		// &&
		// isset( $_GET['activated'] )
	) {
		add_action('admin_notices', 'food_grocery_store_activation_notice');
	}
}
endif;

add_action( 'after_setup_theme', 'food_grocery_store_setup' );

// Notice after Theme Activation

function food_grocery_store_activation_notice() {

	$food_grocery_store_meta = get_option( 'food_grocery_store_admin_notice' );

	if (!$food_grocery_store_meta) {
		echo '<div id="food-grocery-store-welcome-notice" class="notice notice-success is-dismissible welcome-notice">';
		echo '<div class="notice-row">';
			echo '<div class="notice-text">';
				echo '<p class="welcome-text1">'. esc_html__( '🎉 Welcome to VW Themes,', 'food-grocery-store' ) .'</p>';
				echo '<p class="welcome-text2">'. esc_html__( 'You are now using the Food Grocery Store, a beautifully designed theme to kickstart your website.', 'food-grocery-store' ) .'</p>';
				echo '<p class="welcome-text3">'. esc_html__( 'To help you get started quickly, use the options below:', 'food-grocery-store' ) .'</p>';
				echo '<span class="import-btn"><a href="'. esc_url( admin_url( 'admin.php?page=food_grocery_store_guide' ) ) .'" class="button button-primary">'. esc_html__( 'IMPORT DEMO', 'food-grocery-store' ) .'</a></span>';
				echo '<span class="demo-btn"><a href="'. esc_url( 'https://www.vwthemes.net/vw-grocery-pro/' ) .'" class="button button-primary" target=_blank>'. esc_html__( 'VIEW DEMO', 'food-grocery-store' ) .'</a></span>';
				echo '<span class="upgrade-btn"><a href="'. esc_url( 'https://www.vwthemes.com/products/grocery-store-wordpress-theme' ) .'" class="button button-primary" target=_blank>'. esc_html__( 'UPGRADE TO PRO', 'food-grocery-store' ) .'</a></span>';
				echo '<span class="bundle-btn"><a href="'. esc_url( 'https://www.vwthemes.com/products/wp-theme-bundle' ) .'" class="button button-primary" target=_blank>'. esc_html__( 'BUNDLE OF 400+ THEMES', 'food-grocery-store' ) .'</a></span>';
			echo '</div>';
			echo '<div class="notice-img1">';
				echo '<img src="' . esc_url( get_template_directory_uri() . '/inc/getstart/images/arrow-notice.png' ) . '" width="180" alt="' . esc_attr__( 'Food Grocery Store', 'food-grocery-store' ) . '" />';
			echo '</div>';
			echo '<div class="notice-img2">';
				echo '<img src="' . esc_url( get_template_directory_uri() . '/inc/getstart/images/bundle-notice.png' ) . '" width="180" alt="' . esc_attr__( 'Food Grocery Store', 'food-grocery-store' ) . '" />';
			echo '</div>';	
		echo '</div>';	
	echo '</div>';
}
}

/* Theme Widgets Setup */
function food_grocery_store_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Blog Sidebar', 'food-grocery-store' ),
		'description'   => __( 'Appears on blog page sidebar', 'food-grocery-store' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget mb-5 p-3 %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title px-3 py-2">',
		'after_title'   => '</h3>',
	) );
	
	register_sidebar( array(
		'name'          => __( 'Page Sidebar', 'food-grocery-store' ),
		'description'   => __( 'Appears on page sidebar', 'food-grocery-store' ),
		'id'            => 'sidebar-2',
		'before_widget' => '<aside id="%1$s" class="widget mb-5 p-3 %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title px-3 py-2">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Sidebar 3', 'food-grocery-store' ),
		'description'   => __( 'Appears on blog page sidebar', 'food-grocery-store' ),
		'id'            => 'sidebar-3',
		'before_widget' => '<aside id="%1$s" class="widget mb-5 p-3 %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title px-3 py-2">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Navigation 1', 'food-grocery-store' ),
		'description'   => __( 'Appears on footer 1', 'food-grocery-store' ),
		'id'            => 'footer-1',
		'before_widget' => '<aside id="%1$s" class="widget py-3 %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title px-0 py-2">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Navigation 2', 'food-grocery-store' ),
		'description'   => __( 'Appears on footer 2', 'food-grocery-store' ),
		'id'            => 'footer-2',
		'before_widget' => '<aside id="%1$s" class="widget py-3 %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title px-0 py-2">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Navigation 3', 'food-grocery-store' ),
		'description'   => __( 'Appears on footer 3', 'food-grocery-store' ),
		'id'            => 'footer-3',
		'before_widget' => '<aside id="%1$s" class="widget py-3 %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title px-0 py-2">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Navigation 4', 'food-grocery-store' ),
		'description'   => __( 'Appears on footer 4', 'food-grocery-store' ),
		'id'            => 'footer-4',
		'before_widget' => '<aside id="%1$s" class="widget py-3 %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title px-0 py-2">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Shop Page Sidebar', 'food-grocery-store' ),
		'description'   => __( 'Appears on shop page', 'food-grocery-store' ),
		'id'            => 'woocommerce-shop-sidebar',
		'before_widget' => '<aside id="%1$s" class="widget mb-5 p-3 %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title px-3 py-2">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Single Product Sidebar', 'food-grocery-store' ),
		'description'   => __( 'Appears on single product page', 'food-grocery-store' ),
		'id'            => 'woocommerce-single-sidebar',
		'before_widget' => '<aside id="%1$s" class="widget mb-5 p-3 %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title px-3 py-2">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Social Icon', 'food-grocery-store' ),
		'description'   => __( 'Appears on right side footer', 'food-grocery-store' ),
		'id'            => 'footer-icon',
		'before_widget' => '<aside id="%1$s" class="widget mb-5 p-3 %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title px-3 py-2">',
		'after_title'   => '</h3>',
	) );     
}
add_action( 'widgets_init', 'food_grocery_store_widgets_init' );

/* Theme Font URL */
function food_grocery_store_font_url() {
	$font_url      = '';
	$font_family   = array(
		'ABeeZee:ital@0;1',
	 	'Abril Fatface',
	 	'Acme',
	 	'Alfa Slab One',
	 	'Allura',
	 	'Anton', 
	 	'Architects Daughter',
	 	'Archivo:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Arimo:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700',
	 	'Arsenal:ital,wght@0,400;0,700;1,400;1,700',
	 	'Arvo:ital,wght@0,400;0,700;1,400;1,700',
	 	'Alegreya Sans:ital,wght@0,100;0,300;0,400;0,500;0,700;0,800;0,900;1,100;1,300;1,400;1,500;1,700;1,800;1,900',
	 	'Asap:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Assistant:wght@200;300;400;500;600;700;800',
	 	'Averia Serif Libre:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700',
	 	'Bangers',
	 	'Boogaloo',
	 	'Bad Script',
	 	'Barlow Condensed:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Bitter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Bree Serif',
	 	'BenchNine:wght@300;400;700',
	 	'Cabin:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700',
	 	'Cardo:ital,wght@0,400;0,700;1,400',
	 	'Courgette',
	 	'Caveat Brush',
	 	'Cherry Swash:wght@400;700',
	 	'Cormorant Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700',
	 	'Crimson Text:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700',
	 	'Cuprum:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700',
	 	'Cookie',
	 	'Coming Soon',
	 	'Charm:wght@400;700',
	 	'Chewy',
	 	'Days One',
	 	'DM Serif Display:ital@0;1',
	 	'Dosis:wght@200;300;400;500;600;700;800',
	 	'EB Garamond:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800',
	 	'Economica:ital,wght@0,400;0,700;1,400;1,700',
	 	'Exo 2:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Fira Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Fredoka One',
	 	'Fjalla One',
	 	'Frank Ruhl Libre:wght@300;400;500;700;900',
	 	'Gabriela',
	 	'Gloria Hallelujah',
	 	'Great Vibes',
	 	'Handlee',
	 	'Hammersmith One',
	 	'Heebo:wght@100;200;300;400;500;600;700;800;900',
	 	'Hind:wght@300;400;500;600;700',
	 	'Inconsolata:wght@200;300;400;500;600;700;800;900',
	 	'Indie Flower',
	 	'IM Fell English SC',
	 	'Julius Sans One',
	 	'Jomhuria',
	 	'Josefin Slab:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700',
	 	'Josefin Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700',
	 	'Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Kaushan Script',
	 	'Krub:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;1,200;1,300;1,400;1,500;1,600;1,700',
	 	'Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900',
	 	'Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700',
	 	'Libre Baskerville:ital,wght@0,400;0,700;1,400',
	 	'Literata:ital,opsz,wght@0,7..72,200;0,7..72,300;0,7..72,400;0,7..72,500;0,7..72,600;0,7..72,700;0,7..72,800;0,7..72,900;1,7..72,200;1,7..72,300;1,7..72,400;1,7..72,500;1,7..72,600;1,7..72,700;1,7..72,800;1,7..72,900',
	 	'Lobster',
	 	'Lobster Two:ital,wght@0,400;0,700;1,400;1,700',
	 	'Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900',
	 	'Marck Script',
	 	'Marcellus',
	 	'Merienda One',
	 	'Monda:wght@400;700',
	 	'Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Mulish:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;0,1000;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900;1,1000',
	 	'Noto Serif:ital,wght@0,400;0,700;1,400;1,700',
	 	'Nunito Sans:ital,wght@0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,600;1,700;1,800;1,900',
	 	'Open Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800',
	 	'Overpass:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Overpass Mono:wght@300;400;500;600;700',
	 	'Oxygen:wght@300;400;700',
	 	'Oswald:wght@200;300;400;500;600;700',
	 	'Orbitron:wght@400;500;600;700;800;900',
	 	'Patua One',
	 	'Pacifico',
	 	'Padauk:wght@400;700',
	 	'Playball',
	 	'Playfair Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'PT Sans:ital,wght@0,400;0,700;1,400;1,700',
	 	'PT Serif:ital,wght@0,400;0,700;1,400;1,700',
	 	'Philosopher:ital,wght@0,400;0,700;1,400;1,700',
	 	'Permanent Marker',
	 	'Poiret One',
	 	'Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Prata',
	 	'Quicksand:wght@300;400;500;600;700',
	 	'Quattrocento Sans:ital,wght@0,400;0,700;1,400;1,700',
	 	'Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900',
	 	'Roboto Condensed:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700',
	 	'Rokkitt:wght@100;200;300;400;500;600;700;800;900',
	 	'Ropa Sans:ital@0;1',
	 	'Russo One',
	 	'Righteous',
	 	'Saira:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Satisfy',
	 	'Sen:wght@400;700;800',
	 	'Source Sans Pro:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700;1,900',
	 	'Shadows Into Light Two',
	 	'Shadows Into Light',
	 	'Sacramento',
	 	'Sail',
	 	'Shrikhand',
	 	'Staatliches',
	 	'Stylish',
	 	'Tangerine:wght@400;700',
	 	'Titillium Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700',
	 	'Trirong:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700',
	 	'Unica One',
	 	'VT323',
	 	'Varela Round',
	 	'Vampiro One',
	 	'Vollkorn:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Volkhov:ital,wght@0,400;0,700;1,400;1,700',
	 	'Work Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900',
	 	'Yanone Kaffeesatz:wght@200;300;400;500;600;700',
	 	'ZCOOL XiaoWei'
	);
	
	$query_args = array(
		'family'	=> rawurlencode(implode('|',$font_family)),
	);
	$font_url = add_query_arg($query_args,'//fonts.googleapis.com/css');
	return $font_url;
	$contents = wptt_get_webfont_url( esc_url_raw( $fonts_url ) );
}

/* Theme enqueue scripts */
function food_grocery_store_scripts() {	
	wp_enqueue_style( 'food-grocery-store-font', food_grocery_store_font_url(), array() );
	wp_enqueue_style( 'food-grocery-store-block-style', get_theme_file_uri('/assets/css/blocks.css') );
	wp_enqueue_style( 'food-grocery-store-block-patterns-style-frontend', get_theme_file_uri('/inc/block-patterns/css/block-frontend.css') );
	wp_enqueue_style( 'bootstrap-style', get_template_directory_uri().'/assets/css/bootstrap.css' );
	wp_enqueue_style( 'food-grocery-store-basic-style', get_stylesheet_uri() );
	/* Inline style sheet */
	require get_parent_theme_file_path( '/custom-style.php' );
	wp_add_inline_style( 'food-grocery-store-basic-style',$food_grocery_store_custom_css );
	wp_enqueue_style( 'font-awesome-css', get_template_directory_uri().'/assets/css/fontawesome-all.css' );
	wp_enqueue_script( 'jquery-superfish', get_theme_file_uri( '/assets/js/jquery.superfish.js' ), array( 'jquery' ), '2.1.2', true );
	wp_enqueue_script( 'bootstrap-js', get_template_directory_uri(). '/assets/js/bootstrap.js', array('jquery') ,'',true);
	wp_enqueue_script( 'food-grocery-store-custom-scripts', get_template_directory_uri() . '/assets/js/custom.js', array('jquery'),'' ,true );

	if (get_theme_mod('food_grocery_store_animation', true) == true){
		wp_enqueue_script( 'jquery-wow', get_template_directory_uri() . '/assets/js/wow.js', array('jquery') );
		wp_enqueue_style( 'animate-css', get_template_directory_uri().'/assets/css/animate.css' );
	}
	
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	/* Enqueue the Dashicons script */
	wp_enqueue_style( 'dashicons' );
}
add_action( 'wp_enqueue_scripts', 'food_grocery_store_scripts' );

/**
 * Enqueue block editor style
 */
function food_grocery_store_block_editor_styles() {
	wp_enqueue_style( 'food-grocery-store-font', food_grocery_store_font_url(), array() );
    wp_enqueue_style( 'food-grocery-store-block-patterns-style-editor', get_theme_file_uri( '/inc/block-patterns/css/block-editor.css' ), false, '1.0', 'all' );
    wp_enqueue_style( 'bootstrap-style', get_template_directory_uri().'/assets/css/bootstrap.css' );
}
add_action( 'enqueue_block_editor_assets', 'food_grocery_store_block_editor_styles' );

function food_grocery_store_sanitize_dropdown_pages( $page_id, $setting ) {
  	// Ensure $input is an absolute integer.
  	$page_id = absint( $page_id );
  	// If $page_id is an ID of a published page, return it; otherwise, return the default.
  	return ( 'publish' == get_post_status( $page_id ) ? $page_id : $setting->default );
}

function food_grocery_store_sanitize_choices( $input, $setting ) {
    global $wp_customize; 
    $control = $wp_customize->get_control( $setting->id ); 
    if ( array_key_exists( $input, $control->choices ) ) {
        return $input;
    } else {
        return $setting->default;
    }
}

function food_grocery_store_sanitize_float( $input ) {
	return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

function food_grocery_store_sanitize_number_range( $number, $setting ) {
	
	// Ensure input is an absolute integer.
	$number = absint( $number );
	
	// Get the input attributes associated with the setting.
	$atts = $setting->manager->get_control( $setting->id )->input_attrs;
	
	// Get minimum number in the range.
	$min = ( isset( $atts['min'] ) ? $atts['min'] : $number );
	
	// Get maximum number in the range.
	$max = ( isset( $atts['max'] ) ? $atts['max'] : $number );
	
	// Get step.
	$step = ( isset( $atts['step'] ) ? $atts['step'] : 1 );
	
	// If the number is within the valid range, return it; otherwise, return the default
	return ( $min <= $number && $number <= $max && is_int( $number / $step ) ? $number : $setting->default );
}

function food_grocery_store_sanitize_phone_number( $phone ) {
	return preg_replace( '/[^\d+]/', '', $phone );
}

/* Excerpt Limit Begin */
function food_grocery_store_string_limit_words($string, $word_limit) {
	$words = explode(' ', $string, ($word_limit + 1));
	if(count($words) > $word_limit)
	array_pop($words);
	return implode(' ', $words);
}

add_action( 'wp_ajax_food_grocery_store_reset_all_settings', 'food_grocery_store_reset_all_settings' );
function food_grocery_store_reset_all_settings() {
	remove_theme_mod( 'food_grocery_store_slider_arrows' );
	remove_theme_mod( 'food_grocery_store_slider_page' );
	remove_theme_mod( 'food_grocery_store_slider_title_hide_show' );
	remove_theme_mod( 'food_grocery_store_slider_content_hide_show' );
	remove_theme_mod( 'food_grocery_store_slider_content_option' );
	remove_theme_mod( 'food_grocery_store_slider_content_padding_top_bottom' );
	remove_theme_mod( 'food_grocery_store_slider_content_padding_left_right' );
	remove_theme_mod( 'food_grocery_store_slider_excerpt_number' );
	remove_theme_mod( 'food_grocery_store_slider_height' );
	remove_theme_mod( 'food_grocery_store_slider_speed' );
	remove_theme_mod( 'food_grocery_store_footer_background_color' );
	remove_theme_mod( 'food_grocery_store_footer_text' );
	remove_theme_mod( 'food_grocery_store_copyright_font_size' );
	remove_theme_mod( 'food_grocery_store_copyright_alignment' );
	remove_theme_mod( 'food_grocery_store_footer_scroll' );
	remove_theme_mod( 'food_grocery_store_scroll_top_alignment' );
	wp_send_json_success(
		array(
			'success' => true,
			'message' => "Reset Completed",
		)
	);
}



// Change number or products per row to 3
add_filter('loop_shop_columns', 'food_grocery_store_loop_columns');
	if (!function_exists('food_grocery_store_loop_columns')) {
	function food_grocery_store_loop_columns() {
		return get_theme_mod( 'food_grocery_store_products_per_row', '3' ); 
		// 3 products per row
	}
}

//Change number of products that are displayed per page (shop page)
add_filter( 'loop_shop_per_page', 'food_grocery_store_products_per_page' );
function food_grocery_store_products_per_page( $cols ) {
  	return  get_theme_mod( 'food_grocery_store_products_per_page',9);
}

function food_grocery_store_logo_title_hide_show(){
	if(get_theme_mod('food_grocery_store_logo_title_hide_show') == '1' ) {
		return true;
	}
	return false;
}

function food_grocery_store_tagline_hide_show(){
	if(get_theme_mod('food_grocery_store_tagline_hide_show',0) == '1' ) {
		return true;
	}
	return false;
}

//Active Callback
function food_grocery_store_default_slider(){
	if(get_theme_mod('food_grocery_store_slider_type', 'Default slider') == 'Default slider' ) {
		return true;
	}
	return false;
}

function food_grocery_store_advance_slider(){
	if(get_theme_mod('food_grocery_store_slider_type', 'Default slider') == 'Advance slider' ) {
		return true;
	}
	return false;
}

function food_grocery_store_blog_post_featured_image_dimension(){
	if(get_theme_mod('food_grocery_store_blog_post_featured_image_dimension') == 'custom' ) {
		return true;
	}
	return false;
}



if (!function_exists('food_grocery_store_edit_link')) :

    function food_grocery_store_edit_link($view = 'default')
    {
        global $post;
            edit_post_link(
                sprintf(
                    wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers */
                        __('Edit <span class="screen-reader-text">%s</span>', 'food-grocery-store'),
                        array(
                            'span' => array(
                                'class' => array(),
                            ),
                        )
                    ),
                    get_the_title()
                ),
                '<span class="edit-link"><i class="fas fa-edit"></i>',
                '</span>'
            );

    }
endif;


/* Implement the Custom Header feature. */
require get_template_directory() . '/inc/custom-header.php';

function food_grocery_store_init_setup() {
	/* Custom template tags for this theme. */
	require get_template_directory() . '/inc/template-tags.php';

	/* Customizer additions. */
	require get_template_directory() . '/inc/customizer.php';

	/* Customizer additions. */
	require get_template_directory() . '/inc/themes-widgets/social-icon.php';

	/* Typography */
	require get_template_directory() . '/inc/typography/ctypo.php';

	/* Plugin Activation */
	require get_template_directory() . '/inc/getstart/plugin-activation.php';

	/* get started */
	require get_template_directory() . '/inc/getstart/getstart.php';

	/* TGM. */
	require get_template_directory() . '/inc/tgm/tgm.php';

	/* Block Pattern */
	require get_template_directory() . '/inc/block-patterns/block-patterns.php';

	/* Webfonts */
	require get_template_directory() . '/inc/wptt-webfont-loader.php';

	/* Customizer additions. */
	require get_template_directory() . '/inc/themes-widgets/about-us-widget.php';

	/* Customizer additions. */
	require get_template_directory() . '/inc/themes-widgets/contact-us-widget.php';

	//define
	define('FOOD_GROCERY_STORE_FREE_THEME_DOC',__('https://preview.vwthemesdemo.com/docs/free-food-grocery-store/','food-grocery-store'));
	define('FOOD_GROCERY_STORE_SUPPORT',__('https://wordpress.org/support/theme/food-grocery-store/','food-grocery-store'));
	define('FOOD_GROCERY_STORE_REVIEW',__('https://wordpress.org/support/theme/food-grocery-store/reviews','food-grocery-store'));
	define('FOOD_GROCERY_STORE_BUY_NOW',__('https://www.vwthemes.com/products/grocery-store-wordpress-theme','food-grocery-store'));
	define('FOOD_GROCERY_STORE_LIVE_DEMO',__('https://www.vwthemes.net/vw-grocery-pro/','food-grocery-store'));
	define('FOOD_GROCERY_STORE_PRO_DOC',__('https://preview.vwthemesdemo.com/docs/food-grocery-store-pro/','food-grocery-store'));
	define('FOOD_GROCERY_STORE_FAQ',__('https://www.vwthemes.com/faqs/','food-grocery-store'));
	define('FOOD_GROCERY_STORE_CONTACT',__('https://www.vwthemes.com/contact/','food-grocery-store'));
	define('FOOD_GROCERY_STORE_CHILD_THEME',__('https://developer.wordpress.org/themes/advanced-topics/child-themes/','food-grocery-store'));
	define('FOOD_GROCERY_STORE_CREDIT',__('https://www.vwthemes.com/products/free-grocery-wordpress-theme','food-grocery-store'));
	define('FOOD_GROCERY_STORE_THEME_BUNDLE_BUY_NOW',__('https://www.vwthemes.com/products/wp-theme-bundle','food-grocery-store'));
	define('FOOD_GROCERY_STORE_THEME_BUNDLE_DOC',__('https://preview.vwthemesdemo.com/docs/theme-bundle/','food-grocery-store'));


	if ( ! function_exists( 'food_grocery_store_credit' ) ) {
		function food_grocery_store_credit(){
			echo "<a href=".esc_url(FOOD_GROCERY_STORE_CREDIT)." target='_blank'>".esc_html__('Grocery WordPress Theme','food-grocery-store')."</a>";
		}
	}

	if ( ! defined( 'FOOD_GROCERY_STORE_GO_PRO_URL' ) ) {
		define( 'FOOD_GROCERY_STORE_GO_PRO_URL', 'https://www.vwthemes.com/products/grocery-store-wordpress-theme');
	}

	if ( ! defined( 'FOOD_GROCERY_STORE_GET_STARTED_URL' ) ) {
		define( 'FOOD_GROCERY_STORE_GET_STARTED_URL', 'themes.php?page=food_grocery_store_guide');
	}
}
add_action( 'after_setup_theme', 'food_grocery_store_init_setup' );	

// Admin notice code START
function food_grocery_store_dismissed_notice() {
	update_option( 'food_grocery_store_admin_notice', true );
}
add_action( 'wp_ajax_food_grocery_store_dismissed_notice', 'food_grocery_store_dismissed_notice' );

//After Switch theme function
add_action('after_switch_theme', 'food_grocery_store_getstart_setup_options');
function food_grocery_store_getstart_setup_options () {
    update_option('food_grocery_store_admin_notice', false );
}
// Admin notice code END