# current state and experiments

This file is historical incident context.

For the authoritative current live state, read `07-current-live-state.md` first.

This file is the current incident log in plain English.

## what we confirmed

### 1. intermittent 503 is real

The user saw real `503` responses on the main document request in the browser.

It was not a fake frontend error. It was not cache hallucination.

### 2. the 503 is at least partly auth-state dependent

When the `wordpress_logged_in_*` cookie was present, the user could hit `503`.

When that cookie was removed, the site loaded.

That means at least one failure path depends on logged-in WordPress state.

### 3. every input field losing focus was a frontend bug

The “can’t type because focus disappears after a second” bug was not random browser nonsense.

The custom `gls-script.js` was re-running translation logic repeatedly after load and on DOM mutations. That is exactly the kind of code that can blow away focus.

## backups made during this incident

A fresh backup set was created on the Hostinger server before changes.

Primary backup path:

- `/home/u595644545/backups/ruskyobchod-2026-04-09-093435`

It includes:

- fresh DB dump
- domain archive
- logs
- config snapshots

## changes already made in production

### step 1, removed hacked Elementor Pro bootstrap block

What changed:

- removed the custom fake-license and HTTP interception block from live `elementor-pro.php`

Why:

- it was hacked, non-standard, and calling out to a nullware domain

Result:

- site still loaded
- did not solve the 503

### step 2, disabled frontend-triggered WordPress cron

What changed:

- added `define( 'DISABLE_WP_CRON', true );` to live `wp-config.php`

Why:

- to test whether request-triggered cron load was causing spikes

Result:

- site still loaded
- did not solve the 503

### step 3, disabled browser-side `gls-script.js`

What changed:

- stopped enqueuing `gls-script.js` from the `gastronom-lang-switcher` plugin

Why:

- the script was repeatedly rewriting DOM after load
- input focus bug matched that behavior exactly

Result:

- input fields stopped losing focus on the public site
- language switching stopped working

This was a diagnostic result, not a production-ready fix.

## what broke because of step 3

By disabling `gls-script.js`, we also disabled the client-side language layer.

That means:

- RU/SK switcher behavior is broken
- browser-side text swapping is gone
- some checkout/cart wording rewrites are gone
- client-side language cookie/localStorage sync is weakened
- custom checkout DOM tweaks from that script are off

So the site became more stable, but less translated.

That means step 3 proved where the bug lives, but it is too destructive to be the intended end state. Inputs reportedly worked the day before, so the operational assumption for the next agent should be "recent regression inside the existing translation path", not "replace the translation system during the emergency."

## login and relogin notes

### login cookie and 503

- logged-in cookie path can hit `503`
- anonymous path can behave differently

### relogin behavior

The login experience itself was also affected by the frontend mess.

At one point the user could not keep focus in login inputs either. The broad site-wide input-focus issue strongly suggests the language-switcher script was part of that confusion on the public side.

One nuance:

- `gls-script.js` was not directly loaded on `wp-login.php`
- so public-site input breakage and login-page weirdness may not have been one single cause

Still, the general theme is the same: too much client-side DOM meddling around forms.

## the current truth

Right now the tradeoff is simple:

- with `gls-script.js` disabled, inputs behave
- with `gls-script.js` disabled, language switching is broken

That gives the next agent the real short-term direction:

1. treat step 3 as proof that the bug sits inside `gls-script.js`
2. restore translation behavior by isolating and removing only the focus-breaking regression inside the existing `gls` path
3. keep bigger architecture changes out of the emergency fix

## what the next agent should assume

- the live site is not clean
- the current repo is the control surface
- the language-switcher JS is guilty until proven safe
- the Dotypos integration is powerful enough to break business logic if touched carelessly
