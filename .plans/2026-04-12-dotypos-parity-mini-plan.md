# Dotypos Parity Mini-Plan

## current fact pattern

- live `gastronom-lang-switcher.php` matches repo
- live `gastronom-stock-fix.php` does not match repo
- live does not contain:
  - `mu-plugins/rusky-dotypos-stock-bridge.php`
  - `mu-plugins/rusky-weight-preorder.php`

## critical constraint

Do **not** deploy the missing Dotypos/preorder owner files first.

Reason:

- current live `gastronom-stock-fix.php` still defines multiple `gastronom_*` Dotypos/preorder functions without the extraction-era guard pattern
- if owner MU files define the same compatibility wrappers first, live can hit function redeclaration fatals when the old monolith loads

## safe order

1. prove the target `gastronom-stock-fix.php` parity step is behavior-safe enough
2. deploy `gastronom-stock-fix.php` first
3. verify storefront/account/checkout/commerce baselines
4. only then deploy:
   - `rusky-dotypos-stock-bridge.php`
   - `rusky-weight-preorder.php`
5. verify again
6. only after file-set parity, begin Dotypos integration proof

## parity scope

This is not broad refactor rollout.

The parity block exists only to make live file ownership match the already-documented repo boundary closely enough that integration can be handled intentionally.

## stop line

If `gastronom-stock-fix.php` parity cannot be proven with acceptable risk, do not continue to owner-file deploy in the same cycle.

