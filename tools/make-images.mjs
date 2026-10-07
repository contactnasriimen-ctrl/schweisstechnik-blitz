// Erzeugt assets/img/og.jpg (1200×630, Hero-Szene) und assets/img/apple-touch-icon.png (180×180)
import { createRequire } from 'module';
const require = createRequire('D:/Projects/_e2e-tools/package.json');
const puppeteer = require('puppeteer-core');
const browser = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: 'new', args: ['--use-angle=swiftshader', '--enable-unsafe-swiftshader', '--ignore-gpu-blocklist', '--hide-scrollbars'] });

const page = await browser.newPage();
await page.setViewport({ width: 1200, height: 630, deviceScaleFactor: 1 });
await page.goto('http://localhost:8091/', { waitUntil: 'networkidle2' });
await new Promise((r) => setTimeout(r, 8000));
await page.addStyleTag({ content: '.nav,.hero__hud,.hero__tags,.hero__lead,.hero__cta,.hero__facts,.cursor{display:none!important}.hero{padding-bottom:70px}' });
await new Promise((r) => setTimeout(r, 400));
await page.screenshot({ path: 'theme/schweisstechnik-blitz/assets/img/og.jpg', type: 'jpeg', quality: 86 });

const icon = await browser.newPage();
await icon.setViewport({ width: 180, height: 180 });
await icon.goto('http://localhost:8091/assets/img/favicon.svg');
await icon.setContent('<html><body style="margin:0;background:#0e0e10"><img src="http://localhost:8091/assets/img/favicon.svg" style="width:180px;height:180px;display:block"></body></html>', { waitUntil: 'load' });
await icon.screenshot({ path: 'theme/schweisstechnik-blitz/assets/img/apple-touch-icon.png' });
await browser.close();
console.log('ok');
