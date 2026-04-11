## Summary

Mapped the remaining residual surface inside `gls_normalize_storefront_chrome_html()` after the verified-safe removals already completed.

## Remaining non-regex replacements

RU branch:

- `>Gastronom</h3>` -> `>Гастроном</h3>`
- `Slovenská republika` -> `Словацкая Республика`
- `Zapísaná v OR OS Bratislava I,` -> `Зарегистрирована в торговом реестре окружного суда Братислава I,`
- `Oddiel: Sro, Vložka č. 182562/B` -> `Раздел s.r.o., № записи 182562/B`
- `>Ok</button>` -> `>Ок</button>`
- `aria-label="Ok"` -> `aria-label="Ок"`
- `aria-label="Nie"` -> `aria-label="Нет"`

SK branch:

- reverse replacements for the same set

## Status

Retain for now:

- footer/legal company block
- cookie notice buttons
- footer brand heading

## Reason

The footer/legal removal attempt already failed in live verification, which proves this residual layer still carries real storefront behavior for at least part of this set.

## Next safe direction

Do not remove more entries from this residual set until there is direct proof for each exact phrase group.

The safer next work area is additive source-level translation coverage outside this residual map, followed by proof-based removal of only one phrase group at a time.
