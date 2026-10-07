# Schweisstechnik Blitz – Website & WordPress-Theme

Cinematic Website für Schweisstechnik Blitz (Inhaber Basel Konbose, Biessenhofen) – als **installierbares WordPress-Theme mit Elementor-Widgets** und als statische Version.

## WordPress-Theme

**Installieren:** `dist/schweisstechnik-blitz-theme.zip` unter *Design → Themes → Theme hochladen* installieren und aktivieren (Elementor empfohlen, kostenlos).
Beim Aktivieren wird automatisch angelegt:

- **Startseite** aus Elementor-Widgets (Hero mit 3D-Szene + Schnellanfrage, Laufband, Leistungen, Werte, Steignaht, Inhaber, Ablauf, Einsatzgebiet, FAQ, Kontakt)
- **Leistungsseiten** `/leistungen/<leistung>/` und **Ortsseiten** `/schweisser/<ort>/` (Kaufbeuren, Marktoberdorf, Buchloe, Mindelheim, Kempten, Füssen, Landsberg am Lech, Memmingen, München) – gleiche Adressen wie die bisherige Website
- **Impressum** `/impressum/` und **Datenschutzerklärung** `/datenschutz/`

Bearbeiten:

- **Elementor:** Kategorie „Schweisstechnik Blitz“ – Texte, Listen (Leistungen, FAQ, Orte, Ablauf …), Farben und Abstände pro Abschnitt. `*Wort*` wird gold-kursiv.
- **Customizer → Schweisstechnik Blitz:** Telefon, WhatsApp, E-Mail, Adresse, USt-ID, Hoster, Farben, Effekte (Startanimation, 3D, Körnung), SEO-Titel/-Beschreibung, Empfänger des Formulars.
- **Design → Blitz Einrichtung:** Übersicht aller Seiten, fehlende Seiten anlegen, Startseite neu aufbauen.
- **Anfragen:** Formulareingänge werden gemailt und unter „Anfragen“ gespeichert (Löschung nach 180 Tagen). Schutz: Honeypot + max. 5 Anfragen/Stunde je IP.

SEO: H1 mit Ortsbezug, Title/Description je Seite (oder Yoast/Rank Math), Schema.org (HomeAndConstructionBusiness, Service, BreadcrumbList, FAQPage), interne Verlinkung über Footer, Karte und Hashtags, WordPress-Sitemap `/wp-sitemap.xml`.

## Projektstruktur

- `theme/schweisstechnik-blitz/` – **Quelle** (Theme). `inc/content.php` = Standardtexte, `inc/render.php` = Markup aller Abschnitte, `inc/elementor/` = Widgets, `assets/` = CSS/JS/3D/Schriften.
- `tools/build-static.php` – erzeugt `static/` (gleiche Seiten ohne WordPress, Formular über `send.php`).
- `tools/build-zip.php` – erzeugt `dist/schweisstechnik-blitz-theme.zip`.
- `tools/deploy.mjs` – Vorschau auf GitHub Pages (noindex).

**Lokal:** `start.bat` → baut `static/` und startet http://localhost:8091 (Formular-Mails landen in `static/data/outbox/`).
**Tests:** `node tools/e2e.mjs <ordner>` (statisch, :8091) · `node tools/e2e-wp.mjs <ordner>` (WordPress + Elementor, :8092).

Website powered by [over-tech.de](https://over-tech.de/)
