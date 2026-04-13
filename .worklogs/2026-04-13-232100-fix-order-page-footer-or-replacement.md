# Fix order page footer OR replacement

## Context
Slovak order pages still showed mixed footer text `Zapísaná v ALEBO OS Bratislava I,` because `rusky-order-page-language.php` used a broad `OR -> ALEBO` replacement in the customer order-page normalization layer.

## Change
- removed broad standalone `OR/ALEBO` replacements from order-page normalization maps
- kept only explicit order-page phrase replacements and em-dash separator replacements

## Verification plan
- deploy only `rusky-order-page-language.php`
- verify `order-received` for `11102` still loads and footer legal line renders `OR OS Bratislava I`
