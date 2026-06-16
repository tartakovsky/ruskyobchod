#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"

case "$REMOTE_ROOT" in
    *"'"*)
        echo "REMOTE_ROOT must not contain a single quote" >&2
        exit 2
        ;;
esac

ssh -p "$REMOTE_PORT" -o BatchMode=yes "$REMOTE_HOST" "REMOTE_ROOT='$REMOTE_ROOT' php" <<'PHP'
<?php
declare(strict_types=1);

$root = getenv('REMOTE_ROOT') ?: '';
$config_path = rtrim($root, '/') . '/wp-config.php';
if ($root === '' || !is_readable($config_path)) {
    fwrite(STDERR, "FAIL cannot read wp-config.php at {$config_path}\n");
    exit(2);
}

$config = file_get_contents($config_path);
if ($config === false) {
    fwrite(STDERR, "FAIL cannot load wp-config.php\n");
    exit(2);
}

function cfg_const(string $config, string $name): string
{
    $pattern = "~define\\(\\s*['\"]" . preg_quote($name, '~') . "['\"]\\s*,\\s*(['\"])(.*?)\\1\\s*\\)\\s*;~s";
    if (!preg_match($pattern, $config, $m)) {
        fwrite(STDERR, "FAIL missing {$name} in wp-config.php\n");
        exit(2);
    }
    return stripcslashes($m[2]);
}

function cfg_prefix(string $config): string
{
    if (!preg_match('~\\$table_prefix\\s*=\\s*([\'"])(.*?)\\1\\s*;~s', $config, $m)) {
        fwrite(STDERR, "FAIL missing table_prefix in wp-config.php\n");
        exit(2);
    }
    return stripcslashes($m[2]);
}

function quote_ident(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function print_rows(string $title, array $rows, array $columns): void
{
    echo "\n{$title}\n";
    if (!$rows) {
        echo "  OK none\n";
        return;
    }
    foreach ($rows as $row) {
        $parts = [];
        foreach ($columns as $column) {
            $parts[] = $column . '=' . (string)($row[$column] ?? '');
        }
        echo "  - " . implode(' | ', $parts) . "\n";
    }
}

$db_name = cfg_const($config, 'DB_NAME');
$db_user = cfg_const($config, 'DB_USER');
$db_pass = cfg_const($config, 'DB_PASSWORD');
$db_host = cfg_const($config, 'DB_HOST');
$prefix = cfg_prefix($config);

$dsn_host = $db_host;
$dsn_port = null;
if (str_contains($db_host, ':')) {
    [$dsn_host, $dsn_port] = explode(':', $db_host, 2);
}

$dsn = 'mysql:host=' . $dsn_host . ';dbname=' . $db_name . ';charset=utf8mb4';
if ($dsn_port !== null && ctype_digit($dsn_port)) {
    $dsn .= ';port=' . $dsn_port;
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "FAIL DB connection failed: " . $e->getMessage() . "\n");
    exit(2);
}

$posts = quote_ident($prefix . 'posts');
$postmeta = quote_ident($prefix . 'postmeta');
$terms = quote_ident($prefix . 'terms');
$term_taxonomy = quote_ident($prefix . 'term_taxonomy');
$term_relationships = quote_ident($prefix . 'term_relationships');
$options = quote_ident($prefix . 'options');

$summary = [
    'published_products' => (int)$pdo->query("SELECT COUNT(*) FROM {$posts} WHERE post_type='product' AND post_status='publish'")->fetchColumn(),
    'product_categories' => (int)$pdo->query("SELECT COUNT(*) FROM {$term_taxonomy} WHERE taxonomy='product_cat'")->fetchColumn(),
];

