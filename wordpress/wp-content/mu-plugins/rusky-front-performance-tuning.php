<?php
/**
 * Plugin Name: Rusky Front Performance Tuning
 * Description: Safe front-end performance trims for the live storefront.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rfpt_is_front_page_request(): bool
{
    return !is_admin() && !wp_doing_ajax() && !is_customize_preview() && is_front_page();
}

function rfpt_homepage_derivative_map(): array
{
    return [
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/gastronom-logo.png' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/gastronom-logo-home-200.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2025/09/image_2400x1200.png' => 'https://ruskyobchod.sk/wp-content/uploads/2025/09/image_2400x1200-home-1600.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/Alko.jpg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/Alko-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/close-up-futuristic-soft-drink-scaled.jpg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/close-up-futuristic-soft-drink-scaled-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/Dzhemy.jpg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/Dzhemy-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/68dd38210edc84693653f675-1.jpeg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/68dd38210edc84693653f675-1-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/Karamel.jpg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/Karamel-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/68de59a50edc846936543a75-1-scaled.jpeg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/68de59a50edc846936543a75-1-scaled-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/Konfety.jpg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/Konfety-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/68de579d0edc846936543a05-1-scaled.jpeg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/68de579d0edc846936543a05-1-scaled-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/Lapsha.jpg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/Lapsha-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/68dd3840de9da6af4df9450f-1.jpeg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/68dd3840de9da6af4df9450f-1-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/68de5c100edc846936543b2c-1.jpeg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/68de5c100edc846936543b2c-1-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/68dd3854de9da6af4df94511-1.jpeg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/68dd3854de9da6af4df94511-1-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/Konservatsiya.jpg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/Konservatsiya-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/68de5c40de9da6af4df98a4c-1.jpeg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/68de5c40de9da6af4df98a4c-1-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/68de5c5ade9da6af4df98a4f-1.jpeg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/68de5c5ade9da6af4df98a4f-1-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/68dd3880d82091e41c7c9b59-1.jpeg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/68dd3880d82091e41c7c9b59-1-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/68de5c770edc846936543b3f-1.jpeg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/68de5c770edc846936543b3f-1-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/Dllya-zdorovya.jpg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/Dllya-zdorovya-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/Prazdnichnye.jpg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/Prazdnichnye-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/Hleb.jpg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/Hleb-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/ad3dd88d6df011eeb16356181a0358a2_upscaled.jpeg.webp' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/ad3dd88d6df011eeb16356181a0358a2_upscaled-home-560.webp',
        'https://ruskyobchod.sk/wp-content/uploads/2026/03/Pngtree-dark-chocolate-bar-on-white_15459579-1-.png' => 'https://ruskyobchod.sk/wp-content/uploads/2026/03/Pngtree-dark-chocolate-bar-on-white_15459579-1--home-560.webp',
    ];
}

function rfpt_pick_inline_style_handle(array $preferredHandles): ?string
{
    foreach ($preferredHandles as $handle) {
        if (wp_style_is($handle, 'registered') || wp_style_is($handle, 'enqueued')) {
            return $handle;
        }
    }

    return null;
}

function rfpt_homepage_cookie_notice_css(): string
{
    static $css = null;

    if ($css !== null) {
        return $css;
    }

    $path = WP_CONTENT_DIR . '/plugins/cookie-notice/css/front.min.css';
    if (!is_file($path)) {
        $css = '';
        return $css;
    }

    $css = trim((string) file_get_contents($path));
    return $css;
}

function rfpt_homepage_icon_svg(string $icon, string $class = ''): string
{
    if (!function_exists('is_front_page') || !is_front_page() || is_admin()) {
        return '';
    }

    $paths = [
        'bars' => '<path d="M3 6.75h18M3 12h18M3 17.25h18" />',
        'times' => '<path d="M6 6l12 12M18 6 6 18" />',
        'user' => '<path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0" />',
        'cart' => '<path d="M3.75 5.25h2.1l1.8 8.25h8.88l1.47-6.75H6.9M9 18.75a1.125 1.125 0 1 0 0 2.25 1.125 1.125 0 0 0 0-2.25Zm7.5 0a1.125 1.125 0 1 0 0 2.25 1.125 1.125 0 0 0 0-2.25Z" />',
        'chevron-down' => '<path d="m6 9 6 6 6-6" />',
        'arrow-up' => '<path d="M12 19.5V4.5m0 0-5.25 5.25M12 4.5l5.25 5.25" />',
    ];

    if (!isset($paths[$icon])) {
        return '';
    }

    $classAttr = trim('rfpt-home-icon ' . $class);

    return '<svg class="' . esc_attr($classAttr) . '" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths[$icon] . '</svg>';
}

/**
 * WordPress emoji assets are not needed for the storefront and add extra JS/CSS.
 */
