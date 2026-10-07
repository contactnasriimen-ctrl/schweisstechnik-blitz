<?php
/**
 * Customizer: Firmendaten, Darstellung, SEO, Formular.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'customize_register',
	function ( $wp ) {
		$c = blitz_defaults()['company'];

		$wp->add_panel( 'blitz', array( 'title' => 'Schweisstechnik Blitz', 'priority' => 20 ) );

		/* Firmendaten */
		$wp->add_section( 'blitz_company', array( 'title' => 'Firmendaten & Kontakt', 'panel' => 'blitz' ) );
		$fields = array(
			'name'         => 'Firmenname',
			'owner'        => 'Inhaber',
			'phone'        => 'Telefon (Anzeige)',
			'phone_intl'   => 'Telefon international (für Links, z. B. +4915164325150)',
			'phone_pretty' => 'Telefon international (Anzeige)',
			'whatsapp'     => 'WhatsApp-Nummer (nur Ziffern, mit Ländervorwahl)',
			'email'        => 'E-Mail',
			'street'       => 'Straße und Hausnummer',
			'zip'          => 'PLZ',
			'city'         => 'Ort',
			'vat'          => 'USt-IdNr.',
			'hoster'       => 'Hoster (für die Datenschutzerklärung)',
			'sister_label' => 'Footer: Überschrift Partnerseite',
			'sister_name'  => 'Footer: Name Partnerseite',
			'sister_url'   => 'Footer: Link Partnerseite',
			'credit_name'  => 'Footer: Website erstellt von',
			'credit_url'   => 'Footer: Link Webagentur',
		);
		foreach ( $fields as $key => $label ) {
			$wp->add_setting(
				'blitz_' . $key,
				array(
					'default'           => $c[ $key ],
					'sanitize_callback' => false !== strpos( $key, 'url' ) ? 'esc_url_raw' : ( 'email' === $key ? 'sanitize_email' : 'sanitize_text_field' ),
				)
			);
			$wp->add_control( 'blitz_' . $key, array( 'label' => $label, 'section' => 'blitz_company', 'type' => 'email' === $key ? 'email' : ( false !== strpos( $key, 'url' ) ? 'url' : 'text' ) ) );
		}

		/* Darstellung */
		$wp->add_section( 'blitz_look', array( 'title' => 'Darstellung & Effekte', 'panel' => 'blitz' ) );
		$toggles = array(
			'loader' => 'Startanimation („Lichtbogen zünden“) auf der Startseite',
			'webgl'  => '3D-Schweißszene im Hero (three.js)',
			'grain'  => 'Film-Körnung über der Seite',
		);
		foreach ( $toggles as $key => $label ) {
			$wp->add_setting( 'blitz_' . $key, array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
			$wp->add_control( 'blitz_' . $key, array( 'label' => $label, 'section' => 'blitz_look', 'type' => 'checkbox' ) );
		}
		$colors = array(
			'color_accent' => array( 'Akzentfarbe (Glut)', '#ff6a1a' ),
			'color_gold'   => array( 'Goldton (kursive Wörter)', '#e9bd7c' ),
		);
		foreach ( $colors as $key => $c2 ) {
			$wp->add_setting( 'blitz_' . $key, array( 'default' => $c2[1], 'sanitize_callback' => 'sanitize_hex_color' ) );
			$wp->add_control( new WP_Customize_Color_Control( $wp, 'blitz_' . $key, array( 'label' => $c2[0], 'section' => 'blitz_look' ) ) );
		}
		$wp->add_setting( 'blitz_footer_kicker', array( 'default' => blitz_defaults()['footer']['kicker'], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'blitz_footer_kicker', array( 'label' => 'Footer: Satz über der Telefonnummer', 'section' => 'blitz_look', 'type' => 'text' ) );

		/* SEO */
		$wp->add_section( 'blitz_seo', array( 'title' => 'SEO (Startseite)', 'panel' => 'blitz', 'description' => 'Wird ignoriert, wenn Yoast SEO oder Rank Math aktiv ist.' ) );
		$wp->add_setting( 'blitz_seo_title', array( 'default' => blitz_defaults()['seo']['title'], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'blitz_seo_title', array( 'label' => 'Seitentitel (ca. 55–65 Zeichen)', 'section' => 'blitz_seo', 'type' => 'text' ) );
		$wp->add_setting( 'blitz_seo_description', array( 'default' => blitz_defaults()['seo']['description'], 'sanitize_callback' => 'sanitize_textarea_field' ) );
		$wp->add_control( 'blitz_seo_description', array( 'label' => 'Meta-Beschreibung (ca. 150–160 Zeichen)', 'section' => 'blitz_seo', 'type' => 'textarea' ) );

		/* Formular */
		$wp->add_section( 'blitz_form', array( 'title' => 'Anfrageformular', 'panel' => 'blitz' ) );
		$wp->add_setting( 'blitz_form_email', array( 'default' => '', 'sanitize_callback' => 'sanitize_email' ) );
		$wp->add_control( 'blitz_form_email', array( 'label' => 'Anfragen senden an (leer = Firmen-E-Mail)', 'section' => 'blitz_form', 'type' => 'email' ) );
		$wp->add_setting( 'blitz_form_store', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
		$wp->add_control( 'blitz_form_store', array( 'label' => 'Anfragen zusätzlich unter „Anfragen“ im Admin speichern (180 Tage)', 'section' => 'blitz_form', 'type' => 'checkbox' ) );
	}
);
