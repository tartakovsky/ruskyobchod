## Context

Manual live smoke found mixed-language cart shell that the readiness pack did not yet cover.

## Findings

- SK cart still showed Russian `Доставка`
- SK cart still showed English `Shipping to ...`

## Goal

Normalize these remaining cart shipping strings server-side and extend verification so the regression cannot silently return.

## Changes

- extended `rusky-theme-chrome-language.php` shipping phrase coverage for RU/SK
- added readiness assertions for cart shipping address labels in RU and SK

## Safety

- server-side text normalization only
- no theme edits
- no cash, Dotypos, stock, or checkout business logic changes
