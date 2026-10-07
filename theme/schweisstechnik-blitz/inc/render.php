<?php
/**
 * Ausgabe aller Abschnitte. Jede Funktion bekommt ein Daten-Array (überschreibt die Standardinhalte)
 * und gibt das fertige Markup aus – identisch für Elementor-Widgets, Theme-Fallback und statischen Export.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'BLITZ_THEME' ) || exit;

function blitz_merge( $section, $d ) {
	$base = blitz_defaults();
	$base = isset( $base[ $section ] ) ? $base[ $section ] : array();
	foreach ( (array) $d as $k => $v ) {
		if ( null !== $v && '' !== $v && array() !== $v ) {
			$base[ $k ] = $v;
		}
	}
	return $base;
}

function blitz_label( $num, $text ) {
	$n = $num ? '<b>' . blitz_h( $num ) . '</b> ' : '';
	return '<p class="label">' . $n . blitz_h( $text ) . '</p>';
}

/* ==========================================================================
   Kopf & Fuß (Navigation, Menü, Footer, Dock)
   ========================================================================== */

function blitz_nav_items() {
	$items = array(
		array( 'leistungen', 'Leistungen' ),
		array( 'steignaht', 'Steignaht' ),
		array( 'ablauf', 'Ablauf' ),
		array( 'einsatzgebiet', 'Einsatzgebiet' ),
		array( 'faq', 'Fragen' ),
		array( 'kontakt', 'Kontakt' ),
	);
	$out = array();
	if ( ! blitz_static() && function_exists( 'has_nav_menu' ) && has_nav_menu( 'primary' ) ) {
		$locations = get_nav_menu_locations();
		$menu      = wp_get_nav_menu_items( $locations['primary'] );
		foreach ( (array) $menu as $m ) {
			if ( empty( $m->menu_item_parent ) ) {
				$out[] = array( $m->url, $m->title );
			}
		}
		if ( $out ) {
			return $out;
		}
	}
	foreach ( $items as $i ) {
		$out[] = array( blitz_anchor( $i[0] ), $i[1] );
	}
	return $out;
}

function blitz_render_chrome_top( $args = array() ) {
	$c       = blitz_company();
	$loader  = ! empty( $args['loader'] );
	$items   = blitz_nav_items();
	$home    = blitz_is_front() ? '#top' : blitz_url( '/' );
	?>
<a class="skip" href="#inhalt">Zum Inhalt springen</a>
<?php if ( $loader ) : ?>
<div class="loader" aria-hidden="true">
  <div class="loader__brand"><span>Schweisstechnik</span><b>Blitz</b></div>
  <div class="loader__seam"><i class="loader__bead"></i><i class="loader__arc"></i></div>
  <div class="loader__meta"><span>Lichtbogen zünden</span><span class="loader__count">000</span></div>
</div>
<div class="flash" aria-hidden="true"></div>
<?php endif; ?>
<?php if ( blitz_opt( 'grain', true ) ) : ?><div class="grain" aria-hidden="true"></div><?php endif; ?>
<div class="cursor" aria-hidden="true"><i class="cursor__ring"></i><i class="cursor__dot"></i></div>
<div class="seam-progress" aria-hidden="true"><i class="seam-progress__bead"></i><i class="seam-progress__head"></i></div>

<header class="nav" data-nav>
  <a class="brand" href="<?php echo blitz_h( $home ); ?>" aria-label="<?php echo blitz_h( $c['name'] ); ?> – Startseite">
    <?php echo blitz_icon( 'logo' ); ?>
    <span class="brand__text"><small>Schweisstechnik</small><strong>Blitz</strong></span>
  </a>
  <nav class="nav__links" aria-label="Hauptnavigation">
	<?php foreach ( $items as $it ) : ?>
    <a href="<?php echo blitz_h( $it[0] ); ?>"><?php echo blitz_h( $it[1] ); ?></a>
	<?php endforeach; ?>
  </nav>
  <a class="nav__call" href="<?php echo blitz_h( blitz_tel() ); ?>" data-magnetic><i class="pulse"></i><span><?php echo blitz_h( $c['phone'] ); ?></span></a>
  <button class="nav__burger" type="button" aria-expanded="false" aria-controls="menu" aria-label="Menü öffnen"><i></i><i></i></button>
</header>

<div class="menu" id="menu" hidden>
  <nav class="menu__links" aria-label="Mobile Navigation">
	<?php foreach ( $items as $n => $it ) : ?>
    <a href="<?php echo blitz_h( $it[0] ); ?>"><small><?php echo sprintf( '%02d', $n + 1 ); ?></small><?php echo blitz_h( $it[1] ); ?></a>
	<?php endforeach; ?>
  </nav>
  <div class="menu__foot">
    <a href="<?php echo blitz_h( blitz_tel() ); ?>"><?php echo blitz_h( $c['phone'] ); ?></a>
    <a href="<?php echo blitz_h( blitz_wa() ); ?>" target="_blank" rel="noopener">WhatsApp</a>
    <span><?php echo blitz_h( $c['street'] . ' · ' . $c['zip'] . ' ' . $c['city'] ); ?></span>
  </div>
</div>
	<?php
}

function blitz_render_chrome_bottom() {
	$c   = blitz_company();
	$d   = blitz_defaults();
	$f   = $d['footer'];
	if ( ! blitz_static() ) {
		$f['kicker'] = blitz_opt( 'footer_kicker', $f['kicker'] );
	}
	?>
<footer class="footer">
  <div class="wrap">
    <div class="footer__cta">
      <p class="footer__kicker"><?php echo blitz_h( $f['kicker'] ); ?></p>
      <a class="footer__phone" href="<?php echo blitz_h( blitz_tel() ); ?>" data-magnetic><?php echo blitz_h( $c['phone'] ); ?></a>
    </div>

    <div class="footer__grid">
      <div>
        <p class="footer__h"><?php echo blitz_h( $c['name'] ); ?></p>
        <p>Inhaber <?php echo blitz_h( $c['owner'] ); ?><br><?php echo blitz_h( $c['street'] ); ?><br><?php echo blitz_h( $c['zip'] . ' ' . $c['city'] ); ?></p>
        <p><a href="<?php echo blitz_h( blitz_tel() ); ?>"><?php echo blitz_h( $c['phone_pretty'] ); ?></a><br><a href="mailto:<?php echo blitz_h( $c['email'] ); ?>"><?php echo blitz_h( $c['email'] ); ?></a></p>
      </div>
      <div>
        <p class="footer__h">Leistungen</p>
        <ul>
	<?php foreach ( $d['services']['items'] as $s ) : ?>
          <li><a href="<?php echo blitz_h( blitz_page_url( 'leistung', $s['slug'] ) ); ?>"><?php echo blitz_h( $s['title'] ); ?></a></li>
	<?php endforeach; ?>
        </ul>
      </div>
      <div>
        <p class="footer__h">Einsatzorte</p>
        <ul class="footer__towns">
	<?php foreach ( $d['area']['towns'] as $t ) : ?>
          <li><a href="<?php echo blitz_h( blitz_page_url( 'ort', $t['slug'] ) ); ?>">Schweißer <?php echo blitz_h( $t['name'] ); ?></a></li>
	<?php endforeach; ?>
        </ul>
      </div>
      <div>
        <p class="footer__h">Rechtliches</p>
        <ul>
          <li><a href="<?php echo blitz_h( blitz_page_url( 'impressum' ) ); ?>">Impressum</a></li>
          <li><a href="<?php echo blitz_h( blitz_page_url( 'datenschutz' ) ); ?>">Datenschutzerklärung</a></li>
        </ul>
	<?php if ( $c['sister_url'] ) : ?>
        <p class="footer__h footer__h--gap"><?php echo blitz_h( $c['sister_label'] ); ?></p>
        <ul><li><a href="<?php echo blitz_h( $c['sister_url'] ); ?>" target="_blank" rel="noopener"><?php echo blitz_h( $c['sister_name'] ); ?></a></li></ul>
	<?php endif; ?>
      </div>
    </div>

    <ul class="footer__tags" aria-label="Hashtags">
	<?php foreach ( $d['seo']['hashtags'] as $tag ) : ?>
      <li><?php echo blitz_h( $tag ); ?></li>
	<?php endforeach; ?>
    </ul>

    <div class="footer__word" data-word3d aria-hidden="true"><span data-text="<?php echo blitz_h( $f['word'] ); ?>"><?php echo blitz_h( $f['word'] ); ?></span></div>

    <div class="footer__bottom">
      <span>© <?php echo date( 'Y' ); ?> <?php echo blitz_h( $c['name'] ); ?></span>
	<?php if ( $c['credit_url'] ) : ?>
      <a href="<?php echo blitz_h( $c['credit_url'] ); ?>" target="_blank" rel="noopener">Website powered by <?php echo blitz_h( $c['credit_name'] ); ?></a>
	<?php endif; ?>
    </div>
  </div>
</footer>

<nav class="dock" aria-label="Schnellkontakt">
  <a href="<?php echo blitz_h( blitz_tel() ); ?>"><?php echo blitz_icon( 'phone' ); ?>Anrufen</a>
  <a href="<?php echo blitz_h( blitz_wa() ); ?>" target="_blank" rel="noopener"><?php echo blitz_icon( 'whatsapp' ); ?>WhatsApp</a>
</nav>
	<?php
}

