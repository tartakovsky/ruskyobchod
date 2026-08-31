# Disable Meta Pixel on staging

## Scope

The isolated staging site was still rendering the browser-side Meta Pixel.
Although the staging safety guard already blocks WordPress server-side HTTP
and mail, a browser visiting staging could load `connect.facebook.net` and
send a test page view to Meta.

## Change

`rusky_meta_pixel_enabled()` now returns false only when the explicit
`RUSKY_STAGING_MODE` constant is enabled. Production does not define that
constant, so its Pixel behavior is unchanged.

## Verification plan

- lint the updated MU plugin;
- deploy and check staging HTML has no Meta Pixel URL or pixel image;
- verify staging isolation and WooCommerce compatibility checks;
- deploy the same committed file to production and verify the production
  storefront still renders the Pixel and all critical hashes match.
