<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI only.\n");
    exit(1);
}

$root = $argv[1] ?? '';
if ($root === '' || !is_dir($root) || !is_file(rtrim($root, '/') . '/wp-load.php')) {
    fwrite(STDERR, "Usage: php convert-used-images-to-webp-live.php /absolute/path/to/wordpress\n");
    exit(1);
}

$root = rtrim($root, '/');
require $root . '/wp-load.php';

$threshold_bytes = 150 * 1024;
$used = [];

function add_attachment_files_for_webp(int $attachment_id, array &$used): void {
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
    add_attachment_files_for_webp((int) get_post_thumbnail_id($product_id), $used);

    $gallery = get_post_meta($product_id, "_product_image_gallery", true);
    if (!$gallery) {
        continue;
    }

    foreach (array_filter(array_map("intval", explode(",", $gallery))) as $attachment_id) {
        add_attachment_files_for_webp($attachment_id, $used);
    }
}

$term_ids = get_terms([
    "taxonomy" => "product_cat",
    "hide_empty" => false,
    "fields" => "ids",
]);

foreach ($term_ids as $term_id) {
    add_attachment_files_for_webp((int) get_term_meta($term_id, "thumbnail_id", true), $used);
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

$converted = 0;
$skipped = 0;
$saved_bytes = 0;
$report = [];

foreach ($used as $path => $size) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (!in_array($ext, ["jpg", "jpeg", "png"], true)) {
        $skipped++;
        continue;
    }
    if ($size <= $threshold_bytes) {
        $skipped++;
        continue;
    }

    $dest = $path . ".webp";
    if (file_exists($dest)) {
        $skipped++;
        continue;
    }

    try {
        $image = new Imagick($path);
        if ($image->getNumberImages() > 1) {
            $image = $image->coalesceImages();
            foreach ($image as $frame) {
                $frame->setImageFormat("webp");
                $frame->setImageCompressionQuality(78);
                $frame->stripImage();
            }
            $image->writeImages($dest, true);
        } else {
            $image->setImageFormat("webp");
            $image->setImageCompressionQuality(78);
            $image->stripImage();
            $image->writeImage($dest);
        }
        $image->clear();
        $image->destroy();
        clearstatcache(true, $dest);
        $new_size = file_exists($dest) ? filesize($dest) : 0;
        $saved_bytes += max(0, $size - $new_size);
        $converted++;
        $report[] = $size . "\t" . $new_size . "\t" . $path;
    } catch (Throwable $e) {
        $report[] = "ERROR\t" . $path . "\t" . $e->getMessage();
    }
}

echo "USED_TOTAL=" . count($used) . PHP_EOL;
echo "CONVERTED=" . $converted . PHP_EOL;
echo "SKIPPED=" . $skipped . PHP_EOL;
echo "SAVED_BYTES=" . $saved_bytes . PHP_EOL;

foreach (array_slice($report, 0, 120) as $line) {
    echo $line . PHP_EOL;
}
