<?php
/**
 * Impressum & Datenschutzerklärung (Vorlagen; werden beim Einrichten als Seiteninhalt gespeichert
 * und sind danach im WordPress-Editor bzw. mit Elementor frei änderbar).
 *
 * @package SchweisstechnikBlitz
 */

defined( 'BLITZ_THEME' ) || exit;

function blitz_legal_title( $type ) {
	return 'impressum' === $type ? 'Impressum' : 'Datenschutzerklärung';
}

function blitz_legal_html( $type ) {
	$c = blitz_company();
	$h = 'blitz_h';
	$tel  = '<a href="' . $h( blitz_tel() ) . '">' . $h( $c['phone_pretty'] ) . '</a>';
	$mail = '<a href="mailto:' . $h( $c['email'] ) . '">' . $h( $c['email'] ) . '</a>';

	if ( 'impressum' === $type ) {
		$html  = '<h2>Angaben gemäß § 5 DDG</h2>';
		$html .= '<p>' . $h( $c['name'] ) . '<br>' . $h( $c['street'] ) . '<br>' . $h( $c['zip'] . ' ' . $c['city'] ) . '<br>Deutschland</p>';
		$html .= '<h2>Vertreten durch</h2><p>' . $h( $c['owner'] ) . '</p>';
		$html .= '<h2>Kontakt</h2><p>Telefon: ' . $tel . '<br>E-Mail: ' . $mail . '</p>';
		if ( $c['vat'] ) {
			$html .= '<h2>Umsatzsteuer-ID</h2><p>Umsatzsteuer-Identifikationsnummer gemäß § 27a Umsatzsteuergesetz:<br>' . $h( $c['vat'] ) . '</p>';
		}
		$html .= '<h2>Verantwortlich für den Inhalt nach § 18 Abs. 2 MStV</h2><p>' . $h( $c['owner'] ) . '<br>' . $h( $c['street'] . ', ' . $c['zip'] . ' ' . $c['city'] ) . '</p>';
		$html .= '<h2>Verbraucherstreitbeilegung</h2><p>Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>';
		$html .= '<h2>Haftung für Inhalte und Links</h2><p>Die Inhalte dieser Website wurden mit größter Sorgfalt erstellt. Für die Richtigkeit, Vollständigkeit und Aktualität können wir jedoch keine Gewähr übernehmen. Für Inhalte externer Links sind ausschließlich deren Betreiber verantwortlich; zum Zeitpunkt der Verlinkung waren keine Rechtsverstöße erkennbar.</p>';
		return $html;
	}

	$hoster = $c['hoster'] ? ' (' . $h( $c['hoster'] ) . ')' : '';
	$html   = '<h2>1. Verantwortlicher</h2><p>' . $h( $c['name'] ) . ', Inhaber ' . $h( $c['owner'] ) . '<br>' . $h( $c['street'] . ', ' . $c['zip'] . ' ' . $c['city'] ) . '<br>Telefon: ' . $tel . ' · E-Mail: ' . $mail . '</p>';
	$html  .= '<h2>2. Hosting und Server-Logfiles</h2><p>Diese Website wird bei einem externen Dienstleister gehostet' . $hoster . '. Beim Aufruf der Seiten werden technisch notwendige Daten (IP-Adresse, Datum und Uhrzeit, aufgerufene Seite, Browser) in Server-Logfiles verarbeitet, um die Website sicher und stabil bereitzustellen. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO. Mit dem Hoster besteht ein Vertrag zur Auftragsverarbeitung.</p>';
	$html  .= '<h2>3. Keine Cookies, kein Tracking, lokale Schriften und Skripte</h2><p>Diese Website setzt keine Analyse- oder Marketing-Cookies und bindet keine Tracking-Dienste ein. Schriften und Skripte werden lokal vom eigenen Server geladen; es besteht keine Verbindung zu Google Fonts, Content-Delivery-Netzwerken oder anderen Drittanbietern. Damit die kurze Startanimation bei weiteren Seitenaufrufen übersprungen wird, speichert Ihr Browser für die Dauer der Sitzung einen technischen Hinweis (Session Storage); dieser wird nicht an uns übertragen.</p>';
	$html  .= '<h2>4. Kontaktformular, E-Mail und Telefon</h2><p>Wenn Sie uns über das Formular, per E-Mail oder telefonisch kontaktieren, verarbeiten wir Ihre Angaben (Name, Kontaktdaten, Beschreibung des Auftrags) zur Bearbeitung Ihrer Anfrage und zur Erstellung eines Angebots. Rechtsgrundlage ist Art. 6 Abs. 1 lit. b DSGVO (vorvertragliche Maßnahmen). Formularanfragen werden per E-Mail an uns übermittelt und zusätzlich im geschützten Verwaltungsbereich der Website gespeichert; dort werden sie nach 180 Tagen automatisch gelöscht. Kommt ein Auftrag zustande, bewahren wir die Daten im Rahmen der gesetzlichen Aufbewahrungspflichten auf. Zum Schutz vor Missbrauch wird Ihre IP-Adresse ausschließlich in verschlüsselter (gehashter) Form für höchstens eine Stunde vorgehalten.</p>';
	$html  .= '<h2>5. WhatsApp</h2><p>Auf der Website befindet sich ein Link zu WhatsApp. Erst wenn Sie ihn anklicken, wird eine Verbindung zu WhatsApp (Meta Platforms Ireland Ltd.) hergestellt. Für die Verarbeitung in WhatsApp gelten die Datenschutzbestimmungen des Anbieters. Wenn Sie uns lieber nicht über WhatsApp schreiben möchten, nutzen Sie bitte Telefon, E-Mail oder das Formular.</p>';
	$html  .= '<h2>6. Ihre Rechte</h2><p>Sie haben das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung, Datenübertragbarkeit sowie Widerspruch gegen Verarbeitungen auf Grundlage von Art. 6 Abs. 1 lit. f DSGVO. Wenden Sie sich dazu einfach an die oben genannten Kontaktdaten. Außerdem können Sie sich bei einer Datenschutz-Aufsichtsbehörde beschweren, in Bayern beim Bayerischen Landesamt für Datenschutzaufsicht (BayLDA), Promenade 18, 91522 Ansbach.</p>';
	$html  .= '<h2>7. Stand</h2><p>Stand: Oktober 2026</p>';
	return $html;
}