/* ==========================================================================
   HERO (Startseite und Unterseiten) – mit 3D-Szene, Telefon und Schnellanfrage
   ========================================================================== */

function blitz_render_hero( $d = array() ) {
	$d    = blitz_merge( 'hero', $d );
	$c    = blitz_company();
	$sub  = ! empty( $d['sub'] );
	$form = ! empty( $d['show_form'] ) && 'no' !== $d['show_form'];
	$show3d = ! isset( $d['show_3d'] ) || 'no' !== $d['show_3d'];
	$id   = isset( $d['id'] ) ? $d['id'] : 'top';
	$cls  = 'hero blitz-sec' . ( $sub ? ' hero--sub' : '' ) . ( $form ? ' hero--form' : '' ) . ( $show3d ? '' : ' no-webgl' );
	$h1   = blitz_plain( $d['eyebrow'] ) . ': ' . blitz_plain( $d['title1'] . ' ' . $d['title2'] );
	?>
<section class="<?php echo blitz_h( $cls ); ?>" id="<?php echo blitz_h( $id ); ?>" aria-labelledby="<?php echo blitz_h( $id ); ?>-title">
  <div class="hero__stage" aria-hidden="true">
	<?php if ( $show3d ) : ?><canvas class="hero__canvas"></canvas><?php endif; ?>
    <div class="hero__fallback"><i></i></div>
  </div>
  <div class="hero__shade" aria-hidden="true"></div>

	<?php if ( ! isset( $d['show_tags'] ) || 'no' !== $d['show_tags'] ) : ?>
  <div class="hero__tags" aria-hidden="true">
    <div class="tag tag--arc" data-tag="arc"><i class="tag__dot"></i><i class="tag__line"></i><span class="tag__body"><b><?php echo blitz_h( $d['tag_arc'][0] ); ?></b><small><?php echo blitz_h( $d['tag_arc'][1] ); ?></small></span></div>
    <div class="tag tag--temper" data-tag="temper"><i class="tag__dot"></i><i class="tag__line"></i><span class="tag__body"><b><?php echo blitz_h( $d['tag_temper'][0] ); ?></b><small><?php echo blitz_h( $d['tag_temper'][1] ); ?></small></span></div>
  </div>
	<?php endif; ?>

  <div class="hero__inner wrap">
    <div class="hero__copy">
	<?php if ( ! empty( $d['crumbs'] ) ) : ?>
      <nav class="crumbs" aria-label="Brotkrumen" data-hero-fade>
		<?php
		$last = count( $d['crumbs'] ) - 1;
		foreach ( $d['crumbs'] as $i => $cr ) :
			if ( $i === $last ) :
				?>
        <span aria-current="page"><?php echo blitz_h( $cr[0] ); ?></span>
			<?php else : ?>
        <a href="<?php echo blitz_h( $cr[1] ); ?>"><?php echo blitz_h( $cr[0] ); ?></a><i>/</i>
				<?php
			endif;
		endforeach;
		?>
      </nav>
	<?php endif; ?>
      <h1 class="hero__h1" id="<?php echo blitz_h( $id ); ?>-title" aria-label="<?php echo blitz_h( $h1 ); ?>">
        <span class="eyebrow" data-scramble><i class="pulse"></i><span><?php echo blitz_h( $d['eyebrow'] ); ?></span></span>
        <span class="hero__title">
          <span class="hero__l1"><?php echo blitz_h( $d['title1'] ); ?></span>
	<?php if ( '' !== trim( $d['title2'] ) ) : ?>
          <em class="hero__l2 heat-text"><?php echo blitz_h( $d['title2'] ); ?></em>
	<?php endif; ?>
        </span>
      </h1>
      <p class="hero__lead" data-hero-fade><?php echo blitz_h( $d['lead'] ); ?></p>
      <div class="hero__cta" data-hero-fade>
        <a class="btn btn--molten" href="<?php echo blitz_h( blitz_tel() ); ?>" data-magnetic><?php echo blitz_icon( 'phone' ); ?><span><?php echo blitz_h( trim( $c['phone'] . ' ' . $d['cta_call'] ) ); ?></span></a>
        <a class="btn btn--ghost" href="#kontakt" data-magnetic><span><?php echo blitz_h( $d['cta_form'] ); ?></span><?php echo blitz_icon( 'arrow' ); ?></a>
      </div>
    </div>
	<?php if ( $form ) : ?>
		<?php blitz_render_quickform( $d ); ?>
	<?php endif; ?>
  </div>

	<?php if ( ! empty( $d['facts'] ) ) : ?>
  <dl class="hero__facts wrap" data-hero-fade>
		<?php foreach ( $d['facts'] as $i => $fact ) : ?>
    <div><dt><?php echo 0 === $i && preg_match( '/^(\d+)(.*)$/u', $fact['value'], $m ) ? '<span data-count="' . (int) $m[1] . '">' . (int) $m[1] . '</span>' . blitz_h( $m[2] ) : blitz_h( $fact['value'] ); ?></dt><dd><?php echo blitz_h( $fact['label'] ); ?></dd></div>
		<?php endforeach; ?>
  </dl>
	<?php endif; ?>

	<?php if ( ! isset( $d['show_hud'] ) || 'no' !== $d['show_hud'] ) : ?>
  <div class="hero__hud" aria-hidden="true">
		<?php foreach ( $d['hud'] as $line ) : ?>
    <span><?php echo blitz_kses( $line ); ?></span>
		<?php endforeach; ?>
  </div>
	<?php endif; ?>
</section>
	<?php
}

