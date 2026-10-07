<?php
/**
 * Kopfbereich.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
wp_body_open();
$blitz_pro_header = function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' );
if ( ! $blitz_pro_header ) {
	blitz_render_chrome_top( array( 'loader' => blitz_show_loader() ) );
}
?>
<main id="inhalt" class="site-main">
