# Find My Files — Secure File Recovery System

**Feature for:** ICA GROUPE client  
**Module:** Plus Agency Laravel CMS  
**Built:** April 2026  
**Status:** Complete (All 4 Methods)

---

## Table of Contents

1. [Overview](#1-overview)
2. [Recovery Methods — Summary](#2-recovery-methods--summary)
3. [Method 1 — Email + Order Number](#3-method-1--email--order-number)
4. [Method 2 — Email + Phone (OTP)](#4-method-2--email--phone-otp)
5. [Method 3 — Email + Payment Reference](#5-method-3--email--payment-reference)
6. [Method 4 — Expired Link Regeneration](#6-method-4--expired-link-regeneration)
7. [Shared Flow — Download & File Delivery](#7-shared-flow--download--file-delivery)
8. [Architecture & Security Model](#8-architecture--security-model)
9. [Database Schema](#9-database-schema)
10. [Models](#10-models)
11. [Controller Reference](#11-controller-reference)
12. [Routes](#12-routes)
13. [Views](#13-views)
14. [Email Template](#14-email-template)
15. [Rate Limiting](#15-rate-limiting)
16. [Token System](#16-token-system)
17. [Risk Scoring](#17-risk-scoring)
18. [File Download — ZIP Streaming](#18-file-download--zip-streaming)
19. [Permalink / Menu Integration](#19-permalink--menu-integration)
20. [Configuration Requirements](#20-configuration-requirements)
21. [File & Directory Map](#21-file--directory-map)
22. [Known Limitations & Future Work](#22-known-limitations--future-work)

---

## 1. Overview

**Find My Files** allows customers who purchased tender documents to securely re-download their files at any time. Four independent recovery methods are available. Every method ends the same way: a signed one-time download link is emailed to the address on record, and the customer clicks it to download a ZIP of their purchased files.

### Core Principles

| Principle | Implementation |
|---|---|
| **Anti-enumeration** | All failure paths return the same neutral "link sent" page — the customer can never determine why a lookup failed |
| **Token security** | Raw tokens are never stored — only `SHA-256(token)` is kept in the database |
| **Progressive rate limiting** | Separate counters per IP, email hash, and device fingerprint with escalating lockout periods |
| **Immutable audit trail** | Every event is appended to `access_logs` — no updates, no deletes |
| **Secure delivery** | Files are streamed as a temporary ZIP; the temp file is deleted after send |

---

## 2. Recovery Methods — Summary

| # | Method | Input required | Route |
|---|---|---|---|
| 1 | Email + Order Number | Email address + order number | `POST /find-my-files/request-link` |
| 2 | Email + Phone (OTP) | Email address + phone number | `POST /find-my-files/otp/request` then `/otp/verify` |
| 3 | Email + Payment Reference | Email address + payment reference code | `POST /find-my-files/payment-ref` |
| 4 | Expired Link / Regenerate | Email address only | `POST /find-my-files/regenerate` |

All methods share:
- The same AJAX form container at `/find-my-files`
- The same progressive rate limiter
- The same `SecureToken` issuance and download flow
- The same email delivery via PHPMailer

---

## 3. Method 1 — Email + Order Number

**Controller method:** `requestLink()`  
**Route:** `POST /find-my-files/request-link`

### What the customer enters
- Email address
- Order number (case-insensitive; normalised to uppercase server-side)
- reCAPTCHA (if enabled in admin settings)

### Validation pipeline (11 steps)

```
1.  Format validation — email, order_number, reCAPTCHA
2.  Log attempt with risk score (LINK_REQUESTED / PENDING)
3.  Rate limit check — IP + email hash + device hash
4.  Order lookup — TenderPurchase by order_number (case-insensitive)
5.  Email match — compare SHA-256 hashes (case-insensitive)
6.  Payment status — must be "Completed"
7.  Revoke any existing active tokens for this order
8.  Generate HMAC-SHA256 signed raw token
9.  Store only SHA-256(rawToken) in secure_tokens
10. Build signed download URL → route('find_my_files.download', ['t' => $rawToken])
11. Send email, log LINK_SENT / OK
```

Steps 4, 5, and 6 **all return the same neutral redirect on failure** — the customer cannot distinguish between them.

### AJAX response format

```json
// Success
{ "status": "success", "redirect": "/find-my-files/link-sent" }

// Validation error
{ "status": "error", "type": "validation" }

// Rate limited
{ "status": "error", "type": "rate_limited", "minutes": 28 }

// Order not found / email mismatch / payment issue (all identical):
{ "status": "error", "type": "order_not_found" }
{ "status": "error", "type": "email_mismatch" }
{ "status": "error", "type": "invalid_status" }
```

> Note: In practice the UI redirects to the "link sent" page for order/email/payment failures too — the JSON error types above are logged but the JS still shows the neutral confirmation.

### Access log results

| Result value | Meaning |
|---|---|
| `PENDING` | Initial log before lookup |
| `ORDER_NOT_FOUND` | No purchase with that order number |
| `EMAIL_MISMATCH` | Order found but email doesn't match |
| `INVALID_STATUS` | Payment not Completed |
| `OK` (on LINK_SENT event) | Link successfully emailed |

---

## 4. Method 2 — Email + Phone (OTP)

**Controller methods:** `requestOtp()`, `verifyOtp()`, `resendOtp()`  
**Routes:**
- `POST /find-my-files/otp/request`
- `POST /find-my-files/otp/verify`
- `POST /find-my-files/otp/resend`

This method is a two-step flow: the customer first submits their email + phone to trigger an SMS OTP, then submits the 6-digit code to verify and receive the download link.

### Step A — Request OTP (`requestOtp`)

**Input:** email, phone number

**Pipeline:**

```
1.  Format validation — email (required|email), phone (required, min:6, max:20, digits/spaces/+/dashes only)
2.  Rate limit check — same 3-key check as Method 1
3.  Lookup — find a Completed TenderPurchase where email AND phone match (phone compared as digits only via SHA-256 hash)
4.  If no match → return neutral success with fake session_token (anti-enumeration)
5.  If match → expire any pending OTP sessions for this order
6.  Generate 6-digit OTP (random_int, zero-padded), create OtpVerification record with:
    - session_token (UUID)
    - email_hash, phone_hash (SHA-256)
    - otp_hash = SHA-256(rawOtp) — never the raw OTP
    - expires_at = now + 10 minutes
    - status = "pending"
    - masked_phone (e.g. +88 0***** **12)
7.  Send SMS via SmsGatewayInterface (Twilio or configured provider)
8.  If SMS fails → expire the OTP record, return { type: "sms_failed" }
9.  Log OTP_SENT, return { status, session_token, masked_phone, resend_after: 60 }
```

**AJAX response (success):**
```json
{
  "status": "success",
  "session_token": "uuid-v4",
  "masked_phone": "+88 0***** **12",
  "resend_after": 60
}
```

The UI uses the `session_token` to poll or submit the verify step. The `masked_phone` is shown in the UI ("Code sent to +88 0***** **12").

### Step B — Verify OTP (`verifyOtp`)

**Input:** session_token (UUID), otp_code (6-digit string)

**Pipeline:**

```
1.  Format validation — session_token (size:36), otp_code (size:6, digits only)
2.  Load OtpVerification by session_token
3.  Check isUsable() — status must be "pending" and not expired
4.  Compare SHA-256(submitted_otp) to stored otp_hash
5.  If wrong OTP → increment attempts; if attempts >= 3 → mark "exhausted"
6.  If correct → mark OTP "verified", revoke existing active tokens for the order
7.  Issue new SecureToken (same as Method 1 steps 8–9)
8.  Email download link, log LINK_SENT / OTP_VERIFIED_OK
9.  Return { status: "success", redirect: "/find-my-files/link-sent" }
```

**OTP error responses:**
```json
{ "status": "error", "type": "otp_invalid", "attempts_left": 2 }
{ "status": "error", "type": "otp_exhausted" }
{ "status": "error", "type": "otp_expired" }
{ "status": "error", "type": "session_invalid" }
```

### Step C — Resend OTP (`resendOtp`)

**Input:** session_token

**Pipeline:**

```
1.  Validate session_token exists and is still usable
2.  Check canResend() — last_resend_at must be > 60 seconds ago
3.  Generate new OTP, update otp_hash + expires_at + last_resend_at + reset attempts to 0
4.  Resend SMS
5.  Return { status: "success", resend_after: 60 }
```

If resend is too soon: `{ "status": "error", "type": "resend_too_soon", "resend_after": 42 }`

### OtpVerification constants

| Constant | Value | Meaning |
|---|---|---|
| `MAX_ATTEMPTS` | `3` | Wrong guesses before session exhausted |
| `OTP_TTL_MIN` | `10` | Minutes until OTP expires |
| `RESEND_DELAY` | `60` | Seconds between resend requests |

### `otp_verifications` table columns

| Column | Notes |
|---|---|
| `session_token` | UUID — identifies the OTP session to the browser |
| `email_hash` | SHA-256 of email |
| `phone_hash` | SHA-256 of digits-only phone |
| `otp_hash` | SHA-256 of the raw OTP — raw OTP never stored |
| `order_id` | Linked TenderPurchase.order_number |
| `expires_at` | now + 10 min |
| `attempts` | Wrong-guess counter |
| `last_resend_at` | Timestamp of last SMS send |
| `status` | `pending` / `verified` / `expired` / `exhausted` |
| `masked_phone` | Pre-computed masked string for UI display |
| `ip` / `device_hash` | For audit |

---

## 5. Method 3 — Email + Payment Reference

**Controller method:** `requestByPaymentRef()`  
**Route:** `POST /find-my-files/payment-ref`

### What the customer enters
- Email address
- Payment reference code (alphanumeric + dash/underscore, 4–100 chars; stored in `tender_purchases.payment_reference`)

### Pipeline

```
1.  Format validation — email, payment_reference (regex: /^[A-Za-z0-9\-_]+$/)
2.  Rate limit check
3.  Lookup — find a Completed TenderPurchase where payment_reference matches (normalised to uppercase) AND email matches (case-insensitive)
4.  If no match → increment rate limit, log PAYREF_NO_MATCH, return neutral success redirect
5.  Revoke existing active tokens for the order
6.  Issue new SecureToken
7.  Email download link
8.  Log LINK_SENT / PAYREF_OK
9.  Return { status: "success", redirect: "/find-my-files/link-sent" }
```

### Notes

- `payment_reference` is stored on `tender_purchases` and populated at checkout time by the payment gateway.
- The reference is normalised to uppercase on lookup (`strtoupper(trim(...))`).
- If no match, the response is **identical to a successful request** — neutral anti-enumeration.

### Access log results

| Result value | Meaning |
|---|---|
| `PAYREF_NO_MATCH` | No purchase found with that email + reference |
| `PAYREF_OK` (on LINK_SENT) | Match found, link sent |

---

## 6. Method 4 — Expired Link Regeneration

**Controller method:** `requestRegenerate()`  
**Route:** `POST /find-my-files/regenerate`

### What the customer enters
- Email address only (no order number required)

### Pipeline

```
1.  Format validation — email only
2.  Rate limit check
3.  Lookup — find the most recent Completed TenderPurchase for this email (sorted by created_at desc)
4.  If no match → increment rate limit, log REGEN_NO_MATCH, return neutral success redirect
5.  Regeneration cap — count SecureTokens for this email + order in the last 24h; if >= 3 → return { type: "regen_limit" }
6.  Revoke any existing active tokens for the order
7.  Issue new SecureToken
8.  Email download link
9.  Log LINK_SENT / REGEN_OK
10. Return { status: "success", redirect: "/find-my-files/link-sent" }
```

### Anti-abuse cap

`MAX_REGEN_PER_DAY = 3` — at most 3 new tokens can be generated for the same order per 24-hour window. This prevents token farming via the email-only method. When exceeded:

```json
{ "status": "error", "type": "regen_limit" }
```

The UI shows a "Daily regeneration limit reached" message.

### Notes

- This method finds the **most recent** purchase by email, not a specific one. If a customer has multiple orders, the latest one is used.
- No order number is required — intended for customers who no longer have their order confirmation email.

### Access log results

| Result value | Meaning |
|---|---|
| `REGEN_NO_MATCH` | No completed purchase for this email |
| `REGEN_LIMIT_EXCEEDED` | Daily cap of 3 tokens reached |
| `REGEN_OK` (on LINK_SENT) | New link issued |

---

## 7. Shared Flow — Download & File Delivery

All 4 methods converge after a `SecureToken` is issued and the email is sent:

```
[Email arrives] ──► /find-my-files/download?t=TOKEN ──► Download button ──► /find-my-files/download/stream?t=TOKEN ──► ZIP file
```

### Download Preview Page (`/find-my-files/download?t=TOKEN`)

- Re-validates the token via `resolveToken()`.
- If invalid/expired/exhausted → renders `download_error.blade.php` ("Link Invalid or Expired").
- If valid → **increments `download_count`** and logs `LINK_CLICKED`.
- Shows: token status, expiry time, remaining downloads, IP/device/risk score analysis panel.
- Full-width **"Download the Secure Files"** button → stream route.

> **download_count is incremented on page visit, not on stream.** If a customer loads the download page 3 times they exhaust the token even without downloading. This is by design — it counts link clicks, not file bytes received.

### ZIP Stream (`/find-my-files/download/stream?t=TOKEN`)

- Re-validates token (abort 403 if invalid).
- Loads all `TenderModule` records with `status=1` for the purchased tender.
- Builds a ZIP at `storage/app/temp/` (configurable via `FMF_ZIP_TEMP_PATH` env var).
- Files resolved from `FMF_MODULES_PATH` env var (default: `{project_root}/assets/front/files/tender_modules/`).
- Streams the ZIP as `tender_documents_{ORDER_NUMBER}.zip`.
- Deletes the temp ZIP after send.
- Logs `DOWNLOAD_SUCCESS`.

> The stream does **not** increment `download_count` — that happens on the preview page.

---

## 8. Architecture & Security Model

### Token Generation (shared by all methods)

```php
$payload = implode('|', [
    $purchase->order_number,   // order identity
    $emailHash,                // SHA-256 of email
    now()->timestamp,          // timestamp prevents replay
    Str::random(16),           // entropy
]);
$rawToken = hash_hmac('sha256', $payload, config('app.key'));
// Only hash('sha256', $rawToken) is stored in DB — never the raw token
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

Email addresses are never stored in plaintext in `secure_tokens`, `access_logs`, or `otp_verifications`.

---

## 9. Database Schema

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
| `download_count` | tinyint | Incremented on download page visit |
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
| `result` | string (nullable) | `OK`, `ORDER_NOT_FOUND`, `OTP_SENT`, `PAYREF_OK`, etc. |
| `created_at` | timestamp | **Immutable — no `updated_at`** |

**Indexes:** `event_type`, `order_id`, `created_at`

**Event types:**

| Constant | Meaning |
|---|---|
| `LINK_REQUESTED` | Form submitted (all methods) |
| `LINK_SENT` | Email dispatched successfully |
| `LINK_CLICKED` | Download page visited with valid token |
| `DOWNLOAD_SUCCESS` | ZIP stream completed |
| `DOWNLOAD_FAILED` | Token invalid/expired/exhausted at stream |
| `RATE_LIMIT_TRIGGERED` | Request blocked by rate limiter |

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

### `otp_verifications`

| Column | Type | Notes |
|---|---|---|
| `id` | bigIncrements (PK) | |
| `session_token` | string (unique) | UUID — browser-side session identifier |
| `email_hash` | string | SHA-256 of email |
| `phone_hash` | string | SHA-256 of digits-only phone |
| `otp_hash` | string | SHA-256 of the 6-digit OTP |
| `order_id` | string (nullable) | Linked order number |
| `expires_at` | timestamp | now + 10 minutes |
| `attempts` | tinyint | Wrong-guess counter |
| `last_resend_at` | timestamp (nullable) | For resend rate limit |
| `status` | string | `pending` / `verified` / `expired` / `exhausted` |
| `masked_phone` | string (nullable) | Pre-computed, for UI display |
| `ip` | string (nullable) | |
| `device_hash` | string (nullable) | |
| `created_at` / `updated_at` | timestamps | |

---

## 10. Models

### `App\SecureToken`

```php
public $incrementing = false;  // UUID primary key
protected $keyType = 'string';

static::creating(fn($m) => $m->id ??= Str::uuid());  // auto-generate UUID

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
public $timestamps = false;  // immutable, created_at only

const LINK_REQUESTED       = 'LINK_REQUESTED';
const LINK_SENT            = 'LINK_SENT';
const LINK_CLICKED         = 'LINK_CLICKED';
const DOWNLOAD_SUCCESS     = 'DOWNLOAD_SUCCESS';
const DOWNLOAD_FAILED      = 'DOWNLOAD_FAILED';
const RATE_LIMIT_TRIGGERED = 'RATE_LIMIT_TRIGGERED';

public static function record(string $eventType, array $data = []): void
{
    static::create(array_merge(['event_type' => $eventType], $data));
}
```

### `App\RateLimitAttempt`

```php
public function isBlocked(): bool
{
    return $this->blocked_until && $this->blocked_until->isFuture();
}

public function minutesUntilUnblock(): int
{
    if (!$this->isBlocked()) return 0;
    return (int) ceil(now()->diffInSeconds($this->blocked_until) / 60);
}
```

### `App\OtpVerification`

```php
const MAX_ATTEMPTS  = 3;    // wrong guesses before exhausted
const OTP_TTL_MIN   = 10;   // OTP valid for 10 minutes
const RESEND_DELAY  = 60;   // seconds between resends

public function isUsable(): bool { ... }         // pending + not expired
public function canResend(): bool { ... }        // last_resend_at > 60s ago
public function verifyOtp(string $raw): bool { return hash('sha256', $raw) === $this->otp_hash; }
public static function generateOtp(): string { return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT); }
public static function maskPhone(string $phone): string { ... }  // e.g. +88 0***** **12
```

---

## 11. Controller Reference

**File:** `app/Http/Controllers/Front/FindMyFilesController.php`

### Constants

| Constant | Value | Meaning |
|---|---|---|
| `IP_LIMIT` | `10` | Max attempts per IP before block |
| `EMAIL_LIMIT` | `5` | Max attempts per email before block |
| `BLOCK_MINUTES` | `[5, 15, 28, 1440]` | Progressive lockout: 5 min → 15 min → 28 min → 24 hr |
| `TOKEN_TTL_HOURS` | `24` | Token validity window |
| `MAX_DOWNLOADS` | `3` | Max download page visits per token |
| `MAX_REGEN_PER_DAY` | `3` | Max Method 4 regenerations per order per 24h |

### Private Helpers

| Method | Purpose |
|---|---|
| `getCurrentLang()` | Resolves active Language from session or default |
| `getVersion($lang)` | Returns theme version string (`dark` → `default`) |
| `emailHash(string $email)` | `sha256(lowercase(trim($email)))` |
| `phoneHash(string $phone)` | `sha256(digits-only phone)` |
| `deviceHash(Request $req)` | `sha256(userAgent + ip)` |
| `generateToken($purchase, $hash, $req)` | HMAC-SHA256 signed token |
| `checkRateLimit($req, $hash)` | Checks 3 rate limit keys; returns `{type, minutes}` or null |
| `incrementAttempts($req, $hash)` | Increments all 3 keys; applies progressive block |
| `riskScore($req, $hash)` | Returns 0–100 score based on recent `access_logs` |
| `sendDownloadEmail($purchase, $url, $be)` | PHPMailer dispatch; silently fails on error |
| `resolveToken(string $raw)` | Looks up by SHA-256 hash; auto-expires overdue tokens |
| `downloadError($lang)` | Returns `download_error` view with language context |

### Public Actions

| Method | Route | Description |
|---|---|---|
| `index()` | `GET /find-my-files` | Request form |
| `requestLink()` | `POST /find-my-files/request-link` | Method 1 AJAX handler |
| `linkSent()` | `GET /find-my-files/link-sent` | Neutral confirmation page |
| `securityInfo()` | `GET /find-my-files/security-verification` | Security transparency page |
| `download()` | `GET /find-my-files/download?t=TOKEN` | Download preview page |
| `downloadStream()` | `GET /find-my-files/download/stream?t=TOKEN` | ZIP file delivery |
| `requestOtp()` | `POST /find-my-files/otp/request` | Method 2 — step A |
| `verifyOtp()` | `POST /find-my-files/otp/verify` | Method 2 — step B |
| `resendOtp()` | `POST /find-my-files/otp/resend` | Method 2 — resend |
| `requestByPaymentRef()` | `POST /find-my-files/payment-ref` | Method 3 |
| `requestRegenerate()` | `POST /find-my-files/regenerate` | Method 4 |

---

## 12. Routes

Registered in `routes/web.php`:

```php
// Landing page (via permalink system)
Route::get('/find-my-files', 'Front\FindMyFilesController@index')->name('find_my_files');

// Shared pages
Route::get('/find-my-files/link-sent',            'Front\FindMyFilesController@linkSent')->name('find_my_files.link_sent');
Route::get('/find-my-files/security-verification','Front\FindMyFilesController@securityInfo')->name('find_my_files.security_info');
Route::get('/find-my-files/download',             'Front\FindMyFilesController@download')->name('find_my_files.download');
Route::get('/find-my-files/download/stream',      'Front\FindMyFilesController@downloadStream')->name('find_my_files.stream');

// Method 1
Route::post('/find-my-files/request-link', 'Front\FindMyFilesController@requestLink')->name('find_my_files.request_link');

// Method 2 — OTP
Route::post('/find-my-files/otp/request', 'Front\FindMyFilesController@requestOtp')->name('find_my_files.otp_request');
Route::post('/find-my-files/otp/verify',  'Front\FindMyFilesController@verifyOtp')->name('find_my_files.otp_verify');
Route::post('/find-my-files/otp/resend',  'Front\FindMyFilesController@resendOtp')->name('find_my_files.otp_resend');

// Method 3
Route::post('/find-my-files/payment-ref', 'Front\FindMyFilesController@requestByPaymentRef')->name('find_my_files.payment_ref');

// Method 4
Route::post('/find-my-files/regenerate',  'Front\FindMyFilesController@requestRegenerate')->name('find_my_files.regenerate');
```

---

## 13. Views

All views extend `front.$version.layout` and receive `$bse`, `$currentLang`, `$version`, `$bs`.

| View file | Route | Description |
|---|---|---|
| `front/find-my-files/index.blade.php` | `find_my_files` | Main form — all 4 method panels |
| `front/find-my-files/link_sent.blade.php` | `find_my_files.link_sent` | Neutral confirmation |
| `front/find-my-files/security_info.blade.php` | `find_my_files.security_info` | Transparency page |
| `front/find-my-files/download.blade.php` | `find_my_files.download` | Download preview |
| `front/find-my-files/download_error.blade.php` | — | Invalid/expired token error |

### `index.blade.php` — Method Panel Structure

The page uses a left sidebar (method selector) + right panel (active form):

| Button `data-method` | Panel ID | Method |
|---|---|---|
| `order_number` | `panel-order_number` | Method 1 — email + order |
| `phone_otp` | `panel-phone_otp` | Method 2 — email + OTP |
| `payment_ref` | `panel-payment_ref` | Method 3 — payment reference |
| `expired_link` | `panel-expired_link` | Method 4 — regenerate |
| `contact_support` | `panel-contact_support` | Contact form / link |

Hash-based auto-activation: appending `#method=expired_link` to the URL activates the Method 4 panel automatically (used by the download error page "Regenerate My Link" button).

### `download.blade.php` — Template Variables

| Variable | Type | Source |
|---|---|---|
| `$token` | `SecureToken` | Resolved from `?t=` query param |
| `$rawToken` | string | Raw token (passed to stream URL) |
| `$riskScore` | float | 0–100 from `riskScore()` |
| `$riskLabel` | string | `Low` / `Medium` / `High` |
| `$riskColor` | string | CSS hex color |
| `$streamUrl` | string | `route('find_my_files.stream', ['t' => $rawToken])` |

---

## 14. Email Template

**File:** `resources/views/mail/secure_download_link.blade.php`

- Full table-based layout — Outlook + Gmail safe, all styles inline
- `onerror="this.style.display='none'"` on checkmark image with `✓` text fallback

### Template Variables

| Variable | Description |
|---|---|
| `$purchase` | TenderPurchase model |
| `$downloadUrl` | Full signed URL to `/find-my-files/download?t=TOKEN` |
| `$expiresAt` | Formatted: `d M Y, H:i` |
| `$maxDownloads` | `3` |
| `$fromName` | From `basic_extended.from_name` or `config('app.name')` |
| `$appUrl` | `config('app.url')` |

---

## 15. Rate Limiting

Three independent counters checked and incremented for every failed attempt:

| Key format | Tracks |
|---|---|
| `ip:x.x.x.x` | All requests from this IP |
| `email:SHA256_HASH` | All requests for this email |
| `device:SHA256_HASH` | All requests from this browser/device |

### Progressive Block Schedule

| Cumulative attempts | Lockout duration |
|---|---|
| 1 – 3 | No block (free attempts) |
| ≥ 4 | 5 minutes |
| ≥ 5 | 15 minutes |
| ≥ 6 | 28 minutes |
| ≥ 7 | 24 hours |

A block is triggered by the **first key to cross a threshold**. All 3 keys are incremented together on every failed lookup. Blocked IPs/emails skip lookup entirely — `incrementAttempts()` is not called when already blocked.

### What triggers an increment (all methods)
- Order not found / no match
- Email does not match
- Payment status not Completed
- OTP lookup failures

### What does NOT trigger an increment
- Format validation failures (returned before rate limit check)
- Successful lookups

---

## 16. Token System

### Lifecycle

```
[Created]   → status=active, expires_at = now + 24h
[Page visit]→ download_count++ (via download() action)
[Exhausted] → status=expired when download_count >= max_downloads
[Timed out] → status=expired (lazy: set on next resolveToken() call)
[Re-request]→ all previous active tokens for the order are revoked before new token issued
```

### Token Validation (`SecureToken::isValid()`)

All three conditions must be true:
1. `status === 'active'`
2. `expires_at` is in the future
3. `download_count < max_downloads`

### Security Properties

- Raw token travels **only** in the email URL and `?t=` query parameter
- DB stores **only** `SHA-256(rawToken)` — a database dump yields nothing usable
- Lookup is `WHERE token_hash = SHA-256(rawToken)` — O(1) via unique index
- Tokens are single-order: `order_id` on the token is verified against the purchase before streaming

---

## 17. Risk Scoring

Computed by `riskScore(Request $request, string $emailHash): float`. Returns **0–100**.

| Signal | Points | Cap |
|---|---|---|
| Recent `LINK_REQUESTED` events from same IP in last 10 min | +10 per event | 50 |
| Recent `LINK_REQUESTED` events from same email hash in last 1 hour | +10 per event | 50 |

| Score | Label | Color |
|---|---|---|
| < 30 | Low | `#16a34a` (green) |
| 30–69 | Medium | `#d97706` (amber) |
| ≥ 70 | High | `#dc2626` (red) |

The risk score is **displayed** on the download page and **logged** on every access log entry. It does **not** block requests — rate limiting handles blocking.

---

## 18. File Download — ZIP Streaming

**Controller method:** `downloadStream()`

### Process

```
1.  Re-validate token (abort 403 if invalid)
2.  Load TenderPurchase by token->order_id
3.  Fetch all TenderModule records with status=1 for the tender
4.  Create temp directory (FMF_ZIP_TEMP_PATH env, default: storage/app/temp/)
5.  Open ZipArchive at {tempDir}/tender_{ORDER}_{time()}.zip
6.  For each module with a non-empty tender_file:
    - Resolve path: FMF_MODULES_PATH/{filename} (default: assets/front/files/tender_modules/)
    - Sanitise entry name: preg_replace('/[^a-zA-Z0-9_\-]/', '_', $module->name) + ext
    - Add to ZIP
7.  If 0 files added → abort 404
8.  Log DOWNLOAD_SUCCESS
9.  Stream with response()->download()->deleteFileAfterSend(true)
```

### Environment overrides (for testing)

| Env var | Default | Purpose |
|---|---|---|
| `FMF_MODULES_PATH` | `{project}/../assets/front/files/tender_modules` | Source file directory |
| `FMF_ZIP_TEMP_PATH` | `storage/app/temp` | Temp ZIP output directory |

---

## 19. Permalink / Menu Integration

| Column | Value |
|---|---|
| `permalink` | `find-my-files` |
| `type` | `find_my_files` |
| `details` | `0` |

The permalink router in `routes/web.php` registers this as `route('find_my_files')`. The page can be added to site navigation via **Admin → Menu Builder**.

The `tender_details` permalink must have `details=1` for the tender detail route to register correctly.

---

## 20. Configuration Requirements

### reCAPTCHA

Enable at **Admin → Settings → Basic Settings** → reCAPTCHA. If `is_recaptcha = 0`, the captcha is hidden and the validation rule is skipped.

### Email / SMTP

`sendDownloadEmail()` reads from `basic_settings_extended`:

| Setting | Column |
|---|---|
| SMTP on/off | `is_smtp` |
| Host | `smtp_host` |
| Username | `smtp_username` |
| Password | `smtp_password` |
| Encryption | `encryption` |
| Port | `smtp_port` |
| From address | `from_mail` |
| From name | `from_name` |

Mail failures are silently swallowed — the customer always reaches the confirmation page. Check server mail logs if emails are not arriving.

### SMS (Method 2 — OTP)

OTP SMS is sent via `SmsGatewayInterface` (bound in `AppServiceProvider`). Configure Twilio credentials at **Admin → Script Settings** (stored in `basic_settings_extra`):

| Field | Column |
|---|---|
| Enable Twilio | `twilio_status` |
| Account SID | `twilio_account_sid` |
| Auth Token | `twilio_auth_token` |
| From number | `twilio_from_number` |

### Storage

```bash
chmod -R 775 storage/app/temp
chown -R www-data:www-data storage/app/temp
```

---

## 21. File & Directory Map

```
app/
├── AccessLog.php                          # Immutable audit log model
├── OtpVerification.php                    # OTP session model (Method 2)
├── RateLimitAttempt.php                   # Rate limit tracker model
├── SecureToken.php                        # Signed download token model
├── Services/SmsGateway/                   # SMS gateway interface + Twilio adapter
└── Http/Controllers/Front/
    └── FindMyFilesController.php          # All 4 methods + download

database/migrations/
├── 2026_04_12_000001_create_secure_tokens_table.php
├── 2026_04_12_000002_create_access_logs_table.php
├── 2026_04_12_000003_create_rate_limit_attempts_table.php
├── 2026_04_18_000001_create_otp_verifications_table.php
└── 2026_04_18_000002_add_twilio_fields_to_basic_extras_table.php

resources/views/
├── front/find-my-files/
│   ├── index.blade.php                    # Main form — all 4 method panels
│   ├── link_sent.blade.php                # Neutral confirmation
│   ├── security_info.blade.php            # Transparency page
│   ├── download.blade.php                 # Download preview
│   └── download_error.blade.php          # Invalid/expired token error
└── mail/
    └── secure_download_link.blade.php     # HTML email (all methods)

routes/web.php                             # Lines ~215–231: all FMF routes

assets/front/files/tender_modules/        # Source files for ZIP (owned by www-data)
storage/app/temp/                          # Temporary ZIP files (auto-deleted after send)
```

---

## 22. Known Limitations & Future Work

| Item | Details |
|---|---|
| **No email check image asset** | Email template references `assets/front/img/email-check.png`. The `✓` text fallback works, but the image 404s. Add a 26×26px white checkmark PNG. |
| **Social links are `href="#"`** | Facebook, Twitter, WhatsApp links in the email and download page footer are placeholder hrefs. Replace with actual ICA GROUPE URLs. |
| **No admin UI** | Access logs, token revocation, and rate limit management require manual DB queries. |
| **Rate limit counters never reset** | `attempts` is cumulative. A scheduled prune job would prevent unbounded growth. |
| **No cleanup job for expired tokens** | `secure_tokens`, `otp_verifications`, and `access_logs` grow indefinitely. Cron jobs to prune old records needed in production. |
| **Method 4 uses most recent order only** | If a customer has multiple orders, Method 4 always links the newest. A future improvement could let them select which order. |

### Recommended Production Tasks

- [ ] Configure reCAPTCHA keys in Admin → Settings
- [ ] Verify SMTP configuration and test email delivery
- [ ] Configure Twilio (SID, token, from number) in Admin → Script Settings and test Method 2
- [ ] Add the ICA GROUPE social media URLs to email and download page footer
- [ ] Add `assets/front/img/email-check.png` (26×26px white checkmark on green circle)
- [ ] Add "Find My Files" to site navigation via Admin → Menu Builder
- [ ] Set up cron to purge `access_logs` older than 90 days
- [ ] Set up cron to purge expired/revoked `secure_tokens` older than 30 days
- [ ] Set up cron to purge `otp_verifications` older than 24 hours
- [ ] Build admin panel: access log viewer, token manager, rate limit unblock