function blitz_render_quickform( $d ) {
	$c    = blitz_company();
	$opts = blitz_defaults()['form'];
	$pre  = isset( $d['form_pick'] ) ? $d['form_pick'] : '';
	?>
    <aside class="hero__form" aria-label="<?php echo blitz_h( $d['form_kicker'] ); ?>" data-hero-fade>
      <div class="qf__head">
        <span class="qf__kicker"><i class="pulse"></i><?php echo blitz_h( $d['form_kicker'] ); ?></span>
        <a class="qf__phone" href="<?php echo blitz_h( blitz_tel() ); ?>">
			<?php echo blitz_icon( 'phone' ); ?>
          <span><small>Direkt anrufen</small><strong><?php echo blitz_h( $c['phone'] ); ?></strong></span>
        </a>
        <a class="qf__wa" href="<?php echo blitz_h( blitz_wa() ); ?>" target="_blank" rel="noopener"><?php echo blitz_icon( 'whatsapp' ); ?><span>Fotos per WhatsApp</span></a>
      </div>
      <form class="qf" action="<?php echo blitz_h( blitz_form_action() ); ?>" method="post" novalidate data-quickform>
        <p class="qf__title"><?php echo blitz_em( $d['form_title'] ); ?></p>
        <p class="qf__text"><?php echo blitz_h( $d['form_text'] ); ?></p>
		<?php echo blitz_form_hidden( 'hero' ); ?>
        <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <label class="field field--sm"><span>Art der Arbeit</span>
          <select name="arbeit[]">
            <option value="">Bitte wählen …</option>
		<?php foreach ( $opts['arbeit'] as $o ) : ?>
            <option<?php echo $o === $pre ? ' selected' : ''; ?>><?php echo blitz_h( $o ); ?></option>
		<?php endforeach; ?>
          </select>
        </label>
        <div class="fields fields--2">
          <label class="field field--sm"><span>Name <abbr title="Pflichtfeld">*</abbr></span><input type="text" name="name" autocomplete="name" required></label>
          <label class="field field--sm"><span>Telefon <abbr title="Pflichtfeld">*</abbr></span><input type="tel" name="telefon" autocomplete="tel" inputmode="tel" required></label>
        </div>
        <label class="field field--sm"><span>Kurz beschreiben (mit Ort)</span><textarea name="beschreibung" rows="2" placeholder="z. B. Riss am Tankstutzen, 87600 Kaufbeuren"></textarea></label>
        <label class="consent consent--sm"><input type="checkbox" name="einwilligung" value="1" required><span>Einverstanden, dass meine Angaben zur Bearbeitung der Anfrage verwendet werden (<a href="<?php echo blitz_h( blitz_page_url( 'datenschutz' ) ); ?>">Datenschutz</a>).</span></label>
        <p class="wizard__error" role="alert" hidden></p>
        <button type="submit" class="btn btn--molten btn--sm qf__submit"><span><?php echo blitz_h( $d['form_button'] ); ?></span><?php echo blitz_icon( 'arrow' ); ?></button>
        <div class="qf__done" hidden tabindex="-1">
          <svg viewBox="0 0 80 80" aria-hidden="true"><circle cx="40" cy="40" r="34"/><path d="M25 41l10 10 20-22"/></svg>
          <p><strong>Anfrage gesendet.</strong> Danke – Sie hören in Kürze von uns. Eilt es? <a href="<?php echo blitz_h( blitz_tel() ); ?>"><?php echo blitz_h( $c['phone'] ); ?></a></p>
        </div>
      </form>
    </aside>
	<?php
}

/* ==========================================================================
   MARQUEE
   ========================================================================== */

function blitz_render_marquee( $d = array() ) {
	$d     = blitz_merge( 'marquee', $d );
	$big   = blitz_lines( $d['big'] );
	$small = blitz_tags( $d['small'] );
	?>
<div class="marquee blitz-sec" aria-hidden="true">
  <div class="marquee__row marquee__row--big"><div class="marquee__track">
	<?php foreach ( $big as $i => $w ) : ?>
    <span<?php echo $i % 2 ? ' class="o"' : ''; ?>><?php echo blitz_h( $w ); ?></span><i>✦</i>
	<?php endforeach; ?>
  </div></div>
  <div class="marquee__row marquee__row--small"><div class="marquee__track">
	<?php foreach ( $small as $w ) : ?>
    <span><?php echo blitz_h( $w ); ?></span>
	<?php endforeach; ?>
  </div></div>
</div>
	<?php
}

/* ==========================================================================
   LEISTUNGEN
   ========================================================================== */

function blitz_render_services( $d = array() ) {
	$d     = blitz_merge( 'services', $d );
	$id    = isset( $d['id'] ) ? $d['id'] : 'leistungen';
	$num   = isset( $d['num'] ) ? $d['num'] : '01';
	$items = $d['items'];
	if ( ! empty( $d['exclude'] ) ) {
		$items = array_values(
			array_filter(
				$items,
				function ( $s ) use ( $d ) {
					return $s['slug'] !== $d['exclude'];
				}
			)
		);
	}
	?>
<section class="services blitz-sec" id="<?php echo blitz_h( $id ); ?>" aria-labelledby="<?php echo blitz_h( $id ); ?>-title">
  <div class="wrap">
    <header class="sec-head">
		<?php echo blitz_label( $num, $d['label'] ); ?>
      <h2 class="h2" id="<?php echo blitz_h( $id ); ?>-title" data-split="chars"><?php echo blitz_em( $d['title'] ); ?></h2>
    </header>
	<?php if ( ! empty( $d['statement'] ) ) : ?>
    <p class="statement" data-scrub-words><?php echo blitz_h( $d['statement'] ); ?></p>
	<?php endif; ?>
    <div class="cards">
	<?php
	foreach ( $items as $i => $s ) :
		$url = ! empty( $s['url'] ) ? $s['url'] : ( ! empty( $s['slug'] ) ? blitz_page_url( 'leistung', $s['slug'] ) : '' );
		?>
      <article class="card" data-tilt>
        <i class="card__glow" aria-hidden="true"></i>
        <div class="card__top"><span class="card__num"><?php echo sprintf( '%02d', $i + 1 ); ?></span><?php echo blitz_icon( ! empty( $s['icon'] ) ? $s['icon'] : 'tank' ); ?></div>
        <h3><?php if ( $url ) : ?><a class="card__title" href="<?php echo blitz_h( $url ); ?>"><?php echo blitz_h( $s['title'] ); ?></a><?php else : ?><?php echo blitz_h( $s['title'] ); ?><?php endif; ?></h3>
        <p><?php echo blitz_h( $s['text'] ); ?></p>
        <ul class="card__tags">
		<?php foreach ( blitz_tags( isset( $s['tags'] ) ? $s['tags'] : '' ) as $t ) : ?>
          <li><?php echo blitz_h( $t ); ?></li>
		<?php endforeach; ?>
        </ul>
        <div class="card__links">
		<?php if ( $url ) : ?>
          <a class="card__link" href="<?php echo blitz_h( $url ); ?>"><?php echo blitz_h( $d['link_text'] ); ?><?php echo blitz_icon( 'arrow' ); ?></a>
		<?php endif; ?>
          <a class="card__link card__link--muted" href="#kontakt"<?php echo ! empty( $s['pick'] ) ? ' data-pick="' . blitz_h( $s['pick'] ) . '"' : ''; ?><?php echo ! empty( $s['hoehe'] ) ? ' data-pick-hoehe="' . blitz_h( $s['hoehe'] ) . '"' : ''; ?>>Anfrage stellen</a>
        </div>
      </article>
	<?php endforeach; ?>
    </div>
  </div>
</section>
	<?php
}

/* ==========================================================================
   WERTE
   ========================================================================== */

function blitz_render_values( $d = array() ) {
	$items = ! empty( $d['items'] ) ? $d['items'] : blitz_defaults()['values'];
	$roman = array( 'I', 'II', 'III', 'IV', 'V', 'VI' );
	?>
<section class="values blitz-sec" aria-label="Arbeitsweise">
  <div class="wrap values__grid">
	<?php foreach ( $items as $i => $v ) : ?>
    <div class="value" data-reveal>
      <i class="value__line" aria-hidden="true"></i>
      <span class="value__idx"><?php echo blitz_h( isset( $roman[ $i ] ) ? $roman[ $i ] : $i + 1 ); ?></span>
      <h3 class="value__word"><?php echo blitz_h( $v['title'] ); ?></h3>
      <p><?php echo blitz_h( $v['text'] ); ?></p>
    </div>
	<?php endforeach; ?>
  </div>
</section>
	<?php
}

/* ==========================================================================
   STEIGNAHT / SCHWEISSPOSITIONEN
   ========================================================================== */

