## Summary

Fixed the live checkout regression where the billing country field became unusable on the storefront.

## Root cause

`wordpress/wp-content/mu-plugins/rusky-theme-chrome-language.php`

The storefront HTML normalizer was translating checkout form nodes after render. Its checkout translation pass included both:

- `select[@data-placeholder]`
- `option`

and then unconditionally wrote `nodeValue` back to every matched node.

On a `<select>` element this collapses nested `<option>` nodes into a single text node. As a result:

- `billing_country` rendered as plain text inside the `<select>`
- WooCommerce `country-select.min.js` hit `null.length`
- the country field stopped working and shipping updates broke

`shipping_country` could still look normal in some cases depending on the selected value path, which made the bug appear inconsistent.

## Fix

Changed the checkout normalization pass so it still translates:

- placeholders
- data labels
- option text
- noscript button text

but never rewrites the text content of the `<select>` element itself.

## Verification

After deploy to live:

- `billing_country` server HTML again contains real `<option>` nodes
- browser DOM shows options for empty / `AT` / `SK`
- default billing country resolves to `SK`
- checkout console no longer shows the WooCommerce `country-select.min.js` error
- shipping methods appear again after checkout reload
