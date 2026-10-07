<?php
/**
 * Standardinhalte – eine Quelle für Theme-Fallback, Elementor-Widgets und statischen Export.
 * Texte mit *Sternchen* werden als <em> (Serif kursiv) ausgegeben.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'BLITZ_THEME' ) || exit;

function blitz_defaults() {
	static $d = null;
	if ( null !== $d ) {
		return $d;
	}

	$d = array(
		'company'   => array(
			'name'          => 'Schweisstechnik Blitz',
			'owner'         => 'Basel Konbose',
			'phone'         => '0151 64325150',
			'phone_intl'    => '+4915164325150',
			'phone_pretty'  => '+49 151 64325150',
			'whatsapp'      => '4915164325150',
			'email'         => 'info@schweisstechnik-blitz.de',
			'street'        => 'Ebenhofener Str. 7',
			'zip'           => '87640',
			'city'          => 'Biessenhofen',
			'region'        => 'Ostallgäu',
			'state'         => 'Bayern',
			'vat'           => '12523900445',
			'lat'           => 47.8316,
			'lon'           => 10.6378,
			'sister_label'  => 'Auch von Basel Konbose',
			'sister_name'   => 'Reinigung Blitz',
			'sister_url'    => 'https://reinigung-blitz.de',
			'credit_name'   => 'over-tech.de',
			'credit_url'    => 'https://over-tech.de/',
			'hoster'        => '',
		),

		'seo'       => array(
			'title'       => 'Schweißer im Ostallgäu – mobile Schweißarbeiten | Schweisstechnik Blitz',
			'description' => 'Schweißer aus Biessenhofen: Elektroschweißen an Tanks, Rohren, Bohrrohren, Platten und Stahlkonstruktionen – auch Steignaht, auf Baustellen und in der Höhe. Kaufbeuren, Kempten, Füssen bis München. 20 Jahre Erfahrung.',
			'hashtags'    => array( '#SchweißerOstallgäu', '#SchweißerKaufbeuren', '#SchweißerMarktoberdorf', '#SchweißerKempten', '#SchweißerFüssen', '#SchweißerBuchloe', '#SchweißerMindelheim', '#SchweißerMemmingen', '#SchweißerLandsberg', '#SchweißerMünchen', '#SchweißenAllgäu', '#MobilerSchweißer', '#Schweißarbeiten', '#Steignaht', '#Elektrodenschweißen', '#Stahlbau', '#Tankschweißen', '#Rohrschweißen' ),
		),

		'hero'      => array(
			'eyebrow'    => 'Schweißer in Biessenhofen, Ostallgäu',
			'title1'     => 'Schweißarbeiten,',
			'title2'     => 'die halten.',
			'lead'       => 'Elektroschweißen aller Art an Tanks, Rohren, Platten und Stahlkonstruktionen – bei Ihnen vor Ort, auf der Baustelle und in der Höhe. Sauber ausgeführt, mit 20 Jahren Erfahrung.',
			'cta_call'   => 'anrufen',
			'cta_form'   => 'Anfrage stellen',
			'facts'      => array(
				array( 'value' => '20 Jahre', 'label' => 'Berufserfahrung' ),
				array( 'value' => 'Stabelektrode', 'label' => 'auch in Steignaht' ),
				array( 'value' => 'Mobil', 'label' => 'Allgäu bis München' ),
			),
			'show_form'  => 'yes',
			'form_kicker'=> 'Schnellanfrage',
			'form_title' => 'Was soll *geschweißt* werden?',
			'form_text'  => 'Ihre Anfrage geht direkt an Basel Konbose.',
			'form_button'=> 'Anfrage senden',
			'tag_arc'    => array( 'Steignaht', 'von unten nach oben geschweißt' ),
			'tag_temper' => array( 'Anlauffarben', 'zeigen, wie die Naht abkühlt' ),
			'hud'        => array( '<b>PF</b> Steigend', '<b>111</b> E-Hand', 'Schmelzbad <b data-temp>≈ 1.500</b> °C' ),
		),

		'marquee'   => array(
			'big'   => array( 'Tankschweißen', 'Stahlbau', 'Rohrschweißen', 'Steignaht', 'Bohrrohre', 'Kehlnaht', 'Baustellenschweißen', 'Arbeiten in der Höhe' ),
			'small' => array( '#Tankschweißen', '#Behälterbau', '#Tankreparatur', '#Stahlbehälter', '#SchweißenAllgäu', '#Stahlkonstruktion', '#Metallbau', '#Geländerbau', '#Rohrleitungsbau', '#Elektrodenschweißen', '#Stumpfnaht', '#EHandSchweißen', '#Stahlmontage', '#MobilerSchweißer', '#SchweißenVorOrt', '#Ostallgäu', '#SchweißerMünchen', '#SchweißerKaufbeuren' ),
		),

		'services'  => array(
			'label'     => 'Leistungen',
			'title'     => 'Was geschweißt *wird*',
			'statement' => 'Von der Reparatur am Behälter bis zur Stahlkonstruktion auf der Baustelle: alles mit der Stabelektrode, in jeder Position.',
			'link_text' => 'Mehr erfahren',
			'items'     => blitz_default_services(),
		),

		'values'    => array(
			array( 'title' => 'Zuverlässig', 'text' => 'Zugesagte Termine werden gehalten. Wenn etwas dazwischenkommt, erfahren Sie es sofort.' ),
			array( 'title' => 'Präzise', 'text' => 'Saubere Nahtvorbereitung, gleichmäßige Lagen, fachgerechte Ausführung.' ),
			array( 'title' => 'Flexibel', 'text' => 'Werkstück, Baustelle, Gerüst: gearbeitet wird dort, wo das Bauteil ist.' ),
		),

		'positions' => array(
			'label' => 'Die Spezialität',
			'title' => 'Steignaht: *von unten nach oben*',
			'text1' => 'Bei der Steignaht wird die senkrechte Naht mit der Stabelektrode von unten nach oben gezogen. Das flüssige Schmelzbad stützt sich dabei auf die bereits erstarrte Naht – so dringt sie tief ein und verbindet die Bauteile über die ganze Wanddicke.',
			'text2' => 'Bei Rohren, Tanks und Bohrrohren, die sich nicht drehen lassen, ist das oft die sicherste Lösung. Tippen Sie auf eine Position, um den Unterschied zu sehen.',
			'items' => array(
				'PA' => array( 'short' => 'Wanne', 't' => 'PA – Wannenlage', 'x' => 'Das Werkstück liegt, geschweißt wird von oben. Die bequemste Lage, wenn sich das Bauteil drehen oder legen lässt.' ),
				'PC' => array( 'short' => 'Quer', 't' => 'PC – Querposition', 'x' => 'Die Naht läuft waagerecht an einer senkrechten Wand. Typisch bei stehenden Tanks und Rohren.' ),
				'PF' => array( 'short' => 'Steigend', 't' => 'PF – Steignaht', 'x' => 'Senkrechte Naht, von unten nach oben. Das Schmelzbad stützt sich auf der erstarrten Naht ab und dringt tief ein – die Spezialität von Schweisstechnik Blitz.' ),
				'PG' => array( 'short' => 'Fallend', 't' => 'PG – Fallnaht', 'x' => 'Senkrechte Naht, von oben nach unten. Schnell, aber mit weniger Einbrand – eher für dünnes Material.' ),
				'PE' => array( 'short' => 'Überkopf', 't' => 'PE – Überkopf', 'x' => 'Geschweißt wird von unten gegen die Decke. Anspruchsvoll, weil das Schmelzbad gegen die Schwerkraft gehalten werden muss.' ),
			),
		),

		'owner'     => array(
			'label'      => 'Inhaber',
			'title'      => 'Ein Schweißer, *ein Ansprechpartner*',
			'lead'       => 'Basel Konbose schweißt seit 20 Jahren – an Tanks, Rohrleitungen und Stahlkonstruktionen, in Betrieben ebenso wie auf Baustellen und Gerüsten.',
			'text'       => 'Bei Schweisstechnik Blitz sprechen Sie direkt mit dem, der auch die Naht zieht. Das spart Umwege und sorgt dafür, dass Absprachen genau so umgesetzt werden, wie sie getroffen wurden.',
			'button'     => 'Direkt mit Basel Konbose sprechen',
			'signature'  => 'Basel Konbose',
			'role'       => 'Inhaber · Schweißer',
			'coin_front' => 'BK',
			'coin_back'  => '20',
			'ring_front' => 'SCHWEISSTECHNIK BLITZ ✦ BIESSENHOFEN ✦ OSTALLGÄU ✦',
			'ring_back'  => 'JAHRE ERFAHRUNG ✦ STABELEKTRODE ✦ STEIGNAHT ✦',
			'caption'    => 'Schweißerzeichen: *Wer schweißt, steht dafür ein.*',
		),

		'process'   => array(
			'label' => 'Ablauf',
			'title' => 'So läuft ein *Auftrag*',
			'sub'   => 'Ohne lange Wartezeit auf ein Angebot – oft reichen ein Anruf und ein paar Fotos.',
			'steps' => array(
				array( 'title' => 'Anrufen oder Fotos schicken', 'text' => 'Beschreiben Sie das Bauteil am Telefon oder schicken Sie Bilder per WhatsApp.' ),
				array( 'title' => 'Einschätzung und Angebot', 'text' => 'Sie erfahren, wie die Naht ausgeführt wird, was es kostet und wann es losgeht.' ),
				array( 'title' => 'Schweißen vor Ort', 'text' => 'Mit eigenem Gerät auf der Baustelle, am Tank oder in der Höhe.' ),
				array( 'title' => 'Saubere Übergabe', 'text' => 'Die Naht wird gemeinsam angeschaut, der Arbeitsplatz aufgeräumt hinterlassen.' ),
			),
		),

		'area'      => array(
			'label' => 'Einsatzgebiet',
			'title' => 'Im ganzen Allgäu *bis München*',
			'text'  => 'Von Biessenhofen aus sind Kaufbeuren, Marktoberdorf und das gesamte Ostallgäu schnell erreicht – Aufträge in München und im Münchner Umland übernehmen wir ebenfalls. Weitere Orte auf Anfrage.',
			'towns' => blitz_default_towns(),
		),

		'faq'       => array(
			'label' => 'Fragen',
			'title' => 'Häufige *Fragen*',
			'sub'   => 'Was Kunden vor dem ersten Anruf wissen möchten.',
			'items' => array(
				array( 'q' => 'Welches Schweißverfahren setzen Sie ein?', 'a' => 'Gearbeitet wird mit dem Lichtbogenhandschweißen, also mit der Stabelektrode (E-Hand). Das Verfahren ist robust, funktioniert auch im Freien und bei Wind und eignet sich für alle Positionen – auch für die Steignaht.' ),
				array( 'q' => 'Kommen Sie auch zu uns auf die Baustelle?', 'a' => 'Ja. Die meisten Aufträge werden direkt vor Ort erledigt – auf Baustellen, in Betrieben, auf Höfen und an Anlagen im ganzen Allgäu und bis München.' ),
				array( 'q' => 'Können Sie auch in der Höhe schweißen?', 'a' => 'Ja, Arbeiten auf Gerüsten und Arbeitsbühnen gehören zum Alltag. Sagen Sie bei der Anfrage kurz, wie hoch die Stelle liegt und wie sie erreichbar ist.' ),
				array( 'q' => 'Was ist eine Steignaht und wann braucht man sie?', 'a' => 'Bei der Steignaht wird eine senkrechte Naht von unten nach oben geschweißt. Sie dringt tiefer ein als die Fallnaht und wird deshalb bei Rohren, Tanks, Bohrrohren und dickeren Blechen eingesetzt, die sich nicht drehen lassen.' ),
				array( 'q' => 'Übernehmen Sie auch kleine Reparaturen?', 'a' => 'Ja. Ein gebrochener Halter, ein gerissener Rahmen oder eine beschädigte Halterung – rufen Sie einfach an. Oft lässt sich am Telefon schon klären, ob sich die Reparatur lohnt.' ),
				array( 'q' => 'Wie bekomme ich schnell ein Angebot?', 'a' => 'Schicken Sie ein paar Fotos und die ungefähren Maße per WhatsApp an 0151 64325150. So lässt sich der Aufwand meist ohne Vor-Ort-Termin einschätzen.' ),
				array( 'q' => 'In welchen Orten sind Sie tätig?', 'a' => 'Rund um Biessenhofen im Ostallgäu, unter anderem in Kaufbeuren, Marktoberdorf, Kempten, Füssen, Buchloe, Mindelheim, Memmingen, Landsberg am Lech und München. Weitere Orte auf Anfrage.' ),
			),
		),

		'contact'   => array(
			'label' => 'Kontakt',
			'title' => 'Was soll geschweißt *werden?*',
			'sub'   => 'Am schnellsten geht es per Telefon. Oder Sie schicken Ihre Anfrage in drei kurzen Schritten.',
		),

		'footer'    => array(
			'kicker' => 'Oft reicht ein Anruf.',
			'word'   => 'Blitz',
		),

		'form'      => array(
			'arbeit'  => array( 'Tank oder Behälter', 'Stahlkonstruktion', 'Rohr oder Leitung', 'Bohrrohr', 'Platten oder Bleche', 'Bohren und Montage', 'Reparatur', 'Etwas anderes' ),
			'ort_art' => array( 'Auf einer Baustelle', 'Im Betrieb oder auf dem Hof', 'Privat' ),
			'hoehe'   => array( 'Nein', 'Ja, Gerüst oder Bühne nötig', 'Weiß ich nicht' ),
			'termin'  => array( 'So bald wie möglich', 'In den nächsten zwei Wochen', 'In diesem Monat', 'Termin ist flexibel' ),
		),
	);

	return $d;
}

/** Leistungen inkl. Inhalte der Unterseiten /leistungen/<slug>/ */
function blitz_default_services() {
	return array(
		array(
			'slug'  => 'tanks-und-behaelter',
			'icon'  => 'tank',
			'title' => 'Tanks und Behälter',
			'text'  => 'Reparaturen, neue Stutzen und Anschlüsse, Verstärkungen und Halterungen an Tanks und Behältern aus Stahl – liegend oder stehend, direkt am Standort.',
			'tags'  => '#Tankschweißen #Behälterbau #Tankreparatur #Stahlbehälter #SchweißenAllgäu',
			'pick'  => 'Tank oder Behälter',
			'h1'    => array( 'Tanks und Behälter', 'schweißen.' ),
			'meta'  => 'Tank schweißen & Behälter reparieren im Allgäu: neue Stutzen, Anschlüsse, Verstärkungen und Halterungen an Stahltanks – mobil vor Ort, auch in Steignaht.',
			'body'  => array(
				'Ob liegender Lagertank, stehender Behälter oder Silo aus Stahl: Schweisstechnik Blitz schweißt direkt am Standort. Das Bauteil muss nicht ausgebaut oder transportiert werden – Basel Konbose kommt mit eigenem Schweißgerät zu Ihnen.',
				'Senkrechte Nähte an stehenden Behältern werden in Steignaht von unten nach oben gezogen. So dringt die Naht tief ein und verbindet die Bleche über die ganze Wanddicke.',
			),
			'list'  => array( 'Neue Stutzen und Anschlüsse einschweißen', 'Risse und beschädigte Stellen reparieren', 'Verstärkungen, Laschen und Halterungen anschweißen', 'Füße, Konsolen und Aufnahmen an Behältern ergänzen' ),
			'note'  => 'Behälter, die brennbare Flüssigkeiten oder Gase enthalten haben, müssen vor dem Schweißen entleert, gereinigt und gegebenenfalls entgast sein. Sprechen Sie das bei der Anfrage an – wir klären gemeinsam, was vorher zu tun ist.',
			'faq'   => array(
				array( 'q' => 'Können Sie einen Tank direkt vor Ort reparieren?', 'a' => 'In den meisten Fällen ja – liegend oder stehend, direkt am Standort. Schicken Sie vorab ein paar Fotos der Stelle per WhatsApp.' ),
				array( 'q' => 'Was muss vor dem Schweißen am Tank vorbereitet werden?', 'a' => 'Der Behälter muss leer sein. Hat er brennbare Stoffe enthalten, muss er gereinigt und entgast sein. Das klären wir vor dem Termin gemeinsam.' ),
			),
		),
		array(
			'slug'  => 'metall-und-stahlkonstruktionen',
			'icon'  => 'truss',
			'title' => 'Metall- und Stahlkonstruktionen',
			'text'  => 'Träger, Rahmen, Stützen, Geländer, Treppen und Podeste: neu verschweißt, erweitert oder repariert.',
			'tags'  => '#Stahlbau #Stahlkonstruktion #Metallbau #Geländerbau #Schweißarbeiten',
			'pick'  => 'Stahlkonstruktion',
			'h1'    => array( 'Stahlkonstruktionen', 'schweißen.' ),
			'meta'  => 'Stahlbau-Schweißarbeiten im Allgäu: Träger, Rahmen, Stützen, Geländer, Treppen und Podeste neu verschweißen, erweitern oder reparieren – mobil vor Ort.',
			'body'  => array(
				'Träger, Rahmen, Stützen, Geländer, Treppen und Podeste: Schweisstechnik Blitz verschweißt neue Konstruktionen, erweitert bestehende und repariert, was gerissen oder gebrochen ist – im Betrieb, auf dem Hof oder direkt auf der Baustelle.',
				'Gearbeitet wird mit der Stabelektrode. Das Verfahren ist robust, unempfindlich gegen Wind und eignet sich für jede Schweißposition – auch über Kopf und in Steignaht.',
			),
			'list'  => array( 'Anschlüsse an Trägern und Stützen', 'Rahmen und Unterkonstruktionen', 'Geländer, Treppen und Podeste ergänzen oder ausbessern', 'Verstärkungen an bestehenden Konstruktionen' ),
			'note'  => 'Bei tragenden Bauteilen richtet sich die Ausführung nach den Vorgaben Ihres Statikers oder Planers.',
			'faq'   => array(
				array( 'q' => 'Schweißen Sie auch an bestehenden Stahlkonstruktionen?', 'a' => 'Ja – Erweiterungen, Verstärkungen und Reparaturen an bestehenden Konstruktionen gehören zum Alltag.' ),
				array( 'q' => 'Übernehmen Sie auch Bohren und Montage?', 'a' => 'Ja. Bauteile werden nicht nur geschweißt, sondern auf Wunsch auch gebohrt, angepasst, verschraubt und montiert.' ),
			),
		),
		array(
			'slug'  => 'rohre-leitungen-bohrrohre',
			'icon'  => 'pipe',
			'title' => 'Rohre, Leitungen und Bohrrohre',
			'text'  => 'Rundnähte an Rohren und Leitungen, das Verlängern und Verbinden von Bohrrohren – in Steignaht für eine durchgehende, dichte Naht.',
			'tags'  => '#Rohrschweißen #Bohrrohre #Rohrleitungsbau #Steignaht #Elektrodenschweißen',
			'pick'  => 'Rohr oder Leitung',
			'h1'    => array( 'Rohre & Bohrrohre', 'schweißen.' ),
			'meta'  => 'Rohre und Bohrrohre schweißen im Allgäu: Rundnähte an Rohrleitungen, Bohrrohre verlängern und verbinden – in Steignaht, mobil direkt an der Baustelle.',
			'body'  => array(
				'Rohre und Leitungen lassen sich vor Ort selten drehen. Deshalb werden Rundnähte in Zwangslage geschweißt – senkrechte Abschnitte in Steignaht von unten nach oben. So entsteht eine durchgehende, dichte Naht über die ganze Wanddicke.',
				'Bohrrohre werden direkt an der Bohrstelle verlängert und verbunden. Basel Konbose kommt mit eigenem Schweißgerät – auch dorthin, wo kein Werkstattanschluss in der Nähe ist.',
			),
			'list'  => array( 'Rundnähte an Rohren und Leitungen', 'Bohrrohre verlängern und verbinden', 'Flansche, Stutzen und Abzweige anschweißen', 'Reparaturen an bestehenden Leitungen' ),
			'note'  => '',
			'faq'   => array(
				array( 'q' => 'Warum werden Rohre in Steignaht geschweißt?', 'a' => 'Bei der Steignaht stützt sich das Schmelzbad auf die erstarrte Naht. Sie dringt tiefer ein als die Fallnaht – wichtig für dichte, tragfähige Rundnähte an Rohren, die sich nicht drehen lassen.' ),
				array( 'q' => 'Schweißen Sie Bohrrohre direkt an der Bohrstelle?', 'a' => 'Ja, mobil mit eigenem Gerät – das Bohrrohr wird dort verlängert und verbunden, wo es gebraucht wird.' ),
			),
		),
		array(
			'slug'  => 'platten-und-bleche',
			'icon'  => 'plate',
			'title' => 'Platten und Bleche',
			'text'  => 'Stumpf- und Kehlnähte an Stahlplatten, auch dickwandig und in mehreren Lagen.',
			'tags'  => '#Kehlnaht #Stumpfnaht #Stahlplatten #Blechbearbeitung #EHandSchweißen',
			'pick'  => 'Platten oder Bleche',
			'h1'    => array( 'Platten & Bleche', 'schweißen.' ),
			'meta'  => 'Stumpf- und Kehlnähte an Stahlplatten und Blechen, auch dickwandig und mehrlagig – E-Hand-Schweißen im Allgäu, mobil vor Ort oder im Betrieb.',
			'body'  => array(
				'Stumpfnaht oder Kehlnaht, dünnes Blech oder dicke Stahlplatte: Die Nahtvorbereitung entscheidet über die Qualität. Bei dickwandigen Platten wird die Naht in mehreren Lagen aufgebaut – Wurzel, Füll- und Decklagen.',
				'So entstehen gleichmäßige, saubere Nähte, die halten – in der Werkstatt Ihres Betriebs oder direkt am Bauteil.',
			),
			'list'  => array( 'Stumpfnähte an Stahlplatten', 'Kehlnähte an T- und Eckstößen', 'Mehrlagige Nähte an dickwandigem Material', 'Ausbessern und Verstärken von Blechen' ),
			'note'  => '',
			'faq'   => array(
				array( 'q' => 'Was ist der Unterschied zwischen Stumpf- und Kehlnaht?', 'a' => 'Bei der Stumpfnaht liegen die Bleche Kante an Kante in einer Ebene. Bei der Kehlnaht stehen sie im Winkel zueinander – zum Beispiel beim T-Stoß.' ),
				array( 'q' => 'Schweißen Sie auch dicke Platten?', 'a' => 'Ja. Dickwandiges Material wird mehrlagig geschweißt, damit die Naht über die ganze Wanddicke trägt.' ),
			),
		),
		array(
			'slug'  => 'bohr-und-montagearbeiten',
			'icon'  => 'drill',
			'title' => 'Bohr- und Montagearbeiten',
			'text'  => 'Bohren, Anpassen, Verschrauben und Montieren – das Bauteil wird nicht nur geschweißt, sondern auch eingebaut.',
			'tags'  => '#Montagearbeiten #Bohrarbeiten #Stahlmontage #Metallmontage #Handwerk',
			'pick'  => 'Bohren und Montage',
			'h1'    => array( 'Bohren & Montieren', 'aus einer Hand.' ),
			'meta'  => 'Bohr- und Montagearbeiten im Allgäu: bohren, anpassen, verschrauben und montieren – Stahlbauteile werden nicht nur geschweißt, sondern auch eingebaut.',
			'body'  => array(
				'Ein Bauteil ist erst fertig, wenn es sitzt. Deshalb übernimmt Schweisstechnik Blitz auch das Bohren, Anpassen, Verschrauben und Montieren – Sie brauchen keinen zweiten Handwerker für den Einbau.',
				'Schweißen und Montage aus einer Hand sparen Abstimmung und Wege: Was besprochen wurde, wird genau so umgesetzt.',
			),
			'list'  => array( 'Bohren und Anpassen von Stahlbauteilen', 'Verschrauben und Befestigen', 'Montage von Halterungen, Konsolen und Geländern', 'Einbau geschweißter Bauteile vor Ort' ),
			'note'  => '',
			'faq'   => array(
				array( 'q' => 'Kann ich Schweißen und Montage zusammen beauftragen?', 'a' => 'Ja – das Bauteil wird geschweißt und anschließend gebohrt, angepasst und eingebaut.' ),
			),
		),
		array(
			'slug'  => 'baustelle-und-hoehe',
			'icon'  => 'scaffold',
			'title' => 'Baustelle und Arbeiten in der Höhe',
			'text'  => 'Mobil mit eigenem Schweißgerät auf der Baustelle, auf Gerüsten und Arbeitsbühnen.',
			'tags'  => '#Baustellenschweißen #MobilerSchweißer #SchweißenVorOrt #ArbeitenInDerHöhe #Ostallgäu',
			'pick'  => '',
			'hoehe' => 'Ja, Gerüst oder Bühne nötig',
			'h1'    => array( 'Schweißen auf der Baustelle', 'und in der Höhe.' ),
			'meta'  => 'Mobiler Schweißer für Baustellen im Allgäu und bis München: Schweißarbeiten vor Ort, auf Gerüsten und Arbeitsbühnen – mit eigenem Schweißgerät.',
			'body'  => array(
				'Schweisstechnik Blitz arbeitet dort, wo das Bauteil ist: auf der Baustelle, im Betrieb, auf dem Hof – und auf Gerüsten oder Arbeitsbühnen. Das eigene Schweißgerät ist immer dabei.',
				'Das Elektrodenschweißen ist für den Außeneinsatz gemacht: robust, unabhängig von Schutzgasflaschen und auch bei Wind zuverlässig.',
			),
			'list'  => array( 'Schweißarbeiten direkt auf der Baustelle', 'Arbeiten auf Gerüsten und Arbeitsbühnen', 'Reparaturen an Anlagen im laufenden Betrieb nach Absprache', 'Einsätze im ganzen Allgäu und bis München' ),
			'note'  => 'Ob ein Gerüst oder eine Arbeitsbühne gestellt werden muss, klären wir vor dem Termin gemeinsam.',
			'faq'   => array(
				array( 'q' => 'Können Sie auch in der Höhe schweißen?', 'a' => 'Ja, Arbeiten auf Gerüsten und Arbeitsbühnen gehören zum Alltag. Sagen Sie bei der Anfrage kurz, wie hoch die Stelle liegt und wie sie erreichbar ist.' ),
				array( 'q' => 'Brauchen Sie auf der Baustelle Strom?', 'a' => 'Klären Sie das am besten bei der Anfrage – dann wird abgestimmt, wie das Schweißgerät vor Ort betrieben wird.' ),
			),
		),
	);
}

