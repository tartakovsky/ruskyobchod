# Admin Order Screen Gross Display Pass

- date: 2026-04-13 23:51:52
- scope: preorder admin owner screen

## Why

- Woo admin order items still show internal net + tax split for preorder items.
- Store pricing model is gross for retail customers.
- The admin screen should present preorder item confirmation in one consistent gross-oriented view without confusing duplicate DPH columns.

## Intended change

- keep order math unchanged
- change only admin owner display layer
- show concise preorder item summary with actual weight and gross line total
- hide redundant tax/internal price columns on preorder admin order screens

## Verification plan

- remote syntax check
- verify-admin-order-screen.sh
- inspect preorder admin order HTML markers
