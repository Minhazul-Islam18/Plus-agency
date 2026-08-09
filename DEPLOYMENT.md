# Deployment Guide — ICA Groupe (Plus-Agency)

Laravel 12 / PHP 8.2+ / MySQL app. Custom bootstrap: **the repo root is the
document root**, not `core/public` — root `index.php` loads `core/vendor/autoload.php`
and `core/bootstrap/app.php` directly. `installer/` is a one-time setup wizard,
`sw.js` (push notification service worker) must live at the domain root.
Queue is `sync` and cache/session are `file` — no Redis, no queue worker, no
cron job required to keep the app running.

---

## Part 1 — Prep the codebase before upload

- [ ] `composer install --no-dev --optimize-autoloader` inside `core/` (run locally or via SSH on the server — do **not** upload `core/vendor/` from a dev machine with dev dependencies)
- [ ] Copy `core/.env.example` → `core/.env`, fill in production values (see below)
- [ ] `php artisan key:generate` if `APP_KEY` is empty
- [ ] Confirm `.gitignore` excludes `core/.env`, `core/storage/*.key`, `core/vendor/`, `assets/front/img/*` uploads you don't want to overwrite
- [ ] Decide: fresh production DB (`php artisan migrate --force`) or import your dev DB dump. If importing a dump, still run `php artisan migrate --force` after so the `migrations` table matches — check `INSERT IGNORE INTO migrations` blocks from any raw-SQL you've already run manually so Laravel doesn't try to re-run them.

