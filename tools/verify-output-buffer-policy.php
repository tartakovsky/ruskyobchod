<?php
declare(strict_types=1);

$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/?verify_output_buffer_policy=1';

require $argv[1] . '/wp-load.php';

function buffer_policy_fail(string $message): void {
    fwrite(STDERR, "FAIL {$message}\n");
    exit(1);
}

function buffer_policy_ok(string $message): void {
    echo "OK   {$message}\n";
}

function buffer_policy_has_action(string $hook, string $function, int $priority): void {
    if (has_action($hook, $function) !== $priority) {
        buffer_policy_fail("{$hook} priority {$priority} is missing {$function}");
    }

    buffer_policy_ok("{$hook} priority {$priority} has {$function}");
}

function buffer_policy_lacks_action(string $hook, string $function): void {
    if (has_action($hook, $function) !== false) {
        buffer_policy_fail("{$hook} still has retired {$function}");
    }

    buffer_policy_ok("{$hook} does not load retired {$function}");
}

function buffer_policy_has_theme_closure(): void {
    global $wp_filter;

    foreach (($wp_filter['template_redirect']->callbacks[20] ?? []) as $callback) {
        $function = $callback['function'] ?? null;
        if (!$function instanceof Closure) {
            continue;
        }

        $file = (new ReflectionFunction($function))->getFileName();
        if (is_string($file) && str_ends_with($file, '/wp-content/mu-plugins/rusky-theme-chrome-language.php')) {
            buffer_policy_ok('catalogue language buffer is owned by rusky-theme-chrome-language.php');
            return;
        }
    }

    buffer_policy_fail('template_redirect priority 20 lacks the catalogue language buffer owner');
}

if (defined('RUSKY_STAGING_MODE') && RUSKY_STAGING_MODE) {
    // The isolated staging profile deliberately runs without the main language
    // plugin, so its two local fallbacks remain active there.
    buffer_policy_lacks_action('template_redirect', 'gls_start_template_output_buffer');
    buffer_policy_has_action('template_redirect', 'rsll_start_template_output_buffer', 1);
    buffer_policy_has_action('template_redirect', 'rfpl_start_template_output_buffer', 130);
} else {
    buffer_policy_has_action('template_redirect', 'gls_start_template_output_buffer', 5);
    buffer_policy_lacks_action('template_redirect', 'rsll_start_template_output_buffer');
    buffer_policy_lacks_action('template_redirect', 'rfpl_start_template_output_buffer');
}
buffer_policy_has_theme_closure();

echo "Output-buffer policy verification complete.\n";
