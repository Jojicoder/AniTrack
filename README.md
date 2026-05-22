# AniTrack

AniTrack is an anime and manga tracking project with two separated versions.

## Versions

```text
AniTrack-html/
```

HTML/CSS/JavaScript version for class presentation.

Open:

```text
AniTrack-html/index.html
```

```text
infinityfree-db/
```

PHP/MySQL version for InfinityFree deployment.

Upload the contents of this folder to InfinityFree `htdocs`.

```text
dist/infinityfree-db-root.zip
```

Ready-to-upload ZIP for InfinityFree. This ZIP is root-ready, so its contents extract directly into `htdocs`.

## Notes

- The school version does not use PHP or MySQL.
- The InfinityFree version uses `.php` files that contain HTML plus PHP database logic.
- The two versions are intentionally separate to avoid old `.html` pages conflicting with PHP/MySQL pages.
