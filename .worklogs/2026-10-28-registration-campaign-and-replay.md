# Registration abuse follow-up — 2026-10-28

Owner reported another registration-related delivery failure. Gmail and the
production database confirm two unsolicited customer registrations on
2026-10-27 at 04:28:43 and 04:28:49 UTC (05:28 Bratislava), IDs3144/3145,
no orders. Both emails contain the same webonlinepostmail.de campaign marker.
One welcome email bounced with 550 5.7.134. No usable HTTP access log was
available, so the exact request route and historical replay are not proven.

The previous guard was active. Turnstile is still unconfigured/inactive.
Its old signed timestamp could be reused, with no browser binding. The guard
now renders a random form token bound to a Secure/HttpOnly/SameSite=Lax session
cookie, and atomically consumes it using a non-autoloaded option. Hourly cleanup
removes expired claims. Account pages explicitly remain uncached. An exact
campaign marker filter rejects these addresses at both standalone validation
and the shared WooCommerce pre-insert validation boundary. Other plus aliases
and email providers remain permitted. No changes to account/checkout options.

This blocks the observed campaign and token replay; it does not claim CAPTCHA
protection or prevention of bots that obtain fresh valid forms and cookies.

Validation: PHP8.2 lint, 33 isolated security checks, 12 live non-creating checks;
public form POST for an existing bogus email returns guard rejection. Home and
My Account HTTP200; empty checkout HTTP302 to cart is expected. No customer,
order or email created; existing bogus accounts preserved. Backup outside webroot:
/home/u595644545/backups/registration-guard-20261028/rusky-registration-guard-113111.php
Deployed SHA256: 38b083d56397ab34ad5c7e593c7cd039dcf5d48711f7b0237c41b503cedbf76e