### Production `.env` values to set

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://icagroupe.world          # bare domain, no subpath, https
TIMEZONE=Africa/Ouagadougou

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=<hostinger_db_name>
DB_USERNAME=<hostinger_db_user>
DB_PASSWORD=<hostinger_db_password>

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp                          # match whatever's configured in admin > Email Settings
```

`APP_DEBUG=false` is not optional — a stack trace leaking DB credentials to
the public is a real, common misconfiguration on shared hosting.

---

## Part 2 — Hostinger hPanel setup

1. **Hosting plan**: Business or Premium (needs SSH access + custom PHP version selection; the cheapest shared plan sometimes lacks SSH — check before buying).
2. **hPanel → Websites → Add Website** (or use the one your plan includes). Note the **primary domain** it assigns for now — you'll repoint the real domain later.
3. **PHP version**: hPanel → Advanced → PHP Configuration → select **PHP 8.2 or 8.3**. Enable extensions: `curl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` (Laravel's usual list — check `composer.json` `require` block if unsure).
4. **MySQL database**: hPanel → Databases → MySQL Databases → create DB + user, grant all privileges, note host (usually `localhost`/`127.0.0.1`), db name, username, password for `.env`.
5. **SSH access**: hPanel → Advanced → SSH Access → enable, note host/port/credentials (or upload your SSH public key).

---

## Part 3 — Upload the code

**Recommended: SSH + git** (clean, repeatable, no zip/unzip corruption risk):

```bash
ssh -p <port> <user>@<host>
cd domains/icagroupe.world/          # or wherever hPanel put the site root
git clone <your-repo-url> .          # or git pull if already cloned
cd core && composer install --no-dev --optimize-autoloader
```

**Fallback: hPanel File Manager** — zip the whole repo locally (exclude
`core/vendor`, `core/storage/logs/*`, `.git/`), upload via File Manager,
extract in place, then use hPanel's Composer button (Advanced → Composer) to
run `composer install --no-dev` inside `core/`, since File Manager plans
often don't have full SSH.

### Set the document root correctly — the part that actually differs from stock Laravel

hPanel → Websites → your domain → **Advanced → Domain settings / Change
document root**. Point it to the **folder containing this repo's `index.php`
and `installer/`** (the repo root), **not** `core/public`. If Hostinger
forces the doc root to be a specific folder like `public_html`, upload the
repo contents *into* `public_html` directly (repo root = `public_html`).

---

## Part 4 — Finish server-side setup

```bash
cd core
php artisan storage:link           # symlinks core/storage/app/public -> core/public/storage
chmod -R 775 storage bootstrap/cache
chown -R <hosting-user>:<hosting-group> storage bootstrap/cache   # match Hostinger's actual PHP-FPM user
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

- [ ] Visit the site once logged out — confirm homepage loads, no 500.
- [ ] **Delete or rename `installer/`** once confirmed working — leaving a setup wizard reachable in production is a real attack surface (it can rewrite `.env`/DB config on some CodeCanyon-style installers). If you might need it again, at minimum password-protect the directory via `.htaccess`.
- [ ] Confirm `sw.js` is reachable at `https://icagroupe.world/sw.js` (push notifications need it at the root, not nested).

---

## Part 5 — Domain: GoDaddy → Cloudflare → Hostinger

Cloudflare being "in the middle" means **Cloudflare becomes your DNS host** —
GoDaddy stops managing DNS entirely once you switch nameservers. The flow:

```
Visitor → Cloudflare (proxy/CDN/cache) → Hostinger (origin server)
             ↑
     GoDaddy only points here (nameserver delegation)
```

### 5a. Add the site to Cloudflare

1. Cloudflare dashboard → **Add a Site** → enter `icagroupe.world` → pick a plan (Free is fine).
2. Cloudflare scans existing DNS records. Review/add:
   - `A` record: `@` → **Hostinger's server IP** (hPanel → Websites → your site → shows the shared IP, or `hosting overview` page)
   - `CNAME`: `www` → `icagroupe.world` (or another `A` record to the same IP)
   - Any `MX`/`TXT` records you currently use for email — **copy these over before switching nameservers**, or email breaks.
   - Set the orange cloud (proxy) **ON** for `A`/`CNAME` records you want CDN'd — that's the whole point of routing through Cloudflare.
3. Cloudflare gives you **two nameservers** (e.g. `xxx.ns.cloudflare.com`, `yyy.ns.cloudflare.com`).

### 5b. Point GoDaddy at Cloudflare

1. GoDaddy → **My Products → DNS** (or Domain Settings) for `icagroupe.world`.
2. **Nameservers → Change → Enter my own nameservers (custom)**.
3. Replace GoDaddy's default nameservers with the two Cloudflare gave you. Save.
4. Propagation: usually 15 min–a few hours, can take up to 24-48h. Cloudflare emails you once it detects the switch and activates the zone.

### 5c. Point the domain at the Hostinger hosting account

hPanel → Websites → your hosting plan → **add `icagroupe.world` as the
domain** for that hosting account (Domains → Manage → point domain, or it's
already set if you created the hosting under that domain in Part 2).

---

## Part 6 — SSL/TLS (get this wrong and you get a redirect loop)

1. **Cloudflare SSL/TLS → Overview → set mode to "Full (strict)"**. Do NOT use "Flexible" — that encrypts visitor↔Cloudflare but sends plaintext HTTP Cloudflare↔Hostinger, which breaks admin login security and can cause infinite redirect loops with Laravel's HTTPS-forcing.
2. On the Hostinger side, issue a real cert on the origin:
   - Easiest: hPanel → SSL → **enable the free Let's Encrypt SSL** for the domain — this is enough for "Full (strict)" to work (Cloudflare accepts any publicly-trusted cert, doesn't have to be its own Origin CA cert).
   - Alternative: generate a **Cloudflare Origin CA certificate** (SSL/TLS → Origin Server → Create Certificate) and install it in hPanel's SSL section instead — slightly more locked-down since it only trusts Cloudflare's connection, but extra setup.
3. Cloudflare SSL/TLS → Edge Certificates → turn on **"Always Use HTTPS"** and **"Automatic HTTPS Rewrites"**.
4. Confirm `APP_URL=https://...` in `.env` (already set in Part 1) so Laravel generates https:// links everywhere.

---

## Part 7 — Cloudflare caching rules for this app

This is a dynamic Laravel app with an admin panel, tender purchase flow, and
session-based auth — **do not blanket-cache everything**, or you'll serve
one visitor's admin session/CSRF token to another, or serve stale AJAX
tender-filter results.

Set up under **Caching → Cache Rules** (replaces the older Page Rules UI):

| Rule | Match | Action |
|---|---|---|
| Bypass admin | `URI Path starts with /admin` | Cache Level: **Bypass** |
| Bypass dynamic/auth routes | `URI Path starts with /tenders`, `/newsletter`, `/store_feedback`, `/admin`, any POST | Cache Level: **Bypass** |
| Cache static assets | `URI Path starts with /assets/` | Cache Level: **Cache Everything**, Edge TTL: 1 month |

Notes specific to this codebase:
- Front-end assets already use cache-busting (`asset_v()` helper appends a
  version query string), so long Cloudflare TTLs on `/assets/` are safe —
  a deploy that changes a CSS/JS file gets a new URL automatically, no purge needed.
- **Do purge cache** after deploys that change *server-rendered HTML*
  (Cloudflare → Caching → Configuration → Purge Everything), since full-page
  HTML isn't cache-busted the same way.
- Turn **Rocket Loader** and **Auto Minify (JS)** OFF under Speed →
  Optimization — this app has hand-tuned vanilla JS (offcanvas menu,
  particle-network canvas, hero slider drag) that can break under
  Cloudflare's JS rewriting. Test the site thoroughly if you turn these on.
- **Bot Fight Mode** (Security → Bots) can block legitimate form POSTs
  (contact, feedback, tender purchase, AJAX filter). If forms mysteriously
  stop working after enabling it, add a WAF exception for those routes.

---

## Part 8 — Real visitor IP through Cloudflare (already handled in code)

`core/app/Http/Middleware/TrustProxies.php` already trusts Cloudflare's
published edge IP ranges and reads `X-Forwarded-For`, so Laravel sees the
real visitor IP once traffic is proxied through Cloudflare — this matters a
lot here since the app has its own rate-limiting/security-logging system
(`rate_limit_attempts`, `access_logs` tables) that would otherwise see every
visitor as Cloudflare's edge IP and either rate-limit everyone as one client
or log garbage. **Nothing to change here** — just confirm after going live
that `request()->ip()` in a test route (or the access_logs table) shows real
visitor IPs, not `173.245.x.x`-style Cloudflare ranges.

---

## Part 9 — Post-launch checklist

- [ ] Homepage loads over `https://icagroupe.world` with a valid padlock (no mixed-content warnings)
- [ ] `www.icagroupe.world` redirects correctly to the canonical domain
- [ ] Admin login works (`/admin`), session persists across page loads
- [ ] Push notification opt-in prompt fires and a test subscribe shows up in `/admin/pushnotification/subscribers` with real device/browser (confirms HTTPS + service worker scope are correct)
- [ ] Tender AJAX filter, contact form, feedback form, newsletter subscribe/unsubscribe all submit successfully (checks Cloudflare isn't caching/blocking POSTs)
- [ ] Check `access_logs`/rate-limit tables show real visitor IPs, not Cloudflare edge IPs
- [ ] `core/storage/logs/laravel.log` has no fresh errors after a few real visits
- [ ] `installer/` is deleted or locked down
- [ ] Full page-cache purge done in Cloudflare after this initial deploy
