<?php
/**
 * Hilfsfunktionen, die sowohl in WordPress als auch im statischen Export (tools/build-static.php) laufen.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'BLITZ_THEME' ) || exit;

/** Läuft der statische Export? */
function blitz_static() {
	return defined( 'BLITZ_STATIC' ) && BLITZ_STATIC;
}

function blitz_h( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' );
}

/** Text escapen, *Wort* → <em>Wort</em> */
function blitz_em( $s ) {
	return preg_replace( '/\*(.+?)\*/u', '<em>$1</em>', blitz_h( $s ) );
}

/** Sternchen entfernen (für Titel/aria-label) */
function blitz_plain( $s ) {
	return trim( preg_replace( '/\s+/u', ' ', str_replace( '*', '', (string) $s ) ) );
}

/** Erlaubtes HTML aus Redaktionsfeldern */
function blitz_kses( $html ) {
	return function_exists( 'wp_kses_post' ) ? wp_kses_post( $html ) : (string) $html;
}

/** Relativer Basis-Pfad im statischen Export ('' auf der Startseite, '../../' in Unterseiten) */
function blitz_base() {
	return isset( $GLOBALS['blitz_static_base'] ) ? $GLOBALS['blitz_static_base'] : '';
}

function blitz_asset( $path ) {
	$path = ltrim( $path, '/' );
	if ( blitz_static() ) {
		return blitz_base() . 'assets/' . $path;
	}
	return get_template_directory_uri() . '/assets/' . $path;
}

function blitz_asset_version() {
	if ( blitz_static() || ! function_exists( 'wp_get_theme' ) ) {
		return BLITZ_VERSION;
	}
	return wp_get_theme( get_template() )->get( 'Version' );
}

function blitz_url( $path = '/' ) {
	if ( blitz_static() ) {
		$p = ltrim( $path, '/' );
		$b = blitz_base();
		return ( '' === $p ) ? ( '' === $b ? './' : $b ) : $b . $p;
	}
	return home_url( $path );
}

function blitz_is_front() {
	if ( blitz_static() ) {
		return ! empty( $GLOBALS['blitz_is_front'] );
	}
	return is_front_page();
}

/** Abschnitt auf der aktuellen Seite? Dann Anker lokal, sonst Startseite + Anker */
function blitz_anchor( $id ) {
	$local = isset( $GLOBALS['blitz_local_ids'] ) ? (array) $GLOBALS['blitz_local_ids'] : array();
	if ( blitz_is_front() || in_array( $id, $local, true ) ) {
		return '#' . $id;
	}
	return blitz_url( '/' ) . '#' . $id;
}

/** Firmendaten (Customizer überschreibt Standardwerte) */
function blitz_company( $key = null ) {
	static $cache = null;
	if ( null === $cache || blitz_static() ) {
		$c = blitz_defaults()['company'];
		if ( ! blitz_static() && function_exists( 'get_theme_mod' ) ) {
			foreach ( $c as $k => $v ) {
				if ( is_float( $v ) ) {
					continue;
				}
				$mod = get_theme_mod( 'blitz_' . $k, null );
				if ( null !== $mod && '' !== $mod ) {
					$c[ $k ] = $mod;
				}
			}
		}
		$cache = $c;
	}
	return null === $key ? $cache : ( isset( $cache[ $key ] ) ? $cache[ $key ] : '' );
}

/** Theme-Option (Customizer-Schalter) */
function blitz_opt( $key, $default = true ) {
	if ( blitz_static() || ! function_exists( 'get_theme_mod' ) ) {
		return $default;
	}
	return get_theme_mod( 'blitz_' . $key, $default );
}

function blitz_tel() {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', blitz_company( 'phone_intl' ) );
}

function blitz_wa( $text = '' ) {
	$url = 'https://wa.me/' . preg_replace( '/\D/', '', blitz_company( 'whatsapp' ) );
	return $text ? $url . '?text=' . rawurlencode( $text ) : $url;
}

/** Ziel des Formulars: admin-ajax in WordPress, send.php im statischen Export */
function blitz_form_action() {
	return blitz_static() ? blitz_base() . 'send.php' : admin_url( 'admin-ajax.php' );
}

function blitz_form_hidden( $source ) {
	$out = '<input type="hidden" name="quelle" value="' . blitz_h( $source ) . '">';
	if ( ! blitz_static() ) {
		$out .= '<input type="hidden" name="action" value="blitz_anfrage">';
	}
	return $out;
}

/** URL einer Leistungs-/Orts-/Rechtsseite (in WP über die angelegte Seite, sonst Pfad) */
function blitz_page_url( $type, $slug = '' ) {
	$paths = array(
		'leistung'    => '/leistungen/' . $slug . '/',
		'ort'         => '/schweisser/' . $slug . '/',
		'leistungen'  => '/leistungen/',
		'orte'        => '/schweisser/',
		'impressum'   => '/impressum/',
		'datenschutz' => '/datenschutz/',
	);
	$path = isset( $paths[ $type ] ) ? $paths[ $type ] : '/';
	if ( ! blitz_static() && function_exists( 'blitz_find_page' ) ) {
		$id = blitz_find_page( $slug ? $type . ':' . $slug : $type );
		if ( $id ) {
			return get_permalink( $id );
		}
	}
	return blitz_url( $path );
}

/** Position eines Ortes auf der Karte (km, Norden oben) relativ zum Firmensitz */
function blitz_map_point( $lat, $lon ) {
	$c  = blitz_defaults()['company'];
	$kx = 111.32 * cos( deg2rad( $c['lat'] ) );
	$x  = ( (float) $lon - $c['lon'] ) * $kx;
	$y  = -( (float) $lat - $c['lat'] ) * 111.2;
	return array( round( $x, 1 ), round( $y, 1 ) );
}

