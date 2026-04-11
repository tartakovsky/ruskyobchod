## Summary

Mapped the three active storefront normalization layers inside `gastronom-lang-switcher.php` so the next migration step can move one boundary at a time instead of editing the file as one undifferentiated block.

## Current layers

### 1. `gls_normalize_server_rendered_html()`

Role:

- broad server-rendered response normalization
- account / checkout / cart shell cleanup
- legal/company text cleanup
- empty cart shell handling

Status:

- mixed owner
- still carries live-critical retained fallback surface

### 2. `gls_normalize_front_page_html()`

Role:

- front-page-specific cleanup
- skip-link normalization
- category-card bilingual text cleanup
- removal of embedded patch script

Status:

- narrower and more self-contained
- better candidate for future extraction than the broad storefront chrome layer

### 3. `gls_normalize_storefront_chrome_html()`

Role:

- residual storefront chrome cleanup for menu/tag text and a small remaining phrase set

Status:

- partially reduced
- still contains retained fallback surface for:
  - footer brand heading
  - footer legal/company text
  - cookie notice buttons

## Operational conclusion

The next safe structural move is not another blind reduction inside `gls_normalize_storefront_chrome_html()`.

The safer next move is to treat these three functions as separate boundaries and only migrate one of them at a time after proving exact coverage on the affected live path.
