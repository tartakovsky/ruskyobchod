# WordPress core integrity contract

## Finding

Production and staging both report WordPress 6.9.7, but the aggregate
architecture contract did not pin the version or verify official core
checksums. Production uses the `ru_RU` package while the isolated staging site
uses `en_US`; comparing staging against `ru_RU` therefore produced a legitimate
`wp-includes/version.php` locale difference.

## Change

A read-only verifier now requires version 6.9.7 and checks production against
the official `ru_RU` checksums and staging against the official `en_US`
checksums. It is included in the aggregate architecture contract.

## Verification

Both core installations pass their locale-correct official checksums. No core
or runtime file changed.
