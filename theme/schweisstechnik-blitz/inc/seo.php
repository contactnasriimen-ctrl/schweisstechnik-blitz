<?php
/**
 * SEO: Titel, Meta-Description, Open Graph und strukturierte Daten (LocalBusiness, Service, Breadcrumbs, FAQ).
 * Ist Yoast SEO oder Rank Math aktiv, übernehmen diese Titel/Description/OG – das Schema bleibt.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'BLITZ_THEME' ) || exit;

/** Firma als LocalBusiness (HomeAndConstructionBusiness) */
function blitz_business_schema() {
	$c     = blitz_company();
	$def   = blitz_defaults();
	$home  = blitz_static() ? 'https://schweisstechnik-blitz.de/' : home_url( '/' );
	$areas = array();
	foreach ( $def['area']['towns'] as $t ) {
		$areas[] = array( '@type' => 'City', 'name' => $t['name'] );
	}
	$areas[] = array( '@type' => 'AdministrativeArea', 'name' => 'Ostallgäu' );
	$areas[] = array( '@type' => 'AdministrativeArea', 'name' => 'Allgäu' );
	$offers  = array();
	foreach ( $def['services']['items'] as $s ) {
		$offers[] = array(
			'@type'       => 'Offer',
			'itemOffered' => array(
				'@type'       => 'Service',
				'name'        => $s['title'],
				'description' => $s['text'],
				'url'         => blitz_static() ? $home . 'leistungen/' . $s['slug'] . '/' : blitz_page_url( 'leistung', $s['slug'] ),
			),
		);
	}
	return array(
		'@type'            => 'HomeAndConstructionBusiness',
		'@id'              => $home . '#firma',
		'name'             => $c['name'],
		'url'              => $home,
		'description'      => $def['seo']['description'],
		'telephone'        => $c['phone_pretty'],
		'email'            => $c['email'],
		'image'            => blitz_static() ? $home . 'assets/img/og.jpg' : blitz_asset( 'img/og.jpg' ),
		'logo'             => blitz_static() ? $home . 'assets/img/apple-touch-icon.png' : blitz_asset( 'img/apple-touch-icon.png' ),
		'founder'          => array( '@type' => 'Person', 'name' => $c['owner'] ),
		'address'          => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $c['street'],
			'postalCode'      => $c['zip'],
			'addressLocality' => $c['city'],
			'addressRegion'   => $c['state'],
			'addressCountry'  => 'DE',
		),
		'geo'              => array( '@type' => 'GeoCoordinates', 'latitude' => $c['lat'], 'longitude' => $c['lon'] ),
		'areaServed'       => $areas,
		'knowsAbout'       => array( 'Elektrodenschweißen', 'Lichtbogenhandschweißen', 'Steignaht', 'Rohrschweißen', 'Tankschweißen', 'Stahlkonstruktionen', 'Montagearbeiten', 'Baustellenschweißen' ),
		'keywords'         => implode( ', ', array_map( function ( $t ) { return ltrim( $t, '#' ); }, $def['seo']['hashtags'] ) ),
		'hasOfferCatalog'  => array( '@type' => 'OfferCatalog', 'name' => 'Schweißarbeiten', 'itemListElement' => $offers ),
	);
}

/** Schema & Meta für eine Unterseite */
function blitz_landing_schema( $landing, $url ) {
	$c   = blitz_company();
	$m   = blitz_landing_meta( $landing );
	$it  = $landing['item'];
	$biz = array( '@id' => ( blitz_static() ? 'https://schweisstechnik-blitz.de/' : home_url( '/' ) ) . '#firma' );
	$svc = array(
		'@type'       => 'Service',
		'name'        => 'ort' === $landing['type'] ? 'Schweißarbeiten in ' . $it['name'] : $it['title'],
		'serviceType' => 'Schweißarbeiten',
		'description' => $m['description'],
		'provider'    => $biz,
		'url'         => $url,
		'areaServed'  => 'ort' === $landing['type'] ? array( '@type' => 'City', 'name' => $it['name'] ) : array( '@type' => 'AdministrativeArea', 'name' => 'Allgäu' ),
	);
	blitz_schema_add( $svc );
	blitz_schema_add(
		array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Startseite', 'item' => blitz_static() ? 'https://schweisstechnik-blitz.de/' : home_url( '/' ) ),
				array( '@type' => 'ListItem', 'position' => 2, 'name' => $m['crumb'][0], 'item' => blitz_static() ? 'https://schweisstechnik-blitz.de/' . ( 'ort' === $landing['type'] ? 'schweisser/' : 'leistungen/' ) : $m['crumb'][1] ),
				array( '@type' => 'ListItem', 'position' => 3, 'name' => 'ort' === $landing['type'] ? $it['name'] : $it['title'], 'item' => $url ),
			),
		)
	);
}

