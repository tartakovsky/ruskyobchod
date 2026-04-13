# Dotypos Stock Reconcile And Readiness

- date: 2026-04-14 00:12:02
- scope: owner lifecycle + readiness

## What was found

- customer/order/payment flows were mostly stable
- remaining unstable block was stock lifecycle after test orders
- local weighted stock and remote Dotypos stock diverged after the test tail

## What was done

- deleted only test orders with store/proof emails:
  - `11058`
  - `11061`
  - `11064`
  - `11067`
  - `11080`
  - `11085`
  - `11101`
  - `11102`
- restored local weighted stock to the last confirmed live point:
  - `10310` -> `1.48 kg`, `_stock=3`
  - `10617` -> `2 kg`, `_stock=5`
  - `10781` -> `0.966 kg`, `_stock=3`
- reconciled remote Dotypos stock to current local owner state:
  - `10310` `-0.27 -> 1.48`
  - `10617` `0.75 -> 2`
  - `10781` `-0.134 -> 0.966`
- updated readiness tooling to current payment model:
  - removed old `bank transfer` expectations
  - hardened float comparison in `verify-dotypos-readonly.sh`
  - aligned proof harness payment title with card flow

## Proof

- `./tools/verify-tuesday-readiness.sh` green
- `./tools/prove-admin-weight-confirmation.sh 10617 cod` green
- `./tools/prove-admin-weight-confirmation.sh 10617 woocommerce_payments` green
- `verify-dotypos-product-state.sh` green for:
  - `10310`
  - `10617`
  - `10781`

## Readiness verdict

- storefront/customer/order/payment flows are at a stable point
- weighted local stock and remote Dotypos stock are aligned
- current blocker level before real integration is low
