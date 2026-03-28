'use strict';

/**
 * BugBoard Analytics – Admin Triager Bot
 *
 * Simulates the internal admin who periodically reviews newly submitted reports.
 * When the bot's browser loads /reports.php any stored XSS payload executes
 * in the admin's session context, giving access to the FLAG_XSS cookie.
 */

const puppeteer = require('puppeteer');

const TARGET       = (process.env.TARGET_URL || 'http://web').replace(/\/$/, '');
const ADMIN_EMAIL  = 'admin@bugboard.local';
const ADMIN_PASS   = 'Adm1n@BugBoard2024!';
const INTERVAL_MS  = parseInt(process.env.BOT_INTERVAL || '30000', 10);

async function runBot() {
    const ts = new Date().toISOString();
    console.log(`[${ts}] [bot] Starting review cycle…`);

    let browser;
    try {
        browser = await puppeteer.launch({
            headless: 'new',
            executablePath: process.env.CHROMIUM_PATH || '/usr/bin/chromium',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--no-first-run',
            ],
        });

        const page = await browser.newPage();
        page.setDefaultNavigationTimeout(15000);
        page.setDefaultTimeout(10000);

        // ── Step 1: Log in as admin ──────────────────────────────────────────
        console.log(`[bot] Navigating to ${TARGET}/login.php`);
        await page.goto(`${TARGET}/login.php`, { waitUntil: 'networkidle2' });

        await page.type('input[name="email"]',    ADMIN_EMAIL);
        await page.type('input[name="password"]', ADMIN_PASS);

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle2' }),
            page.click('button[type="submit"]'),
        ]);

        const postLoginUrl = page.url();
        console.log(`[bot] After login: ${postLoginUrl}`);

        if (postLoginUrl.includes('login.php')) {
            console.error('[bot] Login failed – still on login page');
            return;
        }

        // ── Step 2: Visit the reports page (XSS sink) ───────────────────────
        console.log(`[bot] Visiting ${TARGET}/reports.php`);
        await page.goto(`${TARGET}/reports.php`, { waitUntil: 'networkidle2' });

        // Give any injected JS time to execute and fire HTTP requests
        await new Promise(r => setTimeout(r, 4000));

        console.log(`[bot] Review cycle complete.`);
    } catch (err) {
        console.error(`[bot] Error: ${err.message}`);
    } finally {
        if (browser) {
            await browser.close().catch(() => {});
        }
    }
}

// Run once after a short startup delay (let the web service initialise first)
setTimeout(function cycle() {
    runBot().then(() => setTimeout(cycle, INTERVAL_MS));
}, 8000);

console.log(`[bot] Admin triager bot ready. Cycle interval: ${INTERVAL_MS / 1000}s`);
