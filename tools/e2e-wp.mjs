// WordPress-Test (lokale Testsite auf :8092 mit Elementor): node tools/e2e-wp.mjs <outdir>
import { createRequire } from 'module';
const require = createRequire('D:/Projects/_e2e-tools/package.json');
const puppeteer = require('puppeteer-core');
const out = process.argv[2] || '.';
const BASE = process.env.WP_BASE || 'http://localhost:8092';
const USER = process.env.WP_USER || 'admin', PASS = process.env.WP_PASS || '';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const res = [];
const ok = (n, c, x = '') => { const l = (c ? 'PASS ' : 'FAIL ') + n + (x ? ' – ' + x : ''); res.push(l); console.log(l); };
const browser = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: 'new', args: ['--use-angle=swiftshader', '--enable-unsafe-swiftshader', '--ignore-gpu-blocklist', '--hide-scrollbars'] });

async function open(url, w = 1440, h = 900, ctx = browser) {
  const page = await ctx.newPage();
  const errors = [];
  page.on('pageerror', (e) => errors.push(e.message));
  page.on('console', (m) => { if (m.type() === 'error' && !/favicon|Failed to load resource: the server responded with a status of 404/.test(m.text())) errors.push(m.text()); });
  await page.setViewport({ width: w, height: h, isMobile: w < 600, hasTouch: w < 600 });
  const r = await page.goto(BASE + url, { waitUntil: 'networkidle2', timeout: 90000 });
  return { page, errors, status: r.status() };
}

/* 1. Startseite (Elementor) */
{
  const { page, errors, status } = await open('/');
  await sleep(6500);
  ok('Startseite 200', status === 200);
  const widgets = await page.$$eval('[data-widget_type^="blitz-"]', (els) => els.map((e) => e.getAttribute('data-widget_type')));
  ok('Startseite aus Elementor-Widgets', widgets.length >= 10, widgets.join(', '));
  ok('3D-Canvas aktiv', await page.$eval('.hero', (h) => !h.classList.contains('no-webgl') && !!h.querySelector('canvas.hero__canvas')));
  ok('Schnellanfrage im Hero', !!(await page.$('.hero [data-quickform]')));
  ok('Telefon im Hero', (await page.$eval('.qf__phone strong', (e) => e.textContent)).includes('0151'));
  ok('H1 mit Ortsbezug', (await page.$eval('.hero__h1', (e) => e.getAttribute('aria-label'))).includes('Biessenhofen'));
  ok('München auf der Karte', (await page.$$eval('.town text', (t) => t.map((x) => x.textContent).join('|'))).includes('München'));
  const ld = await page.$$eval('script[type="application/ld+json"]', (s) => s.map((x) => x.textContent).join(''));
  ok('Schema LocalBusiness + FAQ', ld.includes('HomeAndConstructionBusiness') && ld.includes('FAQPage') && ld.includes('München'));
  ok('Meta-Description', !!(await page.$('meta[name="description"]')));
  ok('Keine JS-Fehler (Start)', errors.length === 0, errors.join(' | '));
  await page.screenshot({ path: out + '/wp-home.png' });

  // Schnellanfrage absenden
  await page.select('.qf select[name="arbeit[]"]', 'Bohrrohr');
  await page.type('.qf input[name="name"]', 'WP Test');
  await page.type('.qf input[name="telefon"]', '0170 7654321');
  await page.type('.qf textarea[name="beschreibung"]', 'Bohrrohr verlängern, 87640 Biessenhofen');
  await page.click('.qf input[name="einwilligung"]');
  await page.click('.qf__submit');
  await page.waitForFunction(() => !document.querySelector('.qf__done').hidden, { timeout: 30000 }).catch(() => {});
  ok('Schnellanfrage gesendet', await page.$eval('.qf__done', (e) => !e.hidden));
  await page.close();
}

