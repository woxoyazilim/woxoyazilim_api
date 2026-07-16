#!/usr/bin/env node
/**
 * Full-page screenshot capture script
 * Usage: node capture-screenshot.js <url> <output-path>
 * Example: node capture-screenshot.js "https://example.com" "public/uploads/references/screenshots/test.png"
 */

const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

const url = process.argv[2];
const outputPath = process.argv[3];

if (!url || !outputPath) {
  console.error(JSON.stringify({ success: false, error: 'Usage: node capture-screenshot.js <url> <output-path>' }));
  process.exit(1);
}

(async () => {
  let browser;
  try {
    // Output klasorunu olustur
    const dir = path.dirname(outputPath);
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
    }

    // Sunucuda chromium, lokalde Google Chrome kullan
    const execPath = process.env.CHROME_PATH
      || (require('fs').existsSync('/usr/bin/chromium-browser') ? '/usr/bin/chromium-browser' : undefined)
      || (require('fs').existsSync('/usr/bin/google-chrome') ? '/usr/bin/google-chrome' : undefined)
      || (require('fs').existsSync('/Applications/Google Chrome.app/Contents/MacOS/Google Chrome') ? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' : undefined);

    browser = await puppeteer.launch({
      headless: true,
      ...(execPath ? { executablePath: execPath } : {}),
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-gpu',
        '--disable-web-security',
        '--disable-features=VizDisplayCompositor',
      ],
    });

    const page = await browser.newPage();

    // Desktop viewport
    await page.setViewport({ width: 1440, height: 900 });

    // Cookie/popup banner'lari engellemek icin
    await page.setUserAgent(
      'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    );

    // Sayfayi yukle - networkidle2 ile tum kaynaklar yuklenene kadar bekle
    await page.goto(url, {
      waitUntil: 'networkidle2',
      timeout: 30000,
    });

    // Ekstra bekleme - lazy-load iceriklerin yuklenmesi icin
    await new Promise(r => setTimeout(r, 2000));

    // Full-page screenshot
    await page.screenshot({
      path: outputPath,
      fullPage: true,
      type: 'png',
    });

    console.log(JSON.stringify({
      success: true,
      path: outputPath,
      size: fs.statSync(outputPath).size,
    }));

    process.exit(0);
  } catch (err) {
    console.error(JSON.stringify({
      success: false,
      error: err.message || String(err),
    }));
    process.exit(1);
  } finally {
    if (browser) await browser.close().catch(() => {});
  }
})();
