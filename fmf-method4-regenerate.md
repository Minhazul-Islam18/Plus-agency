# Find My Files — Method 4: Expired Link / Regenerate
## Security & Accountability Document

**Version:** 1.0  
**Date:** 2026-04-22  
**Module:** Secure File Recovery (`/find-my-files`)  
**Method:** Email-only link regeneration for expired or exhausted download tokens

---

## 1. Overview

Method 4 lets a buyer request a fresh download link when their original link has expired (24-hour TTL) or been exhausted (3 opens used up). It is the simplest of the four recovery methods — the buyer provides only their **email address**. No order number, phone, or payment reference is required.

This method is intentionally the lowest friction option. Its security relies on the combination of email-based delivery (only the inbox owner receives the new link), a daily regeneration cap, and the shared rate-limiting system.

**User flow (happy path):**

```
[User] → arrives at download_error page → clicks "Regenerate My Link"
       → lands on /find-my-files with Method 4 pre-activated
       → enters Email only
       → clicks "Send New Download Link"
       → spinner → neutral "if a valid purchase exists, a new link was sent"
       → redirect to link-sent page (same as all other methods)
       → user opens new link from email → downloads their files
```

**Entry point — `download_error.blade.php`:**
When a user lands on the download error page (expired/exhausted/revoked token), the "Regenerate My Link" button deep-links directly to `/find-my-files#method=expired_link`. JavaScript on the FMF page reads the hash, activates Method 4, scrolls the card into view, and cleans the hash from the URL — no manual method selection required.

---

## 2. Key Parameters

| Parameter | Value | Location |
|---|---|---|
| Identity factor | **Email only** | `requestRegenerate()` |
| Max regenerations per order | **3 per 24 hours** | `FindMyFilesController::MAX_REGEN_PER_DAY` |
| Regeneration window | **24 hours** (rolling) | `SecureToken` created_at comparison |
| Order selection | **Most recently created** completed purchase | `sortByDesc('created_at')->first()` |
| Download link TTL | **24 hours** | `FindMyFilesController::TOKEN_TTL_HOURS` |
| Max link opens | **3** | `FindMyFilesController::MAX_DOWNLOADS` |

---

## 3. System Architecture

### 3.1 No New Database Table or Column

Method 4 uses only existing infrastructure:

- **`tender_purchases`** — email match + `payment_status = Completed` filter
- **`secure_tokens`** — regeneration count query + new token issuance
- **`rate_limit_attempts`** — shared rate limiting
- **`access_logs`** — shared audit trail

The regeneration cap is derived by counting `secure_tokens` rows for the same `(email_hash, order_id)` within the last 24 hours:

```php
SecureToken::where('email_hash', $emailHash)
    ->where('order_id', $purchase->order_number)
    ->where('created_at', '>=', now()->subHours(24))
    ->count() >= FindMyFilesController::MAX_REGEN_PER_DAY
```

No counter column, no separate table — the token history already contains everything needed.

### 3.2 Order Selection Logic

If an email address is associated with multiple completed purchases, the system selects the **most recently created** one:

```php
TenderPurchase::where('payment_status', 'Completed')
    ->get()
    ->filter(fn($p) => strtolower(trim($p->email)) === strtolower(trim($submittedEmail)))
    ->sortByDesc('created_at')
    ->first();
```

Buyers who need a link for a specific older order should use **Method 1 (Email + Order Number)** instead.

### 3.3 Deep-Link Auto-Activation

The `download_error.blade.php` "Regenerate My Link" button links to `/find-my-files#method=expired_link`. On page load, a JavaScript handler:

1. Reads `window.location.hash`
2. Extracts `method=expired_link`
3. Deactivates all panels and method buttons
4. Activates the `expired_link` button and `#panel-expired_link`
5. Smooth-scrolls the card into view
6. Calls `history.replaceState()` to clean the hash from the URL bar

This provides a seamless "one click from error to form" experience without any server-side redirect logic.

---

## 4. Security Measures

### 4.1 No Enumeration (Neutral Responses)

When the submitted email does not match any completed purchase, the system returns the **exact same success redirect** as a real match. The user is always sent to the "check your email" confirmation page.

This prevents attackers from using the endpoint as an oracle to determine which email addresses have completed purchases.

> **Why this matters:** Without a neutral response, an attacker could silently probe for valid customer emails by observing which inputs return errors.

For non-matching requests, no email is sent and no token is issued — but the HTTP response and redirect are indistinguishable from a real regeneration.

### 4.2 Regeneration Cap — Not Neutral

Unlike the no-match case, the regeneration limit error **is shown explicitly** to the user (`regen_limit`). This is intentional:

- The cap is reached only after 3 real regenerations for a real order
- Showing the error is a legitimate UX signal ("try again tomorrow")
- It does not expose whether an email exists — the attacker would have needed to trigger 3 successful regenerations already to reach this state
- Without surfacing this error, legitimate users would be silently redirected and confused why they received no email

### 4.3 Email-Only Factor — Compensating Controls

Email is the weakest single factor. Three controls compensate:

**Control 1 — Download link delivered to inbox only.**
Even if an attacker triggers a regeneration for a victim's email, they cannot intercept the link. The victim receives the email and can report unexpected access.

**Control 2 — Regeneration cap (3 per 24h per order).**
Prevents an attacker from flooding a victim with regeneration emails or exhausting their token quota.

**Control 3 — Rate limiting (shared).**
Progressive IP/email/device blocks prevent automated probing.

### 4.4 Rate Limiting (shared with Methods 1–3)

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

Rate limits are checked **before** any database lookup.

### 4.5 SecureToken Revocation

Before issuing a new token, any currently active `SecureToken` for the same order is revoked:

```php
SecureToken::where('order_id', $purchase->order_number)
    ->where('status', 'active')
    ->update(['status' => 'revoked']);
```

This ensures the buyer cannot accumulate multiple simultaneous download tokens by submitting the form repeatedly.

### 4.6 Payment Status Gate

The purchase lookup is scoped to `payment_status = Completed` orders only. Pending or cancelled orders cannot yield a token regardless of email match.

---

## 5. Audit Trail

All significant events are recorded in the `access_logs` table via `AccessLog::record()`.

| Event | Result code | Trigger |
|---|---|---|
| `LINK_REQUESTED / REGEN_NO_MATCH` | Email did not match any completed purchase |
| `LINK_REQUESTED / REGEN_LIMIT_EXCEEDED` | 3 regenerations already issued in the last 24h |
| `RATE_LIMIT_TRIGGERED / RATE_LIMITED` | Request blocked by rate limiter |
| `LINK_SENT / REGEN_OK` | New token issued, download link emailed |

Each log entry includes: `ip`, `user_agent`, `email_hash`, `device_hash`, `order_id` (where available), `result`, `risk_score`, `created_at`.

**Risk Score** (0–100) is computed from:
- Recent `LINK_REQUESTED` events from the same IP in the last 10 minutes (+10 per event, max 50)
- Recent `LINK_REQUESTED` events from the same email hash in the last hour (+10 per event, max 50)

---

## 6. Threat Model

| Threat | Mitigation |
|---|---|
| **Email enumeration** | Neutral response — no-match is indistinguishable from success |
| **Unlimited regeneration abuse** | Max 3 regenerations per order per 24h |
| **Email flooding (victim)** | Regeneration cap limits emails to 3 per day per order |
| **Credential stuffing** | Progressive IP/email/device rate limiting |
| **Token accumulation** | Active tokens revoked before new one is issued |
| **Intercepted link** | Link delivered only to the registered email inbox |
| **Pending order abuse** | Lookup scoped to `payment_status = Completed` only |
| **Mass scanning** | IP rate limit blocks after 5 failures in 15 min |
| **Hash-activation abuse** | Hash is read client-side only; no server-side state change on arrival |

---

## 7. Regeneration Lifecycle

```
[Original purchase]  → SecureToken issued (any method)
                         status=active, expires_at=+24h, max_downloads=3

[Link opened 3×]     → download_count >= max_downloads → status=expired (auto)
[24h elapsed]        → expires_at in past → token invalid

[User clicks error]  → download_error.blade.php
                         → "Regenerate My Link" → /find-my-files#method=expired_link

[Method 4 form]      → email submitted → requestRegenerate()
                         → check rate limit
                         → find most recent Completed purchase
                         → check regen count < 3 in last 24h
                         → revoke old active tokens
                         → issue new SecureToken (fresh 24h TTL, 3 opens)
                         → email download link

[Regen cap hit]      → REGEN_LIMIT_EXCEEDED logged
                         → user shown "try again after 24h" message
                         → contact support CTA available
```

---

## 8. Comparison with Other Methods

| | Method 1 | Method 2 | Method 3 | Method 4 |
|---|---|---|---|---|
| **Factors** | Email + Order No. | Email + Phone (OTP) | Email + Payment Ref | Email only |
| **Steps** | 1 | 2 | 1 | 1 |
| **Requires knowing** | Order number | Registered phone | Payment reference | Just email |
| **Best for** | Most users | Users who forgot order no. | Users who remember payment ref | Users whose link expired |
| **Regen cap** | — | — | — | 3/day/order |
| **New table** | No | Yes (`otp_verifications`) | No | No |

---

## 9. File Map

| File | Role |
|---|---|
| `app/Http/Controllers/Front/FindMyFilesController.php` | `requestRegenerate()` + `MAX_REGEN_PER_DAY` constant |
| `resources/views/front/find-my-files/index.blade.php` | Method 4 panel + hash-activation JS |
| `resources/views/front/find-my-files/download_error.blade.php` | "Regenerate My Link" CTA → deep-link |
| `routes/web.php` | `POST /find-my-files/regenerate` |

---

## 10. Routes

| Method | URI | Name | Controller Action |
|---|---|---|---|
| `POST` | `/find-my-files/regenerate` | `find_my_files.regenerate` | `FindMyFilesController@requestRegenerate` |

The route is unauthenticated (public), protected by rate limiting and CSRF tokens.

---

*This document reflects the implementation as of 2026-04-22.*