function blitz_render_positions( $d = array() ) {
	$d     = blitz_merge( 'positions', $d );
	$items = $d['items'];
	$pf    = $items['PF'];
	?>
<section class="pos blitz-sec" id="steignaht" aria-labelledby="steignaht-title">
  <div class="pos__bgword" aria-hidden="true"><span>Steignaht</span></div>
  <div class="wrap pos__grid">
    <div class="pos__text">
		<?php echo blitz_label( '02', $d['label'] ); ?>
      <h2 class="h2" id="steignaht-title" data-split="chars"><?php echo blitz_em( $d['title'] ); ?></h2>
      <p data-reveal><?php echo blitz_h( $d['text1'] ); ?></p>
      <p data-reveal><?php echo blitz_h( $d['text2'] ); ?></p>
    </div>

    <div class="viewer" data-positions data-reveal>
      <div class="viewer__stage">
        <span class="viewer__code" aria-hidden="true">PF</span>
        <svg class="viewer__svg" viewBox="0 0 480 400" aria-hidden="true">
          <defs>
            <pattern id="hatch" width="10" height="10" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><path d="M0 0v10" stroke="rgba(239,234,226,.22)" stroke-width="1.2"/></pattern>
            <linearGradient id="plateG" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#4a4f57"/><stop offset=".5" stop-color="#2b2f35"/><stop offset="1" stop-color="#1c1f23"/></linearGradient>
            <linearGradient id="beadG" gradientUnits="userSpaceOnUse" x1="240" y1="348" x2="240" y2="80">
              <stop offset="0" stop-color="#59606a"/><stop offset=".35" stop-color="#3d63b8"/><stop offset=".55" stop-color="#7b4fa6"/><stop offset=".72" stop-color="#d9b45b"/><stop offset=".88" stop-color="#ff6a1a"/><stop offset="1" stop-color="#fff1d0"/>
            </linearGradient>
            <radialGradient id="arcG"><stop offset="0" stop-color="#ffffff"/><stop offset=".25" stop-color="#cfeaff" stop-opacity=".9"/><stop offset=".55" stop-color="#ff8a2a" stop-opacity=".45"/><stop offset="1" stop-color="#ff6a1a" stop-opacity="0"/></radialGradient>
          </defs>
          <g class="viewer__grid"><path d="M0 50h480M0 100h480M0 150h480M0 200h480M0 250h480M0 300h480M0 350h480M60 0v400M120 0v400M180 0v400M240 0v400M300 0v400M360 0v400M420 0v400"/></g>
          <g class="v-ceiling"><rect x="40" y="18" width="400" height="22" fill="url(#hatch)"/><path d="M40 40h400" stroke="rgba(239,234,226,.45)"/></g>
          <g class="v-floor"><rect x="40" y="360" width="400" height="24" fill="url(#hatch)"/><path d="M40 360h400" stroke="rgba(239,234,226,.45)"/><text x="44" y="352">Boden</text></g>
          <polygon class="v-plate" points="150,70 330,88 330,348 150,356" fill="url(#plateG)" stroke="rgba(239,234,226,.28)"/>
          <line class="v-groove" x1="240" y1="352" x2="240" y2="79" stroke="#0b0b0c" stroke-width="7" stroke-linecap="round"/>
          <line class="v-bead" x1="240" y1="352" x2="240" y2="352" stroke="url(#beadG)" stroke-width="9" stroke-linecap="round"/>
          <g class="v-arrow"><line x1="0" y1="0" x2="0" y2="0"/><path d="M0 0"/></g>
          <g class="v-sparks"></g>
          <circle class="v-arc" cx="240" cy="352" r="26" fill="url(#arcG)"/>
          <g class="v-electrode" transform="translate(240 352) rotate(145)">
            <rect x="-3" y="-118" width="6" height="114" rx="2" fill="#bdb4a3"/>
            <rect x="-2" y="-6" width="4" height="7" fill="#ffb347"/>
            <rect x="-7" y="-176" width="14" height="60" rx="5" fill="#1d1e21" stroke="rgba(239,234,226,.25)"/>
            <rect x="-8" y="-124" width="16" height="8" rx="2" fill="#b87333"/>
          </g>
        </svg>
        <div class="viewer__corner viewer__corner--tl" aria-hidden="true">Schweißposition</div>
        <div class="viewer__corner viewer__corner--br" aria-hidden="true">DIN EN ISO 6947</div>
      </div>
      <div class="viewer__tabs" role="group" aria-label="Schweißposition wählen">
	<?php foreach ( $items as $key => $it ) : ?>
        <button type="button" data-pos="<?php echo blitz_h( $key ); ?>" data-t="<?php echo blitz_h( $it['t'] ); ?>" data-x="<?php echo blitz_h( $it['x'] ); ?>" aria-pressed="<?php echo 'PF' === $key ? 'true' : 'false'; ?>"><b><?php echo blitz_h( $key ); ?></b><span><?php echo blitz_h( $it['short'] ); ?></span></button>
	<?php endforeach; ?>
      </div>
      <div class="viewer__info" aria-live="polite">
        <h3 class="viewer__title"><?php echo blitz_h( $pf['t'] ); ?></h3>
        <p class="viewer__desc"><?php echo blitz_h( $pf['x'] ); ?></p>
      </div>
    </div>
  </div>
</section>
	<?php
}

/* ==========================================================================
   INHABER / SCHWEISSERZEICHEN (3D-Münze)
   ========================================================================== */

function blitz_render_owner( $d = array() ) {
	$d = blitz_merge( 'owner', $d );
	?>
<section class="mark blitz-sec" id="inhaber" aria-labelledby="inhaber-title">
  <div class="wrap mark__grid">
    <figure class="mark__figure">
      <div class="coin" data-coin aria-hidden="true">
        <div class="coin__body">
          <div class="coin__edge"></div>
          <div class="coin__face coin__face--front">
            <svg viewBox="0 0 200 200"><defs><path id="ring1" d="M100 100m-74 0a74 74 0 1 1 148 0a74 74 0 1 1-148 0"/></defs><circle cx="100" cy="100" r="88" class="coin__rim"/><circle cx="100" cy="100" r="62" class="coin__rim coin__rim--in"/><text class="coin__ringtext"><textPath href="#ring1" startOffset="0"><?php echo blitz_h( $d['ring_front'] ); ?></textPath></text><text x="100" y="121" text-anchor="middle" class="coin__mono"><?php echo blitz_h( $d['coin_front'] ); ?></text></svg>
          </div>
          <div class="coin__face coin__face--back">
            <svg viewBox="0 0 200 200"><defs><path id="ring2" d="M100 100m-74 0a74 74 0 1 1 148 0a74 74 0 1 1-148 0"/></defs><circle cx="100" cy="100" r="88" class="coin__rim"/><circle cx="100" cy="100" r="62" class="coin__rim coin__rim--in"/><text class="coin__ringtext"><textPath href="#ring2" startOffset="0"><?php echo blitz_h( $d['ring_back'] ); ?></textPath></text><text x="100" y="124" text-anchor="middle" class="coin__mono"><?php echo blitz_h( $d['coin_back'] ); ?></text></svg>
          </div>
        </div>
        <i class="coin__shadow"></i>
      </div>
      <figcaption><?php echo blitz_em( $d['caption'] ); ?></figcaption>
    </figure>

    <div class="mark__text">
		<?php echo blitz_label( '03', $d['label'] ); ?>
      <h2 class="h2" id="inhaber-title" data-split="chars"><?php echo blitz_em( $d['title'] ); ?></h2>
      <p class="mark__lead" data-reveal><?php echo blitz_h( $d['lead'] ); ?></p>
      <p data-reveal><?php echo blitz_h( $d['text'] ); ?></p>
      <div class="mark__actions" data-reveal>
        <a class="btn btn--molten" href="<?php echo blitz_h( blitz_tel() ); ?>" data-magnetic><?php echo blitz_icon( 'phone' ); ?><span><?php echo blitz_h( $d['button'] ); ?></span></a>
        <span class="mark__sig"><?php echo blitz_h( $d['signature'] ); ?><small><?php echo blitz_h( $d['role'] ); ?></small></span>
      </div>
    </div>
  </div>
</section>
	<?php
}

/* ==========================================================================
   ABLAUF
   ========================================================================== */

