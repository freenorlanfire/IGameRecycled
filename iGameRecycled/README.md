# iGameRecycled portal

This folder contains a self-contained arcade portal intended to run at:

- `/iGameRecycled/`

The original Mario World project at repository root remains untouched and available as a legacy project.

## Deploy

1. Upload the full `iGameRecycled/` directory to your hosting root.
2. Open `https://your-domain/iGameRecycled/`.
3. Ensure root legacy directories (`/games`, `/img`, `/audio`) are also present, because game wrappers redirect to the existing real game files there.

## Asset sources

- New brand SVGs are in `iGameRecycled/img/`.
- Game thumbnails in cards reference existing root assets under `../img/thumbnail-*.svg`.

## How game routing works

- Catalog cards point to `iGameRecycled/games/<file>.php`.
- Each wrapper redirects to the real existing game file under root `/games/<file>.php`.
- This preserves original standalone game behavior and score endpoint logic.

## Add a new game

1. Add the real game file under root `games/`.
2. Add a thumbnail in root `img/`.
3. Add an entry in `iGameRecycled/partials/config.php` with title, file, thumbnail, category, and tags.

## Ads integration

- Placeholder ad blocks are in `partials/ad-slot.php`.
- `ads.txt` currently contains comments/placeholders only. Replace with real authorized seller data when available.
