# runtime-shim mini-plan

## current position

`rusky-runtime-shim.php` is the only remaining live-vs-repo MU gap.

It is intentionally **not** rolled out by default yet.

## why it is deferred

- it touches the language/runtime control surface
- it filters `option_active_plugins`
- it changes FAR behavior and can trim the main language plugin in sensitive paths
- read-only proof already confirmed:
  - `real-time-find-and-replace/real-time-find-and-replace.php` is not active on live

That means the expected Tuesday-morning value is currently lower than the deployment risk.

## only valid reasons to take this block

1. FAR becomes active again on live
2. a real language/runtime defect appears that is specifically caused by missing runtime filtering
3. a controlled integration step requires plugin-set trimming and cannot be proven without it

## required pre-deploy proof

Before any rollout:

- capture current `active_plugins` on live
- prove whether FAR is active
- prove whether the main language plugin must remain active on the tested path
- identify exact affected user path:
  - public storefront
  - logged-in frontend
  - admin
  - ajax
  - rest

Supporting read-only tools now available:

- `tools/capture-live-active-plugins.sh`
- `tools/audit-live-mu-parity.sh`

## required deployment shape

- one file only:
  - `wordpress/wp-content/mu-plugins/rusky-runtime-shim.php`
- no simultaneous deploy of:
  - `rusky-server-language-core.php`
  - `rusky-language-switcher-lite.php`
  - `gastronom-lang-switcher.php`

## required post-deploy verification

- exact active-plugin proof before/after
- storefront baseline
- checkout shell baseline
- account shell baseline
- commerce RU baseline
- commerce SK baseline
- preorder baseline
- one logged-in frontend spot-check

## rollback rule

If any runtime/user-path regression appears:

- revert only `rusky-runtime-shim.php`
- re-run the same exact proofs
- do not patch around the regression before rollback
