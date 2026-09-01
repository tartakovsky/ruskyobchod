# Production administrator surface contract

## Finding

Production has exactly two known administrator accounts: user IDs 1 and 3017.
The primary account has one existing application password and the second has
none. No users have nonstandard role assignments.

## Change

A read-only verifier now pins administrator IDs and logins, application-password
counts without reading their secrets, and the allowed WordPress/WooCommerce role
names. It is included in the aggregate architecture contract.

## Verification

The current administrator, application-password, and role surfaces pass. No
user, credential, role, capability, or session changed.
