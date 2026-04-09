# architecture review and rebuild plan

This is the adversarial version.

The current architecture is bad because it treats WordPress like a DOM sandbox and WooCommerce like a side effect. The site still works because users are patient and shared hosting is forgiving, not because the design is sound.

## the core architectural mistakes

### 1. language switching was built as post-render DOM surgery

That is the root design error.

Client-side text rewriting can work for tiny cosmetic changes. It is the wrong base for:

- product titles
- navigation
- checkout labels
- payment labels
- shipping labels
- content pages
- input placeholders
- document title
- cache behavior

Once a script owns all of that, it owns the page. That is how a “language switcher” turns into a site-wide failure surface.

### 2. one plugin accumulated unrelated responsibilities

The custom language-switcher now mixes:

- translation
- styling
- content visibility
- sorting
- cookies
- cache hints
- checkout UX
- shipping presentation

That is not a plugin. That is a junk drawer.

### 3. integration logic was patched in place instead of isolated

Dotypos sync behavior should be isolated behind clear extension points, logs, and tests.

Instead, the project history shows direct edits inside the live integration plugin. That leaves no hard boundary between vendor code and project code.

### 4. shared hosting was treated like a forgiving application server

It is not.

On shared hosting, repeated cron tasks, live DOM hacks, heavy plugin stacks, nulled plugins, and WooCommerce AJAX churn all cost more than they look like they cost.

## what it should look like instead

### target principle 1, server-render the chosen language where possible

The language decision should happen before the page is rendered.

The proper order is:

1. determine language from query param or cookie
2. render the correct text on the server
3. let JS do only tiny progressive-enhancement tasks

Client-side JS can still handle the language switch button. It should not be rewriting half the page after load.

### target principle 2, split responsibilities

Break the current custom logic into separate pieces.

Suggested split:

- `gastronom-lang-core`
  - language detection
  - safe server-side filters
  - switcher UI output

- `gastronom-lang-frontend`
  - only tiny browser helpers
  - no MutationObserver over the whole document
  - no repeated timed reruns

- `gastronom-checkout-customizations`
  - checkout-only UX rules
  - no translation ownership

- `gastronom-style-overrides`
  - styling only
  - no business logic

### target principle 3, stop editing vendor plugins directly

For Dotypos:

- keep vendor plugin code as untouched as possible
- move project-specific behavior into a companion plugin or mu-plugin
- wrap stock sync and reconciliation in narrow hooks where possible
- if a vendor patch is unavoidable, keep it as a documented diff in the repo, not a mystery on production

### target principle 4, replace global DOM mutation with narrow hooks

No full-document MutationObserver for translation.

No repeated `setTimeout(translateAll, ...)` passes for whole pages.

If a piece of JS must react to WooCommerce AJAX:

- scope it to the checkout container
- touch only the affected element set
- never rewrite `innerHTML` for labels that contain inputs
- never run blind over every input, button, span, label, and option on the page

### target principle 5, make cache and auth behavior explicit

Right now the site has mixed behavior depending on cookies and logged-in state.

That needs a simple rulebook:

- anonymous pages: cache-safe
- cart, checkout, account, logged-in views: never full-page cached
- language state: server-readable and cache-aware

If a page varies by language, the cache key must vary by language in a controlled way. Not because a frontend script tries to clean up after a wrong render.

## recommended rebuild order

1. Freeze the current `gls-script.js` as legacy code. Do not add new behavior there.
2. Restore language switching with a minimal server-led path.
3. Move checkout-specific DOM behavior out of the translation layer.
4. Reduce Dotypos custom patch surface by moving project logic into a wrapper plugin or mu-plugin.
5. Remove suspicious or hacked plugin code from production.

## what the rebuilt language system should do

- use one language cookie or query param as the source
- render known static labels on the server
- keep content blocks bilingual only where unavoidable
- switch pages by navigation or reload when needed
- keep frontend JS small and local

This is less flashy than instant DOM swapping. It is also less stupid.
