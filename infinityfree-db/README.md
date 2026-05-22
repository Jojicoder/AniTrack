# AniTrack InfinityFree DB Version

This version is for InfinityFree with PHP and MySQL.

Upload the contents of this folder into InfinityFree `htdocs`.

Main pages:

```text
index.php
pages/home.php
pages/library.php
pages/work-detail.php
pages/admin-add-work.php
```

Database setup:

1. Run `database/schema.sql` in phpMyAdmin if the tables do not exist.
2. Run `database/seed.sql` for a fresh database.
3. Run `database/update_image_urls.sql` if existing sample works still have `image_url = NULL`.

Notes:
- `.php` files contain the HTML markup and PHP database logic.
- `pages/works.html` redirects to `pages/home.php` for old links.
- `pages/index.html` redirects to `index.php` for old links.

