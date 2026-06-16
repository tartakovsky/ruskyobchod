# Product category audit tool

## Context

After the live fix for product `11326` (`Karamelky Čierny ríbezle /Карамель Черная смородина`), the follow-up requirement was to prevent similar storefront category drift.

The implementation follows the handoff operating model:

- no production Plugin Editor usage
- no Hostinger File Manager usage
- local repo change first
- live access only for read-only verification
- separate worklog before commit

## Change

Added:

- `tools/audit-product-categories.sh`

The tool runs a read-only category audit over the live WooCommerce database through SSH. It does not load WordPress and does not use WP-CLI, because current full WordPress bootstrap through WP-CLI was observed to segfault.

Security and architecture choices:

- no secrets are stored in the repository
- `wp-config.php` is read only on the server
- DB credentials remain inside the remote SSH/PHP process
- no files are uploaded to production
- no database writes are performed
- audit logic stays in `tools/` as an operational runbook, not inside storefront plugins

## Audit checks

The tool fails on:

- published products without `product_cat`
- suspicious product names inside the alcohol category
- alcohol products assigned to any additional product category
- stored product-category count mismatches

The tool reports, but does not fail on:

- products assigned to more than one `product_cat`
- WooCommerce term-count transient presence

## Verification

Local syntax:

- `sh -n tools/audit-product-categories.sh` passed

Live read-only run:

- command: `./tools/audit-product-categories.sh`
- result: `PASS`
- published products: `486`
- product categories: `22`
- published products without `product_cat`: none
- suspicious products inside alcohol category: none
- alcohol products assigned to additional categories: none
- stored category count mismatches: none
- products assigned to more than one `product_cat`: none

Review-only output noted current WooCommerce term-count transients:

- `_transient_timeout_wc_term_counts`
- `_transient_wc_term_counts`
