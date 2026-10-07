<?php
/**
 * Schweisstechnik Blitz – Theme-Funktionen.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'ABSPATH' ) || exit;

define( 'BLITZ_THEME', true );
define( 'BLITZ_VERSION', '2.0.1' );

$blitz_inc = get_template_directory() . '/inc/';
require $blitz_inc . 'helpers.php';
require $blitz_inc . 'content.php';
require $blitz_inc . 'render.php';
require $blitz_inc . 'legal.php';
require $blitz_inc . 'seo.php';
require $blitz_inc . 'setup.php';
require $blitz_inc . 'customizer.php';
require $blitz_inc . 'form.php';
require $blitz_inc . 'activation.php';
require $blitz_inc . 'admin.php';
require $blitz_inc . 'elementor/module.php';