/* 2. Unterseiten */
for (const [url, h1] of [['/schweisser/muenchen/', 'München'], ['/schweisser/kaufbeuren/', 'Kaufbeuren'], ['/leistungen/tanks-und-behaelter/', 'Tanks'], ['/schweisser/', 'Allgäu'], ['/leistungen/', 'Elektroschweißen'], ['/impressum/', 'Impressum']]) {
  const { page, errors, status } = await open(url);
  await sleep(2500);
  const text = await page.$eval('h1', (e) => e.getAttribute('aria-label') || e.textContent);
  ok(url + ' (' + status + ')', status === 200 && text.includes(h1), text);
  const title = await page.title();
  if (url.includes('muenchen')) {
    ok('Titel München', title.includes('München'), title);
    const ld = await page.$$eval('script[type="application/ld+json"]', (s) => s.map((x) => x.textContent).join(''));
    ok('Schema Service + Breadcrumbs', ld.includes('"Service"') && ld.includes('BreadcrumbList'));
    await page.screenshot({ path: out + '/wp-muenchen.png' });
  }
  if (errors.length) ok('JS-Fehler ' + url, false, errors.join(' | '));
  await page.close();
}

/* 3. Mobil */
{
  const { page, errors } = await open('/', 390, 844);
  await sleep(5000);
  ok('Mobil: kein Überlauf', (await page.evaluate(() => document.documentElement.scrollWidth - innerWidth)) === 0);
  await page.screenshot({ path: out + '/wp-mobile.png' });
  ok('Mobil: keine JS-Fehler', errors.length === 0, errors.join(' | '));
  await page.close();
}

/* 4. Elementor-Editor */
{
  const ctx = await browser.createBrowserContext();
  const ed = await ctx.newPage();
  const editorErrors = [];
  ed.on('pageerror', (e) => editorErrors.push(e.message));
  await ed.setViewport({ width: 1600, height: 1000 });
  const fid = process.env.FRONT_ID || '5';
  const target = BASE + '/wp-admin/post.php?post=' + fid + '&action=elementor';
  await ed.goto(BASE + '/wp-login.php?redirect_to=' + encodeURIComponent(target), { waitUntil: 'domcontentloaded', timeout: 90000 });
  await ed.type('#user_login', USER);
  await ed.type('#user_pass', PASS);
  await Promise.all([ed.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 240000 }), ed.click('#wp-submit')]);
  await ed.waitForFunction(() => window.elementor && window.elementor.documents && window.elementor.documents.getCurrent && document.querySelector('#elementor-preview-iframe'), { timeout: 120000 });
  await sleep(9000);
  const info = await ed.evaluate(() => {
    const cats = Object.keys(window.elementor.config.document ? window.elementor.widgetsCache || {} : {});
    const blitz = Object.keys(window.elementor.widgetsCache || {}).filter((k) => k.indexOf('blitz-') === 0);
    const doc = document.querySelector('#elementor-preview-iframe').contentDocument;
    return { blitz, hero: !!doc.querySelector('.hero'), widgets: doc.querySelectorAll('[data-widget_type^="blitz-"]').length };
  });
  ok('Editor: Blitz-Widgets registriert', info.blitz.length >= 11, info.blitz.join(', '));
  ok('Editor: Vorschau zeigt die Startseite', info.hero && info.widgets >= 10, 'widgets=' + info.widgets);
  // Text eines Widgets ändern
  const changed = await ed.evaluate(async () => {
    const doc = document.querySelector('#elementor-preview-iframe').contentDocument;
    const el = doc.querySelector('[data-widget_type^="blitz-hero"]');
    const id = el.getAttribute('data-id');
    const container = window.elementor.getContainer(id);
    window.$e.run('document/elements/settings', { container, settings: { title1: 'Testtitel Elementor' } });
    for (let i = 0; i < 60; i++) {
      await new Promise((r) => setTimeout(r, 500));
      const now = doc.querySelector('[data-id="' + id + '"] .hero__l1');
      if (now && now.textContent.indexOf('Testtitel') >= 0) return now.textContent;
    }
    const last = doc.querySelector('[data-id="' + id + '"] .hero__l1');
    return last ? last.textContent : '';
  });
  ok('Editor: Änderung erscheint in der Vorschau', changed.includes('Testtitel'), changed);
  ok('Editor: keine JS-Fehler', editorErrors.length === 0, editorErrors.slice(0, 3).join(' | '));
  await ed.screenshot({ path: out + '/wp-editor.png' });
  await ctx.close();
}

console.log(res.join('\n'));
await browser.close();
