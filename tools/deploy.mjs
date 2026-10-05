// Veröffentlicht die statische Vorschau auf GitHub Pages (Branch gh-pages).
// Aufruf im Projektordner: node tools/deploy.mjs
// – baut dist/ (ohne PHP, .htaccess, tools, data), setzt noindex (Vorschau soll den echten Domain nicht konkurrieren)
// – erzwingt einen Push von dist/ auf origin/gh-pages
import fs from 'fs';
import path from 'path';
import { execSync } from 'child_process';

const root = path.resolve(path.dirname(new URL(import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1')), '..');
const dist = path.join(root, 'dist');
const run = (cmd, cwd = root) => execSync(cmd, { cwd, stdio: 'pipe' }).toString().trim();

fs.rmSync(dist, { recursive: true, force: true });
fs.mkdirSync(dist);
for (const f of ['index.html', 'impressum.html', 'datenschutz.html']) {
  let html = fs.readFileSync(path.join(root, f), 'utf8');
  html = html.replace(/<meta name="robots"[^>]*>\s*/g, '');
  html = html.replace('<meta name="viewport"', '<meta name="robots" content="noindex, nofollow">\n<meta name="viewport"');
  fs.writeFileSync(path.join(dist, f), html);
}
fs.cpSync(path.join(root, 'assets'), path.join(dist, 'assets'), { recursive: true });
fs.writeFileSync(path.join(dist, '.nojekyll'), '');
fs.writeFileSync(path.join(dist, 'robots.txt'), 'User-agent: *\nDisallow: /\n');

const remote = run('git remote get-url origin');
run('git init -q', dist);
run('git checkout -q -b gh-pages', dist);
run('git add -A', dist);
run('git commit -q -m "Deploy ' + new Date().toISOString().slice(0, 16).replace('T', ' ') + '"', dist);
run(`git push -q -f "${remote}" gh-pages`, dist);
fs.rmSync(path.join(dist, '.git'), { recursive: true, force: true });
console.log('gh-pages aktualisiert →', remote);
