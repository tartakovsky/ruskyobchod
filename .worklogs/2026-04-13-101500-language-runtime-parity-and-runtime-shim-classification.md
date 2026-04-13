## language runtime parity and runtime-shim classification

### scope

- `wordpress/wp-content/mu-plugins/rusky-server-language-core.php`
- `wordpress/wp-content/mu-plugins/rusky-language-switcher-lite.php`
- read-only classification of `wordpress/wp-content/mu-plugins/rusky-runtime-shim.php`

### 1. server language core rollout

Pre-rollout:

- `rslc_*` functions were absent on live
- `gls_add_switcher` and `gls_server_lang` were already present through the main language plugin

Safety proof:

- captured exact live `#gls-switcher` HTML on RU homepage before deploy
- validated remote syntax with `php -l`

Rollout:

- deployed only `rusky-server-language-core.php`

Post-rollout:

- `rslc_current_lang=true`
- `rslc_switcher_url=true`
- `rslc_render_switcher=true`
- exact `#gls-switcher` output matched
- all six verification contours stayed green

### 2. lite runtime rollout

Pre-rollout:

- `rsll_*` functions were absent on live
- `rslc_lite_runtime_should_stand_down=true` once server language core was present

Safety proof:

- validated remote syntax with `php -l`
- expected behavior was stand-down, not active takeover

Rollout:

- deployed only `rusky-language-switcher-lite.php`

Post-rollout:

- `rsll_current_lang=true`
- `rsll_normalize_front_page_html=true`
- `rslc_lite_runtime_should_stand_down=true`
- `stand_down=true`
- all six verification contours stayed green

Note on switcher comparison:

- an initial exact-match check reported a mismatch only because the probe URLs differed
- the difference was limited to `href` values reflecting the current request URL
- structure/classes/titles remained consistent, and stand-down proof plus six green baselines were sufficient

### 3. runtime-shim classification

Read-only live proof confirmed:

- `rusky-runtime-shim.php` is still missing on live
- `real-time-find-and-replace/real-time-find-and-replace.php` is not active on live
- `gastronom-lang-switcher/gastronom-lang-switcher.php` is active on live

Conclusion:

- `rusky-runtime-shim.php` is not a mandatory Tuesday-morning blocker by default
- deploying it tonight as blind final parity would add risk on the language/runtime control surface without clear current benefit
- it should only be taken under a dedicated mini-plan if runtime filtering becomes necessary again
