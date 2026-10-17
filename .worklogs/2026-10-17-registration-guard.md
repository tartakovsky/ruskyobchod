# Registration abuse guard — 2026-10-17

Owner authorized protecting registrations; only free services permitted.
Gmail outgoing messages and live customer records confirmed fake standalone
WooCommerce registrations behind delivery failures, including customer IDs 3141
and 3142. No orders associated with the three investigated accounts.

Deployed new mu-plugin `rusky-registration-guard.php` from this isolated worktree
based on origin/main 97c341f3. Main checkout contains unrelated owner changes.

Active protections: offscreen honeypot, signed form timestamp (3 seconds to 2
hours), and five attempts per connection IP per 30-minute window. Validation
runs before WooCommerce customer creation and mail. IP transient keys are HMACs;
no raw IP logging. Transient counters are best-effort, not an atomic distributed
rate limiter. Checkout and admin creation are outside this standalone form hook.

Turnstile server verification is implemented but INACTIVE: no site/secret keys
exist. It can be enabled using RUSKY_TURNSTILE_SITE_KEY and
RUSKY_TURNSTILE_SECRET_KEY in private configuration. Tokens must match the home
hostname and registration action; missing tokens and upstream errors reject.
No paid services, subscriptions, or new external accounts created. Local checks
reduce basic bots but do not prevent sophisticated bots with rotating IPs.

Validation: PHP 8.2 lint and 20 standalone security checks passed on server using
private files before deployment. Public My Account response is uncached and
contains guard fields; browser confirms honeypot offscreen. Browser POST with
existing fake-account email and filled honeypot rejected with registration error
without creating an account. Homepage/wp-login HTTP 200. No real registration,
order, or test email created. Existing accounts were not deleted.
