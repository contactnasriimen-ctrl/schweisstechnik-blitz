<?php
/**
 * Baut die statische Version (GitHub-Pages-Vorschau / Hosting ohne WordPress) aus denselben
 * Render-Funktionen wie das WordPress-Theme.  Aufruf:  php tools/build-static.php
 * Ergebnis: static/  (index.html, /leistungen/…, /schweisser/…, /impressum/, /datenschutz/, assets/, send.php)
 */

define( 'BLITZ_THEME', true );
define( 'BLITZ_STATIC', true );
define( 'BLITZ_VERSION', '2.0.1' );

$root  = dirname( __DIR__ );
$theme = $root . '/theme/schweisstechnik-blitz';
$out   = $root . '/static';
$site  = 'https://schweisstechnik-blitz.de/';

require $theme . '/inc/helpers.php';
require $theme . '/inc/content.php';
require $theme . '/inc/render.php';
require $theme . '/inc/legal.php';
require $theme . '/inc/seo.php';

function rrmdir( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return;
	}
	foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST ) as $f ) {
		$f->isDir() ? rmdir( $f->getPathname() ) : unlink( $f->getPathname() );
	}
	rmdir( $dir );
}
function rcopy( $src, $dst ) {
	@mkdir( $dst, 0777, true );
	foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $src, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::SELF_FIRST ) as $f ) {
		$target = $dst . '/' . substr( $f->getPathname(), strlen( $src ) + 1 );
		$f->isDir() ? @mkdir( $target, 0777, true ) : copy( $f->getPathname(), $target );
	}
}

/**
 * Eine Seite schreiben.
 *
 * @param string   $path  z. B. '' (Start), 'schweisser/kaufbeuren/'
 * @param array    $meta  title, description, robots
 * @param callable $body  gibt den Inhalt aus
 */
