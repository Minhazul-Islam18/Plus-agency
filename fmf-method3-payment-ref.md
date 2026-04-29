# Find My Files — Method 3: Email + Payment Reference
## Security & Accountability Document

**Version:** 1.0  
**Date:** 2026-04-22  
**Module:** Secure File Recovery (`/find-my-files`)  
**Method:** Email + Payment / Transaction Reference Number

---

## 1. Overview

Method 3 lets a buyer recover their purchased tender documents by proving ownership of their **email address** combined with the **payment or transaction reference number** they received when paying. This is a single-step method — no OTP, no session state — making it the simplest of the three recovery methods.

Unlike Method 1 (Order Number), the payment reference is a value that comes from an external system (the payment gateway or the buyer's bank), making it harder to guess and more familiar to buyers who recall their payment confirmation rather than their order number.

**User flow (happy path):**

```
[User] → enters Email + Payment Reference
       → clicks "Send Download Link"
       → system matches against purchase record
       → secure download link emailed (always — regardless of match)
       → user opens link from email → downloads their files
```

---

## 2. Key Parameters

| Parameter | Value | Location |
|---|---|---|
| Reference format | Alphanumeric + hyphens/underscores | Validation regex |
| Reference min length | **4 characters** | `requestByPaymentRef()` validation |
| Reference max length | **100 characters** | DB column + validation |
| Reference storage | Uppercased, trimmed | `TenderController@purchase` + `purchaseUpdateReference()` |
| Download link TTL | **24 hours** | `FindMyFilesController::TOKEN_TTL_HOURS` |
| Max link opens | **3** | `FindMyFilesController::MAX_DOWNLOADS` |

---

## 3. System Architecture

### 3.1 Database Change — `tender_purchases`

A single column was added to the existing purchases table:

| Column | Type | Purpose |
|---|---|---|
| `payment_reference` | varchar(100), nullable | Gateway txn ID or bank transfer reference — stored uppercased |

No new table is required. The lookup is a direct column match.

### 3.2 Reference Capture Points

References enter the system at three points:

**Point 1 — Buyer at checkout** (`/tender/purchase`):
The checkout form includes an optional "Payment / Transaction Reference" text input. The buyer can enter their bank transfer reference or gateway confirmation number at the time of purchase. Stored uppercased on save.

**Point 2 — Admin via purchase modal** (`/admin/tender/purchase`):
The purchase details modal includes an editable "Payment Reference" field with an inline Save button. The admin can enter or correct the reference after payment is confirmed — covering online gateways where the transaction ID arrives via callback or is looked up manually.

**Point 3 — Future gateway callbacks** (forward-compatible):
Any gateway callback that returns a transaction ID can write directly to `payment_reference`. The column is already in place.

---

## 4. Security Measures

### 4.1 No Enumeration (Neutral Responses)

When a submitted email + payment reference combination does not match any completed purchase, the system returns the **exact same success redirect** as a real match. The user is always sent to the "check your email" confirmation page.

This prevents attackers from using the endpoint as an oracle to probe which references or emails exist in the database.

> **Why this matters:** A non-neutral response (e.g., "reference not found") would allow an attacker to silently enumerate valid email/reference pairs by observing which inputs return errors.

For non-matching requests, no email is sent and no token is issued — but the HTTP response and redirect are indistinguishable from success.

### 4.2 Exact Match — No Partial or Fuzzy Matching

The lookup uses a strict equality check after normalisation:

```php
$normalised = strtoupper(trim($request->input('payment_reference')));

TenderPurchase::where('payment_status', 'Completed')
    ->whereNotNull('payment_reference')
    ->where('payment_reference', $normalised)
    ->get()
    ->first(fn($p) => strtolower(trim($p->email)) === strtolower(trim($request->input('email'))));
```

- Both the stored reference and the submitted reference are uppercased before comparison
- Email is lowercased and trimmed before comparison
- No LIKE, no partial match, no fuzzy search

### 4.3 Input Sanitisation

The reference field accepts only alphanumeric characters, hyphens, and underscores:

```
regex: /^[A-Za-z0-9\-_]+$/
```

This is enforced at both:
- Server-side: Laravel validation in `requestByPaymentRef()`
- Client-side: JavaScript strips disallowed characters on every `input` event

This prevents SQL injection, XSS, and reference fuzzing via special characters.

### 4.4 Rate Limiting (shared with Methods 1 & 2)

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

Rate limits are checked **before** any database lookup. Blocked requestors cannot probe the purchase table at all.

Failed attempts are incremented on every non-matching lookup (even though a neutral response is returned), so repeated guessing still escalates toward a block.

### 4.5 SecureToken Revocation

When a match is found and a new token is issued, any previously active `SecureToken` for the same order is revoked first. This ensures the buyer cannot accumulate multiple simultaneous download tokens by submitting the same reference multiple times.

```php
SecureToken::where('order_id', $purchase->order_number)
    ->where('status', 'active')
    ->update(['status' => 'revoked']);
```

### 4.6 Payment Status Gate

The lookup is scoped to `payment_status = Completed` purchases only. Pending or cancelled orders cannot yield a token regardless of whether the reference matches.

### 4.7 Reference Guessability

Payment references are externally generated (gateway transaction IDs, bank transfer references) and are not sequential or predictable. They are also typically long (8–40 characters), making brute-force guessing impractical — especially combined with rate limiting.

---

## 5. Audit Trail

All significant events are recorded in the `access_logs` table via `AccessLog::record()`.

| Event | Trigger |
|---|---|
| `LINK_REQUESTED / PAYREF_NO_MATCH` | Email + reference did not match any completed purchase |
| `RATE_LIMIT_TRIGGERED / RATE_LIMITED` | Request blocked by rate limiter |
| `LINK_SENT / PAYREF_OK` | Match found, token issued, download link emailed |

Each log entry includes: `ip`, `user_agent`, `email_hash`, `device_hash`, `order_id`, `result`, `risk_score`, `created_at`.

**Risk Score** (0–100) is computed from:
- Recent `LINK_REQUESTED` events from the same IP in the last 10 minutes (+10 per event, max 50)
- Recent `LINK_REQUESTED` events from the same email hash in the last hour (+10 per event, max 50)

Note: `email_hash` is logged (SHA-256 of the submitted email), never the raw email address.

---

## 6. Threat Model

| Threat | Mitigation |
|---|---|
| **Email/reference enumeration** | Neutral response — no-match is indistinguishable from success |
| **Reference brute-force** | Rate limiting (5 failures → 30 min block); references are externally generated and non-sequential |
| **Credential stuffing** | Progressive IP/email/device rate limiting |
| **Replay attack** | Previous active token revoked before new one issued |
| **Special character injection** | Input restricted to `[A-Za-z0-9\-_]` at both client and server |
| **Pending order abuse** | Lookup scoped to `payment_status = Completed` only |
| **Token accumulation** | Only one active `SecureToken` per order at any time |
| **Database leak** | Email stored only as SHA-256 hash in `access_logs`; reference stored as plaintext in `tender_purchases` (required for matching) |
| **Mass scanning** | IP rate limit blocks after 5 failures in 15 min |

---

## 7. Reference Lifecycle

```
[Checkout]  Buyer enters reference → stored uppercased in tender_purchases.payment_reference
[Admin]     Admin sets/corrects reference via purchase modal → same column
[Recovery]  Buyer submits email + reference → exact match → SecureToken issued → link emailed
[Download]  Buyer opens link → files streamed as ZIP → token download_count incremented
[Expiry]    Token expires after 24 h or 3 opens (whichever comes first)
```

---

## 8. Admin Controls

| Action | Location | Effect |
|---|---|---|
| View payment reference | Admin → Tenders → Enrolls → Details | Read current stored reference |
| Set / update reference | Admin → Tenders → Enrolls → Details → Save | Writes uppercased value to `payment_reference` |
| Clear reference | Admin → Tenders → Enrolls → Details → (empty) → Save | Sets `payment_reference` to `NULL` — disables Method 3 for that order |

Setting a reference to NULL effectively disables Method 3 recovery for that purchase without affecting Methods 1 or 2.

---

## 9. File Map

| File | Role |
|---|---|
| `database/migrations/2026_04_19_000001_add_payment_reference_to_tender_purchases_table.php` | Adds `payment_reference` column |
| `app/TenderPurchase.php` | `payment_reference` added to `$fillable` |
| `app/Http/Controllers/Front/TenderController.php` | Captures reference from checkout form on `purchase()` |
| `app/Http/Controllers/Admin/TenderController.php` | `purchaseUpdateReference()` — admin save action |
| `app/Http/Controllers/Front/FindMyFilesController.php` | `requestByPaymentRef()` — lookup, token issue, email |
| `resources/views/front/tender/tender_details.blade.php` | Optional reference input on checkout form |
| `resources/views/admin/tender/tender/purchase-details.blade.php` | Editable reference field in admin modal |
| `resources/views/front/find-my-files/index.blade.php` | Frontend — Method 3 panel + AJAX flow |
| `routes/web.php` | `/find-my-files/payment-ref` + `/tender/purchase/update-reference` |

---

## 10. Routes

| Method | URI | Name | Controller Action |
|---|---|---|---|
| `POST` | `/find-my-files/payment-ref` | `find_my_files.payment_ref` | `FindMyFilesController@requestByPaymentRef` |
| `POST` | `/tender/purchase/update-reference` | `admin.tender.purchaseUpdateReference` | `Admin\TenderController@purchaseUpdateReference` |

The recovery route is unauthenticated (public), protected by rate limiting and CSRF tokens. The admin route sits inside the `checkpermission:Tender Management` middleware group.

---

*This document reflects the implementation as of 2026-04-22.*
