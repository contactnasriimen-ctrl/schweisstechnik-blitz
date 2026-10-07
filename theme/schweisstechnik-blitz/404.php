<?php
/**
 * Seite nicht gefunden.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="legal legal--404">
	<div class="legal__inner">
		<p class="label"><b>404</b> Seite nicht gefunden</p>
		<h1>Diese Naht <em>führt ins Leere.</em></h1>
		<p>Die Seite gibt es nicht (mehr). Vielleicht hilft einer dieser Wege:</p>
		<p><a class="btn btn--molten" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span>Zur Startseite</span></a> <a class="btn btn--ghost" href="<?php echo esc_url( blitz_tel() ); ?>"><span><?php echo esc_html( blitz_company( 'phone' ) ); ?> anrufen</span></a></p>
	</div>
</section>
<?php
get_footer();
