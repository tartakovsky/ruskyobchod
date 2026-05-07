# Adjust GLS pickup request shape

## Context

GLS pickup scheduling returned HTTP 400 with a .NET exception:

`Object reference not set to an instance of an object`

After downloading the live MyGLS SK WSDL from `api.mygls.sk`, the `PickupRequest` schema showed:

- `ClientNumber` is `xs:int`
- `Count` is `xs:int`
- `Info` is a nullable string field on the request object

The plugin was passing `ClientNumber` as the raw settings value and did not include `Info`.

## Change

Updated `class-gls-shipping-pickup-api-service.php`:

- cast `ClientNumber` to integer
- cast `Count` to integer
- include `Info` as an empty string

## Deploy

Deployed the single changed plugin file to live after `php -l` passed on the staged file.

## Notes

No pickup request was created by the agent during this step.
