# Remove inactive legacy language entrypoint

## Scope

Removed `ruskyobchod_gastronom_lang_switcher.php` from the deployable
`gastronom-lang-switcher` plugin directory.

It was a second, inactive WordPress plugin entrypoint that retained the old
browser-side translation implementation, including timed re-renders. The
active plugin list contains only `gastronom-lang-switcher/gastronom-lang-switcher.php`,
and no tracked runtime file includes the removed entrypoint.

## Preserved behavior

- The active server-rendered language runtime remains in
  `gastronom-lang-switcher.php`.
- No language cookie, switcher URL, output filter, checkout hook, or style
  asset is changed.
- The legacy file remains recoverable from Git history and the production
  backup created before deployment.

## Required verification

- active plugin list still names only the active language entrypoint;
- language runtime surface has no legacy browser translation asset;
- storefront, checkout, account, and RU/SK commerce shells remain green;
- critical file hash parity remains green after deployment.
