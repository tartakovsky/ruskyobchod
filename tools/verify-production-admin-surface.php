<?php
declare(strict_types=1);

$root = $argv[1] ?? '';
if ($root === '') {
    fwrite(STDERR, "Usage: verify-production-admin-surface.php <wp-root>\n");
    exit(2);
}

$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/?verify_production_admin_surface=1';

require $root . '/wp-load.php';

$expected = [
    1 => ['login' => 'Gastronom', 'application_passwords' => 1],
    3017 => ['login' => 'Eugene Tartakovsky', 'application_passwords' => 0],
];

$admins = get_users([
    'role' => 'administrator',
    'orderby' => 'ID',
    'order' => 'ASC',
]);

if (count($admins) !== count($expected)) {
    fwrite(STDERR, 'FAIL administrator count drifted: ' . count($admins) . "\n");
    exit(1);
}

foreach ($admins as $user) {
    $id = (int) $user->ID;
    if (!isset($expected[$id]) || $user->user_login !== $expected[$id]['login']) {
        fwrite(STDERR, "FAIL unexpected administrator: id={$id} login={$user->user_login}\n");
        exit(1);
    }

    $stored = get_user_meta($id, '_application_passwords', true);
    $count = is_array($stored) ? count($stored) : 0;
    if ($count !== $expected[$id]['application_passwords']) {
        fwrite(STDERR, "FAIL application-password count drifted for administrator id={$id}\n");
        exit(1);
    }
}

global $wpdb;
$capabilityRows = $wpdb->get_results($wpdb->prepare(
    "SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s",
    $wpdb->prefix . 'capabilities'
));
$standardRoles = [
    'administrator',
    'editor',
    'author',
    'contributor',
    'subscriber',
    'customer',
    'shop_manager',
];

foreach ($capabilityRows as $row) {
    $capabilities = maybe_unserialize($row->meta_value);
    if (!is_array($capabilities)) {
        continue;
    }
    foreach (array_keys($capabilities) as $role) {
        if (!in_array($role, $standardRoles, true)) {
            fwrite(STDERR, "FAIL unexpected role assignment: user={$row->user_id} role={$role}\n");
            exit(1);
        }
    }
}

echo "OK   production administrator allowlist matches\n";
echo "OK   administrator application-password counts match\n";
echo "OK   no unexpected role assignments exist\n";
