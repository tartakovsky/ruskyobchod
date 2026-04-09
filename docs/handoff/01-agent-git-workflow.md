# agent git workflow

This is the operating contract.

If an agent ignores this and edits production directly, the repo becomes decorative again. Decorative repos are how teams end up with archaeology instead of maintenance.

## source of truth

- GitHub repo: `https://github.com/tartakovsky/ruskyobchod`
- default branch: `main`
- local working tree root: `ruskyobchod/`
- imported WordPress tree: `ruskyobchod/wordpress/`
- live Hostinger path: `/home/u595644545/domains/ruskyobchod.sk/public_html`

## files that belong in this repo

- `wordpress/.htaccess`
- safe WordPress root bootstrap files
- `wordpress/wp-content/themes/`
- `wordpress/wp-content/plugins/`
- `wordpress/wp-content/mu-plugins/`
- handoff docs and worklogs

## files that do not belong in this repo

- `wordpress/wp-config.php`
- uploads
- caches
- backups
- logs
- migration artifacts
- ad hoc one-off scripts from the live web root
- premium/private bundles that were deliberately excluded

## mandatory rules

1. Never edit production through WordPress Plugin Editor.
2. Never edit production through Hostinger File Manager.
3. Never use the live site as your text editor.
4. Pull from GitHub before starting.
5. Make changes locally in this repo.
6. Test locally as far as possible, then deploy deliberately over SSH.
7. Record each meaningful change in a worklog before committing.
8. If production was changed manually, sync the change back into the repo immediately or treat the repo as stale until you do.

## exact operating sequence

### step 1, sync your local copy

```bash
cd /path/to/ruskyobchod
git pull --rebase origin main
git status
```

If `git status` is not clean, stop and understand why.

### step 2, inspect before touching

Read these first when the task touches production behavior:

- `docs/handoff/02-issue-overview.md`
- `docs/handoff/03-current-state-and-experiments.md`
- the latest worklogs in `.worklogs/`

### step 3, make the change locally

Edit only in the repo.

Usual targets:

- `wordpress/wp-content/plugins/gastronom-lang-switcher/`
- `wordpress/wp-content/plugins/gastronom-stock-fix/`
- `wordpress/wp-content/plugins/woocommerce-extension-master/`
- `wordpress/wp-content/themes/food-grocery-store/`

### step 4, verify the changed files

Examples:

```bash
php -l wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php
php -l wordpress/wp-content/plugins/gastronom-stock-fix/gastronom-stock-fix.php
```

If the change is JavaScript or CSS, inspect the diff and verify the intended asset path.

### step 5, deploy only the changed files

Use `rsync` or `scp`, not plugin editor.

Example pattern:

```bash
rsync -av \
  -e "ssh -i ~/.ssh/ruskyobchod_hostinger_ed25519 -p 65002" \
  wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php \
  u595644545@46.202.156.109:/home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/gastronom-lang-switcher/
```

Deploy the smallest possible surface. If one file changed, ship one file.

### step 6, run live checks

Minimum checks after each deploy:

- homepage document returns `200`
- `wp-login.php` returns `200`
- the changed behavior is tested in browser
- no immediate new fatals in logs

### step 7, write worklog and commit

Required sequence:

```bash
cd /path/to/ruskyobchod
echo .worklogs/$(date +%Y-%m-%d-%H%M%S)-your-slug.md
```

Write that file, then:

```bash
git add <changed files> .worklogs/<generated-file>
git commit -m "Short commit message"
git push origin main
```

## deployment guardrails

- deploy code with `rsync` or `scp`
- use `wp-cli` only for WordPress operations, not file sync
- keep deploys small
- keep live tests immediate
- if a deploy causes a regression, restore the single changed file from Git or server backup, not from memory

## live test checklist

- anonymous homepage
- `wp-login.php`
- affected page or component
- if checkout/cart touched: cart, checkout, payment method rendering
- if translation touched: RU and SK toggle behavior
- if Dotypos touched: watch for cron spikes, failed actions, and stock drift

## workflow violations that already hurt this project

- direct Plugin Editor patching on production
- incremental live surgery in large JS files
- mixing business logic, translation, styling, and checkout DOM control in one plugin
- making domain migration changes during ongoing runtime instability

If you are about to repeat one of those, stop.
