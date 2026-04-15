<?php
/**
 * Plugin Name: Rusky Mobile Header Polish
 * Description: Small mobile header layout fixes for the storefront.
 */

declare(strict_types=1);

add_action('wp_enqueue_scripts', static function (): void {
    if (is_admin()) {
        return;
    }

    $deps = [];
    foreach (['food-grocery-store-basic-style', 'rsll-gls-style', 'gls-style'] as $handle) {
        if (wp_style_is($handle, 'registered') || wp_style_is($handle, 'enqueued')) {
            $deps[] = $handle;
        }
    }

    $path = __DIR__ . '/rusky-mobile-header-polish.css';
    $url = content_url('mu-plugins/rusky-mobile-header-polish.css');

    wp_enqueue_style(
        'rusky-mobile-header-polish',
        $url,
        $deps,
        file_exists($path) ? (string) filemtime($path) : '1'
    );
}, 120);
