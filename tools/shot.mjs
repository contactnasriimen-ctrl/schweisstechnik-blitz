// Kontroll-Screenshots: node tools/shot.mjs <path> <out.png> [width] [height] [scrollY] [waitMs] [full]
import { createRequire } from 'module';
const require = createRequire('D:/Projects/_e2e-tools/package.json');
const puppeteer = require('puppeteer-core');

const [, , path = '/', out = 'shot.png', w = '1440', h = '900', scrollY = '0', wait = '4500', full = ''] = process.argv;
const mobile = +w < 600;
const browser = await puppeteer.launch({
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
  headless: 'new',
  args: ['--use-angle=swiftshader', '--enable-unsafe-swiftshader', '--ignore-gpu-blocklist', '--hide-scrollbars'],
});
const page = await browser.newPage();
const errors = [];
page.on('pageerror', (e) => errors.push('pageerror: ' + e.message));
page.on('console', (m) => { if (m.type() === 'error' || m.type() === 'warning') errors.push(m.type() + ': ' + m.text()); });
page.on('requestfailed', (r) => errors.push('failed: ' + r.url()));
await page.setViewport({ width: +w, height: +h, deviceScaleFactor: 1, isMobile: mobile, hasTouch: mobile });
if (mobile) await page.setUserAgent('Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0 Mobile Safari/537.36');
await page.goto('http://localhost:8091' + path, { waitUntil: 'networkidle2', timeout: 60000 });
await new Promise((r) => setTimeout(r, +wait));
if (+scrollY) {
  for (let y = 0; y <= +scrollY; y += 250) { await page.evaluate((yy) => window.scrollTo(0, yy), y); await new Promise((r) => setTimeout(r, 70)); }
  await new Promise((r) => setTimeout(r, 2200));
}
const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
await page.screenshot({ path: out, fullPage: full === 'full' });
console.log(JSON.stringify({ path, overflow, errors: errors.slice(0, 12) }));
await browser.close();
