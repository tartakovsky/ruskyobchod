# Current Review Findings

Date: 2026-04-11

Purpose:

- It is for the next agent to review and fix.

## Confirmed Findings

- High: the language runtime is overlapping.
  - Main plugin:
    - [gastronom-lang-switcher.php](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php#L1004)
  - Emergency MU runtime:
    - [rusky-language-switcher-lite.php](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/mu-plugins/rusky-language-switcher-lite.php#L238)
  - The lite MU plugin has no guard to stand down when the main language plugin is active.
  - It also hooks:
    - [init](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/mu-plugins/rusky-language-switcher-lite.php#L193)
    - [wp_enqueue_scripts](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/mu-plugins/rusky-language-switcher-lite.php#L203)
    - [wp_body_open](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/mu-plugins/rusky-language-switcher-lite.php#L227)
    - [template_redirect](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/mu-plugins/rusky-language-switcher-lite.php#L238)
  - Result:
    - duplicate switcher behavior is possible
    - stacked output buffering is possible
    - front-page rewriting ownership is unclear

- Medium: FAR runtime ownership is still split.
  - [rusky-runtime-shim.php](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/mu-plugins/rusky-runtime-shim.php#L120)
  - [rusky-disable-far.php](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/mu-plugins/rusky-disable-far.php#L11)
  - Both filter `option_active_plugins`.
  - That means runtime ownership is still not actually single-owner.

- Medium: storefront messaging still depends on another layer to hide the inactive language.
  - [rusky-storefront-messaging.php](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/mu-plugins/rusky-storefront-messaging.php#L53)
  - [rusky-storefront-messaging.php](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/mu-plugins/rusky-storefront-messaging.php#L67)
  - These outputs still render both language blocks and rely on stripping later.
  - If the stripping layer is bypassed or partially disabled, users can see both languages.

## Verify First

- Verify: `gastronom-stock-fix.php`
  - File:
    - [gastronom-stock-fix.php](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-stock-fix/gastronom-stock-fix.php)
  - My local review flagged a syntax problem in the pushed file.
  - The other side says this is not confirmed in their environment.
  - Do not argue about it in notes.
  - First action is simple:
    - run syntax check on the exact deployed file in the target environment
    - if broken, fix it before anything else
    - if clean, remove this concern from the report

## What The Next Agent Should Review

1. Decide which language runtime is the real owner.
   - main plugin
   - or lite MU fallback
   - not both

2. Add an explicit guard so the non-owner path does not run.

3. Review every `template_redirect`, `wp_body_open`, and output-buffer hook in:
   - [gastronom-lang-switcher.php](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php)
   - [rusky-language-switcher-lite.php](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/mu-plugins/rusky-language-switcher-lite.php)
   - [rusky-runtime-shim.php](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/mu-plugins/rusky-runtime-shim.php)

4. Remove split ownership where 2 files control the same runtime decision.

5. Convert bilingual storefront fragments to single-language output at the source where possible.

## Short Version

- The main current issue is overlapping runtime ownership.
- The language layer is still not cleanly single-owner.
- FAR control is still split.
- Some storefront text still depends on later stripping.
- `gastronom-stock-fix.php` must be verified first, then either fixed or removed from concern.
