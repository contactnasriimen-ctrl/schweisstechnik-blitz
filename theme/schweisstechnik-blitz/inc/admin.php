<?php
/**
 * Admin: Einrichtungsseite unter „Design → Blitz Einrichtung“ und Hinweis auf Elementor.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	function () {
		add_theme_page( 'Blitz Einrichtung', 'Blitz Einrichtung', 'edit_theme_options', 'blitz-setup', 'blitz_setup_page' );
	}
);

add_action(
	'admin_post_blitz_setup',
	function () {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( 'Keine Berechtigung.' );
		}
		check_admin_referer( 'blitz_setup' );
		$rebuild = ! empty( $_POST['rebuild_front'] );
		blitz_install( $rebuild );
		wp_safe_redirect( admin_url( 'themes.php?page=blitz-setup&done=' . ( $rebuild ? 'front' : 'pages' ) ) );
		exit;
	}
);

function blitz_setup_page() {
	$def        = blitz_defaults();
	$elementor  = did_action( 'elementor/loaded' );
	$front      = blitz_find_page( 'front' );
	$rows       = array( array( 'front', 'Startseite' ), array( 'leistungen', 'Leistungen (Übersicht)' ) );
	foreach ( $def['services']['items'] as $s ) {
		$rows[] = array( 'leistung:' . $s['slug'], '— ' . $s['title'] );
	}
	$rows[] = array( 'orte', 'Einsatzorte (Übersicht)' );
	foreach ( $def['area']['towns'] as $t ) {
		$rows[] = array( 'ort:' . $t['slug'], '— Schweißer in ' . $t['name'] );
	}
	$rows[] = array( 'impressum', 'Impressum' );
	$rows[] = array( 'datenschutz', 'Datenschutzerklärung' );
	?>
	<div class="wrap">
		<h1>Schweisstechnik Blitz – Einrichtung</h1>
		<?php if ( isset( $_GET['done'] ) ) : // phpcs:ignore ?>
			<div class="notice notice-success"><p>Fertig. <?php echo 'front' === $_GET['done'] ? 'Die Startseite wurde neu mit den Blitz-Widgets aufgebaut.' : 'Fehlende Seiten wurden angelegt.'; // phpcs:ignore ?></p></div>
		<?php endif; ?>
		<p>Alle Abschnitte der Startseite sind <strong>Elementor-Widgets</strong> (Kategorie „Schweisstechnik Blitz“): Texte, Listen, Farben und Abstände lassen sich direkt in Elementor ändern, Abschnitte verschieben, löschen oder auf anderen Seiten einsetzen. Firmendaten (Telefon, Adresse, E-Mail) stehen zentral unter <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=blitz' ) ); ?>">Design → Customizer → Schweisstechnik Blitz</a>.</p>
		<?php if ( ! $elementor ) : ?>
			<div class="notice notice-warning inline"><p><strong>Elementor ist nicht aktiv.</strong> Die Website funktioniert trotzdem vollständig – zum Bearbeiten per Drag &amp; Drop bitte <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' ) ); ?>">Elementor installieren</a>. Danach wird die Startseite automatisch mit den Blitz-Widgets aufgebaut.</p></div>
		<?php endif; ?>

		<h2>Seiten</h2>
		<table class="widefat striped" style="max-width:820px">
			<thead><tr><th>Seite</th><th>Adresse</th><th>Bearbeiten</th></tr></thead>
			<tbody>
			<?php
			foreach ( $rows as $r ) :
				$id = blitz_find_page( $r[0] );
				?>
				<tr>
					<td><?php echo esc_html( $r[1] ); ?></td>
					<td><?php echo $id ? '<a href="' . esc_url( get_permalink( $id ) ) . '" target="_blank">' . esc_html( wp_make_link_relative( get_permalink( $id ) ) ) . '</a>' : '<em>fehlt</em>'; ?></td>
					<td>
						<?php if ( $id ) : ?>
							<a href="<?php echo esc_url( get_edit_post_link( $id ) ); ?>">WordPress</a>
							<?php if ( $elementor ) : ?> · <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $id . '&action=elementor' ) ); ?>">Elementor</a><?php endif; ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:20px">
			<?php wp_nonce_field( 'blitz_setup' ); ?>
			<input type="hidden" name="action" value="blitz_setup">
			<p><button class="button button-primary">Fehlende Seiten anlegen</button></p>
			<?php if ( $elementor && $front ) : ?>
				<p><label><input type="checkbox" name="rebuild_front" value="1"> Startseite <strong>neu</strong> mit allen Blitz-Widgets aufbauen (überschreibt Änderungen an der Startseite in Elementor)</label></p>
			<?php endif; ?>
		</form>
	</div>
	<?php
}

/* Hinweis: Elementor empfohlen */
add_action(
	'admin_notices',
	function () {
		if ( did_action( 'elementor/loaded' ) || ! current_user_can( 'install_plugins' ) || get_user_meta( get_current_user_id(), 'blitz_hide_elementor_notice', true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( $screen && 'appearance_page_blitz-setup' === $screen->id ) {
			return;
		}
		echo '<div class="notice notice-info"><p><strong>Schweisstechnik Blitz:</strong> Für die Bearbeitung per Drag &amp; Drop empfehlen wir <a href="' . esc_url( admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' ) ) . '">Elementor</a> (kostenlos). Die Website funktioniert auch ohne. <a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=blitz_hide_notice' ), 'blitz_hide_notice' ) ) . '">Ausblenden</a></p></div>';
	}
);
add_action(
	'admin_post_blitz_hide_notice',
	function () {
		check_admin_referer( 'blitz_hide_notice' );
		update_user_meta( get_current_user_id(), 'blitz_hide_elementor_notice', 1 );
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
		exit;
	}
);
