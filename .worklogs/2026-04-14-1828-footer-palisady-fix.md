## 2026-04-14 18:28

- fixed RU homepage footer contact line in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- corrected `Палисьады` -> `Палисады`
- forced non-breaking spacing in `Вход со&nbsp;стороны улицы&nbsp;Палисады`
- deployed the updated plugin file to live over SSH
- flushed `wp-super-cache` after deploy
- verified:
  - homepage returns `200`
  - `wp-login.php` returns `200`
  - live RU homepage HTML contains `Вход со стороны улицы Палисады`
  - live DOM shows account links as `Мой аккаунт`
