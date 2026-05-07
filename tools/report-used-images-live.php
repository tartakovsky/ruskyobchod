<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI only.\n");
    exit(1);
}

$root = $argv[1] ?? '';
if ($root === '' || !is_dir($root) || !is_file(rtrim($root, '/') . '/wp-load.php')) {
    fwrite(STDERR, "Usage: php report-used-images-live.php /absolute/path/to/wordpress\n");
    exit(1);
}

$root = rtrim($root, '/');
require $root . '/wp-load.php';

$used = [];

function add_attachment_files(int $attachment_id, array &$used): void {
    if (!$attachment_id) {
        return;
    }

    $file = get_attached_file($attachment_id);
    if ($file && file_exists($file)) {
        $used[$file] = filesize($file);
    }

    $meta = wp_get_attachment_metadata($attachment_id);
    if (!is_array($meta) || empty($meta["sizes"]) || !$file) {
        return;
    }

    $base_dir = dirname($file);
    foreach ($meta["sizes"] as $size) {
        if (empty($size["file"])) {
            continue;
        }
        $path = $base_dir . "/" . $size["file"];
        if (file_exists($path)) {
            $used[$path] = filesize($path);
        }
    }
}

$product_ids = get_posts([
    "post_type" => "product",
    "post_status" => "publish",
    "posts_per_page" => -1,
    "fields" => "ids",
]);

foreach ($product_ids as $product_id) {
    add_attachment_files((int) get_post_thumbnail_id($product_id), $used);

    $gallery = get_post_meta($product_id, "_product_image_gallery", true);
    if (!$gallery) {
        continue;
    }

    foreach (array_filter(array_map("intval", explode(",", $gallery))) as $attachment_id) {
        add_attachment_files($attachment_id, $used);
    }
}

$term_ids = get_terms([
    "taxonomy" => "product_cat",
    "hide_empty" => false,
    "fields" => "ids",
]);

foreach ($term_ids as $term_id) {
    add_attachment_files((int) get_term_meta($term_id, "thumbnail_id", true), $used);
}

$content = (string) get_post_field("post_content", 27);
if (preg_match_all('#https?://ruskyobchod\.sk/wp-content/uploads/[^"\s)]+#i', $content, $matches)) {
    foreach ($matches[0] as $url) {
        $relative = preg_replace('#^https?://ruskyobchod\.sk/#', '', $url);
        $path = $root . "/" . $relative;
        if (file_exists($path)) {
            $used[$path] = filesize($path);
        }
    }
}

arsort($used);

echo "USED_FILES=" . count($used) . PHP_EOL;
echo "USED_TOTAL_BYTES=" . array_sum($used) . PHP_EOL;

foreach (array_slice($used, 0, 80, true) as $path => $size) {
    echo $size . "\t" . $path . PHP_EOL;
}
