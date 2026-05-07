<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI only.\n");
    exit(1);
}

$root = $argv[1] ?? getcwd();
$backupDir = $argv[2] ?? sys_get_temp_dir();

if (!is_dir($root) || !is_file($root . '/wp-load.php')) {
    fwrite(STDERR, "Usage: php cleanup-front-page-content.php /absolute/path/to/wordpress [/absolute/path/to/backup-dir]\n");
    exit(1);
}

if (!is_dir($backupDir)) {
    fwrite(STDERR, "Backup directory does not exist: {$backupDir}\n");
    exit(1);
}

require $root . '/wp-load.php';

$frontPageId = (int) get_option('page_on_front');
if ($frontPageId <= 0) {
    fwrite(STDERR, "No static front page is configured.\n");
    exit(1);
}

$post = get_post($frontPageId);
if (!$post instanceof WP_Post) {
    fwrite(STDERR, "Front page post {$frontPageId} was not found.\n");
    exit(1);
}

$original = (string) $post->post_content;
$updated = $original;

$updated = preg_replace(
    '~\s*html\{scroll-behavior:smooth\}.*?(?=<div class="gc-grid" id="gc-categories">)~su',
    "\n\n",
    $updated,
    1,
    $cssRemovals
) ?? $updated;

$updated = preg_replace(
    '~\s*\(function\(\)\{\s*function patchCards\(\).*?\}\)\(\);\s*~su',
    "\n\n",
    $updated,
    1,
    $scriptRemovals
) ?? $updated;

$updated = preg_replace("/\n{3,}/", "\n\n", $updated) ?? $updated;

if ($updated === $original) {
    echo "NO_CHANGES front_page={$frontPageId}\n";
    exit(0);
}

$backupPath = rtrim($backupDir, '/') . '/front-page-' . $frontPageId . '-before-cleanup-' . gmdate('Ymd-His') . '.html';
file_put_contents($backupPath, $original);

$result = wp_update_post([
    'ID' => $frontPageId,
    'post_content' => $updated,
], true);

if (is_wp_error($result)) {
    fwrite(STDERR, "UPDATE_FAILED " . $result->get_error_message() . "\n");
    exit(1);
}

echo "UPDATED front_page={$frontPageId} css_blocks={$cssRemovals} script_blocks={$scriptRemovals} backup={$backupPath}\n";
