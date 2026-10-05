// Mehrere Abschnitte nacheinander: node tools/shots.mjs <outdir> <width> <height> sel1 sel2 ...
import { createRequire } from 'module';
const require = createRequire('D:/Projects/_e2e-tools/package.json');
const puppeteer = require('puppeteer-core');
const [, , outDir, w = '1440', h = '900', ...sels] = process.argv;
const mobile = +w < 600;
const browser = await puppeteer.launch({
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: 'new',
  args: ['--use-angle=swiftshader', '--enable-unsafe-swiftshader', '--ignore-gpu-blocklist', '--hide-scrollbars'],
});
const page = await browser.newPage();
const errors = [];
page.on('pageerror', (e) => errors.push('pageerror: ' + e.message));
page.on('console', (m) => { if (m.type() === 'error' || m.type() === 'warning') errors.push(m.type() + ': ' + m.text()); });
page.on('requestfailed', (r) => errors.push('failed: ' + r.url()));
await page.setViewport({ width: +w, height: +h, deviceScaleFactor: 1, isMobile: mobile, hasTouch: mobile });
if (mobile) await page.setUserAgent('Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0 Mobile Safari/537.36');
await page.goto('http://localhost:8091/', { waitUntil: 'networkidle2', timeout: 60000 });
await new Promise((r) => setTimeout(r, 5000));
let i = 0;
for (const spec of sels) {
  const [sel, off = '0'] = spec.split('@');
  const target = await page.evaluate((s, o) => { const el = document.querySelector(s); if (!el) return null; return el.getBoundingClientRect().top + window.scrollY + (+o); }, sel, off);
  if (target == null) { console.log('missing', sel); continue; }
  const cur = await page.evaluate(() => window.scrollY);
  const step = target > cur ? 220 : -220;
  for (let y = cur; step > 0 ? y < target : y > target; y += step) { await page.evaluate((yy) => window.scrollTo(0, yy), y); await new Promise((r) => setTimeout(r, 60)); }
  await page.evaluate((yy) => window.scrollTo(0, yy), target);
  await new Promise((r) => setTimeout(r, 2600));
  const name = `${outDir}/${String(++i).padStart(2, '0')}-${sel.replace(/[^a-z0-9]+/gi, '')}${off !== '0' ? '-' + off : ''}.png`;
  await page.screenshot({ path: name });
  console.log(name);
}
const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
console.log(JSON.stringify({ overflow, errors: errors.slice(0, 12) }));
await browser.close();
