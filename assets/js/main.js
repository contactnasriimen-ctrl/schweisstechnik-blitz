/* Schweisstechnik Blitz – Interaktion & Animation
   GSAP + ScrollTrigger + Lenis (mit Fallback ohne). Jeder Abschnitt wird über Blitz.mount(root) initialisiert –
   auf der Website beim Laden, im Elementor-Editor bei jedem neu gerenderten Widget. */
(function () {
  'use strict';

  var d = document, root = d.documentElement, body = d.body;
  var $ = function (s, c) { return (c || d).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };
  var clamp = function (v, a, b) { return Math.max(a, Math.min(b, v)); };
  var lerp = function (a, b, t) { return a + (b - a) * t; };

  var reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fine = matchMedia('(hover: hover) and (pointer: fine)').matches;
  var editMode = body.classList.contains('elementor-editor-active') || body.classList.contains('blitz-edit');
  var G = window.gsap, ST = window.ScrollTrigger;
  var hasG = !!(G && ST);
  if (hasG) G.registerPlugin(ST);
  var animate = hasG && !reduced && !editMode;
  if (!animate) root.classList.add('no-scrub');
  var STATIC = /\.github\.io$/.test(location.hostname) || location.protocol === 'file:';

  /* einmalig pro Element */
  function once(el, key) {
    var k = 'blitz' + key;
    if (el.dataset[k]) return false;
    el.dataset[k] = '1';
    return true;
  }
  function each(scope, sel, fn) {
    var list = $$(sel, scope);
    if (scope !== d && scope.matches && scope.matches(sel)) list.unshift(scope);
    list.forEach(fn);
  }

  /* Element sichtbar (oder bereits vorbei) → einmal ausführen */
  function onView(el, fn, margin) {
    if (!('IntersectionObserver' in window)) { fn(); return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting || en.boundingClientRect.top < 0) { io.disconnect(); fn(); }
      });
    }, { rootMargin: margin || '0px 0px -12% 0px' });
    io.observe(el);
  }

  /* ---------- Text zerlegen ---------- */
  function splitChars(el, labelEl) {
    var label = el.textContent.replace(/\s+/g, ' ').trim();
    var chars = [];
    (function walk(node) {
      Array.prototype.slice.call(node.childNodes).forEach(function (n) {
        if (n.nodeType === 3) {
          var frag = d.createDocumentFragment();
          n.textContent.split(/(\s+)/).forEach(function (part) {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.appendChild(d.createTextNode(' ')); return; }
            var w = d.createElement('span'); w.className = 'w';
            Array.from(part).forEach(function (ch) {
              var c = d.createElement('span'); c.className = 'c'; c.textContent = ch;
              w.appendChild(c); chars.push(c);
            });
            frag.appendChild(w);
          });
          n.parentNode.replaceChild(frag, n);
        } else if (n.nodeType === 1) walk(n);
      });
    })(el);
    if (labelEl !== false) {
      (labelEl || el).setAttribute('aria-label', label);
      Array.prototype.slice.call(el.children).forEach(function (ch) { ch.setAttribute('aria-hidden', 'true'); });
    }
    return chars;
  }
  function splitWords(el) {
    var words = el.textContent.trim().split(/\s+/);
    el.textContent = '';
    return words.map(function (w, i) {
      var s = d.createElement('span'); s.className = 'sw'; s.textContent = w;
      el.appendChild(s); if (i < words.length - 1) el.appendChild(d.createTextNode(' '));
      return s;
    });
  }
  function scramble(el, dur) {
    var final = el.textContent, pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789#%&+/';
    var start = performance.now();
    (function tick(now) {
      var p = Math.min((now - start) / (dur * 1000), 1), n = Math.floor(p * final.length), out = final.slice(0, n);
      for (var i = n; i < final.length; i++) out += /[\s,–.:]/.test(final[i]) ? final[i] : pool[(Math.random() * pool.length) | 0];
      el.textContent = out;
      if (p < 1) requestAnimationFrame(tick); else el.textContent = final;
    })(start);
  }

  /* ==========================================================================
     GLOBAL (einmal pro Seite)
     ========================================================================== */
  var lenis = null;
  if (animate && window.Lenis) {
    lenis = new window.Lenis({ lerp: 0.085, smoothWheel: true, wheelMultiplier: 1 });
    lenis.on('scroll', ST.update);
    G.ticker.add(function (t) { lenis.raf(t * 1000); });
    G.ticker.lagSmoothing(0);
  }
  function scrollToEl(t, id) {
    if (lenis) lenis.scrollTo(t, { offset: id === '#top' ? 0 : -72, duration: 1.5 });
    else t.scrollIntoView({ behavior: reduced ? 'auto' : 'smooth' });
  }

  var nav = $('[data-nav]'), burger = $('.nav__burger'), menu = $('#menu');
  var menuOpen = false;
  function openMenu() {
    menuOpen = true; menu.hidden = false;
    requestAnimationFrame(function () { requestAnimationFrame(function () { menu.classList.add('is-open'); }); });
    burger.setAttribute('aria-expanded', 'true'); burger.setAttribute('aria-label', 'Menü schließen');
    root.classList.add('menu-open'); if (nav) nav.classList.remove('is-hidden');
    if (lenis) lenis.stop();
  }
  function closeMenu() {
    if (!menuOpen) return;
    menuOpen = false; menu.classList.remove('is-open');
    burger.setAttribute('aria-expanded', 'false'); burger.setAttribute('aria-label', 'Menü öffnen');
    root.classList.remove('menu-open');
    if (lenis) lenis.start();
    setTimeout(function () { if (!menuOpen) menu.hidden = true; }, 800);
  }
  if (burger && menu) burger.addEventListener('click', function () { menuOpen ? closeMenu() : openMenu(); });
  d.addEventListener('keydown', function (e) { if (e.key === 'Escape' && menuOpen) { closeMenu(); burger.focus(); } });

  /* Anker-Links (auch „/#abschnitt“, wenn wir schon auf der Seite sind) */
  d.addEventListener('click', function (e) {
    var a = e.target.closest('a[href*="#"]');
    if (!a || editMode) return;
    var url;
    try { url = new URL(a.href, location.href); } catch (err) { return; }
    if (url.pathname !== location.pathname || url.origin !== location.origin || url.hash.length < 2) return;
    var t = d.getElementById(decodeURIComponent(url.hash.slice(1)));
    if (!t) return;
    e.preventDefault();
    var wasOpen = menuOpen;
    closeMenu();
    setTimeout(function () {
      scrollToEl(t, url.hash);
      if (!t.hasAttribute('tabindex')) t.setAttribute('tabindex', '-1');
      t.focus({ preventScroll: true });
    }, wasOpen ? 380 : 0);
    if (history.replaceState) history.replaceState(null, '', url.hash === '#top' ? location.pathname : url.hash);
  });

  /* Karten-Links füllen das Formular vor */
  d.addEventListener('click', function (e) {
    var a = e.target.closest('[data-pick], [data-pick-hoehe]');
    if (!a) return;
    var v = a.getAttribute('data-pick'), h = a.getAttribute('data-pick-hoehe');
    if (v) $$('input[name="arbeit[]"]').forEach(function (cb) { if (cb.value === v) cb.checked = true; });
    if (h) $$('input[name="hoehe"]').forEach(function (rb) { if (rb.value === h) rb.checked = true; });
  });

  /* Aktiver Abschnitt */
  var navLinks = $$('.nav__links a');
  if ('IntersectionObserver' in window && navLinks.length) {
    var secIO = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        navLinks.forEach(function (l) { l.classList.toggle('is-active', l.hash === '#' + en.target.id && l.pathname === location.pathname); });
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    navLinks.forEach(function (l) { if (l.pathname === location.pathname && l.hash.length > 1) { var s = d.getElementById(l.hash.slice(1)); if (s) secIO.observe(s); } });
  }

  /* Scroll-Zustand */
  var seamP = $('.seam-progress'), dock = $('.dock');
  var lastY = window.scrollY, ticking = false;
  function onScroll() {
    var y = window.scrollY;
    if (nav) {
      nav.classList.toggle('is-solid', y > 30);
      if (!menuOpen) nav.classList.toggle('is-hidden', y > lastY + 2 && y > 500);
      if (y < lastY - 2) nav.classList.remove('is-hidden');
    }
    lastY = y;
    var max = root.scrollHeight - innerHeight;
    if (seamP) seamP.style.setProperty('--p', (max > 0 ? y / max : 0).toFixed(4));
    if (dock) dock.classList.toggle('is-visible', y > innerHeight * 0.55);
    ticking = false;
  }
  addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
  onScroll();

  /* Cursor */
  if (fine && !reduced && !editMode && $('.cursor')) {
    var cur = $('.cursor'), ring = $('.cursor__ring'), dot = $('.cursor__dot');
    var mx = innerWidth / 2, my = innerHeight / 2, rx = mx, ry = my;
    addEventListener('pointermove', function (e) { mx = e.clientX; my = e.clientY; root.classList.add('has-cursor'); }, { passive: true });
    root.addEventListener('mouseleave', function () { root.classList.remove('has-cursor'); });
    d.addEventListener('pointerover', function (e) {
      cur.classList.toggle('is-link', !!e.target.closest('a, button, label, select, [data-tilt], .towns li'));
    });
    addEventListener('pointerdown', function () { cur.classList.add('is-down'); });
    addEventListener('pointerup', function () { cur.classList.remove('is-down'); });
    (function loop() {
      rx = lerp(rx, mx, 0.2); ry = lerp(ry, my, 0.2);
      ring.style.transform = 'translate3d(' + rx + 'px,' + ry + 'px,0)';
      dot.style.transform = 'translate3d(' + mx + 'px,' + my + 'px,0)';
      requestAnimationFrame(loop);
    })();
  }

  /* ==========================================================================
     HERO
     ========================================================================== */
  var introQueue = [];
  var introReady = false;

  function mountHero(hero) {
    if (!once(hero, 'Hero')) return;
    if (window.BlitzHero3D) window.BlitzHero3D(hero);
    var l1 = $('.hero__l1', hero), l2 = $('.hero__l2', hero);
    if (!animate || !l1) { $$('[data-count]', hero).forEach(function (c) { c.textContent = c.getAttribute('data-count'); }); return; }
    var chars = splitChars(l1, false);
    l1.setAttribute('aria-hidden', 'true');
    var spark = d.createElement('i'); spark.className = 'weld-spark'; $('.hero__title', hero).appendChild(spark);
    G.set(chars, { yPercent: 115, rotateX: -85, opacity: 0 });
    if (l2) G.set(l2, { clipPath: 'inset(-10% 100% -20% 0)' });
    G.set($$('[data-hero-fade]', hero), { y: 34, opacity: 0 });
    G.set($$('.eyebrow, .hero__hud', hero), { opacity: 0 });

    function play() {
      var tl = G.timeline({ defaults: { ease: 'expo.out' } });
      if (nav && hero === $('.hero')) tl.to(nav, { yPercent: 0, duration: 1.2, clearProps: 'transform' }, 0.1);
      tl.to($('.eyebrow', hero), { opacity: 1, duration: 0.6, onStart: function () { var s = $('.eyebrow > span', hero); if (s) scramble(s, 1.1); } }, 0)
        .to(chars, { yPercent: 0, rotateX: 0, opacity: 1, duration: 1.25, stagger: 0.03 }, 0.1);
      if (l2) {
        var proxy = { p: 0 };
        tl.to(proxy, {
          p: 1, duration: 1.5, ease: 'power2.inOut',
          onStart: function () { spark.style.opacity = 1; },
          onUpdate: function () {
            l2.style.clipPath = 'inset(-10% ' + (100 - proxy.p * 100).toFixed(2) + '% -20% 0)';
            spark.style.left = (l2.offsetLeft + l2.offsetWidth * proxy.p) + 'px';
            spark.style.top = (l2.offsetTop + l2.offsetHeight * 0.55) + 'px';
            spark.style.transform = 'scale(' + (0.75 + Math.random() * 0.5) + ')';
          },
          onComplete: function () { G.to(spark, { opacity: 0, duration: 0.5 }); l2.style.clipPath = 'none'; }
        }, 0.55);
      }
      tl.to($$('[data-hero-fade]', hero), { y: 0, opacity: 1, duration: 1.3, stagger: 0.12 }, 0.7)
        .to($$('.hero__hud', hero), { opacity: 1, duration: 1 }, 1.4);
      var cnt = $('[data-count]', hero);
      if (cnt) { var o = { v: 0 }, max = +cnt.getAttribute('data-count'); tl.to(o, { v: max, duration: 1.6, ease: 'power2.out', onUpdate: function () { cnt.textContent = Math.round(o.v); } }, 0.9); }
    }
    if (introReady) play(); else introQueue.push(play);

    var inner = $('.hero__inner', hero);
    if (inner && !hero.classList.contains('hero--form')) {
      G.to(inner, { yPercent: -14, opacity: 0.2, ease: 'none', scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true } });
    }
  }
  function releaseIntro() {
    introReady = true;
    introQueue.splice(0).forEach(function (fn) { fn(); });
  }

  /* Vorspann (nur Startseite) */
  (function runLoader() {
    var loader = $('.loader'), flash = $('.flash');
    if (animate && nav && $('.hero')) G.set(nav, { yPercent: -120 });
    if (!loader) { setTimeout(releaseIntro, 120); return; }
    if (!animate) { loader.remove(); releaseIntro(); return; }
    var seen = false;
    try { seen = sessionStorage.getItem('blitz-intro') === '1'; sessionStorage.setItem('blitz-intro', '1'); } catch (e) { /* privat */ }
    var count = $('.loader__count', loader);
    var heroReady = !!window.__blitzHeroReady || !$('.hero__canvas'), counted = false, fired = false;
    function go() {
      if (fired || !counted || !heroReady) return;
      fired = true;
      G.timeline()
        .to(flash, { opacity: 0.9, duration: 0.07, ease: 'none' })
        .to(flash, { opacity: 0, duration: 0.6, ease: 'power2.out' })
        .to(loader, { clipPath: 'inset(0 0 100% 0)', duration: 1.05, ease: 'expo.inOut', onComplete: function () { loader.remove(); } }, 0.05)
        .add(releaseIntro, 0.45);
    }
    window.addEventListener('blitz:hero-ready', function () { heroReady = true; go(); }, { once: true });
    setTimeout(function () { heroReady = true; go(); }, 3200);
    var o = { p: 0 };
    G.to(o, {
      p: 1, duration: seen ? 0.55 : 1.6, ease: 'power2.inOut',
      onUpdate: function () { loader.style.setProperty('--p', o.p); count.textContent = String(Math.round(o.p * 100)).padStart(3, '0'); },
      onComplete: function () { counted = true; (d.fonts && d.fonts.ready ? d.fonts.ready : Promise.resolve()).then(go); }
    });
  })();

  /* ==========================================================================
     ABSCHNITTE
     ========================================================================== */
  function mountReveals(scope) {
    if (!animate) { each(scope, '.value', function (v) { v.classList.add('is-in'); }); return; }
    each(scope, '[data-split="chars"]', function (el) {
      if (!once(el, 'Split')) return;
      var chars = splitChars(el);
      G.set(chars, { yPercent: 105, rotateX: -75, opacity: 0 });
      onView(el, function () { G.to(chars, { yPercent: 0, rotateX: 0, opacity: 1, duration: 1.2, ease: 'expo.out', stagger: 0.022 }); });
    });
    each(scope, '.label', function (el) {
      if (el.closest('.hero') || el.closest('.landing__card') || !once(el, 'Label')) return;
      G.set(el, { opacity: 0, x: -24 });
      onView(el, function () { G.to(el, { opacity: 1, x: 0, duration: 1, ease: 'expo.out' }); });
    });
    each(scope, '[data-reveal]', function (el) {
      if (!once(el, 'Reveal')) return;
      G.set(el, { y: 44, opacity: 0 });
      onView(el, function () { G.to(el, { y: 0, opacity: 1, duration: 1.2, ease: 'expo.out', delay: Math.random() * 0.12 }); }, '0px 0px -8% 0px');
    });
    each(scope, '.card', function (el) {
      if (!once(el, 'Card')) return;
      G.set(el, { y: 90, opacity: 0, rotationX: -14, transformPerspective: 1100, transformOrigin: '50% 0%' });
      var idx = $$('.card', el.parentNode).indexOf(el) % 3;
      onView(el, function () { G.to(el, { y: 0, opacity: 1, rotationX: 0, duration: 1.4, ease: 'expo.out', delay: idx * 0.12 }); }, '0px 0px -6% 0px');
    });
    each(scope, '[data-scrub-words]', function (el) {
      if (!once(el, 'Scrub')) return;
      var words = splitWords(el);
      G.to(words, {
        keyframes: { '0%': { color: 'rgba(239,234,226,0.14)' }, '45%': { color: '#ffb347' }, '100%': { color: '#efeae2' } },
        ease: 'none', stagger: 0.12,
        scrollTrigger: { trigger: el, start: 'top 82%', end: 'bottom 42%', scrub: 0.6 }
      });
    });
    each(scope, '.value', function (v) { if (once(v, 'Value')) onView(v, function () { v.classList.add('is-in'); }); });
    each(scope, '.pos__bgword span', function (el) {
      if (!once(el, 'Bg')) return;
      G.fromTo(el, { xPercent: 6 }, { xPercent: -38, ease: 'none', scrollTrigger: { trigger: el.closest('.pos'), start: 'top bottom', end: 'bottom top', scrub: true } });
    });
  }

  function mountMagnetic(scope) {
    if (!(fine && animate)) return;
    each(scope, '[data-magnetic]', function (el) {
      if (!once(el, 'Mag')) return;
      var xTo = G.quickTo(el, 'x', { duration: 0.6, ease: 'power3' });
      var yTo = G.quickTo(el, 'y', { duration: 0.6, ease: 'power3' });
      el.addEventListener('pointermove', function (e) {
        var r = el.getBoundingClientRect();
        xTo((e.clientX - r.left - r.width / 2) * 0.22);
        yTo((e.clientY - r.top - r.height / 2) * 0.32);
      });
      el.addEventListener('pointerleave', function () { xTo(0); yTo(0); });
    });
  }

  function mountTilt(scope) {
    if (!(fine && hasG && !reduced)) return;
    each(scope, '[data-tilt]', function (card) {
      if (!once(card, 'Tilt')) return;
      var rxTo = G.quickTo(card, 'rotationX', { duration: 0.8, ease: 'power3' });
      var ryTo = G.quickTo(card, 'rotationY', { duration: 0.8, ease: 'power3' });
      card.addEventListener('pointermove', function (e) {
        var r = card.getBoundingClientRect(), px = (e.clientX - r.left) / r.width, py = (e.clientY - r.top) / r.height;
        card.style.setProperty('--mx', (px * 100).toFixed(1) + '%');
        card.style.setProperty('--my', (py * 100).toFixed(1) + '%');
        rxTo((0.5 - py) * 9); ryTo((px - 0.5) * 11);
      });
      card.addEventListener('pointerleave', function () { rxTo(0); ryTo(0); });
    });
  }

  function mountMarquee(mq) {
    if (!once(mq, 'Marquee')) return;
    $$('.marquee__row', mq).forEach(function (row) { row.appendChild($('.marquee__track', row).cloneNode(true)); });
    if (!hasG || reduced) return;
    mq.classList.add('is-gsap');
    var tw = $$('.marquee__row', mq).map(function (row, i) {
      var tracks = $$('.marquee__track', row);
      return i === 0
        ? G.to(tracks, { xPercent: -100, ease: 'none', duration: 46, repeat: -1 })
        : G.fromTo(tracks, { xPercent: -100 }, { xPercent: 0, ease: 'none', duration: 80, repeat: -1 });
    });
    var skew = 0, speed = 1;
    G.ticker.add(function () {
      var v = lenis ? lenis.velocity : 0;
      speed = lerp(speed, 1 + Math.min(Math.abs(v) * 0.09, 5), 0.08);
      skew = lerp(skew, clamp(-v * 0.35, -8, 8), 0.1);
      tw.forEach(function (t) { t.timeScale(speed); });
      mq.style.setProperty('--skew', skew.toFixed(2) + 'deg');
    });
  }

  /* Schweißpositionen */
  function mountPositions(box) {
    if (!once(box, 'Pos')) return;
    var svg = $('.viewer__svg', box);
    var plate = $('.v-plate', svg), groove = $('.v-groove', svg), bead = $('.v-bead', svg), grad = $('#beadG', svg);
    var arc = $('.v-arc', svg), elec = $('.v-electrode', svg), ceiling = $('.v-ceiling', svg);
    var arrowLine = $('.v-arrow line', svg), arrowHead = $('.v-arrow path', svg), sparksG = $('.v-sparks', svg);
    var code = $('.viewer__code', box), title = $('.viewer__title', box), desc = $('.viewer__desc', box);
    var tabs = $$('[data-pos]', box);

    var WALL = [150, 70, 330, 88, 330, 348, 150, 356];
    var GEO = {
      PA: { plate: [92, 294, 388, 294, 420, 344, 60, 344], seam: [80, 319, 400, 319], ang: 28, ceil: 0 },
      PC: { plate: WALL, seam: [154, 213, 326, 219], ang: 68, ceil: 0 },
      PF: { plate: WALL, seam: [240, 350, 240, 82], ang: 145, ceil: 0 },
      PG: { plate: WALL, seam: [240, 82, 240, 350], ang: 38, ceil: 0 },
      PE: { plate: [92, 44, 388, 44, 420, 94, 60, 94], seam: [80, 69, 400, 69], ang: 205, ceil: 1, below: 1 }
    };
    var cur = { plate: WALL.slice(), seam: GEO.PF.seam.slice(), ang: 145, ceil: 0 };
    var from = null, to = GEO.PF, morphStart = 0, MORPH = reduced ? 1 : 750;
    var weldStart = performance.now(), active = 'PF', running = false;
    var NS = 'http://www.w3.org/2000/svg', sparks = [], sparkIdx = 0;
    for (var i = 0; i < 26; i++) {
      var c = d.createElementNS(NS, 'circle'); c.setAttribute('r', '1.4'); c.setAttribute('cx', '-20'); c.setAttribute('cy', '-20');
      sparksG.appendChild(c); sparks.push({ el: c, x: 0, y: 0, vx: 0, vy: 0, life: 0, max: 1 });
    }
    function ease(t) { return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2; }
    function lerpArr(a, b, k) { return a.map(function (v, i) { return lerp(v, b[i], k); }); }
    function setGeom(g) {
      var p = g.plate;
      plate.setAttribute('points', p[0] + ',' + p[1] + ' ' + p[2] + ',' + p[3] + ' ' + p[4] + ',' + p[5] + ' ' + p[6] + ',' + p[7]);
      groove.setAttribute('x1', g.seam[0]); groove.setAttribute('y1', g.seam[1]); groove.setAttribute('x2', g.seam[2]); groove.setAttribute('y2', g.seam[3]);
      ceiling.style.opacity = g.ceil;
      var dx = g.seam[2] - g.seam[0], dy = g.seam[3] - g.seam[1], L = Math.hypot(dx, dy) || 1, ux = dx / L, uy = dy / L;
      var nx = -uy, ny = ux; if (nx < 0 || (nx === 0 && ny > 0)) { nx = -nx; ny = -ny; }
      if (to && to.below) { nx = -nx; ny = -ny; }
      var off = 38, ax = g.seam[0] + dx * 0.18 + nx * off, ay = g.seam[1] + dy * 0.18 + ny * off;
      var bx = g.seam[0] + dx * 0.82 + nx * off, by = g.seam[1] + dy * 0.82 + ny * off;
      arrowLine.setAttribute('x1', ax.toFixed(1)); arrowLine.setAttribute('y1', ay.toFixed(1));
      arrowLine.setAttribute('x2', bx.toFixed(1)); arrowLine.setAttribute('y2', by.toFixed(1));
      var hx = bx + ux * 12, hy = by + uy * 12;
      arrowHead.setAttribute('d', 'M' + hx.toFixed(1) + ' ' + hy.toFixed(1) + 'L' + (bx + nx * 6).toFixed(1) + ' ' + (by + ny * 6).toFixed(1) + 'L' + (bx - nx * 6).toFixed(1) + ' ' + (by - ny * 6).toFixed(1) + 'Z');
    }
    function emit(x, y, n) {
      for (var k = 0; k < n; k++) {
        var s = sparks[sparkIdx]; sparkIdx = (sparkIdx + 1) % sparks.length;
        var a = Math.random() * Math.PI * 2, sp = 60 + Math.random() * 160;
        s.x = x; s.y = y; s.vx = Math.cos(a) * sp; s.vy = Math.sin(a) * sp - 40; s.life = 0.25 + Math.random() * 0.45; s.max = s.life;
      }
    }
    var last = performance.now();
    function frame(now) {
      if (!running) return;
      if (!box.isConnected) { running = false; return; }
      var dt = Math.min((now - last) / 1000, 0.05); last = now;
      var morphing = false;
      if (from) {
        var k = clamp((now - morphStart) / MORPH, 0, 1), e = ease(k);
        cur.plate = lerpArr(from.plate, to.plate, e); cur.seam = lerpArr(from.seam, to.seam, e);
        cur.ang = lerp(from.ang, to.ang, e); cur.ceil = lerp(from.ceil, to.ceil, e);
        setGeom(cur);
        morphing = k < 1;
        if (!morphing) { from = null; weldStart = now; }
      }
      var s0x = cur.seam[0], s0y = cur.seam[1], s1x = cur.seam[2], s1y = cur.seam[3];
      var t = (now - weldStart) / 1000, WELD = 2.9, CYCLE = 4.4;
      var ph = morphing ? 0 : t % CYCLE, p = clamp(ph / WELD, 0, 1);
      var on = !morphing && ph < WELD;
      var tx = lerp(s0x, s1x, p), ty = lerp(s0y, s1y, p);
      var wv = on ? Math.sin(now / 45) * 2.2 : 0;
      var dx = s1x - s0x, dy = s1y - s0y, L = Math.hypot(dx, dy) || 1;
      tx += (-dy / L) * wv; ty += (dx / L) * wv;
      bead.setAttribute('x1', s0x); bead.setAttribute('y1', s0y);
      bead.setAttribute('x2', (morphing ? s0x : tx).toFixed(1)); bead.setAttribute('y2', (morphing ? s0y : ty).toFixed(1));
      bead.style.opacity = morphing ? 0 : ph > CYCLE - 0.45 ? clamp((CYCLE - ph) / 0.45, 0, 1) : 1;
      if (grad) { grad.setAttribute('x1', s0x); grad.setAttribute('y1', s0y); grad.setAttribute('x2', tx.toFixed(1)); grad.setAttribute('y2', ty.toFixed(1)); }
      var away = on ? 0 : morphing ? 60 : Math.min((ph - WELD) * 90, 60);
      var ar = cur.ang * Math.PI / 180;
      var ex = tx + Math.sin(ar) * away, ey = ty - Math.cos(ar) * away;
      elec.setAttribute('transform', 'translate(' + ex.toFixed(1) + ' ' + ey.toFixed(1) + ') rotate(' + cur.ang.toFixed(1) + ')');
      arc.setAttribute('cx', tx.toFixed(1)); arc.setAttribute('cy', ty.toFixed(1));
      arc.setAttribute('r', on ? (20 + Math.random() * 12).toFixed(1) : '0');
      arc.style.opacity = on ? 0.75 + Math.random() * 0.25 : 0;
      if (on) emit(tx, ty, Math.random() < 0.7 ? 1 : 2);
      sparks.forEach(function (s) {
        if (s.life <= 0) { s.el.setAttribute('cx', '-20'); return; }
        s.life -= dt; s.vy += 380 * dt; s.x += s.vx * dt; s.y += s.vy * dt;
        s.el.setAttribute('cx', s.x.toFixed(1)); s.el.setAttribute('cy', s.y.toFixed(1));
        s.el.setAttribute('opacity', clamp(s.life / s.max, 0, 1).toFixed(2));
      });
      requestAnimationFrame(frame);
    }
    function staticFrame() {
      var sx = cur.seam[0], sy = cur.seam[1], ex = lerp(cur.seam[0], cur.seam[2], 0.7), ey = lerp(cur.seam[1], cur.seam[3], 0.7);
      bead.setAttribute('x1', sx); bead.setAttribute('y1', sy); bead.setAttribute('x2', ex); bead.setAttribute('y2', ey); bead.style.opacity = 1;
      if (grad) { grad.setAttribute('x1', sx); grad.setAttribute('y1', sy); grad.setAttribute('x2', ex); grad.setAttribute('y2', ey); }
      arc.setAttribute('cx', ex); arc.setAttribute('cy', ey); arc.setAttribute('r', 24);
      elec.setAttribute('transform', 'translate(' + ex + ' ' + ey + ') rotate(' + cur.ang + ')');
    }
    function select(btn, user) {
      var key = btn.getAttribute('data-pos');
      if (!GEO[key] || (key === active && user)) return;
      active = key;
      from = { plate: cur.plate.slice(), seam: cur.seam.slice(), ang: cur.ang, ceil: cur.ceil };
      to = GEO[key]; morphStart = performance.now();
      tabs.forEach(function (b) { b.setAttribute('aria-pressed', String(b === btn)); });
      code.textContent = key;
      var t = btn.getAttribute('data-t') || key, x = btn.getAttribute('data-x') || '';
      if (animate) {
        G.timeline()
          .to([title, desc], { opacity: 0, y: -8, duration: 0.2, ease: 'power2.in' })
          .add(function () { title.textContent = t; desc.textContent = x; })
          .to([title, desc], { opacity: 1, y: 0, duration: 0.55, ease: 'expo.out', stagger: 0.06 });
        G.fromTo(code, { opacity: 0, x: 30 }, { opacity: 1, x: 0, duration: 0.9, ease: 'expo.out' });
      } else { title.textContent = t; desc.textContent = x; }
      if (!running) { cur.plate = to.plate.slice(); cur.seam = to.seam.slice(); cur.ang = to.ang; cur.ceil = to.ceil; from = null; setGeom(cur); staticFrame(); }
    }
    tabs.forEach(function (b) { b.addEventListener('click', function () { select(b, true); }); });
    var tabWrap = $('.viewer__tabs', box);
    if (tabWrap) tabWrap.addEventListener('keydown', function (e) {
      if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
      var i = tabs.indexOf(d.activeElement); if (i < 0) return;
      var n = tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
      n.focus(); n.click(); e.preventDefault();
    });
    setGeom(cur);
    if (reduced) { staticFrame(); return; }
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (en) {
        var vis = en[0].isIntersecting;
        if (vis && !running) { running = true; last = performance.now(); requestAnimationFrame(frame); }
        else if (!vis) running = false;
      }).observe(box);
    } else { running = true; requestAnimationFrame(frame); }
  }

  /* 3D-Münze */
  function mountCoin(c) {
    if (!once(c, 'Coin')) return;
    var cbody = $('.coin__body', c), edge = $('.coin__edge', c);
    for (var i = 0; i < 18; i++) {
      var l = d.createElement('i'); l.style.transform = 'translateZ(' + (-8.5 + i) + 'px)'; edge.appendChild(l);
    }
    if (!hasG || reduced) return;
    var st = { ry: -40, rx: 10, mx: 0, my: 0 };
    function apply() {
      var ry = st.ry + st.mx * 26, rx = st.rx - st.my * 18;
      cbody.style.setProperty('--ry', ry.toFixed(2) + 'deg');
      cbody.style.setProperty('--rx', rx.toFixed(2) + 'deg');
      var sheen = (((ry % 360) + 360) % 360) / 360 * 100;
      $$('.coin__face', c).forEach(function (f) { f.style.setProperty('--sheen', (100 - sheen).toFixed(1) + '%'); });
    }
    var sec = c.closest('.mark') || c.parentNode;
    if (animate) G.to(st, { ry: 320, ease: 'none', onUpdate: apply, scrollTrigger: { trigger: sec, start: 'top bottom', end: 'bottom top', scrub: 1 } });
    else G.to(st, { ry: 320, duration: 14, ease: 'none', repeat: -1, onUpdate: apply });
    if (fine) {
      sec.addEventListener('pointermove', function (e) {
        var r = c.getBoundingClientRect();
        G.to(st, { mx: clamp((e.clientX - (r.left + r.width / 2)) / innerWidth * 2, -1, 1), my: clamp((e.clientY - (r.top + r.height / 2)) / innerHeight * 2, -1, 1), duration: 1, ease: 'power3', onUpdate: apply });
      });
      sec.addEventListener('pointerleave', function () { G.to(st, { mx: 0, my: 0, duration: 1.2, ease: 'power3', onUpdate: apply }); });
    }
    apply();
  }

  /* Ablauf */
  function mountProcess(sec) {
    if (!once(sec, 'Proc')) return;
    var track = $('.process__track', sec);
    if (!track) return;
    var steps = $$('.step', track), marks = [];
    function measure(vertical) {
      marks = steps.map(function (s) { return vertical ? (s.offsetTop + 8) / track.offsetHeight : s.offsetLeft / track.offsetWidth; });
    }
    function setP(p) {
      track.style.setProperty('--p', p.toFixed(4));
      track.style.setProperty('--on', p > 0.002 && p < 0.998 ? 1 : 0);
      steps.forEach(function (s, i) { s.classList.toggle('is-on', p >= marks[i] - 0.005); });
    }
    if (!animate) { measure(innerWidth <= 860); setP(1); return; }
    var pin = $('.process__pin', sec);
    var mmq = G.matchMedia();
    mmq.add('(min-width: 861px)', function () {
      measure(false);
      var o = { p: 0 };
      G.to(o, {
        p: 1, ease: 'none', onUpdate: function () { setP(o.p); },
        scrollTrigger: { trigger: pin, start: function () { return pin.offsetHeight > innerHeight * 0.86 ? 'top top+=70' : 'center center'; }, end: '+=130%', pin: true, scrub: 0.8, onRefresh: function () { measure(false); } }
      });
    });
    mmq.add('(max-width: 860px)', function () {
      measure(true);
      var o = { p: 0 };
      G.to(o, {
        p: 1, ease: 'none', onUpdate: function () { setP(o.p); },
        scrollTrigger: { trigger: track, start: 'top 72%', end: 'bottom 55%', scrub: 0.8, onRefresh: function () { measure(true); } }
      });
    });
  }

  /* Einsatzgebiet */
  function mountArea(map) {
    if (!once(map, 'Map')) return;
    var sec = map.closest('.area') || map.parentNode;
    var lines = $$('.map__lines line', map), towns = $$('.town', map), list = $$('.towns li', sec);
    var sweep = $('.map__sweep', map), map3d = $('.map3d', map);
    var preset = $$('.is-hot', sec).map(function (e) { return e.getAttribute('data-town'); });
    function hot(name, on) {
      towns.concat(list).forEach(function (el) { if (el.getAttribute('data-town') === name) el.classList.toggle('is-hot', on || preset.indexOf(name) >= 0); });
    }
    towns.concat(list).forEach(function (el) {
      var n = el.getAttribute('data-town');
      el.addEventListener('pointerenter', function () { hot(n, true); });
      el.addEventListener('pointerleave', function () { hot(n, false); });
    });
    if (!animate) { lines.forEach(function (l) { l.style.strokeDashoffset = 0; }); return; }
    G.set(towns, { opacity: 0 });
    onView(map, function () {
      G.to(lines, { strokeDashoffset: 0, duration: 1.6, ease: 'power3.out', stagger: 0.09 });
      G.to(towns, { opacity: 1, duration: 0.8, ease: 'power2.out', stagger: 0.09, delay: 0.5 });
    }, '0px 0px -20% 0px');
    var ang = 0, vis = false;
    new IntersectionObserver(function (en) { vis = en[0].isIntersecting; }).observe(map);
    G.ticker.add(function (t, dt) {
      if (!vis) return;
      ang = (ang + dt * 0.05) % 360;
      sweep.setAttribute('transform', 'rotate(' + (-ang).toFixed(2) + ')');
    });
    if (fine) {
      var rxTo = G.quickTo(map3d, 'rotationX', { duration: 1.2, ease: 'power3' });
      var rzTo = G.quickTo(map3d, 'rotationZ', { duration: 1.2, ease: 'power3' });
      G.set(map3d, { rotationX: 30, rotationZ: -5 });
      map.addEventListener('pointermove', function (e) {
        var r = map.getBoundingClientRect();
        rxTo(30 - ((e.clientY - r.top) / r.height - 0.5) * 14);
        rzTo(-5 + ((e.clientX - r.left) / r.width - 0.5) * 8);
      });
      map.addEventListener('pointerleave', function () { rxTo(30); rzTo(-5); });
    }
  }

  /* FAQ */
  function mountFaq(list) {
    if (!once(list, 'Faq')) return;
    $$('.qa', list).forEach(function (qa, i) {
      var btn = $('button', qa);
      function set(open) { btn.setAttribute('aria-expanded', String(open)); qa.classList.toggle('is-open', open); }
      if (i === 0) set(true);
      btn.addEventListener('click', function () {
        set(btn.getAttribute('aria-expanded') !== 'true');
        if (hasG) setTimeout(function () { ST.refresh(); }, 650);
      });
    });
  }

  /* ==========================================================================
     FORMULARE (gemeinsamer Versand + Ausweichweg WhatsApp/E-Mail)
     ========================================================================== */
  function phoneHtml() {
    var a = $('a[href^="tel:"]');
    return a ? '<a href="' + a.getAttribute('href') + '">' + a.getAttribute('href').replace('tel:', '') + '</a>' : '';
  }
  function waBase() { var a = $('a[href*="wa.me/"]'); return a ? a.href.split('?')[0] : 'https://wa.me/'; }
  function mailBase() { var a = $('a[href^="mailto:"]'); return a ? a.getAttribute('href').split('?')[0] : 'mailto:'; }

  function formTools(form) {
    var err = $('.wizard__error', form);
    var alt = d.createElement('div');
    alt.className = 'wizard__alt'; alt.hidden = true; alt.setAttribute('role', 'status');
    err.parentNode.insertBefore(alt, err.nextSibling);
    function showErr(html) { err.innerHTML = html; err.hidden = false; if (animate) G.fromTo(err, { x: -8 }, { x: 0, duration: 0.5, ease: 'elastic.out(1, 0.4)' }); }
    function hideErr() { err.hidden = true; alt.hidden = true; }
    function picked(name) {
      return $$('[name="' + name + '"]', form).filter(function (i) { return i.tagName === 'SELECT' ? !!i.value : i.checked; }).map(function (i) { return i.value; }).join(', ');
    }
    function val(name) { var f = form.elements[name]; return f && f.value ? f.value.trim() : ''; }
    function summary() {
      return [
        'Anfrage über die Website', '',
        'Arbeit: ' + (picked('arbeit[]') || '–'),
        'Wo: ' + (picked('ort_art') || '–'),
        'In der Höhe: ' + (picked('hoehe') || '–'),
        'PLZ und Ort: ' + (val('ort') || '–'),
        'Wunschtermin: ' + (val('termin') || '–'),
        'Beschreibung: ' + (val('beschreibung') || '–'), '',
        'Name: ' + val('name'),
        'Telefon: ' + (val('telefon') || '–'),
        'E-Mail: ' + (val('email') || '–')
      ].join('\n');
    }
    function showAlt(intro, onPick) {
      var text = summary();
      var wa = waBase() + '?text=' + encodeURIComponent(text);
      var mail = mailBase() + '?subject=' + encodeURIComponent('Anfrage über die Website – ' + val('name')) + '&body=' + encodeURIComponent(text);
      alt.innerHTML = '<p></p><div class="wizard__alt-btns">' +
        '<a class="btn btn--molten btn--sm" data-alt href="' + wa + '" target="_blank" rel="noopener"><span>Per WhatsApp senden</span></a>' +
        '<a class="btn btn--ghost btn--sm" data-alt href="' + mail + '"><span>Per E-Mail senden</span></a></div>';
      alt.firstChild.textContent = intro;
      alt.hidden = false;
      if (animate) G.fromTo(alt, { opacity: 0, y: 10 }, { opacity: 1, y: 0, duration: 0.6, ease: 'expo.out' });
      alt.onclick = function (e) { if (e.target.closest('[data-alt]')) setTimeout(onPick, 400); };
    }
    function send(submitBtn, onOk, onAltPick) {
      hideErr();
      if (STATIC) { showAlt('Fast geschafft: Ihre Angaben sind schon eingetragen – schicken Sie die Anfrage mit einem Klick ab.', onAltPick); return; }
      submitBtn.disabled = true;
      var label = $('span', submitBtn), old = label.textContent;
      label.textContent = 'Wird gesendet …';
      fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' }, credentials: 'same-origin' })
        .then(function (r) {
          return r.json().then(
            function (j) { return { ok: r.ok && j && j.ok, j: j, status: r.status }; },
            function () { return { ok: false, transport: true }; }
          );
        }, function () { return { ok: false, transport: true }; })
        .then(function (res) {
          if (res.ok) { onOk(); return; }
          if (res.transport || res.status >= 500 || res.status === 429) {
            showAlt((res.j && res.j.message ? res.j.message + ' ' : 'Die Anfrage konnte nicht direkt gesendet werden. ') + 'Schicken Sie sie einfach per WhatsApp oder E-Mail – Ihre Angaben sind schon eingetragen.', onAltPick);
          } else {
            showErr((res.j && res.j.message) || 'Bitte prüfen Sie Ihre Angaben.');
          }
        })
        .then(function () { submitBtn.disabled = false; label.textContent = old; });
    }
    return { showErr: showErr, hideErr: hideErr, send: send, err: err };
  }

  function validContact(form, needPhone) {
    var f = form.elements;
    var name = f.name.value.trim(), tel = f.telefon ? f.telefon.value.trim() : '', mail = f.email ? f.email.value.trim() : '';
    if (!name) return 'Bitte geben Sie Ihren Namen an.';
    if (needPhone && !tel) return 'Bitte geben Sie Ihre Telefonnummer an.';
    if (!tel && !mail) return 'Bitte geben Sie eine Telefonnummer oder E-Mail-Adresse an.';
    if (mail && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(mail)) return 'Bitte prüfen Sie die E-Mail-Adresse.';
    if (!f.einwilligung.checked) return 'Bitte bestätigen Sie die Einwilligung zur Bearbeitung Ihrer Angaben.';
    return '';
  }

  function mountWizard(form) {
    if (!once(form, 'Wiz')) return;
    var panels = $$('.wizard__panel', form), prog = $$('.wizard__progress li', form), seam = $('.wizard__seam', form);
    var prev = $('[data-prev]', form), next = $('[data-next]', form), submit = $('[data-submit]', form);
    var done = $('.wizard__done', form);
    var tools = formTools(form);
    var step = 1;
    function ui() {
      prog.forEach(function (li, i) { li.classList.toggle('is-active', i === step - 1); li.classList.toggle('is-done', i < step - 1); });
      seam.style.setProperty('--p', (step / 3).toFixed(4));
      prev.hidden = step === 1; next.hidden = step === 3; submit.hidden = step !== 3;
    }
    function go(n) {
      var a = panels[step - 1], b = panels[n - 1], dir = n > step ? 1 : -1;
      step = n; ui();
      function swap() {
        a.hidden = true; b.hidden = false;
        if (animate) G.fromTo(b, { opacity: 0, x: 34 * dir }, { opacity: 1, x: 0, duration: 0.6, ease: 'expo.out' });
        b.setAttribute('tabindex', '-1'); b.focus({ preventScroll: true });
        if (hasG) ST.refresh();
      }
      if (animate) G.to(a, { opacity: 0, x: -24 * dir, duration: 0.22, ease: 'power2.in', onComplete: swap }); else swap();
    }
    function val(n) {
      if (n === 1 && !form.querySelector('input[name="arbeit[]"]:checked')) return 'Bitte wählen Sie mindestens eine Arbeit aus.';
      if (n === 3) return validContact(form, false);
      return '';
    }
    function finish(alternative) {
      if (alternative) $('p', done).innerHTML = 'Ihre Anfrage ist in WhatsApp bzw. Ihrem E-Mail-Programm vorbereitet – bitte dort noch auf <b>Senden</b> tippen. Eilt es, rufen Sie einfach an: ' + phoneHtml() + '.';
      form.classList.add('is-done'); done.hidden = false; done.focus({ preventScroll: true });
      if (animate) G.fromTo(done, { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.9, ease: 'expo.out' });
      if (hasG) ST.refresh();
    }
    next.addEventListener('click', function () {
      var m = val(step); if (m) { tools.showErr(m); return; }
      tools.hideErr(); go(step + 1);
    });
    prev.addEventListener('click', function () { tools.hideErr(); go(step - 1); });
    form.addEventListener('change', function () { if (!tools.err.hidden && !val(step)) tools.err.hidden = true; });
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (step < 3) { next.click(); return; }
      var m = val(3); if (m) { tools.showErr(m); return; }
      tools.send(submit, function () { finish(false); }, function () { finish(true); });
    });
    ui();
  }

  function mountQuickform(form) {
    if (!once(form, 'Quick')) return;
    var tools = formTools(form), done = $('.qf__done', form), submit = $('.qf__submit', form);
    function finish(alternative) {
      if (alternative) $('p', done).innerHTML = '<strong>Fast fertig.</strong> Bitte die vorbereitete Nachricht noch absenden. Eilt es? ' + phoneHtml();
      form.classList.add('is-done'); done.hidden = false; done.focus({ preventScroll: true });
    }
    form.addEventListener('change', function () { if (!tools.err.hidden && !validContact(form, true)) tools.err.hidden = true; });
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var m = validContact(form, true); if (m) { tools.showErr(m); return; }
      tools.send(submit, function () { finish(false); }, function () { finish(true); });
    });
  }

  /* Footer-Wort */
  function mountWord(w) {
    if (!once(w, 'Word')) return;
    var span = $('span', w);
    if (animate) G.fromTo(span, { '--rx': '62deg' }, { '--rx': '16deg', ease: 'none', scrollTrigger: { trigger: w, start: 'top bottom', end: 'bottom 85%', scrub: 1 } });
    if (fine) {
      (w.closest('footer') || w).addEventListener('pointermove', function (e) {
        var r = span.getBoundingClientRect();
        span.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 100).toFixed(1) + '%');
        span.style.setProperty('--my', ((e.clientY - r.top) / r.height * 100).toFixed(1) + '%');
        span.style.setProperty('--ry', (((e.clientX / innerWidth) - 0.5) * 16).toFixed(2) + 'deg');
      });
    }
  }

  /* ==========================================================================
     MOUNT
     ========================================================================== */
  function mount(scope) {
    scope = scope || d;
    each(scope, '.hero', mountHero);
    mountReveals(scope);
    mountMagnetic(scope);
    mountTilt(scope);
    each(scope, '.marquee', mountMarquee);
    each(scope, '[data-positions]', mountPositions);
    each(scope, '[data-coin]', mountCoin);
    each(scope, '.process', mountProcess);
    each(scope, '[data-map]', mountArea);
    each(scope, '[data-faq]', mountFaq);
    each(scope, '[data-wizard]', mountWizard);
    each(scope, '[data-quickform]', mountQuickform);
    each(scope, '[data-word3d]', mountWord);
    if (hasG && scope !== d) ST.refresh();
  }
  window.Blitz = { mount: mount };
  mount(d);

  /* Elementor: neu gerenderte Blitz-Widgets im Editor initialisieren */
  function hookElementor() {
    var ef = window.elementorFrontend;
    if (!ef || !ef.hooks) return false;
    ef.hooks.addAction('frontend/element_ready/widget', function ($scope) {
      var el = $scope && $scope[0];
      if (el && /(^|\s)blitz-/.test(el.getAttribute('data-widget_type') || '')) mount(el);
    });
    return true;
  }
  if (!hookElementor() && window.jQuery) window.jQuery(window).on('elementor/frontend/init', hookElementor);

  if (hasG) {
    (d.fonts && d.fonts.ready ? d.fonts.ready : Promise.resolve()).then(function () { ST.refresh(); });
    addEventListener('load', function () { ST.refresh(); });
  }
})();
