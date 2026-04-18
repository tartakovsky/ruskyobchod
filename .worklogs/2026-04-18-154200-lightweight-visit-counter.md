# 2026-04-18 15:42:00 lightweight visit counter

## why

- current live access does not expose reliable access logs or visit analytics
- the safe fallback is a very small first-party counter owned by the repo
- requirement is operational visibility, not heavy analytics

## what changed

- added `wordpress/wp-content/mu-plugins/rusky-visit-counter.php`
  - counts daily unique visits on frontend GET requests only
  - skips admin, ajax, cron, REST, XML-RPC, feeds, robots, and trackbacks
  - uses first-party cookie `rusky_visit_day`
  - stores daily aggregates in `rusky_daily_visit_counts`
  - keeps 60 days of retained daily counts
- added `tools/report-daily-visits.sh`
  - read-only helper to fetch the stored daily counts from live

## intended outcome

- future questions about “were there visits in the last N days?” can be answered from repo-owned data
- implementation stays low-risk and low-overhead
- note: the counter is not retroactive; it starts collecting from deployment time forward
