<?php
/**
 * Seiten: Orts-/Leistungsseiten, Übersichten, Elementor-Seiten und Textseiten (Impressum, Datenschutz …).
 *
 * @package SchweisstechnikBlitz
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	$blitz_key     = get_post_meta( get_the_ID(), '_blitz_key', true );
	$blitz_landing = blitz_current_landing( get_the_ID() );

	if ( $blitz_landing ) {
		blitz_render_landing( $blitz_landing, 'the_content' );
	} elseif ( in_array( $blitz_key, array( 'leistungen', 'orte' ), true ) && ! blitz_built_with_elementor( get_the_ID() ) ) {
		blitz_render_overview( $blitz_key );
	} elseif ( blitz_built_with_elementor( get_the_ID() ) ) {
		the_content();
	} else {
		?>
		<section class="legal">
			<div class="legal__inner">
				<p class="label"><b>§</b> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
				<h1><?php the_title(); ?></h1>
				<div class="prose"><?php the_content(); ?></div>
				<a class="btn btn--ghost legal__back" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span>Zur Startseite</span></a>
			</div>
		</section>
		<?php
	}
endwhile;
get_footer();
