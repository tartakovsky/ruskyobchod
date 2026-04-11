<?php
/**
 * Plugin Name: Rusky Disable FAR Runtime
 * Description: Deprecated compatibility shim. Runtime plugin filtering now belongs to rusky-runtime-shim.php.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rusky_disable_far_runtime_deprecated(): void {
    // Intentionally empty.
    // Keep the file as a compatibility placeholder so production can
    // remove split ownership without introducing a missing-file surprise.
}