function blitz_render_process( $d = array() ) {
	$d = blitz_merge( 'process', $d );
	?>
<section class="process blitz-sec" id="ablauf" aria-labelledby="ablauf-title">
  <div class="process__pin">
    <div class="wrap">
      <header class="sec-head sec-head--row">
        <div>
			<?php echo blitz_label( '04', $d['label'] ); ?>
          <h2 class="h2" id="ablauf-title" data-split="chars"><?php echo blitz_em( $d['title'] ); ?></h2>
        </div>
        <p class="sec-head__sub" data-reveal><?php echo blitz_h( $d['sub'] ); ?></p>
      </header>
      <div class="process__track">
        <div class="process__seam" aria-hidden="true"><i class="process__bead"></i><i class="process__arc"></i></div>
        <ol class="steps">
	<?php foreach ( $d['steps'] as $i => $s ) : ?>
          <li class="step"><span class="step__num"><?php echo sprintf( '%02d', $i + 1 ); ?></span><h3><?php echo blitz_h( $s['title'] ); ?></h3><p><?php echo blitz_h( $s['text'] ); ?></p></li>
	<?php endforeach; ?>
        </ol>
      </div>
    </div>
  </div>
</section>
	<?php
}

/* ==========================================================================
   EINSATZGEBIET (3D-Karte; Orte außerhalb von 44 km werden am Rand angezeigt)
   ========================================================================== */

function blitz_render_area( $d = array() ) {
	$d     = blitz_merge( 'area', $d );
	$towns = $d['towns'];
	$hot   = isset( $d['highlight'] ) ? $d['highlight'] : '';
	$c     = blitz_company();
	$pts   = array();
	foreach ( $towns as $t ) {
		list( $x, $y ) = blitz_map_point( $t['lat'], $t['lon'] );
		$r    = sqrt( $x * $x + $y * $y );
		$edge = $r > 44;
		if ( $edge ) {
			$x = round( $x / $r * 43, 1 );
			$y = round( $y / $r * 43, 1 );
		}
		$pts[] = array( $t, $x, $y, $edge );
	}
	$labels = array(
		'l' => array( -2.4, 0.9, 'end' ),
		'r' => array( 2.4, 0.9, 'start' ),
		'o' => array( 0, -2.8, 'middle' ),
		'u' => array( 0, 4.4, 'middle' ),
	);
	?>
<section class="area blitz-sec" id="einsatzgebiet" aria-labelledby="area-title">
  <div class="wrap area__grid">
    <div class="area__text">
		<?php echo blitz_label( '05', $d['label'] ); ?>
      <h2 class="h2" id="area-title" data-split="chars"><?php echo blitz_em( $d['title'] ); ?></h2>
      <p data-reveal><?php echo blitz_h( $d['text'] ); ?></p>
      <ul class="towns" data-reveal>
	<?php foreach ( $towns as $t ) : ?>
        <li data-town="<?php echo blitz_h( $t['slug'] ); ?>"<?php echo $hot === $t['slug'] ? ' class="is-hot"' : ''; ?>><a href="<?php echo blitz_h( blitz_page_url( 'ort', $t['slug'] ) ); ?>"><span><?php echo blitz_h( $t['name'] ); ?></span><b>ca. <?php echo (int) $t['km']; ?> km</b></a></li>
	<?php endforeach; ?>
      </ul>
    </div>
    <div class="area__map" data-map>
      <div class="map3d">
        <svg class="map" viewBox="-50 -50 100 100" role="img" aria-label="Karte: Einsatzgebiet rund um <?php echo blitz_h( $c['city'] ); ?> mit Entfernungsringen von 10 bis 40 Kilometern">
          <defs>
            <radialGradient id="mapGlow"><stop offset="0" stop-color="#ff6a1a" stop-opacity=".22"/><stop offset="1" stop-color="#ff6a1a" stop-opacity="0"/></radialGradient>
            <linearGradient id="sweepG" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#ffb347" stop-opacity="0"/><stop offset="1" stop-color="#ffb347" stop-opacity=".13"/></linearGradient>
            <marker id="mapArrow" viewBox="0 0 6 6" refX="3" refY="3" markerWidth="3" markerHeight="3" orient="auto"><path d="M0 0L6 3L0 6z" fill="#ffb347"/></marker>
          </defs>
          <circle r="46" fill="url(#mapGlow)"/>
          <g class="map__rings">
            <circle r="10"/><circle r="20"/><circle r="30"/><circle r="40"/>
            <path d="M-46 0h92M0 -46v92" class="map__axis"/>
            <text x="7.6" y="8.4">10 km</text><text x="14.7" y="15.5">20 km</text><text x="21.8" y="22.6">30 km</text><text x="28.9" y="29.7">40 km</text>
            <text x="0" y="-47.2" class="map__n" text-anchor="middle">N</text>
          </g>
          <path class="map__sweep" d="M0 0L44 0A44 44 0 0 0 31.1 -31.1Z" fill="url(#sweepG)"/>
          <g class="map__lines">
	<?php foreach ( $pts as $p ) : ?>
            <line x2="<?php echo $p[1]; ?>" y2="<?php echo $p[2]; ?>" pathLength="1"<?php echo $p[3] ? ' class="map__far" marker-end="url(#mapArrow)"' : ''; ?>/>
	<?php endforeach; ?>
          </g>
          <g class="map__towns">
	<?php
	foreach ( $pts as $p ) :
		list( $t, $x, $y, $edge ) = $p;
		$l    = isset( $labels[ $t['pos'] ] ) ? $labels[ $t['pos'] ] : $labels['r'];
		$name = $t['name'] . ( $edge ? ' · ca. ' . (int) $t['km'] . ' km' : '' );
		?>
            <a href="<?php echo blitz_h( blitz_page_url( 'ort', $t['slug'] ) ); ?>" class="town<?php echo $edge ? ' town--far' : ''; ?><?php echo $hot === $t['slug'] ? ' is-hot' : ''; ?>" data-town="<?php echo blitz_h( $t['slug'] ); ?>" transform="translate(<?php echo $x; ?> <?php echo $y; ?>)"><circle class="town__pulse" r="1.6"/><circle class="town__dot" r=".95"/><text x="<?php echo $l[0]; ?>" y="<?php echo $l[1]; ?>" text-anchor="<?php echo $l[2]; ?>"><?php echo blitz_h( $name ); ?></text></a>
	<?php endforeach; ?>
          </g>
          <g class="map__home"><circle class="map__home-pulse" r="3"/><circle r="1.7" class="map__home-dot"/><text x="3.4" y="1.1"><?php echo blitz_h( $c['city'] ); ?></text></g>
        </svg>
      </div>
    </div>
  </div>
</section>
	<?php
}

/* ==========================================================================
   FAQ (gibt zusätzlich FAQPage-Schema aus)
   ========================================================================== */

function blitz_render_faq( $d = array() ) {
	$d     = blitz_merge( 'faq', $d );
	$items = $d['items'];
	$ents  = array();
	foreach ( $items as $it ) {
		$ents[] = array(
			'@type'          => 'Question',
			'name'           => blitz_plain( $it['q'] ),
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags_compat( $it['a'] ) ),
		);
	}
	blitz_schema_add( array( '@type' => 'FAQPage', 'mainEntity' => $ents ) );
	$uid = 'qa' . substr( md5( serialize( $items ) ), 0, 4 );
	?>
<section class="faq blitz-sec" id="faq" aria-labelledby="faq-title">
  <div class="wrap faq__grid">
    <header class="sec-head faq__head">
		<?php echo blitz_label( '06', $d['label'] ); ?>
      <h2 class="h2" id="faq-title" data-split="chars"><?php echo blitz_em( $d['title'] ); ?></h2>
      <p class="sec-head__sub" data-reveal><?php echo blitz_h( $d['sub'] ); ?></p>
    </header>
    <div class="qa-list" data-faq>
	<?php
	foreach ( $items as $i => $it ) :
		$qid = $uid . '-' . ( $i + 1 );
		?>
      <div class="qa" data-reveal><h3><button type="button" aria-expanded="false" aria-controls="<?php echo $qid; ?>" id="<?php echo $qid; ?>b"><span class="qa__n"><?php echo sprintf( '%02d', $i + 1 ); ?></span><span class="qa__q"><?php echo blitz_h( $it['q'] ); ?></span><i class="qa__icon" aria-hidden="true"></i></button></h3>
        <div class="qa__a" id="<?php echo $qid; ?>" role="region" aria-labelledby="<?php echo $qid; ?>b"><div><p><?php echo blitz_kses( $it['a'] ); ?></p></div></div></div>
	<?php endforeach; ?>
    </div>
  </div>
