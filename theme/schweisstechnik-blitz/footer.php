<?php
/**
 * Fußbereich.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'ABSPATH' ) || exit;
?>
</main>
<?php
$blitz_pro_footer = function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' );
if ( ! $blitz_pro_footer ) {
	blitz_render_chrome_bottom();
}
wp_footer();
?>
</body>
</html>
