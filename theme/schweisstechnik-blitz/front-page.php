<?php
/**
 * Startseite: Elementor-Layout, falls vorhanden – sonst alle Abschnitte mit Standardinhalten.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'ABSPATH' ) || exit;

get_header();
$blitz_id = get_queried_object_id();
if ( $blitz_id && blitz_built_with_elementor( $blitz_id ) ) {
	while ( have_posts() ) {
		the_post();
		the_content();
	}
} else {
	blitz_render_front_default();
}
get_footer();