add_action('init', static function (): void {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
});

/**
 * Dequeue only the clearly unnecessary front-page assets.
 */
add_action('wp_enqueue_scripts', static function (): void {
    if (is_admin() || wp_doing_ajax() || is_customize_preview()) {
        return;
    }

    if (!is_user_logged_in()) {
        wp_dequeue_style('dashicons');
    }

    if (!is_front_page()) {
        return;
    }

    $inlineHandle = rfpt_pick_inline_style_handle([
        'food-grocery-store-basic-style',
        'gls-style',
        'rsll-gls-style',
        'wp-block-library',
    ]);

    if ($inlineHandle) {
        $inlineCss = '';
        foreach ([
            __DIR__ . '/rusky-mobile-header-polish.css',
            __DIR__ . '/rusky-front-page-categories.css',
        ] as $cssPath) {
            if (is_file($cssPath)) {
                $inlineCss .= "\n" . trim((string) file_get_contents($cssPath)) . "\n";
            }
        }

        $inlineCss .= "\n.rfpt-home-icon{display:inline-block;vertical-align:middle;flex:0 0 auto}\n";
        $inlineCss .= ".product-btn .rfpt-home-icon{width:16px;height:16px}\n";
        $inlineCss .= ".account .rfpt-home-icon,.cart_no .rfpt-home-icon{width:1em;height:1em}\n";
        $inlineCss .= ".scrollup .rfpt-home-icon{width:18px;height:18px}\n";
        $inlineCss .= ".responsivetoggle .rfpt-home-icon,.closebtn .rfpt-home-icon{width:22px;height:22px}\n";
        $inlineCss .= "\n" . rfpt_homepage_cookie_notice_css() . "\n";

        if ($inlineCss !== '') {
            wp_add_inline_style($inlineHandle, $inlineCss);
        }
    }

    // The active homepage does not use WOW classes, so this animation bundle is dead weight.
    wp_dequeue_script('jquery-wow');
    wp_deregister_script('jquery-wow');
    wp_dequeue_style('animate-css');
    wp_deregister_style('animate-css');

    // The theme enqueues an enormous Google Fonts URL even when the homepage renders with system fonts.
    wp_dequeue_style('food-grocery-store-font');
    wp_deregister_style('food-grocery-store-font');

    // Homepage shell uses a tiny inline SVG icon set instead of the full Font Awesome bundle.
    wp_dequeue_style('font-awesome-css');
    wp_deregister_style('font-awesome-css');

    // Front page has no pagination markup, so this stylesheet is unused there.
    wp_dequeue_style('wp-pagenavi');
    wp_deregister_style('wp-pagenavi');

    // Homepage does not render Elementor widgets or Woo blocks, so these extra stylesheets are unused there.
    foreach ([
        'rusky-mobile-header-polish',
        'elementor-frontend',
        'elementor-gf-local-roboto',
        'elementor-gf-local-roboto-css',
        'elementor-gf-local-robotoslab',
        'elementor-gf-local-robotoslab-css',
    ] as $handle) {
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }

    // Active homepage has no Woo product/archive/cart widgets, so storefront Woo assets are unnecessary there.
    foreach ([
        'woocommerce-layout',
        'woocommerce-general',
        'woocommerce-smallscreen',
    ] as $handle) {
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }

    foreach ([
        'jquery-superfish',
        'bootstrap-js',
        'jquery-blockui',
        'wc-add-to-cart',
        'sourcebuster-js',
        'wc-order-attribution',
        'woocommerce',
    ] as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }

    wp_dequeue_style('wc-blocks-style');
    wp_deregister_style('wc-blocks-style');
    wp_dequeue_style('food-grocery-store-block-patterns-style-frontend');
    wp_deregister_style('food-grocery-store-block-patterns-style-frontend');
    wp_dequeue_style('rusky-front-page-categories');
    wp_deregister_style('rusky-front-page-categories');
    wp_dequeue_style('cookie-notice-front');
    wp_deregister_style('cookie-notice-front');
}, 99999);

