# Schweisstechnik Blitz – Website

Cinematic One-Pager für Schweisstechnik Blitz (Inhaber Basel Konbose, Biessenhofen).

- `index.html`, `impressum.html`, `datenschutz.html` – Seiten
- `assets/js/hero.js` – 3D-Hero (three.js): Steignaht mit Funkenflug und Anlauffarben
- `assets/js/main.js` – Animationen (GSAP, ScrollTrigger, Lenis), Schweißpositionen, Formular
- `send.php` – Formularversand per E-Mail (PHP-Hosting nötig; ohne PHP bietet das Formular WhatsApp/E-Mail an)
- Schriften und Bibliotheken liegen lokal – keine Verbindungen zu Google Fonts oder CDNs.

**Lokal starten:** `start.bat` → http://localhost:8091 (Formular-Mails landen in `data/outbox/`).
**Vorschau veröffentlichen:** `node tools/deploy.mjs` (GitHub Pages, Branch `gh-pages`, noindex).
**Tests:** `node tools/e2e.mjs <ordner>` bei laufendem Server.

Website powered by [over-tech.de](https://over-tech.de/)