</section>
	<?php
}

function wp_strip_all_tags_compat( $s ) {
	return trim( preg_replace( '/\s+/u', ' ', strip_tags( (string) $s ) ) );
}

/* ==========================================================================
   KONTAKT (3-Schritte-Formular)
   ========================================================================== */

function blitz_render_contact( $d = array() ) {
	$d    = blitz_merge( 'contact', $d );
	$c    = blitz_company();
	$opts = blitz_defaults()['form'];
	?>
<section class="contact blitz-sec" id="kontakt" aria-labelledby="kontakt-title">
  <div class="wrap">
    <header class="sec-head contact__head">
		<?php echo blitz_label( '07', $d['label'] ); ?>
      <h2 class="h2 h2--xl" id="kontakt-title" data-split="chars"><?php echo blitz_em( $d['title'] ); ?></h2>
      <p class="sec-head__sub" data-reveal><?php echo blitz_h( $d['sub'] ); ?></p>
    </header>

    <div class="contact__grid">
      <aside class="direct-list" data-reveal>
        <a class="direct direct--phone" href="<?php echo blitz_h( blitz_tel() ); ?>"><small>Anrufen</small><strong><?php echo blitz_h( $c['phone'] ); ?></strong><i class="direct__arrow" aria-hidden="true"></i></a>
        <a class="direct" href="<?php echo blitz_h( blitz_wa() ); ?>" target="_blank" rel="noopener"><small>WhatsApp</small><strong>Fotos per WhatsApp schicken</strong><i class="direct__arrow" aria-hidden="true"></i></a>
        <a class="direct" href="mailto:<?php echo blitz_h( $c['email'] ); ?>"><small>E-Mail</small><strong><?php echo blitz_h( $c['email'] ); ?></strong><i class="direct__arrow" aria-hidden="true"></i></a>
        <address class="direct direct--addr"><small>Adresse</small><strong><?php echo blitz_h( $c['street'] ); ?><br><?php echo blitz_h( $c['zip'] . ' ' . $c['city'] ); ?></strong></address>
      </aside>

      <form class="wizard" action="<?php echo blitz_h( blitz_form_action() ); ?>" method="post" novalidate data-wizard data-reveal>
        <ol class="wizard__progress" aria-hidden="true">
          <li class="is-active"><b>1</b> Arbeit</li>
          <li><b>2</b> Ort &amp; Zeit</li>
          <li><b>3</b> Kontakt</li>
        </ol>
        <div class="wizard__seam" aria-hidden="true"><i></i></div>
		<?php echo blitz_form_hidden( 'formular' ); ?>
        <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

        <fieldset class="wizard__panel is-active" data-step="1">
          <legend>Welche Arbeit steht an?</legend>
          <p class="hint">Mehrfachauswahl möglich</p>
          <div class="chips">
	<?php foreach ( $opts['arbeit'] as $o ) : ?>
            <label class="chip"><input type="checkbox" name="arbeit[]" value="<?php echo blitz_h( $o ); ?>"><span><?php echo blitz_h( $o ); ?></span></label>
	<?php endforeach; ?>
          </div>
        </fieldset>

        <fieldset class="wizard__panel" data-step="2" hidden>
          <legend>Wo und wann?</legend>
          <fieldset class="sub">
            <legend>Wo wird geschweißt?</legend>
            <div class="chips">
	<?php foreach ( $opts['ort_art'] as $o ) : ?>
              <label class="chip"><input type="radio" name="ort_art" value="<?php echo blitz_h( $o ); ?>"><span><?php echo blitz_h( $o ); ?></span></label>
	<?php endforeach; ?>
            </div>
          </fieldset>
          <fieldset class="sub">
            <legend>Liegt die Stelle in der Höhe?</legend>
            <div class="chips">
	<?php foreach ( $opts['hoehe'] as $o ) : ?>
              <label class="chip"><input type="radio" name="hoehe" value="<?php echo blitz_h( $o ); ?>"><span><?php echo blitz_h( $o ); ?></span></label>
	<?php endforeach; ?>
            </div>
          </fieldset>
          <div class="fields fields--2">
            <label class="field"><span>PLZ und Ort</span><input type="text" name="ort" autocomplete="address-level2" placeholder="z. B. 87600 Kaufbeuren"></label>
            <label class="field"><span>Wunschtermin</span>
              <select name="termin">
	<?php foreach ( $opts['termin'] as $o ) : ?>
                <option><?php echo blitz_h( $o ); ?></option>
	<?php endforeach; ?>
              </select>
            </label>
          </div>
          <label class="field"><span>Beschreibung</span><textarea name="beschreibung" rows="4" placeholder="Was ist kaputt oder soll neu verschweißt werden? Maße, Material, Wanddicke – soweit bekannt."></textarea></label>
        </fieldset>

        <fieldset class="wizard__panel" data-step="3" hidden>
          <legend>Wie erreichen wir Sie?</legend>
          <label class="field"><span>Name <abbr title="Pflichtfeld">*</abbr></span><input type="text" name="name" autocomplete="name" required></label>
          <div class="fields fields--2">
            <label class="field"><span>Telefon</span><input type="tel" name="telefon" autocomplete="tel" inputmode="tel"></label>
            <label class="field"><span>E-Mail</span><input type="email" name="email" autocomplete="email"></label>
          </div>
          <p class="hint">Telefon oder E-Mail genügt.</p>
          <label class="consent"><input type="checkbox" name="einwilligung" value="1" required><span>Ich bin einverstanden, dass meine Angaben zur Bearbeitung der Anfrage verwendet werden. Mehr dazu in der <a href="<?php echo blitz_h( blitz_page_url( 'datenschutz' ) ); ?>">Datenschutzerklärung</a>.</span></label>
        </fieldset>

        <p class="wizard__error" role="alert" hidden></p>

        <div class="wizard__nav">
          <button type="button" class="btn btn--ghost btn--sm" data-prev hidden>Zurück</button>
          <button type="button" class="btn btn--molten btn--sm" data-next><span>Weiter</span><?php echo blitz_icon( 'arrow' ); ?></button>
          <button type="submit" class="btn btn--molten btn--sm" data-submit hidden><span>Anfrage senden</span><?php echo blitz_icon( 'arrow' ); ?></button>
        </div>

        <div class="wizard__done" hidden tabindex="-1">
          <svg viewBox="0 0 80 80" aria-hidden="true"><circle cx="40" cy="40" r="34"/><path d="M25 41l10 10 20-22"/></svg>
          <h3>Anfrage gesendet</h3>
          <p>Vielen Dank! Ihre Anfrage ist angekommen – <?php echo blitz_h( $c['owner'] ); ?> meldet sich bei Ihnen. Eilt es, rufen Sie einfach an: <a href="<?php echo blitz_h( blitz_tel() ); ?>"><?php echo blitz_h( $c['phone'] ); ?></a>.</p>
        </div>
      </form>
    </div>
  </div>
</section>
	<?php
}

/* ==========================================================================
   SUCHBEGRIFFE / HASHTAGS (verlinkt Orts- und Leistungsseiten)
   ========================================================================== */

