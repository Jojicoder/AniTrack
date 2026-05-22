# AniTrack Project Structure

Two versions are kept separate to avoid mixing static HTML demo pages with PHP/MySQL pages.

```text
AniTrack-html/
  HTML/CSS/JavaScript version for class presentation.

infinityfree-db/
  PHP/MySQL version for InfinityFree deployment.
```

Use `AniTrack-html/index.html` for the school HTML version.

Upload the contents of `infinityfree-db/` to InfinityFree `htdocs` for the database version.

Use `dist/infinityfree-db-root.zip` when you want a ready-to-upload InfinityFree archive.

Only `AniTrack-html/` and `infinityfree-db/` are active project versions.
