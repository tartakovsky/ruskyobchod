# Simplify customer consent without changing fulfillment ownership

User authorized the agreed simpler consent flow, RU/SK administration and exact
shortage explanations, requiring handoff.zip and no workaround layers. Read all
seven archive documents; used a separate worktree based on fetched origin/main.
Original dirty worktrees preserved. Main lacked the previously deployed order
review module: copied its October 3 deployed sources and existing guards/carrier
extensions into this worktree, retaining main September 30 fractional-stock and
October 1 native-translation fixes. These dependency changes reconcile source
control; the current release deploys only its nine manifest files.

Matched all 29 production baseline files. Production is PHP 8.2.34, WordPress
6.9.10, WooCommerce 10.7.0. Included official WP 6.9.10 in the isolated verifier.

Existing Review/WordPress command path retains version checks and atomic order
lock. New pure Consent module signs recipient, order, revision, proposal, language
and expiry; explicit customer POST records agreement through that same path. GET
never agrees. Replays are idempotent, old revisions and changed recipients fail.
Public page is private/no-store/no-referrer and does not run payment/POS/shipping.
Consent expires in seven days. Existing mails remain supported manually; no
regeneration/resending, order migration, guard/cutoff change or configuration write.

Administrative manual consent selects email/phone, with actor and UTC timestamp
automatically recorded. Refresh occurs on return to an awaiting card or an explicit
button, without timers or page-wide observers. Admin view localization leaves
stored customer language and source snapshot unchanged. CPT legacy generated title
is corrected only in the editor's view object. Addresses retain line breaks.

Mail explains removed and partially reduced quantities, aggregates duplicate rows,
keeps kg precision, escapes customer/product text, and does not confuse a pack's
actual weight with physical shortage. Customer-request changes are not labelled
stock shortage. Checkout terms error uses the existing server language owner.

All 23 suites passed: 572 checks including native posts/HPOS, actual public HTTP,
mail, original source immutability, POS idempotency, stock and carrier boundaries.
Server PHP 8.2 syntax verification passed. A live controlled transaction is not
performed by this UI update. Report and file-only manifest: docs/order-review/update-20261010.

User clarified after the main deployment: product names must follow the order
language (SK order → SK product names), independently of administrative controls.
Removed the optional catalog-name recovery proposal. One-line presentation change
in existing WordPress owner, checked with 10 language cases, 43 adapter cases and
90 native WP 6.9.10/Woo 10.7.0 posts/HPOS checks. Separate one-file manifest and
fresh backup. Original order/configuration/ownership are unchanged.

User-requested extra testing: full isolated rerun passed 572 checks before that
clarification; installed module passed 44 in-memory business scenarios with SQL,
HTTP and mail blocked. Default WordPress transients isolated to in-process cache.

Investigated real spam using Gmail metadata of the three 11654 emails and safe
server/DNS reads. Confirmed correction DMARC fail/quarantine, no DKIM and Hostinger
envelope domain different from From; no SMTP transport. No mail/DNS settings changed.
Preferred branded mailbox is only proposed and may not exist. MAIL-AUDIT.md records
the actual evidence and pending authenticated SMTP/OAuth setup.

Installed main UI release a63eec3b and order-language clarification 458cf506.
Full read-only file/config/original-order verification passed in a fresh process;
all 32 source baselines confirmed after follow-up. Additional installed tests:
44 business scenarios and 17 signature/consent/language checks, zero database
writes, real messages or real sales. Runtime test objects stay unsaved.

User authorized proceeding with the branded SMTP setup. Prepared a separate,
dormant mail-only owner, 20 isolated checks passed plus PHP 8.2 syntax. No live
mail, DNS or account settings changed. Websupport page is logged out; no browser
autofill, no connected Websupport plugin and no account login in scoped Gmail
registration messages. Existing March invoice only proves domain registration,
not an active mail subscription. Await authenticated Websupport access before
creating a mailbox or choosing/enabling a real SMTP credential.