function blitz_render_keywords( $d = array() ) {
	$def   = blitz_defaults();
	$title = isset( $d['title'] ) && '' !== $d['title'] ? $d['title'] : 'Schweißer in *Ihrer Nähe*';
	$text  = isset( $d['text'] ) ? $d['text'] : 'Mobile Schweißarbeiten im Ostallgäu, im ganzen Allgäu und bis München – mit eigenem Gerät direkt vor Ort.';
	$tags  = ! empty( $d['hashtags'] ) ? blitz_tags( $d['hashtags'] ) : array();
	?>
<section class="keywords blitz-sec" aria-labelledby="keywords-title">
  <div class="wrap">
    <div class="keywords__head">
      <h2 class="keywords__title" id="keywords-title"><?php echo blitz_em( $title ); ?></h2>
      <p><?php echo blitz_h( $text ); ?></p>
    </div>
    <ul class="keywords__list">
	<?php foreach ( $def['area']['towns'] as $t ) : ?>
      <li><a href="<?php echo blitz_h( blitz_page_url( 'ort', $t['slug'] ) ); ?>">#Schweißer<?php echo blitz_h( str_replace( array( ' am ', ' ' ), array( '', '' ), $t['name'] ) ); ?></a></li>
	<?php endforeach; ?>
	<?php foreach ( $def['services']['items'] as $s ) : ?>
      <li><a href="<?php echo blitz_h( blitz_page_url( 'leistung', $s['slug'] ) ); ?>"><?php echo blitz_h( blitz_tags( $s['tags'] )[0] ); ?></a></li>
	<?php endforeach; ?>
	<?php foreach ( $tags as $t ) : ?>
      <li><span><?php echo blitz_h( $t ); ?></span></li>
	<?php endforeach; ?>
    </ul>
  </div>
</section>
	<?php
}

/* ==========================================================================
   UNTERSEITEN (Orte & Leistungen)
   ========================================================================== */

/** Daten einer Unterseite: array( 'type' => 'ort'|'leistung', 'item' => … ) */
function blitz_landing( $type, $slug ) {
	$def  = blitz_defaults();
	$list = 'ort' === $type ? $def['area']['towns'] : $def['services']['items'];
	foreach ( $list as $it ) {
		if ( $it['slug'] === $slug ) {
			return array( 'type' => $type, 'item' => $it );
		}
	}
	return null;
}

/** Überschrift, Lead und Meta einer Unterseite */
function blitz_landing_meta( $landing ) {
	$c  = blitz_company();
	$it = $landing['item'];
	if ( 'ort' === $landing['type'] ) {
		$dir = blitz_direction( $it['lat'], $it['lon'] );
		return array(
			'h1'          => array( 'Schweißer in', $it['name'] . '.' ),
			'eyebrow'     => 'Schweißarbeiten in ' . $it['name'] . ' – ca. ' . (int) $it['km'] . ' km ab ' . $c['city'],
			'lead'        => 'Mobile Schweißarbeiten in ' . $it['name'] . ' und Umgebung: Tanks, Rohre, Bohrrohre, Platten und Stahlkonstruktionen – mit der Stabelektrode, auch in Steignaht, direkt bei Ihnen vor Ort.',
			'title'       => 'Schweißer ' . $it['name'] . ' – mobile Schweißarbeiten vor Ort | ' . $c['name'],
			'description' => 'Schweißer für ' . $it['name'] . ': Elektroschweißen an Tanks, Rohren, Bohrrohren, Platten und Stahlkonstruktionen – mobil vor Ort, auch Steignaht und in der Höhe. Ca. ' . (int) $it['km'] . ' km ' . $dir . ' von ' . $c['city'] . '. 20 Jahre Erfahrung.',
			'crumb'       => array( 'Einsatzorte', blitz_page_url( 'orte' ) ),
			'tags'        => array( '#Schweißer' . str_replace( array( ' am ', ' ' ), '', $it['name'] ), '#Schweißen' . str_replace( array( ' am ', ' ' ), '', $it['name'] ), '#Schweißarbeiten' . str_replace( array( ' am ', ' ' ), '', $it['name'] ), '#MobilerSchweißer', '#Steignaht', '#SchweißenVorOrt' ),
		);
	}
	return array(
		'h1'          => $it['h1'],
		'eyebrow'     => $it['title'] . ' – im Allgäu und bis München',
		'lead'        => $it['text'],
		'title'       => blitz_plain( $it['h1'][0] . ' ' . $it['h1'][1] ) . ' – Allgäu & München | ' . $c['name'],
		'description' => $it['meta'],
		'crumb'       => array( 'Leistungen', blitz_page_url( 'leistungen' ) ),
		'tags'        => blitz_tags( $it['tags'] ),
	);
}

/** Fließtext einer Unterseite (wird beim Anlegen der WordPress-Seite als Inhalt gespeichert) */
function blitz_landing_html( $landing ) {
	$c   = blitz_company();
	$def = blitz_defaults();
	$it  = $landing['item'];
	$h   = 'blitz_h';
	ob_start();
	if ( 'ort' === $landing['type'] ) {
		$dir  = blitz_direction( $it['lat'], $it['lon'] );
		$name = $it['name'];
		$far  = $it['km'] > 45;
		echo '<p>' . $h( $it['about'] ) . ( $far ? ' – ca. ' . (int) $it['km'] . ' km ' . $dir . ' von ' . $c['city'] . '.' : ', ca. ' . (int) $it['km'] . ' km ' . $dir . ' von ' . $c['city'] . '.' ) . ' ' . $h( $c['name'] ) . ' übernimmt in ' . $h( $name ) . ' und Umgebung Schweißarbeiten aller Art. ' . $h( $c['owner'] ) . ' kommt mit eigenem Schweißgerät direkt zu Ihnen: auf die Baustelle, in den Betrieb, auf den Hof oder zum privaten Bauvorhaben.</p>' . "\n";
		if ( $far ) {
			echo '<p>Aufträge in ' . $h( $name ) . ' und im Umland übernehmen wir nach Absprache – am besten kurz anrufen oder Fotos schicken, dann lässt sich Aufwand und Termin schnell klären.</p>' . "\n";
		}
		echo '<h2>Schweißarbeiten in ' . $h( $name ) . '</h2>' . "\n<ul>\n";
		foreach ( $def['services']['items'] as $s ) {
			echo '<li><a href="' . $h( blitz_page_url( 'leistung', $s['slug'] ) ) . '">' . $h( $s['title'] ) . '</a> – ' . $h( $s['text'] ) . "</li>\n";
		}
		echo "</ul>\n";
		echo '<h2>Elektroschweißen mit der Stabelektrode – auch in Steignaht</h2>' . "\n";
		echo '<p>Gearbeitet wird mit dem Lichtbogenhandschweißen (E-Hand). Das Verfahren ist robust, funktioniert auch im Freien und bei Wind und eignet sich für alle Positionen. Senkrechte Nähte an Rohren, Tanks und Bohrrohren werden in Steignaht von unten nach oben gezogen – so dringt die Naht tief ein und verbindet die Bauteile über die ganze Wanddicke.</p>' . "\n";
		echo '<h2>In ' . $h( $name ) . ' schnell zum Angebot</h2>' . "\n<ol>\n";
		echo '<li>Anrufen unter <a href="' . $h( blitz_tel() ) . '">' . $h( $c['phone'] ) . '</a> oder Fotos per <a href="' . $h( blitz_wa() ) . '">WhatsApp</a> schicken.</li>' . "\n";
		echo '<li>Einschätzung und Angebot: wie die Naht ausgeführt wird, was es kostet und wann es losgeht.</li>' . "\n";
		echo '<li>Schweißen vor Ort in ' . $h( $name ) . ' – mit eigenem Gerät, auch auf Gerüsten und Arbeitsbühnen.</li>' . "\n";
		echo '<li>Saubere Übergabe: Die Naht wird gemeinsam angeschaut, der Arbeitsplatz aufgeräumt hinterlassen.</li>' . "\n</ol>\n";
	} else {
		foreach ( $it['body'] as $p ) {
			echo '<p>' . $h( $p ) . "</p>\n";
		}
		echo '<h2>Typische Arbeiten</h2>' . "\n<ul>\n";
		foreach ( $it['list'] as $li ) {
			echo '<li>' . $h( $li ) . "</li>\n";
		}
		echo "</ul>\n";
		if ( ! empty( $it['note'] ) ) {
			echo '<blockquote><p>' . $h( $it['note'] ) . "</p></blockquote>\n";
		}
		echo '<h2>Im Einsatz im ganzen Allgäu und bis München</h2>' . "\n";
		$links = array();
		foreach ( $def['area']['towns'] as $t ) {
			$links[] = '<a href="' . $h( blitz_page_url( 'ort', $t['slug'] ) ) . '">' . $h( $t['name'] ) . '</a>';
		}
		echo '<p>Von ' . $h( $c['city'] ) . ' aus mobil unterwegs – unter anderem in ' . implode( ', ', array_slice( $links, 0, -1 ) ) . ' und ' . end( $links ) . '.</p>' . "\n";
	}
	return trim( ob_get_clean() );
}

