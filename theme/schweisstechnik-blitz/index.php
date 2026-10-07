<?php
/**
 * Fallback-Vorlage (Beiträge, Archive, Suche).
 *
 * @package SchweisstechnikBlitz
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="legal">
	<div class="legal__inner">
		<p class="label"><b>§</b> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
		<h1><?php echo is_singular() ? esc_html( get_the_title() ) : esc_html( wp_strip_all_tags( get_the_archive_title() ? get_the_archive_title() : 'Beiträge' ) ); ?></h1>
		<div class="prose">
		<?php
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				if ( is_singular() ) {
					the_content();
				} else {
					echo '<h2><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2>';
					the_excerpt();
				}
			}
			the_posts_pagination();
		} else {
			echo '<p>Hier gibt es noch keine Inhalte.</p>';
		}
		?>
		</div>
	</div>
</section>
<?php
get_footer();
