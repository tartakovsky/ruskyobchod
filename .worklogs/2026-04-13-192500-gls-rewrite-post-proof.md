## Context

After deploying `a2fec5c4` (`GLS` split into render-blocked and runtime-mutation-blocked requests), the live checks already confirmed:

- public homepage RU/SK = `200`
- logged-in homepage RU/SK = `200`
- logged-in account/cart = `200`
- no duplicated RU/SK block content on those paths

The next risk was unintended collateral damage to the mixed preorder/customer pipeline.

## Proof

Ran controlled mixed-order proof via:

- `sh ./tools/prove-mixed-preorder-emails.sh 10795 10617 bacs 0.44`

Result:

- temporary order reached `await-weight`
- preorder-created mails were still emitted
- admin AJAX confirmation returned success JSON
- order transitioned to `pending`
- actual weight `0.44` was persisted
- exactly one weight-confirmation customer email was sent
- Dotypos update/getProductOnWarehouse calls still executed

## Conclusion

The GLS runtime rewrite did not break the mixed preorder/email/confirmation path in the controlled proof harness.
