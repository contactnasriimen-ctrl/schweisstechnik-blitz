<?php
/**
 * Einrichtung beim Aktivieren: Startseite (mit Elementor-Widgets), Leistungs- und Ortsseiten
 * unter den bisherigen Adressen (/leistungen/…, /schweisser/…), Impressum und Datenschutz.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'ABSPATH' ) || exit;

/** Seiten-ID über den internen Schlüssel (_blitz_key) finden */
function blitz_find_page( $key ) {
	static $map = null;
	if ( null === $map ) {
		$map   = array();
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => 200,
				'meta_key'       => '_blitz_key', // phpcs:ignore WordPress.DB.SlowDBQuery
				'fields'         => 'ids',
			)
		);
		foreach ( $pages as $id ) {
			$map[ get_post_meta( $id, '_blitz_key', true ) ] = $id;
		}
	}
	if ( false === $key ) {
		$map = null;
		return 0;
	}
	return isset( $map[ $key ] ) && 'publish' === get_post_status( $map[ $key ] ) ? $map[ $key ] : 0;
}

function blitz_ensure_page( $key, $title, $slug, $parent = 0, $content = '' ) {
	$existing = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'meta_key'       => '_blitz_key', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}
	// Seite unter derselben Adresse gibt es schon (z. B. von der bisherigen Website) → übernehmen statt doppelt anlegen
	$path = $parent ? get_page_uri( $parent ) . '/' . $slug : $slug;
	$old  = get_page_by_path( $path, OBJECT, 'page' );
	if ( $old && 'trash' !== $old->post_status ) {
		update_post_meta( $old->ID, '_blitz_key', $key );
		if ( 'publish' !== $old->post_status ) {
			wp_update_post( array( 'ID' => $old->ID, 'post_status' => 'publish' ) );
		}
		blitz_find_page( false );
		return (int) $old->ID;
	}
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_parent'  => $parent,
			'post_content' => $content,
			'meta_input'   => array( '_blitz_key' => $key ),
		)
	);
	blitz_find_page( false );
	return is_wp_error( $id ) ? 0 : (int) $id;
}

/**
 * Inhalte anlegen. Vorhandene Seiten werden nie überschrieben.
 *
 * @param bool $rebuild_front Startseite neu mit Elementor-Widgets aufbauen.
 */
function blitz_install( $rebuild_front = false ) {
	$def = blitz_defaults();

	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}

	$front = blitz_ensure_page( 'front', 'Startseite', 'startseite' );
	$leist = blitz_ensure_page( 'leistungen', 'Leistungen', 'leistungen' );
	foreach ( $def['services']['items'] as $s ) {
		blitz_ensure_page( 'leistung:' . $s['slug'], $s['title'], $s['slug'], $leist, blitz_landing_html( blitz_landing( 'leistung', $s['slug'] ) ) );
	}
	$orte = blitz_ensure_page( 'orte', 'Einsatzorte', 'schweisser' );
	foreach ( $def['area']['towns'] as $t ) {
		blitz_ensure_page( 'ort:' . $t['slug'], 'Schweißer in ' . $t['name'], $t['slug'], $orte, blitz_landing_html( blitz_landing( 'ort', $t['slug'] ) ) );
	}
	blitz_ensure_page( 'impressum', 'Impressum', 'impressum', 0, blitz_legal_html( 'impressum' ) );
	blitz_ensure_page( 'datenschutz', 'Datenschutzerklärung', 'datenschutz', 0, blitz_legal_html( 'datenschutz' ) );

	if ( $front ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front );
		if ( did_action( 'elementor/loaded' ) && ( $rebuild_front || ! get_post_meta( $front, '_elementor_data', true ) ) ) {
			blitz_elementor_build_front( $front );
		}
	}
	flush_rewrite_rules();
	update_option( 'blitz_installed', BLITZ_VERSION );
	return $front;
}

add_action(
	'after_switch_theme',
	function () {
		blitz_install( false );
	}
);

/* Wird Elementor erst nach dem Theme aktiviert: Startseite einmalig mit Widgets aufbauen */
add_action(
	'admin_init',
	function () {
		if ( ! did_action( 'elementor/loaded' ) || get_option( 'blitz_elementor_front_done' ) ) {
			return;
		}
		$front = blitz_find_page( 'front' );
		if ( $front && ! get_post_meta( $front, '_elementor_data', true ) && (int) get_option( 'page_on_front' ) === $front ) {
			blitz_elementor_build_front( $front );
		}
		update_option( 'blitz_elementor_front_done', 1 );
	}
);
