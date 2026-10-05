// Prüft die veröffentlichte Vorschau: node tools/live_check.mjs <url> <outdir>
import { createRequire } from 'module';
const require = createRequire('D:/Projects/_e2e-tools/package.json');
const puppeteer = require('puppeteer-core');
const [, , url, out = '.'] = process.argv;
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const res = [];
const ok = (n, c, x = '') => res.push((c ? 'PASS ' : 'FAIL ') + n + (x ? ' – ' + x : ''));
const browser = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: 'new', args: ['--use-angle=swiftshader', '--enable-unsafe-swiftshader', '--ignore-gpu-blocklist', '--hide-scrollbars'] });

for (const [w, h, tag] of [[1440, 900, 'desktop'], [390, 844, 'mobile']]) {
  const page = await browser.newPage();
  const errors = [], bad = [];
  page.on('pageerror', (e) => errors.push(e.message));
  page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
  page.on('response', (r) => { if (r.status() >= 400 && !r.url().includes('favicon.ico')) bad.push(r.status() + ' ' + r.url()); });
  await page.setViewport({ width: w, height: h, isMobile: w < 600, hasTouch: w < 600 });
  await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });
  await sleep(7000);
  ok(tag + ': alle Dateien geladen', bad.length === 0, bad.join(' | '));
  ok(tag + ': keine JS-Fehler', errors.length === 0, errors.join(' | '));
  ok(tag + ': WebGL aktiv', !(await page.$eval('.hero', (e) => e.classList.contains('no-webgl'))));
  ok(tag + ': noindex gesetzt', !!(await page.$('meta[name="robots"][content*="noindex"]')));
  await page.screenshot({ path: `${out}/live-${tag}.png` });

  if (tag === 'desktop') {
    await page.evaluate(() => document.querySelector('#kontakt').scrollIntoView());
    await sleep(1500);
    await page.click('input[value="Stahlkonstruktion"]');
    await page.click('[data-next]'); await sleep(900);
    await page.click('[data-next]'); await sleep(900);
    await page.type('input[name="name"]', 'Test Vorschau');
    await page.type('input[name="email"]', 'test@example.com');
    await page.click('input[name="einwilligung"]');
    await page.click('[data-submit]'); await sleep(1500);
    const href = await page.$eval('.wizard__alt a[href^="https://wa.me/"]', (a) => a.href).catch(() => '');
    ok('Formular: WhatsApp-Weg angeboten', href.includes('Stahlkonstruktion') && href.includes('Test%20Vorschau'));
    ok('Formular: E-Mail-Weg angeboten', !!(await page.$('.wizard__alt a[href^="mailto:info@schweisstechnik-blitz.de"]')));
    await (await page.$('.wizard')).screenshot({ path: `${out}/live-form.png` });
  }
  await page.close();
}
for (const p of ['impressum.html', 'datenschutz.html', 'assets/img/og.jpg']) {
  const page = await browser.newPage();
  const r = await page.goto(url + p);
  ok(p + ' erreichbar', r.status() === 200);
  await page.close();
}
console.log(res.join('\n'));
await browser.close();
