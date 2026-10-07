// Theme-Vorschaubild (1200×900) aus der WordPress-Testsite
import { createRequire } from 'module';
const require = createRequire('D:/Projects/_e2e-tools/package.json');
const puppeteer = require('puppeteer-core');
const browser = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: 'new', args: ['--use-angle=swiftshader', '--enable-unsafe-swiftshader', '--ignore-gpu-blocklist', '--hide-scrollbars'] });
const page = await browser.newPage();
await page.setViewport({ width: 1600, height: 1200, deviceScaleFactor: 0.75 });
await page.goto(process.argv[2] || 'http://localhost:8092/', { waitUntil: 'networkidle2', timeout: 90000 });
await new Promise((r) => setTimeout(r, 8000));
await page.addStyleTag({ content: '.cursor{display:none!important}' });
await page.screenshot({ path: process.argv[3] || 'screenshot.png', type: 'png' });
await browser.close();
console.log('ok');
