## Summary

Cleared the live WooCommerce order history while leaving customer accounts intact.

## Verified before deletion

- WooCommerce orders total: `1496`
- WooCommerce customers total: `17`

## Action

- Deleted all live WooCommerce orders through the WooCommerce REST API.
- Did not delete users or WooCommerce customer accounts.

## Verified after deletion

- WooCommerce orders total: `0`
- WooCommerce customers total: `17`

## Important note

- Registered customer accounts remained intact.
- Any guest-only buyer details that existed only inside order records were removed together with those orders, because that data is stored on the orders themselves.
