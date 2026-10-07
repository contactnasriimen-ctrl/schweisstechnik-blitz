<?php
/**
 * Anfrageformular: AJAX-Empfang, Prüfung, E-Mail und Speicherung als „Anfrage“ im Admin.
 * Antwort immer als JSON {ok, message} – gleiches Format wie send.php im statischen Export.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'blitz_anfrage',
			array(
				'labels'          => array(
					'name'          => 'Anfragen',
					'singular_name' => 'Anfrage',
					'menu_name'     => 'Anfragen',
					'all_items'     => 'Alle Anfragen',
					'edit_item'     => 'Anfrage ansehen',
					'search_items'  => 'Anfragen durchsuchen',
					'not_found'     => 'Noch keine Anfragen.',
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_position'   => 25,
				'menu_icon'       => 'dashicons-email-alt',
				'supports'        => array( 'title', 'editor' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}
);

add_action( 'wp_ajax_blitz_anfrage', 'blitz_handle_form' );
add_action( 'wp_ajax_nopriv_blitz_anfrage', 'blitz_handle_form' );

function blitz_form_reply( $code, $ok, $message ) {
	wp_send_json( array( 'ok' => $ok, 'message' => $message ), $code );
}

function blitz_handle_form() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- öffentliches Kontaktformular (Seiten-Cache-tauglich); Schutz über Honeypot + Ratenbegrenzung
	$post = wp_unslash( $_POST );

	if ( ! empty( $post['website'] ) ) {
		blitz_form_reply( 200, true, 'Anfrage gesendet.' );
	}

	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$rkey = 'blitz_rl_' . md5( $ip . wp_salt( 'nonce' ) );
	$hits = (int) get_transient( $rkey );
	if ( $hits >= 5 ) {
		blitz_form_reply( 429, false, 'Zu viele Anfragen in kurzer Zeit.' );
	}
	set_transient( $rkey, $hits + 1, HOUR_IN_SECONDS );

	$o        = blitz_defaults()['form'];
	$arbeiten = array_values( array_intersect( $o['arbeit'], array_map( 'strval', (array) ( isset( $post['arbeit'] ) ? $post['arbeit'] : array() ) ) ) );
	$pick     = function ( $key, $allowed ) use ( $post ) {
		$v = isset( $post[ $key ] ) ? sanitize_text_field( $post[ $key ] ) : '';
		return in_array( $v, $allowed, true ) ? $v : '';
	};
	$ort_art  = $pick( 'ort_art', $o['ort_art'] );
	$hoehe    = $pick( 'hoehe', $o['hoehe'] );
	$termin   = $pick( 'termin', $o['termin'] );
	$ort      = mb_substr( sanitize_text_field( isset( $post['ort'] ) ? $post['ort'] : '' ), 0, 120 );
	$text     = mb_substr( sanitize_textarea_field( isset( $post['beschreibung'] ) ? $post['beschreibung'] : '' ), 0, 4000 );
	$name     = mb_substr( sanitize_text_field( isset( $post['name'] ) ? $post['name'] : '' ), 0, 120 );
	$telefon  = mb_substr( sanitize_text_field( isset( $post['telefon'] ) ? $post['telefon'] : '' ), 0, 40 );
	$email    = mb_substr( sanitize_email( isset( $post['email'] ) ? $post['email'] : '' ), 0, 160 );
	$quelle   = 'hero' === ( isset( $post['quelle'] ) ? $post['quelle'] : '' ) ? 'Schnellanfrage' : 'Anfrageformular';
	$consent  = isset( $post['einwilligung'] ) && '1' === $post['einwilligung'];
	// phpcs:enable

	if ( ! $arbeiten ) {
		if ( 'Schnellanfrage' === $quelle ) {
			$arbeiten = array( 'Nicht angegeben' );
		} else {
			blitz_form_reply( 422, false, 'Bitte wählen Sie mindestens eine Arbeit aus.' );
		}
	}
	if ( '' === $name ) {
		blitz_form_reply( 422, false, 'Bitte geben Sie Ihren Namen an.' );
	}
	if ( '' === $telefon && '' === $email ) {
		blitz_form_reply( 422, false, 'Bitte geben Sie eine Telefonnummer oder E-Mail-Adresse an.' );
	}
	if ( '' !== $telefon && ! preg_match( '/^[0-9 +()\/.\-]{5,40}$/', $telefon ) ) {
		blitz_form_reply( 422, false, 'Bitte prüfen Sie die Telefonnummer.' );
	}
	if ( ! $consent ) {
		blitz_form_reply( 422, false, 'Bitte bestätigen Sie die Einwilligung zur Bearbeitung Ihrer Angaben.' );
	}

	$lines = array(
		'Neue Anfrage über ' . wp_parse_url( home_url(), PHP_URL_HOST ) . ' (' . $quelle . ')',
		str_repeat( '=', 44 ),
		'',
		'Arbeit:        ' . implode( ', ', $arbeiten ),
		'Wo:            ' . ( $ort_art ? $ort_art : '–' ),
		'In der Höhe:   ' . ( $hoehe ? $hoehe : '–' ),
		'PLZ und Ort:   ' . ( $ort ? $ort : '–' ),
		'Wunschtermin:  ' . ( $termin ? $termin : '–' ),
		'',
		'Beschreibung:',
		'' !== $text ? $text : '–',
		'',
		str_repeat( '-', 44 ),
		'Name:          ' . $name,
		'Telefon:       ' . ( $telefon ? $telefon : '–' ),
		'E-Mail:        ' . ( $email ? $email : '–' ),
		'',
		'Gesendet am ' . wp_date( 'd.m.Y \u\m H:i' ) . ' Uhr. Einwilligung zur Bearbeitung wurde erteilt.',
	);
	$body    = implode( "\n", $lines );
	$subject = 'Anfrage: ' . implode( ', ', $arbeiten ) . ' – ' . $name;

	$stored = false;
	if ( blitz_opt( 'form_store', true ) ) {
		$stored = (bool) wp_insert_post(
			array(
				'post_type'    => 'blitz_anfrage',
				'post_status'  => 'private',
				'post_title'   => $name . ' – ' . implode( ', ', $arbeiten ),
				'post_content' => $body,
			)
		);
	}

	$to      = blitz_opt( 'form_email', '' );
	$to      = $to ? $to : blitz_company( 'email' );
	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( $email ) {
		$headers[] = 'Reply-To: ' . str_replace( array( "\r", "\n", '<', '>' ), '', $name ) . ' <' . $email . '>';
	}
	$mailed = wp_mail( $to, $subject, $body, $headers );

	if ( ! $stored && ! $mailed ) {
		blitz_form_reply( 500, false, 'Die Anfrage konnte gerade nicht gesendet werden.' );
	}
	blitz_form_reply( 200, true, 'Anfrage gesendet.' );
}

/* Anfragen nach 180 Tagen löschen */
add_action(
	'init',
	function () {
		if ( ! wp_next_scheduled( 'blitz_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'blitz_cleanup' );
		}
	}
);
add_action(
	'blitz_cleanup',
	function () {
		$old = get_posts(
			array(
				'post_type'      => 'blitz_anfrage',
				'post_status'    => 'any',
				'posts_per_page' => 200,
				'fields'         => 'ids',
				'date_query'     => array( array( 'before' => '180 days ago' ) ),
			)
		);
		foreach ( $old as $id ) {
			wp_delete_post( $id, true );
		}
	}
);
