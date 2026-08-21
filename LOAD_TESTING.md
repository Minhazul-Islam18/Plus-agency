# Load Test Runbook — ICA Groupe (icagroupe.com)

Procedure for load-testing the live site from your own machine while watching real server resources over SSH. Written for the 50k-visitors/day capacity question on the current Hostinger plan (3072MB RAM, 60 PHP workers, 20,480 KB/s throughput, 1 avg / 512 burst IOPS).

---

## Before anything: the rules

> **This hits a live production site with real customers.** Three rules, no exceptions.

1. **Off-peak only.** Check your analytics for the quietest hour of the day (usually 02:00–05:00 local) and run there. Never during a campaign push, a deadline day for an open tender, or business hours.
2. **Read-only traffic only.** The script below hits homepage, tender listings, tender detail pages, services, portfolio, blog, FAQ — `GET` requests, nothing that writes. It deliberately **excludes**:
   - `/sendmail` (contact form) — would spam the real admin inbox and hit the `throttle:3,10` limiter instantly, producing noise instead of signal
   - `/tender/purchase/submit` and anything under `/find-my-files/*` — real payment gateway (Moneroo) and real customer download tokens. Never load-test a payment endpoint.
   - Anything under `/admin`
3. **Start small, escalate only if healthy.** Begin at 5 concurrent users, not 80. Watch the server the entire time. Stop immediately (`Ctrl+C`) if response times climb past ~2s or error rate climbs past ~1%.

If in doubt, don't run it — ask first.

---

## What you need

- `k6` installed on **your local machine** (not the Hostinger server — shared hosting won't let you install arbitrary binaries via SSH anyway). Install: https://k6.io/docs/getting-started/installation/
- SSH access to the Hostinger server, open in a second terminal, for watching resources live
- A real tender slug from the live site (any published tender's URL) to plug into the script

---

## Two-terminal setup

**Terminal A — SSH into Hostinger, watching resources.** Run this and leave it up for the whole test:

```bash
ssh your-user@your-hostinger-host
cd domains/icagroupe.com/public_html/core   # adjust to your actual path
watch -n 2 "echo '--- PHP-FPM / load ---'; uptime; echo; echo '--- queue_jobs (should stay near 0, draining every ~60s) ---'; mysql -u DB_USER -pDB_PASS DB_NAME -e \"SELECT COUNT(*) FROM queue_jobs;\" 2>/dev/null"
```

If Hostinger's shell doesn't give you `top`/`htop`, use hPanel's **Resource Usage** graph in another browser tab instead — same idea, just visual.

In a third pane, tail the app log so real errors surface immediately:

```bash
tail -f storage/logs/laravel.log
```

**Terminal B — on your own machine, generate the load.**

---

## The test script

Save as `prod-loadtest.js`. Replace `YOUR-TENDER-SLUG-HERE` with a real slug from the live site (URL-encode it if it has accents/apostrophes — the script does this for you, just paste the slug as-is from the URL bar).

```js
import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE = 'https://icagroupe.com';
const TENDER_SLUG = encodeURIComponent('YOUR-TENDER-SLUG-HERE');

// Start conservative. This is stage 1 — see "Escalation" below before
// touching these numbers.
export const options = {
    stages: [
        { duration: '30s', target: 5 },
        { duration: '60s', target: 5 },
        { duration: '20s', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<2000'],
        http_req_failed: ['rate<0.01'],
    },
};

export default function () {
    const pages = ['/', '/tenders', `/tender/${TENDER_SLUG}`, '/services', '/portfolios', '/faq'];
    const path = pages[Math.floor(Math.random() * pages.length)];

    const res = http.get(`${BASE}${path}`);
    check(res, { [`${path} 200`]: (r) => r.status === 200 });

    sleep(1 + Math.random() * 2); // 1-3s between requests, mimics real browsing pace
}
```

Run it:

```bash
k6 run prod-loadtest.js
```

Watch Terminal A the entire time. If `uptime`'s load average climbs steeply, or the log tail starts showing exceptions, stop (`Ctrl+C` in Terminal B) and don't escalate further that session.

---

## Escalation — only if stage 1 was clean

If 5 VUs for a minute produced p95 under ~1s and zero errors, move up one stage at a time. Don't jump straight to a big number.

| Stage | Target VUs | Models |
|---|---|---|
| 1 | 5 | Quiet baseline |
| 2 | 20 | Normal peak-hour sustained load |
| 3 | 40 | Peak-hour burst |
| 4 | 60 | Safety margin above expected peak |

Edit the `stages` array to the next row, rerun, watch Terminal A again. Stop climbing the moment anything looks unhealthy — you don't need to reach stage 4 to get a useful answer; you need to find where it *starts* to strain, which might be stage 2.

---

## Reading the result

| Signal | Healthy | Investigate |
|---|---|---|
| `http_req_duration` p95 | Under ~1s | Climbing past 2-3s as VUs increase |
| `http_req_failed` rate | Under 1% | Any sustained non-zero rate |
| `queue_jobs` row count (Terminal A) | Near 0, draining within ~60s | Growing and not shrinking → cron isn't running, or worker can't keep up |
| Server load average | Stable | Climbing steadily through the test |
| `laravel.log` | Silent | Any exception during the test window |

A p95 that's fine at 20 VUs but degrades sharply at 40 tells you roughly where the ceiling is — that number, multiplied by a safety factor, is your real answer to "can it handle 50k/day."

---

## After the test

- Confirm `queue_jobs` drained to 0 within a minute or two of the test ending (proves the cron worker kept pace)
- Nothing needs cleanup — the script only reads, no rows are created
- If a stage failed, note which one and what the actual error/timing was before trying again — don't just rerun the same numbers hoping for a different result
