# yasujshoes demo on Wasmer Edge

The yasujshoes demo store (WordPress, WooCommerce, the `zagros` theme) packaged for
[Wasmer Edge](https://wasmer.io) and deployed from GitHub (AIDY26/yasujshoes, branch `main`).
It is a client demo with demo products, not a live shop.
Built from the local site `H:\laragon\www\yasujshoes` by `J:\wasmer\tools\build-repo.ps1`;
the full how-to is `J:\wasmer\WASMER-GUIDE.md`.

## What is in the repo

| Path | What it does |
| --- | --- |
| `app.yaml` | The Wasmer app `yasujshoes` (owner `aidenlopez268`, so `yasujshoes.wasmer.app`) with a MySQL database, no volume. |
| `wasmer.toml` | For `wasmer deploy` from this folder; the same layout as the dashboard build (repo at `/app`, WordPress served from `/app/app`). |
| `app/` | WordPress with the active theme, the active plugins, languages and the product images. |
| `app/wp-config.php` | Reads the database details Wasmer provides; no passwords in the repo. Table prefix `wp_`. |
| `app/wasmer-db-import.php` | On the first visit, loads `db/yasujshoes.sql` into the empty database. |
| `db/yasujshoes.sql` | The demo store, with local addresses turned into site-relative ones (`/wp-content/...`). |

wp-admin works for browsing and editing content, but plugin and theme installs are off (they
would not survive a deploy). Outside calls (update checks, licence pings, SMS) are blocked.

## Deploy (Wasmer dashboard)

1. Create the app from GitHub: repo `AIDY26/yasujshoes`, branch `main`, owner `aidenlopez268`, project link `yasujshoes`.
2. Project preset `php`, PHP 8.3. Keep the detected start command
   `phpix -S 0.0.0.0:8080 -t /app/app` (optionally `phpix -d memory_limit=256M -S 0.0.0.0:8080 -t /app/app`).
3. Build and Install Command off, no environment variables, Enable Database on (MySQL).
4. Deploy, open `https://yasujshoes.wasmer.app` and wait a minute or two: the first visit loads the store.
5. Sign in at `/wp-login.php` with the shared demo admin.

Every push to `main` redeploys. To start the demo over: empty the database in the dashboard's DB
Explorer and open the site again.

## Test locally

With Laragon's MySQL running, create an empty database `yasujshoes_wasmer_test`, then from this folder:

```powershell
$env:DB_NAME='yasujshoes_wasmer_test'; $env:DB_USERNAME='root'; $env:DB_HOST='127.0.0.1'; $env:DB_PORT='3306'
H:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe -t app -S 127.0.0.1:8160
```

Open http://127.0.0.1:8160 (the first visit imports the demo).
