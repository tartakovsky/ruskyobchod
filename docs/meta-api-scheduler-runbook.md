# Meta API scheduler runbook

Date: 2026-05-08

Purpose: replace unreliable Meta Business Suite clicking with a repeatable publishing path.

## Current outcome

- Business Suite UI automation is stopped.
- One reel was published at the wrong time: 2026-05-08 00:21 Europe/Bratislava.
- Future weekly posts are not considered scheduled until they are visible in a verified API/tool calendar.

## Recommended path

Use a real scheduler first: Buffer, Metricool, Later, Publer, Hootsuite, or another tool that supports both Facebook Pages and Instagram Business/Reels.

Reason: Instagram publishing through Meta Graph API is not a single "schedule local file" call. Reels and images need public media URLs or a tool that handles upload/hosting/container processing. A scheduler also gives the operator a calendar and visible confirmation.

## Native API path

Use this only after access is ready.

Required Meta data:

- Meta Business ID: `883240858199545`
- Meta Page/asset ID seen in Business Suite URLs: `883209351536029`
- Facebook Page ID for Graph API publishing.
- Instagram Business Account ID connected to the page.
- Long-lived Page access token with publishing permissions.

Discovery commands after a user token exists:

```sh
curl -s "https://graph.facebook.com/v24.0/me/accounts?fields=id,name,access_token,instagram_business_account&access_token=$META_USER_ACCESS_TOKEN"
```

The response should reveal:

- the real Facebook Page ID;
- the Page access token;
- `instagram_business_account.id`, if Instagram is connected correctly.

Required permissions/scopes to verify in Meta App review or token debugger:

- `pages_manage_posts`
- `pages_read_engagement`
- `pages_show_list`
- `instagram_basic`
- `instagram_content_publish`

Required media state:

- For Facebook Page photo posts: local file upload or public `image_url`.
- For Facebook videos/reels: a verified Graph video upload flow.
- For Instagram images/reels: public `image_url` or `video_url`, or resumable upload at publish time.

Important Instagram limitation:

- Instagram Content Publishing creates a media container and then publishes it.
- It does not provide the same reliable future `scheduled_publish_time` flow as Facebook Page posts.
- Containers expire after 24 hours, so do not create Instagram containers days in advance.
- For Instagram/Reels, use either a third-party scheduler or a local scheduled job that runs at the target time, creates the container, waits for processing, and publishes.

## First safe milestone

Do not schedule the full week first.

1. Create a long-lived Page token.
2. Resolve and record:
   - `META_PAGE_ID`
   - `META_IG_USER_ID`
   - `META_PAGE_ACCESS_TOKEN`
3. Put one public test image URL into the plan.
4. Run dry-run.

The checked-in `docs/meta-content-schedule-2026-05-09.json` file is a recorded
May 2026 plan. Entries that already have Meta IDs and are now in the past are
reported as recorded, not prepared for another API call. For a new campaign,
copy that file to a new dated plan and use future `scheduled_at` values.

```sh
node tools/meta-content-scheduler.mjs --plan docs/meta-content-schedule-2026-05-09.json
```

5. Run a real API call only for one Facebook image post:

```sh
META_PAGE_ID=... \
META_PAGE_ACCESS_TOKEN=... \
node tools/meta-content-scheduler.mjs --plan docs/meta-content-schedule-2026-05-09.json --execute --only sat-sweets-facebook
```

6. Verify the scheduled post in Meta Planner and through API response IDs.
7. Only after this, add more Facebook posts.
8. For Instagram/Reels, choose one:
   - external scheduler;
   - local timed job with public media URLs or resumable uploads.

## Time policy

- No night posts.
- Preferred slots:
  - before lunch: `10:30` or `11:30`
  - evening: `17:30` or `18:30`
- Timezone: `Europe/Bratislava`.

## Stop conditions

Stop and do not execute API calls if:

- a scheduled timestamp is before `09:00` or after `19:30`;
- an Instagram/Reels post is being attempted as a future scheduled API object;
- required token or account ID is missing;
- Meta returns an API error that does not include a created scheduled object ID.

## References

- Facebook Page feed/photos API: `scheduled_publish_time` is the scheduling control for Page posts.
- Instagram Content Publishing API: creates containers and publishes them; media URLs must be public for standard upload, containers expire after 24 hours.
