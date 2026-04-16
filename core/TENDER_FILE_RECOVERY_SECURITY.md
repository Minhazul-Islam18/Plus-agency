# Tender File Recovery System — Security Management

## Overview

The Tender File Recovery (Find My Files) system allows verified customers to retrieve their purchased tender documents via a secure, time-limited, single-use download link. Every request, token issuance, and file download is logged, rate-limited, and scored for risk.

---

## 1. Token Architecture

### Generation
Tokens are generated using HMAC-SHA256 signed with the application secret key:

```
payload = order_number | email_hash | unix_timestamp | 16-byte random string
raw_token = HMAC-SHA256(payload, APP_KEY)
```

- The raw token is sent to the user by email and embedded in the download URL as `?t=<raw_token>`
- Only the SHA-256 hash of the raw token is stored in the database (`token_hash`)
- The raw token is never stored — a compromised database cannot reconstruct usable tokens

### Validation (`SecureToken::isValid()`)
A token is valid only if ALL three conditions are met:

| Condition | Detail |
|-----------|--------|
| `status = active` | Not manually revoked or auto-expired |
| `expires_at` is in the future | 24-hour TTL from issuance |
| `download_count < max_downloads` | Default max: 3 link opens |

### Lifecycle
```
[Issued] → active
    ↓ (24h elapsed or link opened 3 times)
[Auto-expired] → expired
    ↓ (new link requested for same order)
[Revoked] → revoked
```

When a new link is requested for an order that already has an active token, the previous token is immediately revoked before issuing the new one. Only one active token per order at any time.

---

## 2. Token Expiry Rules

| Trigger | Behaviour |
|---------|-----------|
| Link opened 3 times | Token marked `expired` on the 3rd open |
| 24 hours elapsed | Token marked `expired` on next access attempt |
| New link requested | Previous active token immediately `revoked` |
| Admin sets Completed (approval email) | New token issued, previous tokens revoked |

**Important:** Expiry is counted on **link opens** (loading the download confirmation page), not on file download. Each time the download page is visited, it consumes one of the 3 allowed opens.

Expiry is lazy — tokens are marked expired when they are next accessed after their TTL, not by a background scheduler.

---

## 3. Request Validation Pipeline

Every link request (`POST /find-my-files/request`) passes through the following checks in order:

```
1. Format Validation     — required email, valid format; order_number 3–50 chars
2. reCAPTCHA (optional)  — if enabled in Basic Settings
3. Rate Limit Check      — IP, email, and device fingerprint
4. Order Lookup          — exact match on order_number (uppercase, trimmed)
5. Email Match           — case-insensitive exact match against purchase record
6. Payment Status Check  — only Completed orders are eligible
7. Revoke Old Tokens     — previous active tokens for the order are revoked
8. Token Issuance        — new HMAC token generated and stored (hash only)
9. Email Dispatch        — download link sent to verified email address
```

Failed checks at steps 4, 5, and 6 increment the rate-limit counter for IP, email hash, and device fingerprint simultaneously.

---

## 4. Rate Limiting

Rate limiting operates on three independent dimensions. A block on any one dimension prevents the request.

| Dimension | Key Format | Window |
|-----------|------------|--------|
| IP address | `ip:<request_ip>` | Rolling |
| Email address | `email:<sha256_of_email>` | Rolling |
| Device fingerprint | `device:<sha256_of_useragent+ip>` | Rolling |

### Progressive Block Escalation

| Cumulative Failed Attempts | Block Duration |
|----------------------------|----------------|
| 5+ | 30 minutes |
| 10+ | 2 hours |
| 20+ | 24 hours |

Blocks apply independently per dimension. An attacker switching email addresses still gets blocked by IP. Switching IPs still gets blocked by email hash.

---

## 5. Risk Scoring

Each request is assigned a risk score (0–100) used for audit visibility:

| Factor | Scoring |
|--------|---------|
| Recent `LINK_REQUESTED` events from same IP in last 10 min | +10 per event, max 50 |
| Recent `LINK_REQUESTED` events for same email in last hour | +10 per event, max 50 |

