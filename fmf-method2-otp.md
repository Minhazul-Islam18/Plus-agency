# Find My Files — Method 2: Email + Phone (OTP)
## Security & Accountability Document

**Version:** 1.0  
**Date:** 2026-04-19  
**Module:** Secure File Recovery (`/find-my-files`)  
**Method:** Email + Phone Number with One-Time Password (OTP)

---

## 1. Overview

Method 2 lets a buyer recover their purchased tender documents by proving ownership of both the **email address** and the **phone number** registered at the time of purchase. Unlike Method 1 (Email + Order Number), this method uses a time-limited, single-use numeric code delivered via SMS — providing a second authentication factor without requiring the buyer to remember an order reference.

**User flow (happy path):**

```
[User] → enters Email + Phone
      → clicks "Send Verification Code"
      → receives 6-digit OTP via SMS
      → enters OTP in digit boxes
      → code verified → secure download link emailed
      → user downloads their files
```

---

## 2. Key Parameters

| Parameter | Value | Location |
|---|---|---|
| OTP length | 6 digits | `OtpVerification::generateOtp()` |
| OTP expiry | **10 minutes** | `OtpVerification::OTP_TTL_MIN` |
| Max wrong attempts | **3** | `OtpVerification::MAX_ATTEMPTS` |
| Resend cooldown | **60 seconds** | `OtpVerification::RESEND_DELAY` |
| Download link TTL | 24 hours | `FindMyFilesController::TOKEN_TTL_HOURS` |
| Max link opens | 3 | `FindMyFilesController::MAX_DOWNLOADS` |

---

## 3. System Architecture

### 3.1 New Database Table — `otp_verifications`

| Column | Type | Purpose |
|---|---|---|
| `session_token` | UUID (unique) | Client-side reference — passed in AJAX, never in URL |
| `email_hash` | SHA-256 | Email stored hashed only — never plaintext |
| `phone_hash` | SHA-256 | Phone digits normalised then hashed — never plaintext |
| `otp_hash` | SHA-256 | OTP stored hashed — never plaintext |
| `order_id` | varchar | Matched purchase order number |
| `expires_at` | timestamp | OTP expiry (10 min from issuance) |
| `attempts` | tinyint | Wrong-entry counter (max 3 before exhaustion) |
| `last_resend_at` | timestamp | Enforces 60 s resend cooldown |
| `status` | enum | `pending / verified / expired / exhausted` |
| `ip` | varchar | IP at time of OTP request |
| `device_hash` | SHA-256 | `sha256(userAgent + ip)` fingerprint |
| `masked_phone` | varchar | Display-safe version e.g. `+1 ***********37` |

### 3.2 SMS Gateway — Pluggable Architecture

```
SmsGatewayInterface (contract)
    ├── TwilioSmsGateway   ← production (credentials in Admin → Plugins)
    └── LogSmsGateway      ← dev/fallback (OTP written to laravel.log)
```

Resolution logic in `AppServiceProvider::register()`:
- If `basic_settings_extra.twilio_status == 1` AND all three Twilio fields are filled → `TwilioSmsGateway`
- Otherwise → `LogSmsGateway`

Admin configures credentials at: **Admin → Basic Settings → Plugins → Twilio SMS (OTP Verification)**

---

## 4. Security Measures

### 4.1 No Enumeration (Neutral Responses)

When a submitted email + phone combination does not match any completed purchase, the system returns the **same success-shaped response** as a real OTP send — including a fake `session_token` (UUID). The user sees "check your phone" regardless of whether a match was found.

This prevents attackers from using the endpoint as an oracle to discover which phone numbers or emails are registered in the system.

> **Why this matters:** Without neutral responses, an attacker can silently scan for valid email/phone pairs by observing error vs. success responses.

The fake session token will silently fail at the verify step (`session_invalid`) — no useful information is returned.

### 4.2 OTP Hashing

The raw 6-digit OTP is **never stored**. Only `sha256(otp_code)` is persisted in `otp_hash`. Verification is done by hashing the submitted code and comparing hashes:

```php
hash('sha256', $rawOtp) === $this->otp_hash
```

### 4.3 Phone & Email Hashing

Phone numbers are normalised (all non-digits stripped) before hashing. Email is lowercased and trimmed. Neither is stored in plaintext anywhere in `otp_verifications`.

```php
// Phone normalisation before hashing
hash('sha256', preg_replace('/\D/', '', $phone))

// Email normalisation before hashing
hash('sha256', strtolower(trim($email)))
```

### 4.4 Brute-Force Protection — OTP Exhaustion

After **3 consecutive wrong OTP entries**, the session status is set to `exhausted`. The session cannot be used again — the user must request a new code. Each new code request:
- Invalidates the previous session (`status = expired`)
- Creates a fresh session with a new UUID and new OTP hash

### 4.5 Rate Limiting (shared with Method 1)

The existing `rate_limit_attempts` table is used, keyed on:
- `ip:<ip_address>`
- `email:<email_hash>`
- `device:<device_hash>`

Progressive blocking schedule:

| Failed attempts | Block duration |
|---|---|
| ≥ 5 | 30 minutes |
| ≥ 10 | 2 hours |
| ≥ 20 | 24 hours |

Rate limits are checked **before** any purchase lookup, so blocked requestors cannot probe the database at all.

### 4.6 Session Token Scope

The `session_token` (UUID v4) is:
- Generated server-side using `Str::uuid()`
- Returned to the browser in the AJAX response JSON body
- **Never placed in a URL** (no GET parameters)
- Stored temporarily in JavaScript memory only
- Invalidated immediately when OTP is verified, expired, or exhausted

