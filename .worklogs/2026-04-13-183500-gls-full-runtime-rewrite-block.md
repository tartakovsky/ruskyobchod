## Context

The previous GLS fixes proved two different problems:

- logged-in operator frontend could fall into `503`
- broad exclusion of logged-in frontend would also disable bilingual-block stripping

That means the file cannot be safely maintained as one monolithic “sensitive runtime” switch.

## Decision

Treat `gastronom-lang-switcher.php` as one rewrite block and split its boundary model into:

- render boundary
- runtime mutation boundary

## Local rewrite candidate

Prepared locally:

- `gls_is_render_blocked_request()`
- `gls_is_runtime_mutation_blocked_request()`

Rewired locally:

- `wp_redirect`
- `locale`
- `determine_locale`
- `gettext`
- `wcpay_locale`
- WooPayments footer/script mutations
- footer block normalization
- template output buffer

## Status

- local syntax check on remote temp file: green
- no live deploy yet at this stage
- next step: deploy one file and verify public/logged-in RU/SK matrix immediately
