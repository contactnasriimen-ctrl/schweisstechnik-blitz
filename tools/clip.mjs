// Ausschnitt des Heros: node tools/clip.mjs <out.png> x y w h [wait]
import { createRequire } from 'module';
const require = createRequire('D:/Projects/_e2e-tools/package.json');
const puppeteer = require('puppeteer-core');
const [, , out, x, y, w, h, wait = '9000'] = process.argv;
const browser = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: 'new', args: ['--use-angle=swiftshader', '--enable-unsafe-swiftshader', '--ignore-gpu-blocklist', '--hide-scrollbars'] });
const page = await browser.newPage();
await page.setViewport({ width: 1440, height: 900 });
await page.goto('http://localhost:8091/', { waitUntil: 'networkidle2' });
await new Promise((r) => setTimeout(r, +wait));
await page.screenshot({ path: out, clip: { x: +x, y: +y, width: +w, height: +h } });
await browser.close();