/** Himmelsrichtung (deutsch) vom Firmensitz aus */
function blitz_direction( $lat, $lon ) {
	list( $x, $y ) = blitz_map_point( $lat, $lon );
	$deg  = ( rad2deg( atan2( $x, -$y ) ) + 360 ) % 360;
	$dirs = array( 'nördlich', 'nordöstlich', 'östlich', 'südöstlich', 'südlich', 'südwestlich', 'westlich', 'nordwestlich' );
	return $dirs[ (int) round( $deg / 45 ) % 8 ];
}

/** „#A #B“ oder Liste → Array von Hashtags */
function blitz_tags( $tags ) {
	if ( is_array( $tags ) ) {
		return array_values( array_filter( array_map( 'trim', $tags ) ) );
	}
	preg_match_all( '/#?[^\s#,]+/u', (string) $tags, $m );
	return array_map(
		function ( $t ) {
			return '#' . ltrim( $t, '#' );
		},
		$m[0]
	);
}

/** Zeilenweise Liste aus Textarea */
function blitz_lines( $text ) {
	if ( is_array( $text ) ) {
		return $text;
	}
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $text ) ) ) );
}

/** Inline-Icons */
function blitz_icon( $name ) {
	$icons = array(
		'tank'     => '<svg class="card__icon" viewBox="0 0 48 48" aria-hidden="true"><rect x="6" y="13" width="36" height="19" rx="9.5"/><path d="M15 13v19M33 13v19"/><path class="hot" d="M24 13v19"/><path d="M12 32l-2 8M36 32l2 8M24 13V8h5"/></svg>',
		'truss'    => '<svg class="card__icon" viewBox="0 0 48 48" aria-hidden="true"><path d="M6 9h36M6 39h36M9 9v30M39 9v30"/><path d="M9 9l15 15 15-15M9 39l15-15 15 15" stroke-opacity=".55"/><circle class="hot" cx="24" cy="24" r="2.2"/></svg>',
		'pipe'     => '<svg class="card__icon" viewBox="0 0 48 48" aria-hidden="true"><path d="M4 16h17v16H4M44 16H27v16h17"/><ellipse cx="4" cy="24" rx="2" ry="8"/><path class="hot" d="M24 13v22"/><path d="M21 16v16M27 16v16"/></svg>',
		'plate'    => '<svg class="card__icon" viewBox="0 0 48 48" aria-hidden="true"><path d="M5 34h38v6H5zM21 34V8h6v26"/><path class="hot" d="M27 34l6-6v6zM21 34l-6-6v6z"/></svg>',
		'drill'    => '<svg class="card__icon" viewBox="0 0 48 48" aria-hidden="true"><path d="M9 7h14v9H9zM16 16v4M13 20h6l-1 17-2 3-2-3z"/><path d="M14 26l4-2M14 31l4-2"/><circle cx="34" cy="30" r="8"/><path class="hot" d="M34 25v10M29 30h10"/></svg>',
		'scaffold' => '<svg class="card__icon" viewBox="0 0 48 48" aria-hidden="true"><path d="M8 44V12M28 44V12M8 20h20M8 32h20M8 44h20"/><path d="M8 20l20 12M8 32l20 12" stroke-opacity=".55"/><path d="M28 12h12"/><path class="hot" d="M38 3l-4 6h5l-4 6"/></svg>',
		'phone'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z"/></svg>',
		'arrow'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
		'whatsapp' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.15l-.3-.18-3 .78.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.25-.12-1.46-.72-1.69-.8-.23-.08-.39-.12-.56.12-.16.25-.64.8-.78.97-.15.16-.29.18-.54.06a6.7 6.7 0 0 1-3.3-2.9c-.25-.43.25-.4.71-1.33.08-.16.04-.3-.02-.43l-.76-1.83c-.2-.48-.4-.41-.56-.42h-.47a.9.9 0 0 0-.66.31 2.8 2.8 0 0 0-.86 2.06 4.8 4.8 0 0 0 1 2.55 11 11 0 0 0 4.2 3.7c1.56.68 2.18.73 2.96.62.48-.07 1.46-.6 1.67-1.18.2-.58.2-1.07.14-1.18-.06-.1-.22-.16-.47-.28z"/></svg>',
		'logo'     => '<svg class="brand__mark" viewBox="0 0 40 40" aria-hidden="true"><defs><linearGradient id="bm" x1="0" y1="1" x2="0" y2="0"><stop offset="0" stop-color="#ff5a10"/><stop offset=".6" stop-color="#ffb347"/><stop offset="1" stop-color="#fff1d0"/></linearGradient></defs><rect x="1" y="1" width="38" height="38" rx="11" fill="none" stroke="currentColor" stroke-opacity=".22"/><path d="M23.2 5.5 11.5 22.2h7.6l-2.6 12.3 12-17h-7.8z" fill="url(#bm)"/></svg>',
	);
	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

/** JSON-LD sammeln und am Seitenende ausgeben */
function blitz_schema_add( $node ) {
	$GLOBALS['blitz_schema'][] = $node;
}

function blitz_schema_print() {
	if ( empty( $GLOBALS['blitz_schema'] ) ) {
		return;
	}
	$graph = array(
		'@context' => 'https://schema.org',
		'@graph'   => $GLOBALS['blitz_schema'],
	);
	echo '<script type="application/ld+json">' . json_encode( $graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "</script>\n";
	$GLOBALS['blitz_schema'] = array();
}