### 4.7 Resend Cooldown

Resending a code is blocked for **60 seconds** after the last send. When a resend is accepted:
- The OTP hash is regenerated (new random code)
- `expires_at` is reset to `now() + 10 min`
- `attempts` is reset to 0
- `last_resend_at` is updated

This prevents SMS bombing (rapidly resending OTPs to flood a phone number).

### 4.8 Previous Session Invalidation

Before issuing a new OTP for an order, all existing `pending` sessions for that `order_id` are set to `expired`. This ensures only one active OTP session exists per order at any time.

### 4.9 SecureToken Revocation

When OTP verification succeeds, any previously active `SecureToken` for the same order is revoked before the new one is issued. This ensures the buyer cannot accumulate multiple simultaneous download tokens.

---

## 5. Audit Trail

All significant events are recorded in the `access_logs` table via `AccessLog::record()`.

| Event | Trigger |
|---|---|
| `LINK_REQUESTED / OTP_SENT` | OTP successfully sent |
| `LINK_REQUESTED / OTP_NO_MATCH` | Email + phone did not match any purchase |
| `LINK_REQUESTED / OTP_SMS_FAILED` | SMS gateway returned failure |
| `LINK_REQUESTED / OTP_EXHAUSTED` | 3rd wrong OTP attempt hit |
| `RATE_LIMIT_TRIGGERED / RATE_LIMITED` | Request blocked by rate limiter |
| `LINK_SENT / OTP_VERIFIED_OK` | OTP verified, download link emailed |

Each log entry includes: `ip`, `user_agent`, `email_hash`, `device_hash`, `order_id`, `result`, `risk_score`, `created_at`.

**Risk Score** (0–100) is computed from:
- Recent `LINK_REQUESTED` events from the same IP in the last 10 minutes (+10 per event, max 50)
- Recent `LINK_REQUESTED` events from the same email hash in the last hour (+10 per event, max 50)

---

## 6. Threat Model

| Threat | Mitigation |
|---|---|
| **Phone/email enumeration** | Neutral "code sent" response regardless of lookup result |
| **OTP brute-force** | 3-attempt exhaustion; new session required each time |
| **OTP interception** | 10-minute TTL; single-use (verified → session closed) |
| **SMS bombing (victim)** | 60-second resend cooldown per session |
| **Credential stuffing** | Progressive IP/email/device rate limiting |
| **Replay attack** | Session token is UUID; OTP hash is single-use |
| **Session hijack** | Session token bound to IP + device hash at request time |
| **Database leak** | No raw OTP, phone, or email stored — only hashes |
| **Mass scanning** | IP rate limit blocks after 5 failures in 15 min |
| **Fake session abuse** | Fake tokens (no-match path) silently fail at verify |

---

## 7. Data Retention

The `otp_verifications` table accumulates records. Recommended cleanup policy:

- Records with `status IN (verified, expired, exhausted)` older than **30 days** can be safely deleted
- Records with `status = pending` older than **1 hour** are effectively dead (OTP long expired) and can be deleted

Suggested artisan command (to be scheduled):

```php
OtpVerification::where('updated_at', '<', now()->subDays(30))
    ->whereIn('status', ['verified', 'expired', 'exhausted'])
    ->delete();
```

---

## 8. Admin Controls

| Setting | Location | Effect |
|---|---|---|
| `twilio_status` | Admin → Plugins | Enables/disables real SMS; falls back to log-only |
| `twilio_account_sid` | Admin → Plugins | Twilio Account SID |
| `twilio_auth_token` | Admin → Plugins | Twilio Auth Token |
| `twilio_from_number` | Admin → Plugins | Sender number (E.164 format) |

When `twilio_status = 0`, the system uses `LogSmsGateway` — OTP codes are written to `storage/logs/laravel.log` instead of being sent via SMS. Useful for testing.

---

## 9. File Map

| File | Role |
|---|---|
| `app/OtpVerification.php` | Eloquent model + OTP helpers |
| `app/Http/Controllers/Front/FindMyFilesController.php` | `requestOtp()`, `verifyOtp()`, `resendOtp()` |
| `app/Services/SmsGateway/SmsGatewayInterface.php` | SMS contract |
| `app/Services/SmsGateway/TwilioSmsGateway.php` | Production SMS (cURL, no SDK) |
| `app/Services/SmsGateway/LogSmsGateway.php` | Dev/fallback — logs OTP instead of sending |
| `app/Providers/AppServiceProvider.php` | Binds `SmsGatewayInterface` from DB config |
| `database/migrations/2026_04_18_000001_create_otp_verifications_table.php` | OTP table schema |
| `database/migrations/2026_04_18_000002_add_twilio_fields_to_basic_extras_table.php` | Admin config columns |
| `resources/views/front/find-my-files/index.blade.php` | Frontend — OTP panel, digit boxes, timer, AJAX |
| `resources/views/admin/basic/scripts.blade.php` | Admin Twilio card |
| `routes/web.php` | `/otp/request`, `/otp/verify`, `/otp/resend` |

---

## 10. Routes

| Method | URI | Name | Controller Action |
|---|---|---|---|
| `POST` | `/find-my-files/otp/request` | `find_my_files.otp_request` | `requestOtp()` |
| `POST` | `/find-my-files/otp/verify` | `find_my_files.otp_verify` | `verifyOtp()` |
| `POST` | `/find-my-files/otp/resend` | `find_my_files.otp_resend` | `resendOtp()` |

All three routes are unauthenticated (public), protected by rate limiting and CSRF tokens.

---

*This document reflects the implementation as of 2026-04-19.*
