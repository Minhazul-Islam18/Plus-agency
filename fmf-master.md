# Find My Files — Master Security & Implementation Document
## Secure File Recovery System — All Methods

**Version:** 1.1  
**Date:** 2026-04-29  
**Module:** Secure File Recovery (`/find-my-files`)  
**Application:** Plus Agency — Tender Document Recovery

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Method Comparison](#2-method-comparison)
3. [Method 1 — Email + Order Number](#3-method-1--email--order-number)
4. [Method 2 — Email + Phone (OTP)](#4-method-2--email--phone-otp)
5. [Method 3 — Email + Payment Reference](#5-method-3--email--payment-reference)
6. [Method 4 — Expired Link / Regenerate](#6-method-4--expired-link--regenerate)
7. [Shared Security Infrastructure](#7-shared-security-infrastructure)
8. [Database Schema](#8-database-schema)
9. [Full Route Map](#9-full-route-map)
10. [Full File Map](#10-full-file-map)
11. [Audit & Logging Reference](#11-audit--logging-reference)
12. [System Constants](#12-system-constants)
13. [Admin Controls](#13-admin-controls)
14. [Data Retention Policy](#14-data-retention-policy)

---

## 1. System Overview

The Find My Files system allows buyers of tender documents to recover secure download links without requiring a user account. All four methods converge on the same final step: a time-limited, use-limited download token is issued and emailed to the buyer's registered address.

### Entry point from tender details

The tender details page contains a "Find My Files" link that passes the current tender's slug:

```
/find-my-files?tender=<slug>
```

This pre-populates the Method 2 (OTP) tender selector. Methods 1, 3, and 4 are not scoped to a specific tender — they search all purchases for the buyer's email.

### Final Output (all methods)

Every successful recovery produces a `SecureToken`:
- Valid for **24 hours**
- Maximum **3 opens** (link-clicks, not file downloads)
- Delivered by email only — never shown on screen
- Streamed as a ZIP archive of all purchased tender modules

### Golden Rule — Neutral Responses

Most methods return identical responses for matching and non-matching lookups to prevent enumeration. **Exceptions apply** — see [Section 7.5](#75-neutral-response-policy) for the full policy.

---

## 2. Method Comparison

| | Method 1 | Method 2 | Method 3 | Method 4 |
|---|---|---|---|---|
| **Name** | Email + Order Number | Email + Phone (OTP) | Email + Payment Reference | Expired Link / Regenerate |
| **Factors** | Email + Order No. | Email + Phone + Tender + OTP code | Email + Payment Ref | Email only |
| **Steps** | 1 | 2 | 1 | 1 |
| **What buyer needs** | Order number | Registered phone in reach | Payment/txn reference | Just email |
| **Verification strength** | Medium | High | Medium-High | Low (compensated) |
| **Tender scope required** | No | **Yes** | No | No |
| **New table** | No | Yes (`otp_verifications`) | No | No |
| **New column** | No | No | Yes (`payment_reference`) | No |
| **Regen cap** | — | — | — | 3 / 24h / order |
| **OTP involved** | No | Yes (6-digit, 10 min) | No | No |
| **SMS required** | No | Yes (Twilio) | No | No |
| **reCAPTCHA** | Yes (if enabled) | Yes (if enabled) | Yes (if enabled) | Yes (if enabled) |
| **Best used when** | Buyer has order email | Buyer forgot order number | Buyer has payment confirmation | Previous link expired/exhausted |

---

## 3. Method 1 — Email + Order Number

### How it works
Buyer submits their email address and order number. System validates the combination against `tender_purchases`, checks payment status, then issues a `SecureToken` and emails the download link.

### Flow
```
Email + Order Number → validate → reCAPTCHA → rate limit → lookup purchase
→ match email + order_number + status=Completed
→ revoke old tokens → issue SecureToken → email link
→ email success → redirect link-sent
→ email failure → return {type: 'email_failed'} error
```

### Validation rules
- `email` — required, valid format
- `order_number` — required, string, 3–50 chars
- reCAPTCHA (if enabled in admin)

### Failure responses

| Type | Meaning | Neutral? |
|---|---|---|
| `validation` | Bad format | — |
| `order_not_found` | No purchase with that order number | Yes |
| `email_mismatch` | Email doesn't match the order | Yes |
| `invalid_status` | Order not completed | Yes |
| `email_failed` | SecureToken issued but email delivery failed | No |
| `rate_limited` | Too many attempts | No |

### Controller method
`FindMyFilesController@requestLink` — `POST /find-my-files/request-link`

---

## 4. Method 2 — Email + Phone (OTP)

### How it works
Buyer selects the specific tender, submits email + registered phone number. If matched to a completed purchase for that tender, a 6-digit OTP is sent via SMS. Buyer enters the code in 6 individual digit boxes. On correct entry, a `SecureToken` is issued and the download link is emailed.

### Tender scope requirement

Method 2 is the **only method** requiring tender selection because email + phone alone are not unique across multiple orders.

- If the page was opened via `?tender=slug`, the tender is pre-populated and locked.
- Otherwise a dropdown lists all tenders, and the buyer must select one before the Send OTP button enables.
- The selected tender slug is submitted as `tender_slug` in the OTP request.
- The Send OTP button remains disabled until: email, phone, tender, and reCAPTCHA are all provided.

### Flow
```
Step 1: Email + Phone + Tender Slug → validate → reCAPTCHA → rate limit
→ match email + phone + tender_id + status=Completed
→ status=Pending found → return {type: 'payment_pending'} (not neutral)
→ no match → neutral fake session returned (no SMS sent)
→ match Completed → invalidate old OTP sessions → generate OTP → send SMS
→ SMS success → return {status: ok, otp_sent: true, session_token, masked_phone}
→ SMS failure → return {status: error, type: 'sms_failed'}

Step 2: session_token + 6-digit code → validate OTP hash
→ correct → issue SecureToken → email link
  → email success → redirect link-sent
  → email failure → return {type: 'email_failed'}
→ wrong → increment attempts → exhausted after 3 wrong entries
```

### `otp_sent` flag

`requestOtp()` includes `otp_sent: true/false` in success responses:

| `otp_sent` | Meaning | UI shown |
|---|---|---|
| `true` | SMS delivered | "A code was sent to +1 ***7" |
| `false` | No-match path (fake session) | "If your details match a purchase, you will receive a code." |

### OTP parameters

| Parameter | Value |
|---|---|
| Length | 6 digits (zero-padded) |
| TTL | 10 minutes |
| Max wrong attempts | 3 (then status = exhausted) |
| Resend cooldown | 60 seconds |
| Storage | `sha256(otp_code)` — raw code never stored |

### SMS Gateway

- **Interface:** `App\Services\SmsGateway\SmsGatewayInterface`
- **Production:** `TwilioSmsGateway` — cURL direct, no SDK dependency
- **Dev/fallback:** `LogSmsGateway` — returns `false` and logs a warning; the OTP verify step is **not** shown to the user
- **Resolution:** reads `basic_settings_extra.twilio_status / sid / token / from` at runtime via `AppServiceProvider`

When Twilio is unconfigured (`twilio_status = 0` or credentials missing), `LogSmsGateway` is bound. It returns `false` → the controller treats this as an SMS failure → `{type: 'sms_failed'}` is returned → the buyer sees an error, not the OTP entry step.

### Session token scope
- UUID v4, server-generated
- Returned in AJAX JSON body only — never in URL
- Lives in JS memory only — not persisted client-side
- Bound to `ip` + `device_hash` at creation
- Invalidated on: verified / expired / exhausted

### Failure responses

| Type | Meaning | Neutral? |
|---|---|---|
| `validation` | Bad format | — |
| `rate_limited` | Too many attempts | No |
| `payment_pending` | Purchase found but not yet approved/completed | No |
| `sms_failed` | Twilio delivery error or gateway not configured | No |
| `email_failed` | Token issued but email delivery failed | No |
| `otp_invalid` | Wrong code (shows attempts left) | — |
| `otp_exhausted` | 3 wrong entries | — |
| `otp_expired` | 10 min elapsed | — |
| `session_invalid` | UUID not found or no-match path | — |
| No-match lookup | Email+phone not found for that tender | Yes (fake session) |

### Controller methods
- `requestOtp()` — `POST /find-my-files/otp/request`
- `verifyOtp()` — `POST /find-my-files/otp/verify`
- `resendOtp()` — `POST /find-my-files/otp/resend`

---

## 5. Method 3 — Email + Payment Reference

### How it works
Buyer submits email + the payment/transaction reference they received when paying (gateway transaction ID or bank transfer reference). System normalises both the stored and submitted reference (strip whitespace, uppercase) and matches against `tender_purchases.payment_reference`.

### Flow
```
Email + Payment Reference → validate → reCAPTCHA → rate limit
→ normalise input: strtoupper(strip_whitespace(trim(input)))
→ load all Completed purchases with non-null payment_reference
→ normalise stored refs the same way → exact match on email + reference
→ match → revoke old tokens → issue SecureToken → email link
  → email success → redirect link-sent
  → email failure → return {type: 'email_failed'}
→ no match → return {type: 'no_match'} (real error, not neutral)
```

### Why no-match is a real error (not neutral)

An attacker would need **both** a valid email address **and** the correct payment reference to enumerate. The reference space is large (gateway transaction IDs, bank refs) and not guessable. Showing a real error improves UX without meaningful security cost.

### Reference normalisation

Both sides are normalised before comparison:

```php
$normalised = strtoupper(preg_replace('/\s+/', '', trim($input)));
$storedNorm = strtoupper(preg_replace('/\s+/', '', trim($purchase->payment_reference)));
```

This means `EXCEPTURI UNDE SEQUI` stored in DB matches `EXCEPTURIUNDERSEQUI` typed by the buyer.

### Reference capture points

| Point | Where | How |
|---|---|---|
| Buyer at checkout | `/tender/purchase` form | Optional text input, stored as-is |
| Admin manual entry | Admin → Tenders → Enrolls → Details modal | Inline editable field + Save button |
| Gateway callbacks | Future — column already in place | Write `payment_reference` on callback |

### Validation rules
- `email` — required, valid format
- `payment_reference` — required, 4–200 chars, regex `[A-Za-z0-9\-_ ]+` (spaces allowed)
- Input stripped of disallowed characters client-side on every keystroke
- reCAPTCHA (if enabled in admin)

### Failure responses

| Type | Meaning | Neutral? |
|---|---|---|
| `validation` | Bad format / disallowed chars | — |
| `rate_limited` | Too many attempts | No |
| `no_match` | Reference or email not found | No |
| `email_failed` | Token issued but email delivery failed | No |

### Controller method
`FindMyFilesController@requestByPaymentRef` — `POST /find-my-files/payment-ref`

---

## 6. Method 4 — Expired Link / Regenerate

### How it works
Buyer submits email only. System finds their most recently created completed purchase **across all tenders**, checks the daily regeneration cap, revokes old tokens, and issues a fresh `SecureToken`. Entry point is the download error page "Regenerate My Link" button which deep-links to `/find-my-files#method=expired_link`.

### Flow
```
Email → validate → reCAPTCHA → rate limit
→ find most recent Completed purchase by email (all tenders)
→ no match → neutral redirect
→ check: count(SecureTokens for order in last 24h) < 3
→ cap hit → regen_limit error (intentionally shown)
→ revoke active tokens → issue SecureToken → email link
  → email success → redirect link-sent
  → email failure → return {type: 'email_failed'}
```

### Regeneration cap
- Max **3 new tokens** per `(email_hash, order_id)` per 24 hours
- Counted from existing `secure_tokens` rows — no new table or column
- Intentionally **not neutral** — user needs to know to wait

### Multiple orders
If email matches more than one completed purchase, the **most recently created** one is used. For a specific older order, buyer should use Method 1.

### Deep-link auto-activation
`download_error.blade.php` → "Regenerate My Link" → `/find-my-files#method=expired_link`

JS reads hash → activates Method 4 panel → scrolls into view → `history.replaceState()` cleans hash.

### Failure responses

| Type | Meaning | Neutral? |
|---|---|---|
| `validation` | Bad email format | — |
| `rate_limited` | Too many attempts | No |
| `regen_limit` | ≥ 3 regenerations in 24h | No (intentional) |
| `email_failed` | Token issued but email delivery failed | No |
| No-match lookup | Email not found | Yes |

### Controller method
`FindMyFilesController@requestRegenerate` — `POST /find-my-files/regenerate`

---

## 7. Shared Security Infrastructure

All four methods use the same underlying security layer.

### 7.1 Rate Limiting — `rate_limit_attempts` table

Every request is checked and recorded against three keys simultaneously:

| Key | Scope |
|---|---|
| `ip:<address>` | All requests from this IP |
| `email:<sha256_hash>` | All requests for this email |
| `device:<sha256_hash>` | All requests from this browser fingerprint |

**Progressive block schedule:**

| Cumulative failed attempts | Block duration |
|---|---|
| ≥ 5 | 30 minutes |
| ≥ 10 | 2 hours |
| ≥ 20 | 24 hours |

Rate limit is checked **before** any database lookup — blocked requestors cannot probe purchase data.

Failed attempts are incremented on: wrong order number, email mismatch, invalid status, no-match lookups (all methods).

### 7.2 Device Fingerprint

```php
hash('sha256', $request->userAgent() . $request->ip())
```

Used as a third rate-limit dimension. Not treated as a reliable identity signal — only used for throttling.

### 7.3 Risk Score (0–100)

Computed on every request for logging purposes:

```
+10 per recent LINK_REQUESTED from same IP    (last 10 min, max 50)
+10 per recent LINK_REQUESTED from same email (last 1 hour,  max 50)
```

Logged to `access_logs.risk_score`. Not used to block — only for monitoring.

### 7.4 SecureToken Architecture

Every successful recovery (all methods) produces one `SecureToken`:

```
status=active
expires_at = now() + 24h
max_downloads = 3
download_count = 0 (incremented on each link-open)
```

Token is a raw HMAC-SHA256 string. Only its hash (`sha256(raw_token)`) is stored. The raw token is placed in the download URL `?t=<raw_token>` — validated by hashing and comparing.

**On each link-open:**
- `download_count` is incremented
- If `download_count >= max_downloads` → `status = expired`

**Before any new token is issued:**
- All `status=active` tokens for the same `order_id` are set to `status=revoked`
- Ensures only one valid token per order at any time

### 7.5 Neutral Response Policy

| Failure scenario | Response returned | Neutral? |
|---|---|---|
| Method 1: order not found | `{status: success, redirect: link-sent}` | Yes |
| Method 1: email mismatch | `{status: success, redirect: link-sent}` | Yes |
| Method 1: status not Completed | `{status: success, redirect: link-sent}` | Yes |
| Method 2: email+phone+tender not found | `{status: ok, otp_sent: false, session_token: fake-uuid}` | Yes |
| Method 2: purchase found, status Pending | `{status: error, type: 'payment_pending'}` | **No** |
| Method 3: email+reference not found | `{status: error, type: 'no_match'}` | **No** |
| Method 4: email not found | `{status: success, redirect: link-sent}` | Yes |

**Explicit errors shown to user (never neutral):**
- `rate_limited` — legitimate throttle feedback
- `regen_limit` — legitimate cap feedback
- `sms_failed` — service error, user needs another method
- `payment_pending` — purchase exists but payment not yet approved
- `no_match` — Method 3 only; reference+email combination not found
- `email_failed` — all methods; token issued but delivery failed
- `otp_invalid` / `otp_exhausted` / `otp_expired` — OTP UX feedback

### 7.6 reCAPTCHA

All four methods include reCAPTCHA v2 when enabled in admin (`basic_settings.is_recaptcha = 1`). Each method has a distinct JS callback and state variable to avoid cross-panel interference:

| Method | Callback | State var |
|---|---|---|
| Method 1 | `window.fmfLinkCaptchaVerified` | `window._fmfLinkCaptcha` |
| Method 2 | `window.fmfOtpCaptchaVerified` | `window._fmfOtpCaptcha` |
| Method 3 | `window.fmfPayrefCaptchaVerified` | `window._fmfPayrefCaptcha` |
| Method 4 | `window.fmfRegenCaptchaVerified` | `window._fmfRegenCaptcha` |

Captcha state is reset on every error response.

### 7.7 CSRF Protection

All POST routes require a valid `_token` (Laravel CSRF). AJAX requests include `X-Requested-With: XMLHttpRequest` and `Accept: application/json` headers.

### 7.8 Email Hashing

```php
hash('sha256', strtolower(trim($email)))
```

Raw email addresses are never stored in `access_logs`, `secure_tokens`, or `otp_verifications`. Only SHA-256 hashes are persisted.

### 7.9 `sendDownloadEmail()` return contract

`FindMyFilesController::sendDownloadEmail()` returns `bool`:
- `true` — email sent successfully
- `false` — PHPMailer threw an exception

All callers check this return value. On `false`, a `{type: 'email_failed'}` JSON error is returned instead of redirecting to the link-sent page.

---

## 8. Database Schema

### `tender_purchases` (modified)
```
order_number       varchar    — unique order identifier
email              varchar    — buyer's email (plaintext — required for lookup)
phone_number       varchar    — buyer's phone (plaintext — required for OTP lookup)
payment_reference  varchar    — gateway txn ID or bank ref (added for Method 3)
tender_id          bigint     — FK to tenders.id (used for Method 2 scoping)
payment_status     varchar    — must be 'Completed' to qualify for any method
```

### `secure_tokens`
```
id               uuid PK
order_id         varchar       — links to tender_purchases.order_number
email_hash       varchar(64)   — sha256(lowercase email)
token_hash       varchar(64)   — sha256(raw_token)
issued_at        timestamp
expires_at       timestamp     — issued_at + 24h
max_downloads    int           — 3
download_count   int           — incremented on each link-open
status           enum          — active / expired / revoked
device_hash      varchar(64)
ip               varchar(45)
```

### `otp_verifications` (Method 2 only)
```
id               bigint PK
session_token    uuid unique   — returned to browser, never in URL
email_hash       varchar(64)   — sha256(lowercase email)
phone_hash       varchar(64)   — sha256(digits-only phone)
otp_hash         varchar(64)   — sha256(raw_otp_code)
order_id         varchar       — matched purchase
expires_at       timestamp     — created_at + 10 min  [cast: 'datetime']
attempts         tinyint       — wrong-entry counter (max 3)
last_resend_at   timestamp     — enforces 60s resend cooldown  [cast: 'datetime']
status           enum          — pending / verified / expired / exhausted
ip               varchar(45)
device_hash      varchar(64)
masked_phone     varchar(20)   — e.g. +1 ***********37
```

> **Note:** `OtpVerification` model uses `$casts = ['expires_at' => 'datetime', 'last_resend_at' => 'datetime']`. The deprecated `$dates` property was not used as it does not auto-cast in Laravel 10.

### `rate_limit_attempts`
```
key              varchar       — "ip:x.x.x.x" / "email:hash" / "device:hash"
attempts         int           — cumulative failed attempts
blocked_until    timestamp     — null if not blocked
last_attempt_at  timestamp
```

### `access_logs`
```
event_type       varchar       — LINK_REQUESTED / LINK_SENT / RATE_LIMIT_TRIGGERED / etc.
order_id         varchar       — blank on no-match events
ip               varchar(45)
device_hash      varchar(64)
user_agent       varchar(255)
email_hash       varchar(64)
risk_score       int           — 0–100
result           varchar       — event-specific code (e.g. OTP_SENT, PAYREF_OK)
```

---

## 9. Full Route Map

### Frontend — Public (unauthenticated, CSRF + rate limited)

| Method | URI | Route name | Action |
|---|---|---|---|
| GET | `/find-my-files` | `find_my_files` | `index()` |
| POST | `/find-my-files/request-link` | `find_my_files.request_link` | `requestLink()` |
| GET | `/find-my-files/link-sent` | `find_my_files.link_sent` | `linkSent()` |
| GET | `/find-my-files/security-verification` | `find_my_files.security_info` | `securityInfo()` |
| GET | `/find-my-files/download` | `find_my_files.download` | `download()` |
| GET | `/find-my-files/download/stream` | `find_my_files.stream` | `downloadStream()` |
| POST | `/find-my-files/otp/request` | `find_my_files.otp_request` | `requestOtp()` |
| POST | `/find-my-files/otp/verify` | `find_my_files.otp_verify` | `verifyOtp()` |
| POST | `/find-my-files/otp/resend` | `find_my_files.otp_resend` | `resendOtp()` |
| POST | `/find-my-files/payment-ref` | `find_my_files.payment_ref` | `requestByPaymentRef()` |
| POST | `/find-my-files/regenerate` | `find_my_files.regenerate` | `requestRegenerate()` |

### Tender details (related)

| Method | URI | Route name | Action |
|---|---|---|---|
| GET | `/<permalink>/{slug}` | `tender_details` | `Front\TenderController@tenderDetails` |

> **Changed in v1.1:** Tender details route uses `{slug}` (not `{id}`). Controller looks up by `Tender::where('slug', $slug)`. Links from tenders listing and featured tenders updated accordingly.

### Admin — Protected (`checkpermission:Tender Management`)

| Method | URI | Route name | Action |
|---|---|---|---|
| POST | `/admin/tender/purchase/payment-status` | `admin.tender.purchasePaymentStatus` | `purchasePaymentStatus()` |
| POST | `/admin/tender/purchase/update-reference` | `admin.tender.purchaseUpdateReference` | `purchaseUpdateReference()` |
| GET | `/admin/tender/purchase/{id}/invoice` | `admin.tender.invoiceDownload` | `invoiceDownload()` |
| POST | `/admin/tender/purchase/{id}/generate-invoice` | `admin.tender.purchaseGenerateInvoice` | `purchaseGenerateInvoice()` |

---

## 10. Full File Map

### Controllers
| File | Role |
|---|---|
| `app/Http/Controllers/Front/FindMyFilesController.php` | All 4 methods + download + stream |
| `app/Http/Controllers/Admin/TenderController.php` | `purchaseUpdateReference()` (Method 3 admin save) |
| `app/Http/Controllers/Front/TenderController.php` | Captures `payment_reference` at checkout; tender details by slug |

### Models
| File | Role |
|---|---|
| `app/SecureToken.php` | Download token — issued by all methods |
| `app/OtpVerification.php` | OTP session model (Method 2 only) |
| `app/TenderPurchase.php` | Purchase record — primary lookup target |
| `app/AccessLog.php` | Audit log writer |
| `app/RateLimitAttempt.php` | Rate limit tracker |

### Services (Method 2)
| File | Role |
|---|---|
| `app/Services/SmsGateway/SmsGatewayInterface.php` | SMS contract — `send(string $to, string $message): bool` |
| `app/Services/SmsGateway/TwilioSmsGateway.php` | Production SMS delivery |
| `app/Services/SmsGateway/LogSmsGateway.php` | Dev/fallback — logs warning, returns `false` |
| `app/Providers/AppServiceProvider.php` | Binds SMS gateway from DB config |

### Views — Frontend
| File | Role |
|---|---|
| `resources/views/front/find-my-files/index.blade.php` | Main page — all 4 method panels + JS |
| `resources/views/front/find-my-files/link_sent.blade.php` | Success confirmation page |
| `resources/views/front/find-my-files/download.blade.php` | Token validation + download trigger |
| `resources/views/front/find-my-files/download_error.blade.php` | Expired/invalid token error + Method 4 CTA |
| `resources/views/front/find-my-files/security_info.blade.php` | Security best practices page |
| `resources/views/front/tender/tender_details.blade.php` | Checkout form — payment_reference input; FMF link with `?tender=slug` |
| `resources/views/mail/secure_download_link.blade.php` | Download link email template |

### Views — Admin
| File | Role |
|---|---|
| `resources/views/admin/tender/tender/purchase.blade.php` | Purchases list |
| `resources/views/admin/tender/tender/purchase-details.blade.php` | Details modal — payment_reference editable field |
| `resources/views/admin/basic/scripts.blade.php` | Twilio SMS config card |

### Migrations
| File | Role |
|---|---|
| `2026_04_12_000001_create_secure_tokens_table.php` | SecureToken table |
| `2026_04_12_000002_create_access_logs_table.php` | Audit log table |
| `2026_04_12_000003_create_rate_limit_attempts_table.php` | Rate limit table |
| `2026_04_18_000001_create_otp_verifications_table.php` | OTP session table (Method 2) |
| `2026_04_18_000002_add_twilio_fields_to_basic_extras_table.php` | Twilio admin config columns |
| `2026_04_19_000001_add_payment_reference_to_tender_purchases_table.php` | `payment_reference` column (Method 3) |

---

## 11. Audit & Logging Reference

### Structured DB audit (`access_logs`)

All events are written to `access_logs` via `AccessLog::record($eventType, $meta)`.

#### Event types
| Constant | Value | When fired |
|---|---|---|
| `LINK_REQUESTED` | `link_requested` | Any recovery attempt (all methods) |
| `LINK_SENT` | `link_sent` | Successful token issue + email sent |
| `LINK_CLICKED` | `link_clicked` | User opens a download link |
| `DOWNLOAD_SUCCESS` | `download_success` | ZIP streamed successfully |
| `DOWNLOAD_FAILED` | `download_failed` | Invalid token on stream request |
| `RATE_LIMIT_TRIGGERED` | `rate_limit_triggered` | Request blocked by rate limiter |

#### Result codes by method

| Code | Method | Meaning |
|---|---|---|
| `INVALID_INPUT` | 1 | Validation failed |
| `ORDER_NOT_FOUND` | 1 | Order number not in DB |
| `EMAIL_MISMATCH` | 1 | Email doesn't match order |
| `INVALID_STATUS` | 1 | Order not Completed |
| `ORDER_EMAIL_FAILED` | 1 | Token issued, email delivery failed |
| `OTP_SENT` | 2 | OTP delivered via SMS |
| `OTP_NO_MATCH` | 2 | Email + phone not found for that tender |
| `OTP_SMS_FAILED` | 2 | Twilio delivery failure or gateway not configured |
| `OTP_PAYMENT_PENDING` | 2 | Purchase found but payment not Completed |
| `OTP_EXHAUSTED` | 2 | 3 wrong OTP entries |
| `OTP_VERIFIED_OK` | 2 | OTP correct, link sent |
| `OTP_EMAIL_FAILED` | 2 | Token issued, email delivery failed |
| `PAYREF_NO_MATCH` | 3 | Email + reference not found |
| `PAYREF_OK` | 3 | Match found, link sent |
| `PAYREF_EMAIL_FAILED` | 3 | Token issued, email delivery failed |
| `REGEN_NO_MATCH` | 4 | Email not found |
| `REGEN_LIMIT_EXCEEDED` | 4 | Daily cap hit |
| `REGEN_OK` | 4 | Regeneration successful |
| `REGEN_EMAIL_FAILED` | 4 | Token issued, email delivery failed |
| `RATE_LIMITED` | All | Rate limit block triggered |

### PHP application log (`storage/logs/laravel.log`)

Controllers emit structured `Log::info/warning` entries with method-specific prefixes for easy `grep`:

| Prefix | Used in |
|---|---|
| `[OrderNumber]` | `requestLink()` — order lookup, email result |
| `[OTP/requestOtp]` | `requestOtp()` — purchase lookup, SMS result |
| `[PayRef]` | `requestByPaymentRef()` — load count, match result, email result |
| `[Regenerate]` | `requestRegenerate()` — purchase found, regen count, email result |
| `[OTP/SMS]` | `LogSmsGateway::send()` — warning when no gateway configured |

---

## 12. System Constants

All defined in `App\Http\Controllers\Front\FindMyFilesController`:

| Constant | Value | Purpose |
|---|---|---|
| `IP_LIMIT` | 10 | Max requests per IP per 15 min window |
| `EMAIL_LIMIT` | 5 | Max requests per email per hour |
| `BLOCK_MINUTES` | [30, 120, 1440] | Progressive block durations (min) |
| `TOKEN_TTL_HOURS` | 24 | SecureToken validity window |
| `MAX_DOWNLOADS` | 3 | Max link-opens per token |
| `MAX_REGEN_PER_DAY` | 3 | Max regenerations per order per 24h (Method 4) |

Defined in `App\OtpVerification`:

| Constant | Value | Purpose |
|---|---|---|
| `OTP_TTL_MIN` | 10 | OTP validity window (minutes) |
| `MAX_ATTEMPTS` | 3 | Wrong entries before exhaustion |
| `RESEND_DELAY` | 60 | Seconds between resend requests |

---

## 13. Admin Controls

### Twilio SMS (Method 2)
Located at: **Admin → Basic Settings → Plugins → Twilio SMS (OTP Verification)**

| Field | DB Column | Effect |
|---|---|---|
| Status toggle | `basic_settings_extra.twilio_status` | `0` = log-only mode; `1` = real SMS |
| Account SID | `basic_settings_extra.twilio_account_sid` | Twilio account identifier |
| Auth Token | `basic_settings_extra.twilio_auth_token` | Twilio authentication token |
| From Number | `basic_settings_extra.twilio_from_number` | E.164 sender number |

When status is `0` or credentials are missing, `LogSmsGateway` is bound. It returns `false` and logs a warning to `laravel.log`. The controller treats this as SMS failure — the buyer sees a `sms_failed` error and is **not** shown the OTP entry step. OTP codes are **not** written to the log in this mode.

### Payment Reference (Method 3)
Located at: **Admin → Tenders → Enrolls → [purchase row] → Details**

| Action | Effect |
|---|---|
| Set reference | Enables Method 3 recovery for that order |
| Clear reference (empty save) | Sets `payment_reference = NULL` — disables Method 3 for that order |

### Payment Status Toggle
Located at: **Admin → Tenders → Enrolls → [purchase row] → Status dropdown**

Only `Completed` purchases are eligible for any recovery method. Setting status to `Pending`:
- Methods 1, 3, 4 — neutral redirect (order not found response)
- Method 2 — explicit `payment_pending` error shown to buyer

---

## 14. Data Retention Policy

### `secure_tokens`
- Active tokens expire after 24h (automatic via `isValid()` check)
- Suggested cleanup: delete rows where `status IN (expired, revoked)` and `updated_at < now() - 90 days`

### `otp_verifications`
- OTPs expire after 10 minutes
- Suggested cleanup: delete rows where `status IN (verified, expired, exhausted)` and `updated_at < now() - 30 days`
- Delete rows where `status = pending` and `created_at < now() - 1 hour` (dead sessions)

### `rate_limit_attempts`
- Blocks lift automatically via `blocked_until` comparison
- Suggested cleanup: delete rows where `last_attempt_at < now() - 7 days`

### `access_logs`
- Immutable audit records — do not delete
- Archive rows older than 1 year to cold storage if table grows large

### Suggested scheduled command (artisan)
```php
// Add to App\Console\Kernel::schedule()
$schedule->call(function () {
    SecureToken::whereIn('status', ['expired','revoked'])
        ->where('updated_at', '<', now()->subDays(90))->delete();

    OtpVerification::whereIn('status', ['verified','expired','exhausted'])
        ->where('updated_at', '<', now()->subDays(30))->delete();

    OtpVerification::where('status', 'pending')
        ->where('created_at', '<', now()->subHour())->delete();

    RateLimitAttempt::where('last_attempt_at', '<', now()->subDays(7))->delete();
})->daily();
```

---

*This document reflects the full implementation as of 2026-04-29 (v1.1).*  
*Individual method documents: `fmf-method2-otp.md`, `fmf-method3-payment-ref.md`, `fmf-method4-regenerate.md`*
