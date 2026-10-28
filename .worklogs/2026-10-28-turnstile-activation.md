# Turnstile activation — 2026-10-28

Owner authorized full free Turnstile setup. Created the Gastronom registration
widget in the owner's Cloudflare account, Managed mode, only hostname
`gastronom-bratislava.sk`, pre-clearance disabled. No DNS, hosting, or paid-plan
changes. Widget created through the authenticated normal Chrome session.

Installed `rusky-turnstile-config.php`, a secret-free MU loader gated to the
production home hostname. Credentials are in
`/home/u595644545/.gastronom-turnstile-private.php`, outside the public web root,
mode0600. Private keys and short-lived browser responses are NOT in Git.
Existing `rrg_keys()` and registration verifier now load the real keys.

Verification: public form embeds the matching sitekey and registration action,
loads Cloudflare api.js, does not expose the secret. PHP token parsing passed
before installation. Ten live checks passed, including rejection of absent and
forged CAPTCHA responses, valid configured secret (invalid-input-response,
not invalid-input-secret), unchanged checkout validation and enabled signup
options, no newly created users. A real automatically generated browser widget
response was accepted by production Siteverify with hostname
`gastronom-bratislava.sk`, action `registration`. No challenge was manually or
automatically solved by the agent; no test customer/order/mail was created.

CAPTCHA scope is the standalone WooCommerce registration form. Checkout retains
its existing behavior. Campaign filtering also covers WooCommerce's shared
customer creation validation. Existing spam accounts were not removed.

Rollback: move the secret-free config loader out of mu-plugins to disable
Turnstile integration; earlier session/honeypot/campaign protections remain.
