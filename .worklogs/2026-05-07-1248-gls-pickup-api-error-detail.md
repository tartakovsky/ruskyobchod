# Add GLS pickup API error detail

## Context

GLS pickup scheduling continued to return only:

`GLS API returned error code: 400`

The plugin discarded the response body for non-200 HTTP responses, so the rejected field could not be diagnosed.

## Change

Updated `includes/api/class-gls-shipping-pickup-api-service.php` to include a sanitized, truncated response body in the exception when GLS returns a non-200 response.

## Deploy

Deployed the single changed plugin file to live after `php -l` passed on the staged file.

## Notes

This is a diagnostic improvement. No pickup request was created by the agent during this step.
