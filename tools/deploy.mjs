// Veröffentlicht die statische Vorschau auf GitHub Pages (Branch gh-pages).
// Aufruf im Projektordner: node tools/deploy.mjs
// – baut static/ aus dem Theme (php tools/build-static.php)
// – kopiert nach dist/pages (ohne PHP/.htaccess), setzt noindex (Vorschau soll die echte Domain nicht konkurrieren)
// – erzwingt einen Push auf origin/gh-pages
import fs from 'fs';
import path from 'path';
import { execSync } from 'child_process';

const root = path.resolve(path.dirname(new URL(import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1')), '..');
const php = fs.existsSync('C:/xampp/php/php.exe') ? 'C:/xampp/php/php.exe' : 'php';
const run = (cmd, cwd = root) => execSync(cmd, { cwd, stdio: 'pipe' }).toString().trim();

run(`"${php}" tools/build-static.php`);
const src = path.join(root, 'static');
const dist = path.join(root, 'dist', 'pages');
fs.rmSync(dist, { recursive: true, force: true });
fs.cpSync(src, dist, { recursive: true });
for (const f of ['send.php', '.htaccess', 'data']) fs.rmSync(path.join(dist, f), { recursive: true, force: true });

(function walk(dir) {
  for (const e of fs.readdirSync(dir, { withFileTypes: true })) {
    const p = path.join(dir, e.name);
    if (e.isDirectory()) { if (e.name !== 'assets') walk(p); continue; }
    if (!e.name.endsWith('.html')) continue;
    let html = fs.readFileSync(p, 'utf8');
    html = html.replace(/<meta name="robots"[^>]*>\s*/g, '');
    html = html.replace('<meta name="viewport"', '<meta name="robots" content="noindex, nofollow">\n<meta name="viewport"');
    fs.writeFileSync(p, html);
  }
})(dist);
fs.writeFileSync(path.join(dist, '.nojekyll'), '');
fs.writeFileSync(path.join(dist, 'robots.txt'), 'User-agent: *\nDisallow: /\n');
fs.rmSync(path.join(dist, 'sitemap.xml'), { force: true });

const remote = run('git remote get-url origin');
run('git init -q', dist);
run('git checkout -q -b gh-pages', dist);
run('git add -A', dist);
run('git commit -q -m "Deploy ' + new Date().toISOString().slice(0, 16).replace('T', ' ') + '"', dist);
run(`git push -q -f "${remote}" gh-pages`, dist);
fs.rmSync(path.join(dist, '.git'), { recursive: true, force: true });
console.log('gh-pages aktualisiert →', remote);
