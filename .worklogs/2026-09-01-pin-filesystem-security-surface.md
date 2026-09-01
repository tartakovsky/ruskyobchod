# Pin the production filesystem security surface

## Finding

The production web root contains no symbolic links, no world-writable files or
directories, and `wp-config.php` has mode `0640`.

## Change

The live security verifier now treats those conditions as contracts. This
detects a symlink-based escape, an accidentally writable public path, or
weakened config permissions before such drift becomes persistent.

## Verification

The expanded security-surface verifier passed. No server file, permission, or
runtime setting changed.