$missing_category = $pdo->query("
    SELECT p.ID AS id, p.post_title AS title
    FROM {$posts} p
    WHERE p.post_type='product'
      AND p.post_status='publish'
      AND NOT EXISTS (
          SELECT 1
          FROM {$term_relationships} tr
          JOIN {$term_taxonomy} tt ON tt.term_taxonomy_id=tr.term_taxonomy_id
          WHERE tt.taxonomy='product_cat'
            AND tr.object_id=p.ID
      )
    ORDER BY p.ID
")->fetchAll();

$suspicious_alcohol = $pdo->query("
    SELECT
      p.ID AS id,
      p.post_title AS title,
      COALESCE(pm.meta_value, '') AS dotypos_id,
      GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ' | ') AS categories
    FROM {$posts} p
    JOIN {$term_relationships} tr ON tr.object_id=p.ID
    JOIN {$term_taxonomy} tt ON tt.term_taxonomy_id=tr.term_taxonomy_id AND tt.taxonomy='product_cat'
    JOIN {$terms} t ON t.term_id=tt.term_id
    LEFT JOIN {$postmeta} pm ON pm.post_id=p.ID AND pm.meta_key='dotypos_product_id'
    WHERE p.post_type='product'
      AND p.post_status='publish'
      AND t.term_id=196
      AND LOWER(p.post_title) REGEXP 'karamel|карамель|bonb|конфет|zephir|зефир|vafle|вафл|pernik|perník|пряник|cukr|слад'
    GROUP BY p.ID
    ORDER BY p.post_title
")->fetchAll();

$alcohol_with_non_alcohol_category = $pdo->query("
    SELECT
      p.ID AS id,
      p.post_title AS title,
      GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ' | ') AS categories
    FROM {$posts} p
    JOIN {$term_relationships} tr ON tr.object_id=p.ID
    JOIN {$term_taxonomy} tt ON tt.term_taxonomy_id=tr.term_taxonomy_id AND tt.taxonomy='product_cat'
    JOIN {$terms} t ON t.term_id=tt.term_id
    WHERE p.post_type='product'
      AND p.post_status='publish'
    GROUP BY p.ID
    HAVING SUM(CASE WHEN t.term_id=196 THEN 1 ELSE 0 END) > 0
       AND COUNT(DISTINCT t.term_id) > 1
    ORDER BY p.post_title
")->fetchAll();

$count_mismatches = $pdo->query("
    SELECT
      t.term_id AS term_id,
      t.name AS category,
      tt.count AS stored_count,
      COUNT(p.ID) AS actual_publish_count
    FROM {$terms} t
    JOIN {$term_taxonomy} tt ON tt.term_id=t.term_id AND tt.taxonomy='product_cat'
    LEFT JOIN {$term_relationships} tr ON tr.term_taxonomy_id=tt.term_taxonomy_id
    LEFT JOIN {$posts} p ON p.ID=tr.object_id AND p.post_type='product' AND p.post_status='publish'
    GROUP BY t.term_id, t.name, tt.count
    HAVING stored_count<>actual_publish_count
    ORDER BY t.name
")->fetchAll();

$duplicate_same_taxonomy = $pdo->query("
    SELECT
      p.ID AS id,
      p.post_title AS title,
      COUNT(DISTINCT tt.term_taxonomy_id) AS product_cat_count,
      GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ' | ') AS categories
    FROM {$posts} p
    JOIN {$term_relationships} tr ON tr.object_id=p.ID
    JOIN {$term_taxonomy} tt ON tt.term_taxonomy_id=tr.term_taxonomy_id AND tt.taxonomy='product_cat'
    JOIN {$terms} t ON t.term_id=tt.term_id
    WHERE p.post_type='product'
      AND p.post_status='publish'
    GROUP BY p.ID
    HAVING product_cat_count > 1
    ORDER BY p.post_title
    LIMIT 50
")->fetchAll();

$wc_term_transients = $pdo->query("
    SELECT option_name
    FROM {$options}
    WHERE option_name IN ('_transient_wc_term_counts', '_transient_timeout_wc_term_counts')
    ORDER BY option_name
")->fetchAll();

echo "Product category audit for ruskyobchod.sk\n";
echo "remote_root={$root}\n";
echo "published_products={$summary['published_products']}\n";
echo "product_categories={$summary['product_categories']}\n";

print_rows('Published products without product_cat', $missing_category, ['id', 'title']);
print_rows('Suspicious products inside alcohol category', $suspicious_alcohol, ['id', 'title', 'dotypos_id', 'categories']);
print_rows('Alcohol products assigned to additional product categories', $alcohol_with_non_alcohol_category, ['id', 'title', 'categories']);
print_rows('Stored category count mismatches', $count_mismatches, ['term_id', 'category', 'stored_count', 'actual_publish_count']);
print_rows('Products assigned to more than one product_cat (review only)', $duplicate_same_taxonomy, ['id', 'title', 'product_cat_count', 'categories']);
print_rows('WooCommerce term count transients present (review only)', $wc_term_transients, ['option_name']);

$failures = count($missing_category)
    + count($suspicious_alcohol)
    + count($alcohol_with_non_alcohol_category)
    + count($count_mismatches);

echo "\nresult=" . ($failures === 0 ? 'PASS' : 'FAIL') . "\n";
exit($failures === 0 ? 0 : 1);
PHP