/**
 * Elementor local Google-font styles can still be printed late.
 * Suppress only these front-page font styles at output time.
 */
add_filter('style_loader_tag', static function (string $html, string $handle, string $href, string $media): string {
    if (is_admin() || wp_doing_ajax() || is_customize_preview() || !is_front_page()) {
        return $html;
    }

    if (strpos($handle, 'elementor-gf-local-') === 0) {
        return '';
    }

    if (strpos($href, '/wp-content/uploads/elementor/google-fonts/css/') !== false) {
        return '';
    }

    return $html;
}, 99999, 4);

/**
 * Add a concrete localized meta description to the homepage.
 */
add_action('wp_head', static function (): void {
    if (is_admin() || wp_doing_ajax() || is_customize_preview() || !is_front_page()) {
        return;
    }

    $description = 'Gastronom — obchod s ruskými potravinami v Bratislave. Katalóg potravín s doručením po Slovensku a do Rakúska.';

    if (function_exists('gls_is_current_lang_ru') && gls_is_current_lang_ru()) {
        $description = 'Гастроном — русский магазин продуктов в Братиславе. Каталог продуктов с доставкой по Словакии и в Австрию.';
    }

    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
}, 1);

/**
 * Replace the oversized homepage logo with a dedicated derivative.
 */
add_filter('get_custom_logo', static function (string $html): string {
    if (!rfpt_is_front_page_request() || $html === '') {
        return $html;
    }

    $html = strtr($html, [
        'https://ruskyobchod.sk/wp-content/uploads/2026/02/gastronom-logo.png' => 'https://ruskyobchod.sk/wp-content/uploads/2026/02/gastronom-logo-home-200.webp',
    ]);

    return preg_replace_callback(
        '~<img[^>]*class="custom-logo"[^>]*>~i',
        static function (array $matches): string {
            $tag = preg_replace('~\s+srcset="[^"]*"~i', '', $matches[0]) ?? $matches[0];
            $tag = preg_replace('~\s+sizes="[^"]*"~i', '', $tag) ?? $tag;
            $tag = preg_replace('~\s+width="[^"]*"~i', '', $tag) ?? $tag;
            $tag = preg_replace('~\s+height="[^"]*"~i', '', $tag) ?? $tag;

            return str_replace('<img', '<img width="200" height="200"', $tag);
        },
        $html
    ) ?? $html;
}, 20);

/**
 * Normalize front-page content without rewriting the full page output buffer.
 */
add_filter('the_content', static function (string $content): string {
    if (!rfpt_is_front_page_request() || !in_the_loop() || !is_main_query() || $content === '') {
        return $content;
    }

    $content = strtr($content, rfpt_homepage_derivative_map());

    $content = preg_replace_callback(
        '~<img[^>]*class="wp-block-cover__image-background wp-image-8522"[^>]*>~i',
        static function (array $matches): string {
            $tag = preg_replace('~\s+srcset="[^"]*"~i', '', $matches[0]) ?? $matches[0];
            $tag = preg_replace('~\s+sizes="[^"]*"~i', '', $tag) ?? $tag;
            $tag = preg_replace('~\s+width="[^"]*"~i', '', $tag) ?? $tag;
            $tag = preg_replace('~\s+height="[^"]*"~i', '', $tag) ?? $tag;

            return str_replace('<img', '<img width="1600" height="800" sizes="100vw"', $tag);
        },
        $content
    ) ?? $content;

    $content = preg_replace_callback(
        '~<div class="gc-card-img"><img\b[^>]*src="https://ruskyobchod\.sk/wp-content/uploads/[^"]+-home-560\.webp"[^>]*>~i',
        static function (array $matches): string {
            $tag = preg_replace('~\s+width="[^"]*"~i', '', $matches[0]) ?? $matches[0];
            $tag = preg_replace('~\s+height="[^"]*"~i', '', $tag) ?? $tag;

            return str_replace('<div class="gc-card-img"><img', '<div class="gc-card-img"><img width="560" height="560"', $tag);
        },
        $content
    ) ?? $content;

    return $content;
}, 20);
