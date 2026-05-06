# 2026-05-06 13:05 - Search trust recovery

Goal:
- Start Google trust recovery after the cloaking/security incident.

Findings:
- `https://ruskyobchod.sk/wp-sitemap.xml` returns 200 and contains WordPress sitemap entries for pages, products, product categories, and users.
- `https://ruskyobchod.sk/sitemap.xml` redirects to `wp-sitemap.xml`.
- Live `robots.txt` referenced stale `sitemap-1.xml` through `sitemap-22.xml`; at least `sitemap-1.xml` returns 404.

Change:
- Added source-controlled `wordpress/robots.txt` pointing only to the current WordPress sitemap index.
- Added `.htaccess` cleanup for non-existing `.htm` spam URLs to return `410 Gone`.
- Extended `.htaccess` cleanup to cover Search Console examples in the form `/?123456789.htm`.

Live traffic snapshot:
- May 6 diagnosed visits still dominated by bots: 28,461 bot-like vs 31 probably human.
- Top agent remains Googlebot.
- Top spam paths include random numeric `.htm` URLs, confirming the 410 cleanup target.

Verification plan:
- Deploy the clean `robots.txt` and `.htaccess` to live.
- Recheck `robots.txt`, `wp-sitemap.xml`, 410 `.htm`, homepage, Google verification `.html`, and security surface.
- Continue with Google Search Console Security Issues / sitemap submission / URL inspection.

Verification:
- Live `robots.txt` points to `https://ruskyobchod.sk/wp-sitemap.xml`.
- `wp-sitemap.xml` returns 200.
- Test spam `.htm` URL returns 410.
- Test query-string spam `/?10672456763928.htm` returns 410.
- Homepage returns 200.
- Google verification `.html` returns 200.
- Security surface verifier passes.
