# issue overview

This is the short version of how the site got into this state.

## top-level diagnosis

The site has three separate classes of trouble:

1. frontend fragility from the custom language-switcher plugin
2. backend/runtime fragility from direct live patching of the Dotypos integration
3. operational fragility from editing production first and documenting later

These problems overlap. That is why the site feels random even when each single bug has a concrete cause.

## issue family 1, the language-switcher became a monster

The custom `gastronom-lang-switcher` plugin did not stay a language switcher.

Over time it absorbed:

- client-side translation
- site-wide CSS overrides
- menu and label rewriting
- cookie and localStorage language state
- cache variation logic
- sorting behavior
- cart and checkout UX control
- DOM mutation handling
- repeated delayed rerenders after page load

This matters because a script that keeps touching the DOM after load will eventually break something. On this site it broke input focus.

Historical clues from archived worklogs:

- it started as a fully client-side JS translator
- it later added MutationObserver logic
- it already had hangs and observer loop trouble in mid-March
- it later added checkout/cart DOM control with repeated timeouts

That plugin is a long-running source of frontend instability.

## issue family 2, Dotypos integration was patched live over and over

The `woocommerce-extension-master/dotypos.php` integration and its service layer were repeatedly modified on the live site.

Those changes were not just small bugfixes. They altered:

- stock update paths
- product visibility handling
- decimal stock behavior
- overwrite behavior
- cron safety nets
- product-name reconciliation
- auth token refresh logic

Some of those changes probably fixed real stock-sync bugs. They also made the runtime harder to trust, because third-party integration code was being rewritten in place on shared hosting.

This matters for the current incident because:

- extra cron activity increases shared-hosting load
- bad overwrite logic can create stale or conflicting product state
- live patching without repo discipline means no one can quickly answer “what code is running right now?”

## issue family 3, too much was edited directly in production

The history shows a repeated pattern:

- patch live through Plugin Editor
- test on the live site
- patch again
- document after the fact

That workflow explains why the codebase drifted into a pile of exceptions.

It also explains why the repo had to be created after the fact instead of being the normal workflow from the start.

## current high-risk components

### 1. custom browser-side language logic

Files:

- `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- `wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js`
- `wordpress/wp-content/plugins/gastronom-lang-switcher/gls-style.css`

Risk:

- frontend breakage
- language-toggle breakage
- checkout/cart DOM interference

### 2. Dotypos integration

Files:

- `wordpress/wp-content/plugins/woocommerce-extension-master/dotypos.php`
- service layer inside the same plugin

Risk:

- stock drift
- background task load
- runtime instability
- side effects on product titles and visibility

### 3. nulled or suspicious third-party code

Live finding from current incident:

- `elementor-pro` on the server had non-standard hacked code and outbound behavior

That is both a security and stability risk. The public repo excludes that plugin, so the live server still needs separate cleanup there.

## current incident in one sentence

The site is unstable because it combines a long-abused custom frontend control plugin, repeatedly patched integration code, and a shared-hosting runtime with very little room for nonsense.