function page( $path, $meta, $body, $landing = null ) {
	global $out, $site;
	$depth                        = substr_count( trim( $path, '/' ), '/' ) + ( '' === trim( $path, '/' ) ? 0 : 1 );
	$GLOBALS['blitz_static_base'] = str_repeat( '../', $depth );
	$GLOBALS['blitz_is_front']    = '' === $path;
	$GLOBALS['blitz_local_ids']   = array();
	$GLOBALS['blitz_schema']      = array();
	$b                            = blitz_base();
	$canon                        = $site . $path;

	ob_start();
	$body();
	$main = ob_get_clean();

	ob_start();
	blitz_render_chrome_top( array( 'loader' => '' === $path ) );
	$top = ob_get_clean();

	ob_start();
	blitz_render_chrome_bottom();
	blitz_schema_add( blitz_business_schema() );
	if ( $landing ) {
		blitz_landing_schema( $landing, $canon );
	}
	blitz_schema_print();
	$bottom = ob_get_clean();

	$h    = 'blitz_h';
	$html = '<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>' . $h( $meta['title'] ) . '</title>
<meta name="description" content="' . $h( $meta['description'] ) . '">
' . ( ! empty( $meta['robots'] ) ? '<meta name="robots" content="' . $h( $meta['robots'] ) . '">' . "\n" : '' ) . '<meta name="theme-color" content="#09090a">
<link rel="canonical" href="' . $h( $canon ) . '">
<meta property="og:type" content="website">
<meta property="og:locale" content="de_DE">
<meta property="og:site_name" content="Schweisstechnik Blitz">
<meta property="og:title" content="' . $h( $meta['title'] ) . '">
<meta property="og:description" content="' . $h( $meta['description'] ) . '">
<meta property="og:url" content="' . $h( $canon ) . '">
<meta property="og:image" content="' . $site . 'assets/img/og.jpg">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="' . $b . 'assets/img/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="' . $b . 'assets/img/apple-touch-icon.png">
<link rel="preload" href="' . $b . 'assets/fonts/archivo.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="' . $b . 'assets/fonts/instrument-serif-italic.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="' . $b . 'assets/css/style.css?v=' . BLITZ_VERSION . '">
<script>document.documentElement.classList.add(\'js\');</script>
</head>
<body class="blitz">
' . $top . '
<main id="inhalt">
' . $main . '
</main>
' . $bottom . '
<script src="' . $b . 'assets/vendor/gsap/gsap.min.js" defer></script>
<script src="' . $b . 'assets/vendor/gsap/ScrollTrigger.min.js" defer></script>
<script src="' . $b . 'assets/vendor/lenis/lenis.min.js" defer></script>
<script src="' . $b . 'assets/js/main.js?v=' . BLITZ_VERSION . '" defer></script>
<script type="module" src="' . $b . 'assets/js/hero.js?v=' . BLITZ_VERSION . '"></script>
</body>
</html>
';
	$file = $out . '/' . ( '' === $path ? '' : $path ) . ( '404' === $path ? '.html' : 'index.html' );
	if ( '404' === $path ) {
		$file = $out . '/404.html';
	}
	@mkdir( dirname( $file ), 0777, true );
	file_put_contents( $file, $html );
	echo '  ' . substr( $file, strlen( $out ) ) . "\n";
}

function legal_page( $type ) {
	return function () use ( $type ) {
		echo '<section class="legal"><div class="legal__inner"><p class="label"><b>§</b> Schweisstechnik Blitz</p><h1>' . blitz_h( blitz_legal_title( $type ) ) . '</h1><div class="prose">' . blitz_legal_html( $type ) . '</div><a class="btn btn--ghost legal__back" href="' . blitz_h( blitz_url( '/' ) ) . '"><span>Zur Startseite</span></a></div></section>';
	};
}

/* ---------- Bauen ---------- */
rrmdir( $out );
mkdir( $out, 0777, true );
echo "Statische Seiten:\n";

$def = blitz_defaults();
page( '', array( 'title' => $def['seo']['title'], 'description' => $def['seo']['description'] ), 'blitz_render_front_default' );

page(
	'leistungen/',
	array( 'title' => 'Leistungen – Elektroschweißen aller Art | Schweisstechnik Blitz', 'description' => 'Tanks und Behälter, Stahlkonstruktionen, Rohre und Bohrrohre, Platten und Bleche, Bohr- und Montagearbeiten – mobil auf der Baustelle und in der Höhe.' ),
	function () {
		blitz_render_overview( 'leistungen' );
	}
);
foreach ( $def['services']['items'] as $s ) {
	$l = blitz_landing( 'leistung', $s['slug'] );
	$m = blitz_landing_meta( $l );
	page(
		'leistungen/' . $s['slug'] . '/',
		array( 'title' => $m['title'], 'description' => $m['description'] ),
		function () use ( $l ) {
			blitz_render_landing( $l );
		},
		$l
	);
}

page(
	'schweisser/',
	array( 'title' => 'Schweißer im Allgäu bis München – Einsatzorte | Schweisstechnik Blitz', 'description' => 'Einsatzorte von Schweisstechnik Blitz: Kaufbeuren, Marktoberdorf, Kempten, Füssen, Buchloe, Mindelheim, Memmingen, Landsberg am Lech und München – mobile Schweißarbeiten vor Ort.' ),
	function () {
		blitz_render_overview( 'orte' );
	}
);
foreach ( $def['area']['towns'] as $t ) {
	$l = blitz_landing( 'ort', $t['slug'] );
	$m = blitz_landing_meta( $l );
	page(
		'schweisser/' . $t['slug'] . '/',
		array( 'title' => $m['title'], 'description' => $m['description'] ),
		function () use ( $l ) {
			blitz_render_landing( $l );
		},
		$l
	);
}

page( 'impressum/', array( 'title' => 'Impressum – Schweisstechnik Blitz', 'description' => 'Impressum von Schweisstechnik Blitz, Inhaber Basel Konbose, Biessenhofen.', 'robots' => 'noindex, follow' ), legal_page( 'impressum' ) );
page( 'datenschutz/', array( 'title' => 'Datenschutzerklärung – Schweisstechnik Blitz', 'description' => 'Datenschutzerklärung von Schweisstechnik Blitz.', 'robots' => 'noindex, follow' ), legal_page( 'datenschutz' ) );
page(
	'404',
	array( 'title' => 'Seite nicht gefunden – Schweisstechnik Blitz', 'description' => 'Diese Seite gibt es nicht.', 'robots' => 'noindex' ),
	function () {
		echo '<section class="legal legal--404"><div class="legal__inner"><p class="label"><b>404</b> Seite nicht gefunden</p><h1>Diese Naht <em>führt ins Leere.</em></h1><p>Die Seite gibt es nicht (mehr).</p><p><a class="btn btn--molten" href="/"><span>Zur Startseite</span></a></p></div></section>';
	}
);

rcopy( $theme . '/assets', $out . '/assets' );
copy( $root . '/send.php', $out . '/send.php' );
copy( $root . '/.htaccess', $out . '/.htaccess' );
@mkdir( $out . '/data', 0777, true );
copy( $root . '/data/.htaccess', $out . '/data/.htaccess' );

/* Sitemap */
$urls = array( '', 'leistungen/', 'schweisser/' );
foreach ( $def['services']['items'] as $s ) {
	$urls[] = 'leistungen/' . $s['slug'] . '/';
}
foreach ( $def['area']['towns'] as $t ) {
	$urls[] = 'schweisser/' . $t['slug'] . '/';
}
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ( $urls as $u ) {
	$xml .= '  <url><loc>' . $site . $u . '</loc><lastmod>' . date( 'Y-m-d' ) . '</lastmod></url>' . "\n";
}
$xml .= "</urlset>\n";
file_put_contents( $out . '/sitemap.xml', $xml );
file_put_contents( $out . '/robots.txt', "User-agent: *\nAllow: /\n\nSitemap: {$site}sitemap.xml\n" );
echo "Fertig → static/\n";
