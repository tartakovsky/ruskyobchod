# Production configured plugin inventory contract

## Finding

Runtime policy checks asserted required and forbidden subsets for individual
request contexts, but did not reject an unrelated plugin newly persisted in
the production `active_plugins` option.

## Change

A read-only verifier now reads the raw persisted option directly from the
database, compares it with the exact 19-plugin production allowlist, and checks
that every configured entry file exists. The verifier is part of the aggregate
architecture contract.

## Verification

- the current raw production option contains exactly the documented 19 entries;
- every configured plugin entry file exists;
- the verifier refuses to run against staging;
- production configuration and runtime files were not changed.