Risk score is stored in the access log with every event. It is displayed to the user on the download confirmation page as Low / Medium / High with a colour indicator.

---

## 6. Access Logging

Every security-relevant event is written to the `access_logs` table with a UUID primary key.

| Event Type | When Recorded |
|------------|---------------|
| `LINK_REQUESTED` | Every request attempt (pass or fail) |
| `LINK_SENT` | Successful token issuance and email dispatch |
| `RATE_LIMIT_TRIGGERED` | Request blocked by rate limiter |
| `LINK_CLICKED` | Download confirmation page loaded (link opened) |
| `DOWNLOAD_SUCCESS` | ZIP file streamed to user |
| `DOWNLOAD_FAILED` | Invalid/expired/exhausted token used at stream |

Each log entry captures:
- Event type and result
- IP address
- Device fingerprint (SHA-256 of User-Agent + IP)
- User-Agent string (truncated to 255 chars)
- Email hash (SHA-256, never plain email)
- Order ID
- Risk score at time of event
- Timestamp

Sensitive data (email address, token values) is never stored in logs — only their hashes.

---

## 7. File Delivery

Files are streamed as a ZIP archive via a separate authenticated endpoint (`/find-my-files/stream`).

- Token is re-validated at stream time — no assumption is made that the download page and stream are accessed in the same session
- If the token is invalid or exhausted at stream time, `403 Forbidden` is returned
- ZIP is assembled on the fly into `storage/app/temp/`, streamed, then deleted immediately after send (`deleteFileAfterSend(true)`)
- Module files are stored outside the web root at `assets/front/files/tender_modules/`
- File names in the ZIP are sanitised: `preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name)`

---

## 8. Data Privacy

| Data | Storage |
|------|---------|
| Customer email | Stored only as SHA-256 hash in `secure_tokens` and `access_logs` |
| Raw token | Never stored — only its SHA-256 hash is in the database |
| Device fingerprint | SHA-256 of User-Agent + IP, not reversible |
| IP address | Stored in plain text in `access_logs` and `rate_limit_attempts` |

---

## 9. Configuration Reference

Configurable constants in `FindMyFilesController`:

| Constant | Default | Description |
|----------|---------|-------------|
| `TOKEN_TTL_HOURS` | `24` | Hours until a token expires |
| `MAX_DOWNLOADS` | `3` | Maximum link opens per token |
| `IP_LIMIT` | `10` | Max requests per IP per 15-min window |
| `EMAIL_LIMIT` | `5` | Max requests per email per hour |
| `BLOCK_MINUTES` | `[30, 120, 1440]` | Progressive block durations in minutes |

---

## 10. Database Tables

| Table | Purpose |
|-------|---------|
| `secure_tokens` | One row per issued token. UUID PK. Tracks status, expiry, open count. |
| `access_logs` | Immutable audit log. UUID PK. One row per security event. |
| `rate_limit_attempts` | Rolling counters keyed by IP / email hash / device hash. |

---

## 11. Security Boundaries Summary

| Threat | Mitigation |
|--------|------------|
| Token theft from DB | Raw tokens never stored; only SHA-256 hash |
| Token guessing / brute force | HMAC-SHA256 + app key; rate limiting blocks enumeration |
| Link sharing (forwarded email) | Max 3 opens; after 3rd open the token is expired |
| Link reuse after expiry | `isValid()` checked on every page load AND stream request |
| Multiple links for same order | Previous active tokens revoked before issuing new one |
| Automated scraping | reCAPTCHA (configurable); rate limiting on IP + device |
| Email enumeration | Same error message shown for both "order not found" and "email mismatch" responses... actually distinct messages — see note below |
| Expired token bypass | Token is re-validated at the stream endpoint independently of the download page |
| File path traversal | Module filenames stored in DB; full path constructed server-side, not from user input |

> **Note on error messages:** Currently, distinct error messages are returned for `order_not_found` vs `email_mismatch`. If minimising information leakage is a priority, both can be unified to a single "No matching order found" message.

---

*Document generated: April 2026. Reflects the current implementation in `FindMyFilesController.php`, `SecureToken.php`, `AccessLog.php`, and `RateLimitAttempt.php`.*
