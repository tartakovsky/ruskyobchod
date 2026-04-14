## Context

The next phase after the Dotypos runtime hardening was to verify the real language ownership on the live site.

The historical risk in handoff was:
- broad JS-driven translation
- MutationObserver / delayed reruns
- post-render DOM ownership

Before changing anything else, the current live runtime needed a proof of what actually loads now.

## What was checked

Audited live pages:
- homepage RU
- account RU
- cart RU

Checked in browser and by raw HTML:
- loaded script assets
- inline script presence
- server-rendered switcher output
- server-rendered critical language shell

Also checked current active plugins and spot-audited the live `elementor-pro` bootstrap entry file.

## Findings

### 1. Legacy `gls-script.js` is not loaded on the critical public pages that were audited

Confirmed absent on:
- home
- account
- cart

Also confirmed absent from the page HTML itself.

That means the critical public language path is no longer owned by the old large client-side translation file on those routes.

### 2. The current language path is already largely server-first

Observed:
- switcher links are server-rendered
- titles and major page chrome are server-rendered in RU
- account/cart shells are server-rendered in RU
- footer/legal strings are server-rendered in RU

This matches the current PHP ownership in:
- `gastronom-lang-switcher.php`
- `rusky-server-language-core.php`
- related `mu-plugins` helpers

### 3. Remaining JS on these pages is not the old translation runtime

The audited pages still load normal theme/plugin/frontend JS, but not the large `gls-script.js` translation file.

### 4. `elementor-pro` is still active, but the current entry bootstrap file does not show the previously reported hacked block at the top

This is not a full security clearance.
It is only a readonly baseline note that the entry file header now looks standard.

## Change

Added new verifier:
- [`tools/verify-language-runtime-surface.sh`](/Users/alexandertartakovsky/ruskyobchod/tools/verify-language-runtime-surface.sh)

It asserts on live pages:
- no `gls-script.js`
- no legacy `translateAll(...)`
- no legacy language `localStorage` runtime
- no legacy `MutationObserver`
- switcher container is server-rendered
- core account/cart RU shell is server-rendered

## Verification

`./tools/verify-language-runtime-surface.sh` -> green

## Result

This changes the next-phase risk profile:

- the main current problem is no longer “remove `gls-script.js` from critical pages”
- that part is already true on the audited pages
- next language work should focus on preserving and extending the current server-first path
- do not reintroduce the old JS translation owner during future fixes
