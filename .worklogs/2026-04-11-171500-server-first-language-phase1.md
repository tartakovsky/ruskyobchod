# Server-First Language Phase 1

Date: 2026-04-11

## Scope

Begin the server-first translation migration without deploying new live changes in the same cycle.

This first slice targets:

- shared language context ownership
- switcher ownership overlap
- bilingual source output in storefront messaging

## What changed

### Shared server language core

Added:

- `wordpress/wp-content/mu-plugins/rusky-server-language-core.php`

This file now owns shared helpers for:

- current language resolution
- switcher URL generation
- switcher HTML rendering
- lite-runtime stand-down decision

### Main language plugin

Updated:

- `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`

The plugin now delegates these primitives to the shared server core when available:

- current language
- switcher URL
- server language
- switcher rendering

### Emergency lite runtime

Updated:

- `wordpress/wp-content/mu-plugins/rusky-language-switcher-lite.php`

The lite runtime now explicitly stands down on its own hooks when the main language runtime is available:

- `init`
- `wp_enqueue_scripts`
- `wp_body_open`
- `template_redirect`

This reduces double ownership on:

- switcher rendering
- cookie handling
- output buffering
- front-page normalization

### Storefront messaging

Updated:

- `wordpress/wp-content/mu-plugins/rusky-storefront-messaging.php`

Converted these outputs from bilingual source markup to single-language server output:

- frozen-products delivery notice
- footer credit

## Verification

- `git diff --check` passed
- no new formatting or whitespace errors introduced
- the changed messaging owner no longer emits `gls-content-ru/sk` wrappers for the two converted outputs

## Result

- the first server-first translation owner now exists
- emergency lite runtime no longer has to compete for switcher ownership when the main plugin is present
- two storefront blocks no longer depend on later stripping to hide the inactive language

## Remaining work in this migration

- FAR single-owner cleanup
- more source-level single-language rendering for storefront blocks
- runtime hook review around output buffering and `template_redirect`
- separate verification of `gastronom-stock-fix.php` syntax in an environment that has PHP available