/** Orte im Einsatzgebiet inkl. Unterseiten /schweisser/<slug>/ */
function blitz_default_towns() {
	return array(
		array( 'slug' => 'kaufbeuren', 'name' => 'Kaufbeuren', 'lat' => 47.8803, 'lon' => 10.6222, 'km' => 6, 'pos' => 'l', 'about' => 'Die kreisfreie Stadt Kaufbeuren liegt direkt nördlich von Biessenhofen' ),
		array( 'slug' => 'marktoberdorf', 'name' => 'Marktoberdorf', 'lat' => 47.7787, 'lon' => 10.6176, 'km' => 6, 'pos' => 'l', 'about' => 'Die Kreisstadt des Ostallgäus liegt direkt südlich von Biessenhofen' ),
		array( 'slug' => 'buchloe', 'name' => 'Buchloe', 'lat' => 48.0322, 'lon' => 10.7175, 'km' => 23, 'pos' => 'r', 'about' => 'Buchloe im Norden des Ostallgäus' ),
		array( 'slug' => 'mindelheim', 'name' => 'Mindelheim', 'lat' => 48.0457, 'lon' => 10.4888, 'km' => 26, 'pos' => 'l', 'about' => 'Die Kreisstadt des Unterallgäus' ),
		array( 'slug' => 'kempten', 'name' => 'Kempten', 'lat' => 47.7267, 'lon' => 10.3139, 'km' => 27, 'pos' => 'u', 'about' => 'Kempten, die größte Stadt des Allgäus' ),
		array( 'slug' => 'fuessen', 'name' => 'Füssen', 'lat' => 47.5696, 'lon' => 10.7004, 'km' => 29, 'pos' => 'r', 'about' => 'Füssen im Süden des Ostallgäus' ),
		array( 'slug' => 'landsberg-am-lech', 'name' => 'Landsberg am Lech', 'lat' => 48.0476, 'lon' => 10.8988, 'km' => 30, 'pos' => 'o', 'about' => 'Die Große Kreisstadt Landsberg am Lech' ),
		array( 'slug' => 'memmingen', 'name' => 'Memmingen', 'lat' => 47.9837, 'lon' => 10.1815, 'km' => 38, 'pos' => 'u', 'about' => 'Die kreisfreie Stadt Memmingen' ),
		array( 'slug' => 'muenchen', 'name' => 'München', 'lat' => 48.1374, 'lon' => 11.5755, 'km' => 78, 'pos' => 'u', 'about' => 'Die Landeshauptstadt München' ),
	);
}
