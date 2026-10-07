<?php
/**
 * Elementor-Integration: Kategorie, Widgets, Aufbau der Startseite.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'ABSPATH' ) || exit;

/** Reihenfolge der Widgets auf der Startseite */
function blitz_front_widgets() {
	return array( 'blitz-hero', 'blitz-marquee', 'blitz-services', 'blitz-values', 'blitz-positions', 'blitz-owner', 'blitz-process', 'blitz-area', 'blitz-faq', 'blitz-contact' );
}

add_action(
	'elementor/elements/categories_registered',
	function ( $manager ) {
		$manager->add_category(
			'blitz',
			array(
				'title' => 'Schweisstechnik Blitz',
				'icon'  => 'eicon-flash',
			)
		);
	}
);

add_action(
	'elementor/widgets/register',
	function ( $widgets ) {
		require_once __DIR__ . '/widgets.php';
		foreach ( array( 'Hero', 'Marquee', 'Services', 'Values', 'Positions', 'Owner', 'Process', 'Area', 'Faq', 'Contact', 'Keywords' ) as $cls ) {
			$name = 'Blitz_W_' . $cls;
			$widgets->register( new $name() );
		}
	}
);

/* Theme-Assets auch im Elementor-Editor (Vorschau) registrieren */
add_action(
	'elementor/preview/enqueue_styles',
	function () {
		wp_enqueue_style( 'blitz-style' );
	}
);

function blitz_eid() {
	return substr( md5( uniqid( '', true ) . wp_rand() ), 0, 7 );
}

/** Startseite als Elementor-Layout mit allen Blitz-Widgets speichern (Standardinhalte kommen aus den Widgets) */
function blitz_elementor_build_front( $post_id ) {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return false;
	}
	$plugin    = \Elementor\Plugin::$instance;
	$container = isset( $plugin->experiments ) && $plugin->experiments->is_feature_active( 'container' );
	$data      = array();
	foreach ( blitz_front_widgets() as $w ) {
		$widget = array(
			'id'         => blitz_eid(),
			'elType'     => 'widget',
			'widgetType' => $w,
			'settings'   => array(),
			'elements'   => array(),
		);
		if ( $container ) {
			$data[] = array(
				'id'       => blitz_eid(),
				'elType'   => 'container',
				'isInner'  => false,
				'settings' => array(
					'content_width' => 'full',
					'padding'       => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ),
					'flex_gap'      => array( 'unit' => 'px', 'size' => 0, 'column' => '0', 'row' => '0', 'isLinked' => true ),
					'css_classes'   => 'blitz-con',
				),
				'elements' => array( $widget ),
			);
		} else {
			$data[] = array(
				'id'       => blitz_eid(),
				'elType'   => 'section',
				'isInner'  => false,
				'settings' => array( 'layout' => 'full_width', 'gap' => 'no', 'css_classes' => 'blitz-con' ),
				'elements' => array(
					array(
						'id'       => blitz_eid(),
						'elType'   => 'column',
						'isInner'  => false,
						'settings' => array( '_column_size' => 100 ),
						'elements' => array( $widget ),
					),
				),
			);
		}
	}
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $post_id, '_wp_page_template', 'elementor_header_footer' );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
	delete_post_meta( $post_id, '_elementor_css' );
	if ( isset( $plugin->files_manager ) ) {
		$plugin->files_manager->clear_cache();
	}
	return true;
}
