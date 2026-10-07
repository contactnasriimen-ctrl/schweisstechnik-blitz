<?php
/**
 * Elementor-Widgets „Schweisstechnik Blitz“. Jedes Widget gibt dasselbe Markup aus wie das Theme
 * (inc/render.php); Standardwerte kommen aus inc/content.php.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager as CM;
use Elementor\Repeater;

abstract class Blitz_Widget_Base extends \Elementor\Widget_Base {

	public function get_categories() {
		return array( 'blitz' );
	}

	public function get_keywords() {
		return array( 'blitz', 'schweissen', 'schweißen', 'schweisstechnik' );
	}

	public function get_style_depends(): array {
		return array( 'blitz-style' );
	}

	public function get_script_depends(): array {
		return array( 'blitz-main' );
	}

	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	protected function is_dynamic_content(): bool {
		return true;
	}

	/** Wurzel-Selektor für Stil-Steuerungen */
	protected function root() {
		return '{{WRAPPER}} .blitz-sec';
	}

	protected function section( $id, $label, $tab = null ) {
		$this->start_controls_section( $id, array( 'label' => $label, 'tab' => $tab ? $tab : CM::TAB_CONTENT ) );
	}

	protected function txt( $id, $label, $default, $type = 'text', $extra = array() ) {
		$map = array( 'text' => CM::TEXT, 'area' => CM::TEXTAREA, 'switch' => CM::SWITCHER, 'num' => CM::NUMBER );
		$this->add_control(
			$id,
			array_merge(
				array(
					'label'       => $label,
					'type'        => $map[ $type ],
					'default'     => $default,
					'label_block' => 'switch' !== $type && 'num' !== $type,
				),
				'switch' === $type ? array( 'return_value' => 'yes', 'label_on' => 'An', 'label_off' => 'Aus' ) : array(),
				$extra
			)
		);
	}

	protected function hint() {
		$this->add_control(
			'blitz_hint_' . $this->get_name(),
			array(
				'type'            => CM::RAW_HTML,
				'raw'             => 'Tipp: <code>*Wort*</code> wird in Gold kursiv dargestellt. Telefon, Adresse und E-Mail stehen zentral im Customizer.',
				'content_classes' => 'elementor-descriptor',
			)
		);
	}

	/** Gemeinsamer Stil-Bereich: Hintergrund, Abstände, Akzentfarben */
	protected function style_controls() {
		$this->section( 'blitz_style', 'Bereich', CM::TAB_STYLE );
		$this->add_control(
			'blitz_bg',
			array(
				'label'     => 'Hintergrundfarbe',
				'type'      => CM::COLOR,
				'selectors' => array( $this->root() => 'background: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'blitz_pad',
			array(
				'label'              => 'Innenabstand oben/unten',
				'type'               => CM::DIMENSIONS,
				'size_units'         => array( 'px', 'vh', 'rem' ),
				'allowed_dimensions' => array( 'top', 'bottom' ),
				'selectors'          => array( $this->root() => 'padding-top: {{TOP}}{{UNIT}}; padding-bottom: {{BOTTOM}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'blitz_accent',
			array(
				'label'     => 'Akzentfarbe (Glut)',
				'type'      => CM::COLOR,
				'selectors' => array( '{{WRAPPER}}' => '--molten: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'blitz_gold',
			array(
				'label'     => 'Goldton (kursive Wörter)',
				'type'      => CM::COLOR,
				'selectors' => array( '{{WRAPPER}}' => '--gold: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'blitz_ink',
			array(
				'label'     => 'Textfarbe',
				'type'      => CM::COLOR,
				'selectors' => array( '{{WRAPPER}}' => '--ink: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	/** Repeater-Zeilen auf Felder reduzieren */
	protected function rows( $rows, $keys ) {
		$out = array();
		foreach ( (array) $rows as $r ) {
			$row = array();
			foreach ( $keys as $k ) {
				$row[ $k ] = isset( $r[ $k ] ) ? $r[ $k ] : '';
			}
			$out[] = $row;
		}
		return $out;
	}

	protected function s() {
		return $this->get_settings_for_display();
	}
}

/* ==========================================================================
   Hero
   ========================================================================== */
class Blitz_W_Hero extends Blitz_Widget_Base {
	public function get_name() {
		return 'blitz-hero';
	}
	public function get_title() {
		return 'Blitz · Hero mit 3D & Anfrage';
	}
	public function get_icon() {
		return 'eicon-header';
	}

	protected function register_controls() {
		$d = blitz_defaults()['hero'];
		$this->section( 'content', 'Inhalt' );
		$this->hint();
		$this->txt( 'eyebrow', 'Zeile über dem Titel (Teil der H1 – wichtig für SEO)', $d['eyebrow'] );
		$this->txt( 'title1', 'Titel Zeile 1', $d['title1'] );
		$this->txt( 'title2', 'Titel Zeile 2 (Glut-Verlauf)', $d['title2'] );
		$this->txt( 'lead', 'Einleitung', $d['lead'], 'area' );
		$this->txt( 'cta_call', 'Text nach der Telefonnummer im Button', $d['cta_call'] );
		$this->txt( 'cta_form', 'Zweiter Button', $d['cta_form'] );
		$rep = new Repeater();
		$rep->add_control( 'value', array( 'label' => 'Wert', 'type' => CM::TEXT, 'default' => '' ) );
		$rep->add_control( 'label', array( 'label' => 'Beschriftung', 'type' => CM::TEXT, 'default' => '' ) );
		$this->add_control(
			'facts',
			array(
				'label'       => 'Kennzahlen',
				'type'        => CM::REPEATER,
				'fields'      => $rep->get_controls(),
				'default'     => $d['facts'],
				'title_field' => '{{{ value }}}',
			)
		);
		$this->end_controls_section();

		$this->section( 'form', 'Schnellanfrage & Telefon' );
		$this->txt( 'show_form', 'Formular im Hero anzeigen', 'yes', 'switch' );
		$this->txt( 'form_kicker', 'Kopfzeile', $d['form_kicker'] );
		$this->txt( 'form_title', 'Überschrift', $d['form_title'] );
		$this->txt( 'form_text', 'Text', $d['form_text'] );
		$this->txt( 'form_button', 'Button', $d['form_button'] );
		$this->end_controls_section();

		$this->section( 'scene', '3D-Szene & Hinweise' );
		$this->txt( 'show_3d', '3D-Schweißszene', 'yes', 'switch' );
		$this->txt( 'show_tags', 'Hinweise an der Naht', 'yes', 'switch' );
		$this->txt( 'tag_arc_t', 'Hinweis 1 – Titel', $d['tag_arc'][0] );
		$this->txt( 'tag_arc_s', 'Hinweis 1 – Text', $d['tag_arc'][1] );
		$this->txt( 'tag_temper_t', 'Hinweis 2 – Titel', $d['tag_temper'][0] );
		$this->txt( 'tag_temper_s', 'Hinweis 2 – Text', $d['tag_temper'][1] );
		$this->txt( 'show_hud', 'Technik-Anzeige unten rechts', 'yes', 'switch' );
		$this->end_controls_section();

		$this->style_controls();
	}

	protected function render() {
		$s = $this->s();
		blitz_render_hero(
			array(
				'eyebrow'     => $s['eyebrow'],
				'title1'      => $s['title1'],
				'title2'      => $s['title2'],
				'lead'        => $s['lead'],
				'cta_call'    => $s['cta_call'],
				'cta_form'    => $s['cta_form'],
				'facts'       => $this->rows( $s['facts'], array( 'value', 'label' ) ),
				'show_form'   => 'yes' === $s['show_form'] ? 'yes' : 'no',
				'form_kicker' => $s['form_kicker'],
				'form_title'  => $s['form_title'],
				'form_text'   => $s['form_text'],
				'form_button' => $s['form_button'],
				'show_3d'     => 'yes' === $s['show_3d'] ? 'yes' : 'no',
				'show_tags'   => 'yes' === $s['show_tags'] ? 'yes' : 'no',
				'show_hud'    => 'yes' === $s['show_hud'] ? 'yes' : 'no',
				'tag_arc'     => array( $s['tag_arc_t'], $s['tag_arc_s'] ),
				'tag_temper'  => array( $s['tag_temper_t'], $s['tag_temper_s'] ),
				'id'          => 'top',
			)
		);
	}
}

/* ==========================================================================
   Laufband
   ========================================================================== */
class Blitz_W_Marquee extends Blitz_Widget_Base {
	public function get_name() {
		return 'blitz-marquee';
	}
	public function get_title() {
		return 'Blitz · Laufband & Hashtags';
	}
	public function get_icon() {
		return 'eicon-animation-text';
	}
	protected function root() {
		return '{{WRAPPER}} .marquee';
	}
	protected function register_controls() {
		$d = blitz_defaults()['marquee'];
		$this->section( 'content', 'Inhalt' );
		$this->txt( 'big', 'Große Wörter (eines pro Zeile)', implode( "\n", $d['big'] ), 'area', array( 'rows' => 8 ) );
		$this->txt( 'small', 'Hashtags (mit Leerzeichen getrennt)', implode( ' ', $d['small'] ), 'area', array( 'rows' => 6 ) );
		$this->end_controls_section();
		$this->style_controls();
	}
	protected function render() {
		$s = $this->s();
		blitz_render_marquee( array( 'big' => $s['big'], 'small' => $s['small'] ) );
	}
}

/* ==========================================================================
   Leistungen
   ========================================================================== */
class Blitz_W_Services extends Blitz_Widget_Base {
	public function get_name() {
		return 'blitz-services';
	}
	public function get_title() {
		return 'Blitz · Leistungen';
	}
	public function get_icon() {
		return 'eicon-gallery-grid';
	}
	protected function register_controls() {
		$d = blitz_defaults()['services'];
		$this->section( 'content', 'Inhalt' );
		$this->hint();
		$this->txt( 'label', 'Kicker', $d['label'] );
		$this->txt( 'title', 'Überschrift', $d['title'] );
		$this->txt( 'statement', 'Leitsatz (leuchtet beim Scrollen auf)', $d['statement'], 'area' );
		$this->txt( 'link_text', 'Linktext zur Unterseite', $d['link_text'] );

		$icons   = array( 'tank' => 'Tank', 'truss' => 'Stahlbau', 'pipe' => 'Rohr', 'plate' => 'Platte', 'drill' => 'Bohren', 'scaffold' => 'Gerüst' );
		$arbeit  = array( '' => '—' ) + array_combine( blitz_defaults()['form']['arbeit'], blitz_defaults()['form']['arbeit'] );
		$rep     = new Repeater();
		$rep->add_control( 'icon', array( 'label' => 'Symbol', 'type' => CM::SELECT, 'options' => $icons, 'default' => 'tank' ) );
		$rep->add_control( 'title', array( 'label' => 'Titel', 'type' => CM::TEXT, 'default' => 'Neue Leistung', 'label_block' => true ) );
		$rep->add_control( 'text', array( 'label' => 'Text', 'type' => CM::TEXTAREA, 'default' => '' ) );
		$rep->add_control( 'tags', array( 'label' => 'Hashtags', 'type' => CM::TEXT, 'default' => '', 'label_block' => true ) );
		$rep->add_control( 'slug', array( 'label' => 'Unterseite (Slug unter /leistungen/)', 'type' => CM::TEXT, 'default' => '' ) );
		$rep->add_control( 'url', array( 'label' => 'Oder eigener Link', 'type' => CM::TEXT, 'default' => '', 'label_block' => true ) );
		$rep->add_control( 'pick', array( 'label' => 'Wählt im Formular vor', 'type' => CM::SELECT, 'options' => $arbeit, 'default' => '' ) );
		$items = array();
		foreach ( $d['items'] as $it ) {
			$items[] = array( 'icon' => $it['icon'], 'title' => $it['title'], 'text' => $it['text'], 'tags' => $it['tags'], 'slug' => $it['slug'], 'url' => '', 'pick' => $it['pick'] );
		}
		$this->add_control(
			'items',
			array(
				'label'       => 'Leistungen',
				'type'        => CM::REPEATER,
				'fields'      => $rep->get_controls(),
				'default'     => $items,
				'title_field' => '{{{ title }}}',
			)
		);
		$this->end_controls_section();
		$this->style_controls();
	}
	protected function render() {
		$s     = $this->s();
		$items = $this->rows( $s['items'], array( 'icon', 'title', 'text', 'tags', 'slug', 'url', 'pick' ) );
		foreach ( $items as &$it ) {
			if ( '' === $it['pick'] && 'baustelle-und-hoehe' === $it['slug'] ) {
				$it['hoehe'] = 'Ja, Gerüst oder Bühne nötig';
			}
		}
		unset( $it );
		blitz_render_services(
			array(
				'label'     => $s['label'],
				'title'     => $s['title'],
				'statement' => $s['statement'],
				'link_text' => $s['link_text'],
				'items'     => $items,
			)
		);
	}
}

/* ==========================================================================
   Werte
   ========================================================================== */
class Blitz_W_Values extends Blitz_Widget_Base {
	public function get_name() {
		return 'blitz-values';
	}
	public function get_title() {
		return 'Blitz · Werte';
	}
	public function get_icon() {
		return 'eicon-columns';
	}
	protected function register_controls() {
		$this->section( 'content', 'Inhalt' );
		$rep = new Repeater();
		$rep->add_control( 'title', array( 'label' => 'Wort', 'type' => CM::TEXT, 'default' => '' ) );
		$rep->add_control( 'text', array( 'label' => 'Text', 'type' => CM::TEXTAREA, 'default' => '' ) );
		$this->add_control(
			'items',
			array(
				'label'       => 'Werte',
				'type'        => CM::REPEATER,
				'fields'      => $rep->get_controls(),
				'default'     => blitz_defaults()['values'],
				'title_field' => '{{{ title }}}',
			)
		);
		$this->end_controls_section();
		$this->style_controls();
	}
	protected function render() {
		$s = $this->s();
		blitz_render_values( array( 'items' => $this->rows( $s['items'], array( 'title', 'text' ) ) ) );
	}
}

/* ==========================================================================
   Steignaht / Schweißpositionen
   ========================================================================== */
class Blitz_W_Positions extends Blitz_Widget_Base {
	public function get_name() {
		return 'blitz-positions';
	}
	public function get_title() {
		return 'Blitz · Steignaht (Positionen)';
	}
	public function get_icon() {
		return 'eicon-tabs';
	}
	protected function register_controls() {
		$d = blitz_defaults()['positions'];
		$this->section( 'content', 'Inhalt' );
		$this->hint();
		$this->txt( 'label', 'Kicker', $d['label'] );
		$this->txt( 'title', 'Überschrift', $d['title'] );
		$this->txt( 'text1', 'Absatz 1', $d['text1'], 'area' );
		$this->txt( 'text2', 'Absatz 2', $d['text2'], 'area' );
		$this->end_controls_section();
		$this->section( 'items', 'Positionen' );
		foreach ( $d['items'] as $key => $it ) {
			$this->add_control( 'h_' . $key, array( 'label' => $key, 'type' => CM::HEADING, 'separator' => 'before' ) );
			$this->txt( $key . '_short', 'Kurzname', $it['short'] );
			$this->txt( $key . '_t', 'Titel', $it['t'] );
			$this->txt( $key . '_x', 'Beschreibung', $it['x'], 'area' );
		}
		$this->end_controls_section();
		$this->style_controls();
	}
	protected function render() {
		$s     = $this->s();
		$items = array();
		foreach ( blitz_defaults()['positions']['items'] as $key => $it ) {
			$items[ $key ] = array(
				'short' => $s[ $key . '_short' ],
				't'     => $s[ $key . '_t' ],
				'x'     => $s[ $key . '_x' ],
			);
		}
		blitz_render_positions(
			array(
				'label' => $s['label'],
				'title' => $s['title'],
				'text1' => $s['text1'],
				'text2' => $s['text2'],
				'items' => $items,
			)
		);
	}
}

/* ==========================================================================
   Inhaber / 3D-Münze
   ========================================================================== */
class Blitz_W_Owner extends Blitz_Widget_Base {
	public function get_name() {
		return 'blitz-owner';
	}
	public function get_title() {
		return 'Blitz · Inhaber & Schweißerzeichen';
	}
	public function get_icon() {
		return 'eicon-person';
	}
	protected function register_controls() {
		$d = blitz_defaults()['owner'];
		$this->section( 'content', 'Inhalt' );
		$this->hint();
		foreach ( array( 'label' => 'Kicker', 'title' => 'Überschrift' ) as $k => $l ) {
			$this->txt( $k, $l, $d[ $k ] );
		}
		$this->txt( 'lead', 'Einleitung', $d['lead'], 'area' );
		$this->txt( 'text', 'Text', $d['text'], 'area' );
		foreach ( array( 'button' => 'Button', 'signature' => 'Unterschrift', 'role' => 'Funktion' ) as $k => $l ) {
			$this->txt( $k, $l, $d[ $k ] );
		}
		$this->end_controls_section();
		$this->section( 'coin', '3D-Schweißerzeichen' );
		foreach ( array( 'coin_front' => 'Vorderseite (Kürzel)', 'coin_back' => 'Rückseite (Zahl)', 'ring_front' => 'Ringtext vorne', 'ring_back' => 'Ringtext hinten', 'caption' => 'Bildunterschrift' ) as $k => $l ) {
			$this->txt( $k, $l, $d[ $k ] );
		}
		$this->end_controls_section();
		$this->style_controls();
	}
	protected function render() {
		$s    = $this->s();
		$keys = array( 'label', 'title', 'lead', 'text', 'button', 'signature', 'role', 'coin_front', 'coin_back', 'ring_front', 'ring_back', 'caption' );
		blitz_render_owner( array_intersect_key( $s, array_flip( $keys ) ) );
	}
}

/* ==========================================================================
   Ablauf
   ========================================================================== */
class Blitz_W_Process extends Blitz_Widget_Base {
	public function get_name() {
		return 'blitz-process';
	}
	public function get_title() {
		return 'Blitz · Ablauf';
	}
	public function get_icon() {
		return 'eicon-time-line';
	}
	protected function register_controls() {
		$d = blitz_defaults()['process'];
		$this->section( 'content', 'Inhalt' );
		$this->hint();
		$this->txt( 'label', 'Kicker', $d['label'] );
		$this->txt( 'title', 'Überschrift', $d['title'] );
		$this->txt( 'sub', 'Text', $d['sub'], 'area' );
		$rep = new Repeater();
		$rep->add_control( 'title', array( 'label' => 'Schritt', 'type' => CM::TEXT, 'default' => '', 'label_block' => true ) );
		$rep->add_control( 'text', array( 'label' => 'Text', 'type' => CM::TEXTAREA, 'default' => '' ) );
		$this->add_control(
			'steps',
			array(
				'label'       => 'Schritte',
				'type'        => CM::REPEATER,
				'fields'      => $rep->get_controls(),
				'default'     => $d['steps'],
				'title_field' => '{{{ title }}}',
			)
		);
		$this->end_controls_section();
		$this->style_controls();
	}
	protected function render() {
		$s = $this->s();
		blitz_render_process(
			array(
				'label' => $s['label'],
				'title' => $s['title'],
				'sub'   => $s['sub'],
				'steps' => $this->rows( $s['steps'], array( 'title', 'text' ) ),
			)
		);
	}
}

/* ==========================================================================
   Einsatzgebiet
   ========================================================================== */
class Blitz_W_Area extends Blitz_Widget_Base {
	public function get_name() {
		return 'blitz-area';
	}
	public function get_title() {
		return 'Blitz · Einsatzgebiet (Karte)';
	}
	public function get_icon() {
		return 'eicon-map-pin';
	}
	protected function register_controls() {
		$d = blitz_defaults()['area'];
		$this->section( 'content', 'Inhalt' );
		$this->hint();
		$this->txt( 'label', 'Kicker', $d['label'] );
		$this->txt( 'title', 'Überschrift', $d['title'] );
		$this->txt( 'text', 'Text', $d['text'], 'area' );
		$rep = new Repeater();
		$rep->add_control( 'name', array( 'label' => 'Ort', 'type' => CM::TEXT, 'default' => '' ) );
		$rep->add_control( 'slug', array( 'label' => 'Unterseite (Slug unter /schweisser/)', 'type' => CM::TEXT, 'default' => '' ) );
		$rep->add_control( 'lat', array( 'label' => 'Breitengrad', 'type' => CM::NUMBER, 'step' => 0.0001, 'default' => 47.9 ) );
		$rep->add_control( 'lon', array( 'label' => 'Längengrad', 'type' => CM::NUMBER, 'step' => 0.0001, 'default' => 10.6 ) );
		$rep->add_control( 'km', array( 'label' => 'Entfernung (km)', 'type' => CM::NUMBER, 'default' => 10 ) );
		$rep->add_control( 'pos', array( 'label' => 'Beschriftung', 'type' => CM::SELECT, 'options' => array( 'l' => 'links', 'r' => 'rechts', 'o' => 'oben', 'u' => 'unten' ), 'default' => 'r' ) );
		$towns = array();
		foreach ( $d['towns'] as $t ) {
			$towns[] = array_intersect_key( $t, array_flip( array( 'name', 'slug', 'lat', 'lon', 'km', 'pos' ) ) );
		}
		$this->add_control(
			'towns',
			array(
				'label'       => 'Orte (über 44 km erscheinen als Pfeil am Kartenrand)',
				'type'        => CM::REPEATER,
				'fields'      => $rep->get_controls(),
				'default'     => $towns,
				'title_field' => '{{{ name }}} · {{{ km }}} km',
			)
		);
		$this->end_controls_section();
		$this->style_controls();
	}
	protected function render() {
		$s     = $this->s();
		$towns = $this->rows( $s['towns'], array( 'name', 'slug', 'lat', 'lon', 'km', 'pos' ) );
		foreach ( $towns as &$t ) {
			$t['slug'] = $t['slug'] ? $t['slug'] : sanitize_title( $t['name'] );
		}
		unset( $t );
		blitz_render_area(
			array(
				'label' => $s['label'],
				'title' => $s['title'],
				'text'  => $s['text'],
				'towns' => $towns,
			)
		);
	}
}

/* ==========================================================================
   FAQ
   ========================================================================== */
class Blitz_W_Faq extends Blitz_Widget_Base {
	public function get_name() {
		return 'blitz-faq';
	}
	public function get_title() {
		return 'Blitz · Häufige Fragen (mit FAQ-Schema)';
	}
	public function get_icon() {
		return 'eicon-accordion';
	}
	protected function register_controls() {
		$d = blitz_defaults()['faq'];
		$this->section( 'content', 'Inhalt' );
		$this->hint();
		$this->txt( 'label', 'Kicker', $d['label'] );
		$this->txt( 'title', 'Überschrift', $d['title'] );
		$this->txt( 'sub', 'Text', $d['sub'] );
		$rep = new Repeater();
		$rep->add_control( 'q', array( 'label' => 'Frage', 'type' => CM::TEXT, 'default' => '', 'label_block' => true ) );
		$rep->add_control( 'a', array( 'label' => 'Antwort', 'type' => CM::TEXTAREA, 'default' => '' ) );
		$this->add_control(
			'items',
			array(
				'label'       => 'Fragen',
				'type'        => CM::REPEATER,
				'fields'      => $rep->get_controls(),
				'default'     => $d['items'],
				'title_field' => '{{{ q }}}',
			)
		);
		$this->end_controls_section();
		$this->style_controls();
	}
	protected function render() {
		$s = $this->s();
		blitz_render_faq(
			array(
				'label' => $s['label'],
				'title' => $s['title'],
				'sub'   => $s['sub'],
				'items' => $this->rows( $s['items'], array( 'q', 'a' ) ),
			)
		);
	}
}

/* ==========================================================================
   Kontakt
   ========================================================================== */
class Blitz_W_Contact extends Blitz_Widget_Base {
	public function get_name() {
		return 'blitz-contact';
	}
	public function get_title() {
		return 'Blitz · Kontakt & Anfrageformular';
	}
	public function get_icon() {
		return 'eicon-form-horizontal';
	}
	protected function register_controls() {
		$d = blitz_defaults()['contact'];
		$this->section( 'content', 'Inhalt' );
		$this->hint();
		$this->txt( 'label', 'Kicker', $d['label'] );
		$this->txt( 'title', 'Überschrift', $d['title'] );
		$this->txt( 'sub', 'Text', $d['sub'], 'area' );
		$this->end_controls_section();
		$this->style_controls();
	}
	protected function render() {
		$s = $this->s();
		blitz_render_contact( array( 'label' => $s['label'], 'title' => $s['title'], 'sub' => $s['sub'] ) );
	}
}

/* ==========================================================================
   Suchbegriffe / Hashtags
   ========================================================================== */
class Blitz_W_Keywords extends Blitz_Widget_Base {
	public function get_name() {
		return 'blitz-keywords';
	}
	public function get_title() {
		return 'Blitz · Hashtags & Einsatzorte (SEO)';
	}
	public function get_icon() {
		return 'eicon-tags';
	}
	protected function register_controls() {
		$this->section( 'content', 'Inhalt' );
		$this->hint();
		$this->txt( 'title', 'Überschrift', 'Schweißer in *Ihrer Nähe*' );
		$this->txt( 'text', 'Text', 'Mobile Schweißarbeiten im Ostallgäu, im ganzen Allgäu und bis München – mit eigenem Gerät direkt vor Ort.', 'area' );
		$this->txt( 'hashtags', 'Zusätzliche Hashtags', implode( ' ', array_slice( blitz_defaults()['seo']['hashtags'], 10 ) ), 'area' );
		$this->end_controls_section();
		$this->style_controls();
	}
	protected function render() {
		$s = $this->s();
		blitz_render_keywords( array( 'title' => $s['title'], 'text' => $s['text'], 'hashtags' => $s['hashtags'] ) );
	}
}
