# Strict staging plugin allowlist

## Finding

The runtime policy verifier checked required and explicitly forbidden plugins,
but did not reject unrelated active plugins on the intentionally minimal
staging site. A temporary `tinymce-advanced` activation therefore passed the
old policy check.

## Change

The staging profile now allows only WooCommerce, the Dotypos extension, and
`gastronom-stock-fix`. Any other plugin that remains loaded in a tested request
context fails the verifier. Production keeps its existing required/forbidden
policy and is not subject to the staging allowlist.

## Verification

- all seven production contexts passed;
- all seven staging contexts passed with the normal minimal profile;
- temporarily activating `tinymce-advanced` caused the expected failure;
- the proof deactivated `tinymce-advanced` through a cleanup trap and restored
  the staging profile.
