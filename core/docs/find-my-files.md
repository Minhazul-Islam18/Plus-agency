# Find My Files — Secure File Recovery System

**Feature for:** ICA GROUPE client  
**Module:** Plus Agency Laravel CMS  
**Built:** April 2026  
**Status:** Complete (Method 1 — Email + Order Number)

---

## Table of Contents

1. [Overview](#1-overview)
2. [User Flow — Step by Step](#2-user-flow--step-by-step)
3. [Architecture & Security Model](#3-architecture--security-model)
4. [Database Schema](#4-database-schema)
5. [Models](#5-models)
6. [Controller Reference](#6-controller-reference)
7. [Routes](#7-routes)
8. [Views](#8-views)
9. [Email Template](#9-email-template)
10. [Rate Limiting](#10-rate-limiting)
11. [Token System](#11-token-system)
12. [Risk Scoring](#12-risk-scoring)
13. [File Download (ZIP Streaming)](#13-file-download--zip-streaming)
14. [Permalink / Menu Integration](#14-permalink--menu-integration)
15. [Configuration Requirements](#15-configuration-requirements)
16. [File & Directory Map](#16-file--directory-map)
17. [Extending the Feature](#17-extending-the-feature)
18. [Known Limitations & Future Work](#18-known-limitations--future-work)

---

## 1. Overview

**Find My Files** allows customers who purchased tender documents to securely re-download their files at any time. The customer provides their email address and order number. If a paid, non-refunded purchase exists matching both, a one-time signed download link is sent to that email.

### Core Principles

| Principle | Implementation |
|---|---|
| **Anti-enumeration** | All failure paths (order not found, email mismatch, payment issue) return the same neutral "link sent" page — the user can never determine why a lookup failed |
| **Token security** | Raw tokens are never stored — only `SHA-256(token)` is stored in the database |
| **Progressive rate limiting** | Separate counters per IP, email hash, and device fingerprint with escalating lockout periods |
| **Immutable audit trail** | Every event is appended to `access_logs` — no updates, no deletes |
| **Secure delivery** | Files are streamed as a temporary ZIP; the temp file is deleted after send |

---

## 2. User Flow — Step by Step

```
Step 1 ──► Step 2 ──► Step 4 ──► Step 7 (email) ──► Step 8 ──► Download
  Form       Submit    Confirmed   Email arrives     Preview      ZIP
             (AJAX)    page                          & verify
             
              └──► Step 5/6: Error states (validation / rate limit)
              └──► Step 3: Security info page (linked from form)
```

### Step 1 — Request Form (`/find-my-files`)
- Two-column layout: left = recovery method selector, right = form
- Fields: **Email address**, **Order number**
- Google reCAPTCHA v2 (conditional on `basic_settings.is_recaptcha`)
- Security Best Practices box with **"Learn more →"** link to Step 3
- 5 method buttons: Method 1 active, Methods 2–4 "coming soon" (disabled), Method 5 = Contact Support

### Step 2 — AJAX Submission
- `POST /find-my-files/request-link`
- Spinner overlay displayed during processing
- Returns JSON `{status, type, redirect, minutes}`
- On success → JS redirects to Step 4
- On `validation` error → inline validation alert shown
- On `rate_limited` error → rate limit alert shown with countdown minutes, reCAPTCHA + submit button hidden

### Step 3 — Security Verification Info (`/find-my-files/security-verification`)
- Public transparency page explaining the 6 server-side checks performed
- Linked from the Security Best Practices box on Step 1
- Static page — no dynamic data required

### Step 4 — Confirmation Page (`/find-my-files/link-sent`)
- Neutral: "If we found an order matching your details, a link has been sent."
- Anti-enumeration: shown for both success AND all silent failure paths
- Buttons: [Resend Link by Email] → back to Step 1 | [Return to Home]

### Step 5 — Validation Error (inline, Step 1 page)
- Red alert: "Please fill in all required fields correctly."
- reCAPTCHA + submit button remain visible

### Step 6 — Rate Limit Error (inline, Step 1 page)
- Orange/red alert: "Too many attempts. Try again in X minutes."
- reCAPTCHA + submit button **hidden** (wrapped in `#fmf-submit-area`)
- Links: "Change verification method" | "Contact support"

### Step 7 — Email Delivery
- PHPMailer, HTML table-based format (Outlook + Gmail safe, all styles inline)
- Subject: `Your Secure Download Link — Order {ORDER_NUMBER}`
- Contains: green checkmark, download button, 24h expiry notice, order number, social links, security disclaimer
- Expiry date shown below the card

### Step 8 — Download Page (`/find-my-files/download?t=TOKEN`)
- Two-panel card: **Validated Token** (signature OK, 24h expiry, remaining downloads) + **Security Analysis** (IP verified, device fingerprint, risk score)
- Full-width **"Download the Secure Files"** button → `/find-my-files/download/stream?t=TOKEN`
- Invalid/expired token shows the error page instead

### Download Stream (`/find-my-files/download/stream?t=TOKEN`)
- Re-validates token server-side
- Collects all `tender_modules` files for the purchased tender
- Builds a temporary ZIP at `storage/app/temp/`
- Streams it as `tender_documents_{ORDER_NUMBER}.zip`
- Deletes the temp ZIP after send
- Increments `download_count`; marks token `expired` when limit reached

---

## 3. Architecture & Security Model

### Validation Pipeline (11 steps in `requestLink()`)

```
1.  Format validation (email, order_number, reCAPTCHA)
2.  Log the attempt with risk score
3.  Rate limit check (IP + email + device)
4.  Order lookup (TenderPurchase by order_number)
5.  Email match check
6.  Payment status check (must be "Completed")
7.  Revoke any existing active tokens for this order
8.  Generate HMAC-SHA256 signed token
9.  Store token hash (never the raw token)
10. Build signed download URL
11. Send email via PHPMailer, log LINK_SENT
```

Steps 4, 5, and 6 **all return the same neutral response** on failure — the user cannot distinguish between them.

### Token Generation

```php
$payload = implode('|', [
    $purchase->order_number,   // order identity
    $emailHash,                // hashed email
    now()->timestamp,          // timestamp
    Str::random(16),           // entropy
]);
$rawToken = hash_hmac('sha256', $payload, config('app.key'));
// Only hash('sha256', $rawToken) is stored in DB
```

### Device Fingerprint

```php
hash('sha256', $request->userAgent() . $request->ip())
```

Used in rate limiting and access logs; never stored in plaintext.

### Email Hash

```php
hash('sha256', strtolower(trim($email)))
```

Email addresses are never stored in plaintext in `secure_tokens` or `access_logs`.

---

## 4. Database Schema

### `secure_tokens`

| Column | Type | Notes |
|---|---|---|
| `id` | UUID (PK) | Auto-generated on create |
| `order_id` | string | `TenderPurchase.order_number` |
| `email_hash` | string | SHA-256 of lowercased email |
| `token_hash` | string (unique) | SHA-256 of the raw token |
| `issued_at` | timestamp | When token was created |
| `expires_at` | timestamp | `issued_at + 24 hours` |
| `max_downloads` | tinyint | Default: 3 |
| `download_count` | tinyint | Incremented on each stream |
| `status` | enum | `active`, `expired`, `revoked` |
| `device_hash` | string (nullable) | SHA-256 of UA + IP |
| `ip` | string (nullable) | Requester IP |
| `created_at` / `updated_at` | timestamps | Laravel standard |

**Indexes:** `token_hash`, `(order_id, status)`

### `access_logs`

| Column | Type | Notes |
|---|---|---|
| `id` | UUID (PK) | Auto-generated on create |
| `event_type` | string | See event constants below |
| `order_id` | string (nullable) | |
| `ip` | string (nullable) | |
| `device_hash` | string (nullable) | |
| `user_agent` | string (nullable) | Truncated to 255 chars |
| `email_hash` | string (nullable) | |
| `risk_score` | float | 0–100 |
| `result` | string (nullable) | `OK`, `ORDER_NOT_FOUND`, `EMAIL_MISMATCH`, etc. |
| `created_at` | timestamp | **Immutable — no `updated_at`** |

**Indexes:** `event_type`, `order_id`, `created_at`

**Event types:**
- `LINK_REQUESTED` — form submitted
- `LINK_SENT` — email dispatched successfully
- `LINK_CLICKED` — download page visited with a valid token
- `DOWNLOAD_SUCCESS` — file stream completed
- `DOWNLOAD_FAILED` — token invalid/expired/exhausted
- `RATE_LIMIT_TRIGGERED` — request blocked by rate limiter

### `rate_limit_attempts`

| Column | Type | Notes |
|---|---|---|
| `id` | bigIncrements (PK) | |
| `key` | string (unique) | `ip:x.x.x.x` / `email:HASH` / `device:HASH` |
| `attempts` | unsignedInt | Cumulative count |
| `blocked_until` | timestamp (nullable) | NULL = not blocked |
| `last_attempt_at` | timestamp (nullable) | |
| `created_at` / `updated_at` | timestamps | |

**Indexes:** `key`, `blocked_until`

---

## 5. Models

### `App\SecureToken`

```php
// UUID primary key, no auto-increment
public $incrementing = false;
protected $keyType = 'string';

// Auto-generate UUID on boot
static::creating(fn($m) => $m->id ??= Str::uuid());

// Token validity check
public function isValid(): bool
{
    return $this->status === 'active'
        && $this->expires_at->isFuture()
        && $this->download_count < $this->max_downloads;
}
```

`$dates`: `issued_at`, `expires_at`

### `App\AccessLog`

```php
// UUID PK, immutable (no updated_at)
public $timestamps = false;

// Event type constants
const LINK_REQUESTED       = 'LINK_REQUESTED';
const LINK_SENT            = 'LINK_SENT';
const LINK_CLICKED         = 'LINK_CLICKED';
const DOWNLOAD_SUCCESS     = 'DOWNLOAD_SUCCESS';
const DOWNLOAD_FAILED      = 'DOWNLOAD_FAILED';
const RATE_LIMIT_TRIGGERED = 'RATE_LIMIT_TRIGGERED';

// Static helper for recording events
public static function record(string $eventType, array $data = []): void
{
    static::create(array_merge(['event_type' => $eventType], $data));
}
```

`$dates`: `created_at`

### `App\RateLimitAttempt`

```php
public function isBlocked(): bool
{
    return $this->blocked_until && $this->blocked_until->isFuture();
}

public function minutesUntilUnblock(): int
{
    if (!$this->isBlocked()) return 0;
    return (int) ceil(now()->diffInMinutes($this->blocked_until, false) * -1);
}
```

---

## 6. Controller Reference

**File:** `app/Http/Controllers/Front/FindMyFilesController.php`

### Constants

| Constant | Value | Meaning |
|---|---|---|
| `IP_LIMIT` | `5` | Max requests per IP per 15 min window |
| `EMAIL_LIMIT` | `3` | Max requests per email hash per hour |
| `BLOCK_MINUTES` | `[15, 60, 1440]` | Progressive lockout durations |
| `TOKEN_TTL_HOURS` | `24` | Token validity window |
| `MAX_DOWNLOADS` | `3` | Max download attempts per token |

### Private Helpers

| Method | Purpose |
|---|---|
| `getCurrentLang()` | Resolves active Language from session or default |
| `getVersion($lang)` | Returns theme version string (`dark` → `default`) |
| `emailHash(string $email)` | `sha256(lowercase(trim($email)))` |
| `deviceHash(Request $req)` | `sha256(userAgent + ip)` |
| `generateToken($purchase, $hash, $req)` | HMAC-SHA256 signed token |
| `checkRateLimit($req, $hash)` | Checks 3 rate limit keys; returns `{type, minutes}` or `null` |
| `incrementAttempts($req, $hash)` | Increments all 3 keys; applies progressive block |
| `riskScore($req, $hash)` | Returns 0–100 score based on recent `access_logs` |
| `sendDownloadEmail($purchase, $url, $be)` | PHPMailer dispatch; silently fails |
| `resolveToken(string $raw)` | Looks up by SHA-256 hash; auto-expires overdue tokens |
| `downloadError($lang)` | Returns `download_error` view with language context |

### Public Actions

| Method | Route | Description |
|---|---|---|
| `index()` | `GET /find-my-files` | Request form (Step 1) |
| `requestLink()` | `POST /find-my-files/request-link` | AJAX handler (Step 2) |
| `linkSent()` | `GET /find-my-files/link-sent` | Confirmation page (Step 4) |
| `securityInfo()` | `GET /find-my-files/security-verification` | Transparency page (Step 3) |
| `download()` | `GET /find-my-files/download?t=TOKEN` | Download preview page (Step 8) |
| `downloadStream()` | `GET /find-my-files/download/stream?t=TOKEN` | ZIP stream (file delivery) |

---

## 7. Routes

Registered in `routes/web.php` under the comment `/** Find My Files — Secure File Recovery **/`:

```php
Route::post('/find-my-files/request-link',
    'Front\FindMyFilesController@requestLink')->name('find_my_files.request_link');

Route::get('/find-my-files/link-sent',
    'Front\FindMyFilesController@linkSent')->name('find_my_files.link_sent');

Route::get('/find-my-files/security-verification',
    'Front\FindMyFilesController@securityInfo')->name('find_my_files.security_info');

Route::get('/find-my-files/download',
    'Front\FindMyFilesController@download')->name('find_my_files.download');

Route::get('/find-my-files/download/stream',
    'Front\FindMyFilesController@downloadStream')->name('find_my_files.stream');
```

The main landing page (`/find-my-files`) is served dynamically through the **permalink system** (see §14).

---

## 8. Views

All views extend `front.$version.layout` and receive `$bse`, `$currentLang`, `$version`, `$bs`.

| View file | Route name | Description |
|---|---|---|
| `front/find-my-files/index.blade.php` | `find_my_files` | Request form |
| `front/find-my-files/link_sent.blade.php` | `find_my_files.link_sent` | Confirmation |
| `front/find-my-files/security_info.blade.php` | `find_my_files.security_info` | Transparency page |
| `front/find-my-files/download.blade.php` | `find_my_files.download` | Download preview |
| `front/find-my-files/download_error.blade.php` | — | Invalid/expired token error |

### `download.blade.php` — Template Variables

| Variable | Type | Source |
|---|---|---|
| `$token` | `SecureToken` | Resolved from `?t=` query param |
| `$rawToken` | string | Raw token (passed to stream URL) |
| `$riskScore` | float | 0–100 from `riskScore()` |
| `$riskLabel` | string | `Low` / `Medium` / `High` |
| `$riskColor` | string | CSS hex color |
| `$streamUrl` | string | `route('find_my_files.stream', ['t' => $rawToken])` |

### `index.blade.php` — JavaScript Behaviour

- **AJAX submission** via `fetch()` with `FormData` (includes CSRF token from hidden `@csrf` field)
- **reCAPTCHA integration:** `fmfCaptchaVerified()` / `fmfCaptchaExpired()` callbacks; `captchaReady` flag gates submission
- **Spinner:** `#fmf-overlay` shown during request, hidden on response
- **Processing heading:** `#fmf-processing-heading` shown during AJAX
- **Error alerts:**
  - `#fmf-error-validation` — format/captcha errors (`display:flex` when shown)
  - `#fmf-error-ratelimit` — rate limit (`display:flex`; hides `#fmf-submit-area`)
- **"Change method" link** — scrolls to `#fmf-methods-section` on the same page
- **`hideErrors()`** — hides both alerts and restores `#fmf-submit-area`

---

## 9. Email Template

**File:** `resources/views/mail/secure_download_link.blade.php`

### Compatibility
- Full table-based layout (no `<div>`, no `flexbox`)
- All styles inline (no `<style>` block)
- Outlook-safe (MSO conditional comments, `PixelsPerInch` meta)
- Gmail-safe (no external CSS)
- Image fallback: `onerror="this.style.display='none'"` on the checkmark image; text `✓` fallback for image-blocked clients

### Template Variables

| Variable | Type | Description |
|---|---|---|
| `$purchase` | `TenderPurchase` | Order model (used for `order_number`, `email`, name) |
| `$downloadUrl` | string | Full signed URL to `/find-my-files/download?t=TOKEN` |
| `$expiresAt` | string | Formatted: `d M Y, H:i` (e.g. `13 Apr 2026, 14:30`) |
| `$maxDownloads` | int | `3` |
| `$fromName` | string | From `basic_extended.from_name` or `config('app.name')` |
| `$appUrl` | string | `config('app.url')` (used for image src base) |

### Email Structure
```
[ Green checkmark circle ]  Your order has been successfully confirmed.
[ Download button — full width blue CTA ]
24h validity notice + max downloads + order number
──────────────────────────────────────────────────
[ ICA circle ]  [ f ][ 𝕏 ][ W ]  Automated email disclaimer
──────────────────────────────────────────────────
Below card: "If you did not request this, ignore this email. Expires: DATE"
```

---

## 10. Rate Limiting

Three independent counters are checked and incremented for every failed attempt:

| Key format | Tracks |
|---|---|
| `ip:x.x.x.x` | All requests from this IP address |
| `email:SHA256_HASH` | All requests for this email |
| `device:SHA256_HASH` | All requests from this browser/device |

### Progressive Block Schedule

| Attempts reached | Lockout duration |
|---|---|
| 3 | 15 minutes |
| 6 | 1 hour |
| 10 | 24 hours |

A block is triggered by the **first key to cross a threshold**. All three keys are incremented together on every failed lookup.

### What triggers an increment

- Order not found
- Email does not match order
- Payment status not "Completed"

What does **not** trigger an increment:
- Format validation failures (returned as `validation` error before rate limit check)
- Successful lookups

---

## 11. Token System

### Lifecycle

```
[Created]  → status=active, expires_at = now + 24h
[Used]     → download_count++
[Exhausted]→ status=expired (when download_count >= max_downloads)
[Time out] → status=expired (lazy: set on next resolveToken() call if expires_at is past)
[Re-request]→ all previous active tokens for the order are revoked (status=revoked)
             before a new token is issued
```

### Token Validation (`SecureToken::isValid()`)

All three conditions must be true:
1. `status === 'active'`
2. `expires_at` is in the future
3. `download_count < max_downloads`

### Security Properties

- The raw token travels **only in the email URL** and **in the `?t=` query parameter**
- The database stores **only** `hash('sha256', $rawToken)` — a rainbow table attack against the DB yields nothing actionable
- Token lookup: `WHERE token_hash = hash('sha256', $rawToken)` — O(1) via unique index
- Tokens are single-order: the `order_id` on the token is verified against the purchase before streaming files

---

## 12. Risk Scoring

Computed by `riskScore(Request $request, string $emailHash): float`.

Returns a value from **0 to 100**.

| Signal | Points | Cap |
|---|---|---|
| Recent `LINK_REQUESTED` events from same IP in last 10 minutes | +10 per event | 50 |
| Recent `LINK_REQUESTED` events from same email hash in last 1 hour | +10 per event | 50 |

**Risk labels and colors:**

| Score | Label | Color |
|---|---|---|
| < 30 | Low | `#16a34a` (green) |
| 30–69 | Medium | `#d97706` (amber) |
| ≥ 70 | High | `#dc2626` (red) |

The risk score is **displayed** on the download page for transparency. It is **logged** on every `access_log` entry. It does **not** block the request on its own — rate limiting handles blocking.

---

## 13. File Download — ZIP Streaming

**Controller method:** `downloadStream()`

### Process

1. Re-validate token (abort 403 if invalid)
2. Load `TenderPurchase` by `token->order_id`
3. Fetch all `TenderModule` records with `status=1` for the tender
4. Create a temp directory at `storage/app/temp/` if needed
5. Open `ZipArchive` at `storage/app/temp/tender_{ORDER}_{TIME}.zip`
6. For each module with a non-empty `tender_file`:
   - Resolve path: `public_path('assets/front/files/tender_modules/' . $module->tender_file)`
   - Add to ZIP as `{safe_module_name}.{ext}` (sanitized with `preg_replace('/[^a-zA-Z0-9_\-]/', '_', ...)`)
7. If 0 files were added → abort 404
8. Increment `download_count`; set `status=expired` if limit reached
9. Log `DOWNLOAD_SUCCESS`
10. Stream with `response()->download()->deleteFileAfterSend(true)`

### Output filename

```
tender_documents_{ORDER_NUMBER}.zip
```

### File source

```
/var/www/minhazul.site/Plus-agency/assets/front/files/tender_modules/{filename}
```

Resolved in code as `base_path('../assets/front/files/tender_modules/')` — one level above `core/`. This is the same directory used by the Tender Module file upload (`TenderModuleController`).

---

## 14. Permalink / Menu Integration

The landing page is registered in the `permalinks` table:

| Column | Value |
|---|---|
| `permalink` | `find-my-files` |
| `type` | `find_my_files` |
| `details` | `0` |

The permalink router in `routes/web.php` handles it:

```php
} elseif ($type == 'find_my_files') {
    $action    = 'Front\FindMyFilesController@index';
    $routeName = 'find_my_files';
}
```

This means:
- The public URL is `http://localhost/Plus-agency/find-my-files`
- `route('find_my_files')` works anywhere in the codebase
- The page can be added to the **Menu Builder** like any other page type

---

## 15. Configuration Requirements

### reCAPTCHA

The form conditionally renders reCAPTCHA based on `basic_settings.is_recaptcha`. To enable it:

1. Go to **Admin → Settings → Basic Settings**
2. Enable reCAPTCHA and enter your **Google reCAPTCHA v2** site key and secret key
3. The controller reads these and sets them via `Config::set('captcha.sitekey', ...)` at runtime

If `is_recaptcha = 0`, the captcha widget is hidden and the `g-recaptcha-response` validation rule is not applied.

### Email / SMTP

The `sendDownloadEmail()` method uses the same SMTP configuration as the rest of the application:

| Setting | Source |
|---|---|
| SMTP enabled | `basic_extended.is_smtp` |
| Host | `basic_extended.smtp_host` |
| Username | `basic_extended.smtp_username` |
| Password | `basic_extended.smtp_password` |
| Encryption | `basic_extended.encryption` |
| Port | `basic_extended.smtp_port` |
| From address | `basic_extended.from_mail` |
| From name | `basic_extended.from_name` |

If SMTP is disabled, PHPMailer uses the server's default `sendmail`.

Mail failures are silently swallowed — the user always reaches the "link sent" confirmation page. Check your server mail logs if emails are not arriving.

### Storage

The ZIP temp files are written to `storage/app/temp/`. Ensure this directory is writable by the web server:

```bash
chmod -R 775 storage/app/temp
chown -R www-data:www-data storage/app/temp
```

---

## 16. File & Directory Map

```
app/
├── AccessLog.php                          # Immutable audit log model
├── RateLimitAttempt.php                   # Rate limit tracker model
├── SecureToken.php                        # Signed download token model
└── Http/Controllers/Front/
    └── FindMyFilesController.php          # Main controller

database/migrations/
├── 2026_04_12_000001_create_secure_tokens_table.php
├── 2026_04_12_000002_create_access_logs_table.php
└── 2026_04_12_000003_create_rate_limit_attempts_table.php

resources/views/
├── front/find-my-files/
│   ├── index.blade.php                    # Step 1 — Request form
│   ├── link_sent.blade.php                # Step 4 — Confirmation
│   ├── security_info.blade.php            # Step 3 — Transparency page
│   ├── download.blade.php                 # Step 8 — Download preview
│   └── download_error.blade.php          # Invalid/expired token error
└── mail/
    └── secure_download_link.blade.php     # Step 7 — HTML email

routes/web.php
├── Line 215-219: Static routes (POST + 4 × GET)
└── Line 1421-1423: Permalink router case for find_my_files

public/assets/front/files/tender_modules/ # Source files for ZIP
storage/app/temp/                          # Temporary ZIP files (auto-deleted)
```

---

## 17. Extending the Feature

### Adding a New Recovery Method

The form (Step 1) already has placeholder buttons for Methods 2–4:

| Method | Placeholder label |
|---|---|
| Method 2 | Email + Phone OTP |
| Method 3 | Email + Payment Reference |
| Method 4 | Expired Link Regeneration |

To activate one:

1. Remove the `disabled` attribute and `title="Coming soon"` from the method button in `index.blade.php`
2. Add a new form section (hidden by default, shown when the method button is clicked via JS)
3. Add a new AJAX handler route pointing to a new controller method
4. Reuse `SecureToken`, `AccessLog`, `RateLimitAttempt` — they are method-agnostic

### Adding an Admin Panel

The three tables are ready for admin UI. Suggested structure:

- **Access Logs Viewer** — paginated table of `access_logs`, filterable by `event_type`, `order_id`, `ip`, date range; shows the 6 verification checkmarks for `LINK_SENT` entries
- **Token Manager** — list of `secure_tokens` with status filter; "Revoke" action sets `status=revoked`
- **Rate Limit Manager** — list of `rate_limit_attempts`; "Unblock" action sets `blocked_until=null`

### Increasing Token TTL or Download Limit

Change the constants in `FindMyFilesController.php`:

```php
const TOKEN_TTL_HOURS = 24;   // ← change here
const MAX_DOWNLOADS   = 3;    // ← change here
```

These values are passed to the email template as `$expiresAt` and `$maxDownloads` respectively.

### Adjusting Rate Limit Thresholds

```php
const IP_LIMIT      = 5;              // requests per window
const EMAIL_LIMIT   = 3;              // requests per window
const BLOCK_MINUTES = [15, 60, 1440]; // 15min → 1hr → 24hr
```

The `incrementAttempts()` method checks cumulative `attempts` against hardcoded thresholds (3, 6, 10). If you change `BLOCK_MINUTES`, also review those thresholds inside the method.

---

## 18. Known Limitations & Future Work

### Current Limitations

| Item | Details |
|---|---|
| **No email check image asset** | The email template references `assets/front/img/email-check.png` which may not exist. The `✓` text fallback renders in all clients, but the image will 404. Add a 26×26px white checkmark PNG to resolve this. |
| **Social links are `href="#"`** | Facebook, Twitter, WhatsApp links in the email footer and download page footer are placeholder `#` hrefs. Replace with actual ICA GROUPE social URLs. |
| **No admin UI** | Access logs, token revocation, and rate limit management require manual DB queries. Admin panel UI is pending. |
| **Methods 2–4 not implemented** | Phone OTP, Payment Reference, and Link Regeneration methods show "coming soon" placeholders. |
| **Rate limit counters never reset** | `rate_limit_attempts.attempts` is cumulative and never decrements. A scheduled job to prune old records would prevent unbounded growth. |
| **No cleanup job for expired tokens** | `secure_tokens` and `access_logs` grow indefinitely. A scheduled `php artisan` command or database event to prune records older than N days would be needed in production. |
| **ZIP built in memory-constrained temp dir** | For very large tender modules, building the ZIP in `storage/app/temp` could be slow. Consider streaming directly to output with `ob_start()` / `ob_end_clean()` for large files. |

### Recommended Production Tasks

- [ ] Configure reCAPTCHA keys in Admin → Settings
- [ ] Verify SMTP configuration and test email delivery
- [ ] Set up a cron job to purge `access_logs` older than 90 days
- [ ] Set up a cron job to purge expired/revoked `secure_tokens` older than 30 days
- [ ] Add the ICA GROUPE social media URLs to the email and download page footer
- [ ] Add `public/assets/front/img/email-check.png` (26×26px white checkmark on green circle)
- [ ] Add "Find My Files" to the site navigation via Menu Builder (Admin → Menu Builder → add `find_my_files` permalink)
- [ ] Build admin panel for access log viewer and token manager