/** FAQ einer Unterseite */
function blitz_landing_faq( $landing ) {
	$it  = $landing['item'];
	$c   = blitz_company();
	$def = blitz_defaults()['faq']['items'];
	if ( 'ort' === $landing['type'] ) {
		$far = $it['km'] > 45;
		return array(
			array( 'q' => 'Kommen Sie für Schweißarbeiten nach ' . $it['name'] . '?', 'a' => $far ? 'Ja. ' . $it['name'] . ' liegt ca. ' . (int) $it['km'] . ' km von ' . $c['city'] . ' entfernt – Aufträge in ' . $it['name'] . ' und im Umland übernehmen wir nach Absprache.' : 'Ja. ' . $it['name'] . ' liegt ca. ' . (int) $it['km'] . ' km von ' . $c['city'] . ' entfernt und gehört zum Einsatzgebiet von ' . $c['name'] . '.' ),
			array( 'q' => 'Übernehmen Sie in ' . $it['name'] . ' auch kleine Reparaturen?', 'a' => 'Ja – ein gebrochener Halter, ein gerissener Rahmen oder eine beschädigte Halterung. Am besten vorab ein paar Fotos per WhatsApp an ' . $c['phone'] . ' schicken.' ),
			$def[0],
			$def[2],
			$def[5],
		);
	}
	return array_merge( $it['faq'], array( $def[0], $def[5] ) );
}

/** Kompletter Inhalt einer Unterseite. $content = HTML oder callable (WordPress: the_content) */
function blitz_render_landing( $landing, $content = null ) {
	$m   = blitz_landing_meta( $landing );
	$it  = $landing['item'];
	$c   = blitz_company();
	$def = blitz_defaults();

	$GLOBALS['blitz_local_ids'] = array( 'kontakt', 'faq', 'leistungen' );

	blitz_render_hero(
		array(
			'id'        => 'top',
			'sub'       => true,
			'eyebrow'   => $m['eyebrow'],
			'title1'    => $m['h1'][0],
			'title2'    => $m['h1'][1],
			'lead'      => $m['lead'],
			'form_pick' => 'leistung' === $landing['type'] ? ( isset( $it['pick'] ) ? $it['pick'] : '' ) : '',
			'show_hud'  => 'no',
			'crumbs'    => array( array( 'Startseite', blitz_url( '/' ) ), $m['crumb'], array( 'ort' === $landing['type'] ? $it['name'] : $it['title'], '' ) ),
		)
	);
	?>
<section class="landing blitz-sec" aria-label="Details">
  <div class="wrap landing__grid">
    <article class="landing__content prose" data-reveal>
	<?php
	if ( is_callable( $content ) ) {
		call_user_func( $content );
	} elseif ( is_string( $content ) && '' !== $content ) {
		echo $content; // phpcs:ignore -- bereits gerendertes HTML
	} else {
		echo blitz_landing_html( $landing ); // phpcs:ignore
	}
	?>
    </article>
    <aside class="landing__aside">
      <div class="landing__card landing__card--call" data-reveal>
        <p class="label"><b>☎</b> Direkt anfragen</p>
        <a class="landing__phone" href="<?php echo blitz_h( blitz_tel() ); ?>"><?php echo blitz_h( $c['phone'] ); ?></a>
        <p>Fotos und Maße gern per <a href="<?php echo blitz_h( blitz_wa() ); ?>" target="_blank" rel="noopener">WhatsApp</a> – oft reicht das für eine Einschätzung.</p>
      </div>
      <div class="landing__card" data-reveal>
        <p class="label">Hashtags</p>
        <ul class="card__tags">
	<?php foreach ( $m['tags'] as $t ) : ?>
          <li><?php echo blitz_h( $t ); ?></li>
	<?php endforeach; ?>
        </ul>
      </div>
      <div class="landing__card" data-reveal>
	<?php if ( 'ort' === $landing['type'] ) : ?>
        <p class="label">Weitere Einsatzorte</p>
        <ul class="landing__links">
			<?php foreach ( $def['area']['towns'] as $t ) : if ( $t['slug'] === $it['slug'] ) { continue; } ?>
          <li><a href="<?php echo blitz_h( blitz_page_url( 'ort', $t['slug'] ) ); ?>">Schweißer <?php echo blitz_h( $t['name'] ); ?></a></li>
			<?php endforeach; ?>
        </ul>
	<?php else : ?>
        <p class="label">Weitere Leistungen</p>
        <ul class="landing__links">
			<?php foreach ( $def['services']['items'] as $s ) : if ( $s['slug'] === $it['slug'] ) { continue; } ?>
          <li><a href="<?php echo blitz_h( blitz_page_url( 'leistung', $s['slug'] ) ); ?>"><?php echo blitz_h( $s['title'] ); ?></a></li>
			<?php endforeach; ?>
        </ul>
	<?php endif; ?>
      </div>
    </aside>
  </div>
</section>
	<?php
	if ( 'ort' === $landing['type'] ) {
		blitz_render_services(
			array(
				'title'     => 'Leistungen in *' . $it['name'] . '*',
				'statement' => '',
			)
		);
		blitz_render_area( array( 'highlight' => $it['slug'] ) );
	} else {
		blitz_render_services(
			array(
				'title'     => 'Weitere *Leistungen*',
				'statement' => '',
				'exclude'   => $it['slug'],
			)
		);
	}
	blitz_render_faq(
		array(
			'title' => 'ort' === $landing['type'] ? 'Fragen aus *' . $it['name'] . '*' : 'Häufige *Fragen*',
			'items' => blitz_landing_faq( $landing ),
		)
	);
	blitz_render_contact();
}

/** Übersichtsseiten /leistungen/ und /schweisser/ */
function blitz_render_overview( $type ) {
	$c = blitz_company();
	$GLOBALS['blitz_local_ids'] = array( 'kontakt', 'faq', 'leistungen', 'einsatzgebiet' );
	if ( 'orte' === $type ) {
		blitz_render_hero(
			array(
				'sub'      => true,
				'eyebrow'  => 'Einsatzgebiet ab ' . $c['city'],
				'title1'   => 'Schweißer im Allgäu',
				'title2'   => 'bis München.',
				'lead'     => 'Mobil mit eigenem Schweißgerät: Kaufbeuren, Marktoberdorf, Kempten, Füssen, Buchloe, Mindelheim, Memmingen, Landsberg am Lech und München.',
				'show_hud' => 'no',
				'crumbs'   => array( array( 'Startseite', blitz_url( '/' ) ), array( 'Einsatzorte', '' ) ),
			)
		);
		blitz_render_area();
		blitz_render_services( array( 'statement' => '' ) );
	} else {
		blitz_render_hero(
			array(
				'sub'      => true,
				'eyebrow'  => 'Leistungen von ' . $c['name'],
				'title1'   => 'Elektroschweißen',
				'title2'   => 'aller Art.',
				'lead'     => 'Tanks und Behälter, Stahlkonstruktionen, Rohre und Bohrrohre, Platten und Bleche, Bohr- und Montagearbeiten – auf der Baustelle und in der Höhe.',
				'show_hud' => 'no',
				'crumbs'   => array( array( 'Startseite', blitz_url( '/' ) ), array( 'Leistungen', '' ) ),
			)
		);
		blitz_render_services();
		blitz_render_positions();
	}
	blitz_render_faq();
	blitz_render_contact();
}

/** Komplette Startseite ohne Elementor */
function blitz_render_front_default() {
	blitz_render_hero();
	blitz_render_marquee();
	blitz_render_services();
	blitz_render_values();
	blitz_render_positions();
	blitz_render_owner();
	blitz_render_process();
	blitz_render_area();
	blitz_render_faq();
	blitz_render_contact();
}
