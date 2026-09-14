# New shop format and primary domain

User explicitly requested a bilingual live homepage announcement, courier pickup note for Bolt/Wolt where access permits, and primary domain migration to gastronom-bratislava.sk before installing the separate order-review candidate.

Published the RU/SK homepage notice dated 14 September, corrected entrance directions in footer/contact content and translations, and cleared stale Elementor element caches. Reversed the old host redirect, changed WP_HOME/WP_SITEURL and selected serialized-safe database URL values, updated hardcoded front-page assets, and preserved old-host POST callbacks. Source files were compared against production hashes before replacement. No orders/staging data changed. Apple Pay's own domain-change callback successfully verified the new domain.

TLS, redirect path/query preservation, canonical/assets, RU/SK homepage/contact, login, category, test cart and checkout payment rendering verified. No test order/payment submitted; cart cleaned. Accessible debug.log not modified since April. External Google Fonts request has a CORS failure on checkout, recorded for follow-up. Bolt/Wolt not posted: no authorized merchant session; official instructions and Slovak note delivered in docs/site-format-20260914/REPORT.md.

Backups outside webroot: /home/u595644545/backups/format-domain-20260914-01. See report for exact selective rollback. Used direct CLI PHP because WP CLI boot segfaults on this host; scripts prepared locally and copied by SSH. Main working tree has unrelated marketing changes; work isolated in site-format-domain-20260914.

Order-review production installation remains pending and its source was not included.
