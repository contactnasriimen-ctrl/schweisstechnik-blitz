// Funktionstest (Server muss auf :8091 laufen): node tools/e2e.mjs <outdir>
import { createRequire } from 'module';
import fs from 'fs';
const require = createRequire('D:/Projects/_e2e-tools/package.json');
const puppeteer = require('puppeteer-core');
const out = process.argv[2] || '.';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const results = [];
const ok = (name, cond, extra = '') => { results.push((cond ? 'PASS ' : 'FAIL ') + name + (extra ? ' – ' + extra : '')); };
const browser = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: 'new', args: ['--use-angle=swiftshader', '--enable-unsafe-swiftshader', '--ignore-gpu-blocklist', '--hide-scrollbars'] });

async function open(w, h, opts = {}) {
  const page = await browser.newPage();
  const errors = [];
  page.on('pageerror', (e) => errors.push(e.message));
  page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
  await page.setViewport({ width: w, height: h, isMobile: w < 600, hasTouch: w < 600 });
  if (opts.reduced) await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
  await page.goto('http://localhost:8091/', { waitUntil: 'networkidle2' });
  await sleep(4500);
  return { page, errors };
}

/* Desktop: Positionen, Karten-Link, Formular */
{
  const { page, errors } = await open(1440, 900);
  ok('Loader entfernt', !(await page.$('.loader')));
  ok('H1 lesbar (aria-label)', (await page.$eval('.hero__title', (e) => e.getAttribute('aria-label'))) === 'Schweißarbeiten, die halten.');
  await page.evaluate(() => document.querySelector('#steignaht').scrollIntoView());
  await sleep(1500);
  await page.click('[data-pos="PE"]');
  await sleep(1600);
  ok('Position PE gewählt', (await page.$eval('.viewer__title', (e) => e.textContent)) === 'PE – Überkopf');
  ok('Decke sichtbar bei PE', +(await page.$eval('.v-ceiling', (e) => getComputedStyle(e).opacity)) > 0.9);
  const vb = await page.$('.viewer');
  await vb.screenshot({ path: out + '/viewer-PE.png' });
  await page.click('[data-pos="PC"]'); await sleep(1700);
  await vb.screenshot({ path: out + '/viewer-PC.png' });
  await page.click('[data-pos="PA"]'); await sleep(1700);
  await vb.screenshot({ path: out + '/viewer-PA.png' });

  await page.evaluate(() => document.querySelector('[data-pick="Rohr oder Leitung"]').click());
  await sleep(2200);
  ok('Karten-Link wählt Arbeit vor', await page.$eval('input[value="Rohr oder Leitung"]', (e) => e.checked));
  await page.evaluate(() => { document.querySelector('input[value="Rohr oder Leitung"]').checked = false; });
  await page.click('[data-next]'); await sleep(400);
  ok('Fehler ohne Auswahl', await page.$eval('.wizard__error', (e) => !e.hidden && e.textContent.includes('mindestens eine')));
  await page.click('input[value="Tank oder Behälter"]');
  await page.click('input[value="Reparatur"]');
  await page.click('[data-next]'); await sleep(900);
  ok('Schritt 2 sichtbar', await page.$eval('[data-step="2"]', (e) => !e.hidden));
  await page.click('input[value="Auf einer Baustelle"]');
  await page.click('input[value="Ja, Gerüst oder Bühne nötig"]');
  await page.type('input[name="ort"]', '87600 Kaufbeuren');
  await page.type('textarea[name="beschreibung"]', 'Riss am Stutzen eines stehenden Heizöltanks, ca. 2 m hoch.');
  await page.click('[data-next]'); await sleep(900);
  ok('Schritt 3 sichtbar', await page.$eval('[data-step="3"]', (e) => !e.hidden));
  await page.click('[data-submit]'); await sleep(400);
  ok('Fehler ohne Name', await page.$eval('.wizard__error', (e) => !e.hidden && e.textContent.includes('Namen')));
  await page.type('input[name="name"]', 'Max Mustermann');
  await page.type('input[name="telefon"]', '0170 1234567');
  await page.click('[data-submit]'); await sleep(400);
  ok('Fehler ohne Einwilligung', await page.$eval('.wizard__error', (e) => !e.hidden && e.textContent.includes('Einwilligung')));
  await page.click('input[name="einwilligung"]');
  const count = () => (fs.existsSync('data/outbox') ? fs.readdirSync('data/outbox').length : 0);
  const before = count();
  await page.click('[data-submit]'); await sleep(2500);
  ok('Erfolgsmeldung', await page.$eval('.wizard__done', (e) => !e.hidden));
  ok('Mail im Outbox abgelegt', count() === before + 1);
  await (await page.$('.wizard')).screenshot({ path: out + '/wizard-done.png' });
  ok('Keine JS-Fehler (Desktop)', errors.length === 0, errors.join(' | '));
  await page.close();
}

/* Mobil: Menü */
{
  const { page, errors } = await open(390, 844);
  await page.click('.nav__burger'); await sleep(1100);
  ok('Menü offen', await page.$eval('#menu', (e) => !e.hidden && e.classList.contains('is-open')));
  await page.screenshot({ path: out + '/menu-mobile.png' });
  await page.click('.menu__links a[href="#faq"]'); await sleep(2500);
  ok('Menü schließt nach Klick', await page.$eval('#menu', (e) => !e.classList.contains('is-open')));
  const y = await page.evaluate(() => document.querySelector('#faq').getBoundingClientRect().top);
  ok('Zu FAQ gescrollt', Math.abs(y) < 200, 'top=' + Math.round(y));
  ok('Kein horizontaler Überlauf', (await page.evaluate(() => document.documentElement.scrollWidth - innerWidth)) === 0);
  ok('Keine JS-Fehler (Mobil)', errors.length === 0, errors.join(' | '));
  await page.close();
}

/* Reduzierte Bewegung */
{
  const { page, errors } = await open(1280, 800, { reduced: true });
  ok('Reduced: Loader weg', !(await page.$('.loader')));
  await page.evaluate(() => document.querySelector('.cards').scrollIntoView());
  await sleep(600);
  ok('Reduced: Karten sichtbar', +(await page.$eval('.card', (e) => getComputedStyle(e).opacity)) === 1);
  ok('Keine JS-Fehler (Reduced)', errors.length === 0, errors.join(' | '));
  await page.close();
}

/* Weitere Breiten ohne Überlauf */
for (const w of [768, 1024, 1280]) {
  const { page, errors } = await open(w, 900);
  ok('Kein Überlauf @' + w, (await page.evaluate(() => document.documentElement.scrollWidth - innerWidth)) === 0);
  if (errors.length) ok('Fehler @' + w, false, errors.join(' | '));
  await page.close();
}

/* Rechtliche Seiten */
for (const p of ['impressum.html', 'datenschutz.html']) {
  const page = await browser.newPage();
  const r = await page.goto('http://localhost:8091/' + p);
  ok(p + ' erreichbar', r.status() === 200);
  await page.close();
}
console.log(results.join('\n'));
await browser.close();