/* ==========================================================================
   WordPress-Teil
   ========================================================================== */

if ( ! blitz_static() ) {

	function blitz_seo_plugin_active() {
		return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' );
	}

	/** Landing-Daten der aktuellen Seite (über Meta _blitz_landing = "ort:slug" / "leistung:slug") */
	function blitz_current_landing( $post_id = null ) {
		$post_id = $post_id ? $post_id : get_queried_object_id();
		$key     = $post_id ? get_post_meta( $post_id, '_blitz_key', true ) : '';
		if ( $key && preg_match( '/^(ort|leistung):(.+)$/', $key, $m ) ) {
			return blitz_landing( $m[1], $m[2] );
		}
		return null;
	}

	function blitz_current_meta() {
		$def = blitz_defaults();
		if ( is_front_page() ) {
			return array(
				'title'       => blitz_opt( 'seo_title', $def['seo']['title'] ),
				'description' => blitz_opt( 'seo_description', $def['seo']['description'] ),
			);
		}
		if ( is_singular() ) {
			$landing = blitz_current_landing();
			if ( $landing ) {
				$m = blitz_landing_meta( $landing );
				return array( 'title' => $m['title'], 'description' => $m['description'] );
			}
			$key = get_post_meta( get_queried_object_id(), '_blitz_key', true );
			if ( 'orte' === $key ) {
				return array( 'title' => 'Schweißer im Allgäu bis München – Einsatzorte | ' . blitz_company( 'name' ), 'description' => 'Einsatzorte von Schweisstechnik Blitz: Kaufbeuren, Marktoberdorf, Kempten, Füssen, Buchloe, Mindelheim, Memmingen, Landsberg am Lech und München – mobile Schweißarbeiten vor Ort.' );
			}
			if ( 'leistungen' === $key ) {
				return array( 'title' => 'Leistungen – Elektroschweißen aller Art | ' . blitz_company( 'name' ), 'description' => 'Tanks und Behälter, Stahlkonstruktionen, Rohre und Bohrrohre, Platten und Bleche, Bohr- und Montagearbeiten – mobil auf der Baustelle und in der Höhe.' );
			}
			$excerpt = get_the_excerpt( get_queried_object_id() );
			return array( 'title' => '', 'description' => $excerpt ? wp_trim_words( $excerpt, 28, '…' ) : $def['seo']['description'] );
		}
		return array( 'title' => '', 'description' => $def['seo']['description'] );
	}

	add_filter(
		'pre_get_document_title',
		function ( $title ) {
			if ( blitz_seo_plugin_active() ) {
				return $title;
			}
			$m = blitz_current_meta();
			return $m['title'] ? $m['title'] : $title;
		},
		20
	);

	add_action(
		'wp_head',
		function () {
			$robots_noindex = is_singular() && in_array( get_post_meta( get_queried_object_id(), '_blitz_key', true ), array( 'impressum', 'datenschutz' ), true );
			if ( $robots_noindex ) {
				echo '<meta name="robots" content="noindex, follow">' . "\n";
			}
			if ( blitz_seo_plugin_active() ) {
				return;
			}
			$m     = blitz_current_meta();
			$title = $m['title'] ? $m['title'] : wp_get_document_title();
			$url   = is_front_page() ? home_url( '/' ) : ( is_singular() ? get_permalink() : home_url( add_query_arg( array() ) ) );
			echo '<meta name="description" content="' . esc_attr( $m['description'] ) . '">' . "\n";
			echo '<meta property="og:type" content="website">' . "\n";
			echo '<meta property="og:locale" content="de_DE">' . "\n";
			echo '<meta property="og:site_name" content="' . esc_attr( blitz_company( 'name' ) ) . '">' . "\n";
			echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
			echo '<meta property="og:description" content="' . esc_attr( $m['description'] ) . '">' . "\n";
			echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
			echo '<meta property="og:image" content="' . esc_url( blitz_asset( 'img/og.jpg' ) ) . '">' . "\n";
			echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		},
		2
	);

	/* Schema: Firma auf jeder Seite, Service + Breadcrumbs auf Unterseiten; FAQ kommt aus dem FAQ-Abschnitt */
	add_action(
		'wp_footer',
		function () {
			blitz_schema_add( blitz_business_schema() );
			if ( is_singular() ) {
				$landing = blitz_current_landing();
				if ( $landing ) {
					blitz_landing_schema( $landing, get_permalink() );
				}
			}
			blitz_schema_print();
		},
		50
	);
}
