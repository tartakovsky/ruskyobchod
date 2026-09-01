# Pin the allowed PHP surface in uploads

## Finding

Production uploads contains exactly two PHP files. Both are tiny conventional
`index.php` directory guards containing only "Silence is golden" comments:

- `gls-shipping-labels/index.php`
- `wpseo-redirects/index.php`

Staging uploads contains no PHP files. The uploads `.htaccess` already blocks
execution, but that control alone would not reveal a newly uploaded web shell.

## Change

The live security verifier now requires exactly those two production files,
pins their SHA-256 hashes, rejects any additional PHP-like file in production
uploads, and requires zero PHP-like files in staging uploads.

## Verification

The expanded security-surface verifier passed against production and staging.
No server file or runtime configuration changed.
