/* Schweisstechnik Blitz – 3D-Hero: Steignaht in Echtzeit
   Zwei Stahlplatten, die Stabelektrode zieht die Naht von unten nach oben,
   die Raupe glüht nach und läuft beim Abkühlen in Anlauffarben an. */
import * as THREE from '../vendor/three/build/three.module.min.js';
import { RoomEnvironment } from '../vendor/three/jsm/environments/RoomEnvironment.js';

const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
let readyFired = false;

function ready() {
  if (readyFired) return;
  readyFired = true;
  window.__blitzHeroReady = true;
  window.dispatchEvent(new CustomEvent('blitz:hero-ready'));
}

/* Startet die Szene für einen Hero (mehrfach aufrufbar, z. B. vom Elementor-Editor) */
function boot(hero) {
  if (!hero || hero.__blitz3d) return;
  const canvas = hero.querySelector('.hero__canvas');
  if (!canvas) return;
  hero.__blitz3d = true;
  try {
    init(hero, canvas);
  } catch (err) {
    console.warn('[hero] WebGL nicht verfügbar:', err);
    hero.classList.add('no-webgl');
    ready();
  }
}
window.BlitzHero3D = boot;
document.querySelectorAll('.hero').forEach(boot);
if (!document.querySelector('.hero__canvas')) ready();

function init(hero, canvas) {
  const stage = canvas.parentElement;
  const withForm = hero.classList.contains('hero--form');
  const small = Math.min(innerWidth, innerHeight) < 700 || matchMedia('(pointer: coarse)').matches;

  /* ---------- Maße & Zeitplan ---------- */
  const PW = 2.7, PH = 7.6, PD = 0.34, GAP = 0.09;
  const FRONT = PD / 2;
  const Y0 = -3.15, Y1 = 3.15, SEAM = Y1 - Y0;
  const T_IN = 0.7, T_WELD = 9.5, T_HOLD = 3.6, T_FADE = 1.2;
  const PERIOD = T_IN + T_WELD + T_HOLD + T_FADE;
  const FLOOR = -PH / 2;
  const f = (v) => v.toFixed(4);

  /* ---------- Renderer ---------- */
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: !small, alpha: true, powerPreference: 'high-performance' });
  renderer.setClearColor(0x000000, 0);
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.1;

  const scene = new THREE.Scene();
  scene.fog = new THREE.Fog(0x09090a, 10, 24);
  const pmrem = new THREE.PMREMGenerator(renderer);
  scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
  scene.environmentIntensity = 0.55;

  const camera = new THREE.PerspectiveCamera(30, 1, 0.1, 80);
  const rig = new THREE.Group();
  rig.rotation.y = -0.52;
  scene.add(rig);

  /* ---------- gemeinsame Uniforms ---------- */
  const U = {
    uT: { value: 0 },      // Schweißzeit (s) seit Zünden
    uFade: { value: 1 },   // 1 → sichtbar, 0 → ausgeblendet
    uArcY: { value: Y0 },  // Höhe des Lichtbogens
    uArcOn: { value: 0 },  // Lichtbogen an/aus (mit Flackern)
  };

  const GLSL_COMMON = /* glsl */`
    vec3 temper(float d) {
      vec3 c = vec3(0.10, 0.10, 0.12);
      c = mix(c, vec3(0.16, 0.27, 0.70), smoothstep(0.02, 0.08, d));
      c = mix(c, vec3(0.46, 0.22, 0.60), smoothstep(0.09, 0.16, d));
      c = mix(c, vec3(0.64, 0.37, 0.16), smoothstep(0.17, 0.24, d));
      c = mix(c, vec3(0.90, 0.74, 0.40), smoothstep(0.25, 0.34, d));
      return c;
    }
    vec3 glow(float h) {
      vec3 c = mix(vec3(0.50, 0.04, 0.0), vec3(1.0, 0.40, 0.06), smoothstep(0.12, 0.5, h));
      return mix(c, vec3(1.0, 0.88, 0.62), smoothstep(0.72, 0.97, h));
    }
  `;

  /* ---------- Gebürsteter Stahl (Canvas-Textur) ---------- */
  function steelTexture() {
    const c = document.createElement('canvas');
    c.width = 512; c.height = 1024;
    const g = c.getContext('2d');
    g.fillStyle = '#626770'; g.fillRect(0, 0, 512, 1024);
    for (let i = 0; i < 70; i++) {
      const x = Math.random() * 512, y = Math.random() * 1024, r = 30 + Math.random() * 140;
      const gr = g.createRadialGradient(x, y, 0, x, y, r);
      gr.addColorStop(0, `rgba(28,32,40,${0.12 + Math.random() * 0.25})`);
      gr.addColorStop(1, 'rgba(28,32,40,0)');
      g.fillStyle = gr; g.fillRect(x - r, y - r, r * 2, r * 2);
    }
    for (let i = 0; i < 3200; i++) {
      const y = Math.random() * 1024, l = 30 + Math.random() * 380, x = Math.random() * 560 - 40;
      g.fillStyle = Math.random() < 0.5 ? `rgba(255,255,255,${Math.random() * 0.07})` : `rgba(0,0,0,${Math.random() * 0.09})`;
      g.fillRect(x, y, l, 1);
    }
    const t = new THREE.CanvasTexture(c);
    t.colorSpace = THREE.SRGBColorSpace;
    t.anisotropy = Math.min(8, renderer.capabilities.getMaxAnisotropy());
    return t;
  }
  const steel = steelTexture();

  function glowTexture() {
    const c = document.createElement('canvas');
    c.width = c.height = 128;
    const g = c.getContext('2d');
    const gr = g.createRadialGradient(64, 64, 0, 64, 64, 64);
    gr.addColorStop(0, 'rgba(255,255,255,1)');
    gr.addColorStop(0.18, 'rgba(255,255,255,.55)');
    gr.addColorStop(0.45, 'rgba(255,255,255,.12)');
    gr.addColorStop(1, 'rgba(255,255,255,0)');
    g.fillStyle = gr; g.fillRect(0, 0, 128, 128);
    const t = new THREE.CanvasTexture(c);
    t.colorSpace = THREE.SRGBColorSpace;
    return t;
  }
  const glowTex = glowTexture();

  /* ---------- Platten mit Wärmeeinflusszone ---------- */
  function plateMaterial(edgeX) {
    const m = new THREE.MeshStandardMaterial({ map: steel, color: 0xffffff, metalness: 0.78, roughness: 0.42 });
    m.onBeforeCompile = (s) => {
      Object.assign(s.uniforms, U);
      s.uniforms.uEdge = { value: edgeX };
      s.vertexShader = s.vertexShader
        .replace('#include <common>', '#include <common>\nvarying vec3 vP;')
        .replace('#include <begin_vertex>', '#include <begin_vertex>\nvP = position;');
      s.fragmentShader = s.fragmentShader
        .replace('#include <common>', `#include <common>
          varying vec3 vP;
          uniform float uT, uFade, uArcY, uArcOn, uEdge;
          ${GLSL_COMMON}`)
        .replace('#include <color_fragment>', `#include <color_fragment>
          float dEdge = abs(vP.x - uEdge);
          float dd = dEdge + sin(vP.y * 7.0) * 0.012 + sin(vP.y * 23.0 + 1.3) * 0.006;
          float born = (vP.y - (${f(Y0)})) / ${f(SEAM)} * ${f(T_WELD)};
          float age = uT - born;
          float inSeam = step(${f(Y0)}, vP.y) * step(vP.y, ${f(Y1)});
          float welded = step(0.0, age) * inSeam;
          float front = clamp(smoothstep(${f(FRONT - 0.003)}, ${f(FRONT)}, vP.z) + step(dEdge, 0.002), 0.0, 1.0);
          float dev = smoothstep(0.25, 3.2, age);
          float band = 1.0 - smoothstep(0.32, 0.52, dd);
          float tMask = band * welded * dev * uFade * front;
          diffuseColor.rgb = mix(diffuseColor.rgb, temper(dd), tMask * 0.95);
        `)
        .replace('#include <metalnessmap_fragment>', `#include <metalnessmap_fragment>
          metalnessFactor = mix(metalnessFactor, 0.3, tMask);
          roughnessFactor = mix(roughnessFactor, 0.28, tMask);
        `)
        .replace('#include <emissivemap_fragment>', `#include <emissivemap_fragment>
          {
            float dy = vP.y - uArcY;
            float ga = exp(-(dd * dd * 20.0 + dy * dy * 9.0)) * uArcOn;
            float tr = welded * exp(-age * 0.75) * exp(-dd * dd * 50.0);
            float pre = uArcOn * step(0.0, dy) * exp(-dd * dd * 40.0) * exp(-dy * 5.0) * 0.25;
            totalEmissiveRadiance += vec3(1.0, 0.34, 0.06) * (ga * 2.4 + tr * 1.5 + pre) * uFade * clamp(front + 0.25, 0.0, 1.0);
            totalEmissiveRadiance += temper(dd) * tMask * 0.16;
          }
        `);
    };
    return m;
  }
  const plateGeo = new THREE.BoxGeometry(PW, PH, PD);
  const plateL = new THREE.Mesh(plateGeo, plateMaterial(PW / 2));
  plateL.position.x = -(PW / 2 + GAP / 2);
  const plateR = new THREE.Mesh(plateGeo, plateMaterial(-PW / 2));
  plateR.position.x = PW / 2 + GAP / 2;
  rig.add(plateL, plateR);

  const floor = new THREE.Mesh(
    new THREE.PlaneGeometry(40, 40),
    new THREE.MeshStandardMaterial({ color: 0x0c0c0e, roughness: 0.82, metalness: 0.25 })
  );
  floor.rotation.x = -Math.PI / 2;
  floor.position.y = FLOOR - 0.001;
  rig.add(floor);

  /* ---------- Schweißraupe (Instanzen mit Geburtszeit) ---------- */
  const N = small ? 160 : 220;
  const beadGeo = new THREE.SphereGeometry(1, 14, 10);
  const born = new Float32Array(N);
  const beadMat = new THREE.MeshStandardMaterial({ color: 0x8a8f96, metalness: 0.72, roughness: 0.3 });
  beadMat.onBeforeCompile = (s) => {
    Object.assign(s.uniforms, U);
    s.vertexShader = s.vertexShader
      .replace('#include <common>', '#include <common>\nattribute float aBorn;\nuniform float uT, uFade;\nvarying float vAge;')
      .replace('#include <begin_vertex>', `#include <begin_vertex>
        float age = uT - aBorn;
        vAge = age;
        transformed *= smoothstep(0.0, 0.2, age) * uFade;`);
    s.fragmentShader = s.fragmentShader
      .replace('#include <common>', `#include <common>\nvarying float vAge;\n${GLSL_COMMON}`)
      .replace('#include <color_fragment>', `#include <color_fragment>
        diffuseColor.rgb = mix(vec3(0.46, 0.48, 0.52), vec3(0.34, 0.33, 0.46), smoothstep(1.0, 6.0, vAge));`)
      .replace('#include <emissivemap_fragment>', `#include <emissivemap_fragment>
        { float h = exp(-max(vAge, 0.0) * 0.8); totalEmissiveRadiance += glow(h) * h * 3.4; }`);
  };
  const bead = new THREE.InstancedMesh(beadGeo, beadMat, N);
  {
    const m4 = new THREE.Matrix4(), q = new THREE.Quaternion(), e = new THREE.Euler(), p = new THREE.Vector3(), sc = new THREE.Vector3();
    for (let i = 0; i < N; i++) {
      const k = (i + 0.5) / N;
      born[i] = k * T_WELD;
      p.set(Math.sin(i * 0.8) * 0.012, Y0 + k * SEAM, FRONT - 0.035);
      e.set(-0.55 + (Math.random() - 0.5) * 0.12, 0, (Math.random() - 0.5) * 0.25);
      q.setFromEuler(e);
      sc.set(0.125 + Math.random() * 0.016, 0.06 + Math.random() * 0.01, 0.075);
      m4.compose(p, q, sc);
      bead.setMatrixAt(i, m4);
    }
  }
  beadGeo.setAttribute('aBorn', new THREE.InstancedBufferAttribute(born, 1));
  bead.frustumCulled = false;
  rig.add(bead);

  /* ---------- Stabelektrode mit Halter ---------- */
  const elecDir = new THREE.Vector3(0.66, -0.34, 0.68).normalize();
  const elec = new THREE.Group();
  elec.quaternion.setFromUnitVectors(new THREE.Vector3(0, 1, 0), elecDir);
  rig.add(elec);
  const rod = new THREE.Mesh(
    new THREE.CylinderGeometry(0.03, 0.03, 1, 12).translate(0, 0.5, 0),
    new THREE.MeshStandardMaterial({ color: 0xcfc5b2, roughness: 0.85, metalness: 0.05 })
  );
  const tip = new THREE.Mesh(new THREE.SphereGeometry(0.036, 12, 8), new THREE.MeshBasicMaterial({ color: new THREE.Color(1.0, 0.78, 0.5).multiplyScalar(2.2) }));
  const holder = new THREE.Group();
  const jaw = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.085, 0.16, 18).translate(0, 0.08, 0), new THREE.MeshStandardMaterial({ color: 0xb87333, metalness: 0.9, roughness: 0.32 }));
  const handle = new THREE.Mesh(new THREE.CylinderGeometry(0.1, 0.088, 1.05, 22).translate(0, 0.68, 0), new THREE.MeshStandardMaterial({ color: 0x17181a, roughness: 0.5, metalness: 0.15 }));
  const cable = new THREE.Mesh(
    new THREE.TubeGeometry(new THREE.CatmullRomCurve3([
      new THREE.Vector3(0, 1.18, 0), new THREE.Vector3(0.05, 1.8, 0.05), new THREE.Vector3(0.45, 2.6, -0.2), new THREE.Vector3(1.6, 3.2, -1.1), new THREE.Vector3(3.2, 3.4, -2.2),
    ]), 40, 0.05, 8),
    new THREE.MeshStandardMaterial({ color: 0x111113, roughness: 0.6 })
  );
  holder.add(jaw, handle, cable);
  elec.add(rod, tip, holder);

  /* ---------- Lichtbogen ---------- */
  function sprite(color, opacity) {
    const s = new THREE.Sprite(new THREE.SpriteMaterial({ map: glowTex, color, transparent: true, opacity, blending: THREE.AdditiveBlending, depthWrite: false, depthTest: false, toneMapped: false }));
    s.renderOrder = 10;
    rig.add(s);
    return s;
  }
  const core = sprite(new THREE.Color(0.86, 0.94, 1.0), 1);
  const halo = sprite(new THREE.Color(1.0, 0.5, 0.18), 0.6);
  const bloom = sprite(new THREE.Color(1.0, 0.34, 0.08), small ? 0.12 : 0.2);
  const streak = sprite(new THREE.Color(0.55, 0.76, 1.0), 0.45);

  const arcLight = new THREE.PointLight(0xffa25a, 0, 0, 2);
  const arcBlue = new THREE.PointLight(0x9fd0ff, 0, 0, 2);
  rig.add(arcLight, arcBlue);
  const rim = new THREE.DirectionalLight(0x6f8cff, 1.5);
  rim.position.set(-6, 4, -5);
  const key = new THREE.DirectionalLight(0xffe2c4, 0.4);
  key.position.set(4, 6, 8);
  const fill = new THREE.DirectionalLight(0x8fa3c8, 0.55);
  fill.position.set(-7, 1.5, 6);
  scene.add(rim, key, fill);

  /* ---------- Funken ---------- */
  const SP = small ? 240 : 560;
  const sPos = new Float32Array(SP * 3), sVel = new Float32Array(SP * 3), sLife = new Float32Array(SP), sMax = new Float32Array(SP).fill(1);
  const lPos = new Float32Array(SP * 6), lCol = new Float32Array(SP * 6), hCol = new Float32Array(SP * 3);
  for (let i = 0; i < SP; i++) sPos[i * 3 + 1] = -100;
  const lineGeo = new THREE.BufferGeometry();
  lineGeo.setAttribute('position', new THREE.BufferAttribute(lPos, 3).setUsage(THREE.DynamicDrawUsage));
  lineGeo.setAttribute('color', new THREE.BufferAttribute(lCol, 3).setUsage(THREE.DynamicDrawUsage));
  const lines = new THREE.LineSegments(lineGeo, new THREE.LineBasicMaterial({ vertexColors: true, transparent: true, blending: THREE.AdditiveBlending, depthWrite: false, toneMapped: false }));
  const headGeo = new THREE.BufferGeometry();
  headGeo.setAttribute('position', new THREE.BufferAttribute(sPos, 3).setUsage(THREE.DynamicDrawUsage));
  headGeo.setAttribute('color', new THREE.BufferAttribute(hCol, 3).setUsage(THREE.DynamicDrawUsage));
  const heads = new THREE.Points(headGeo, new THREE.PointsMaterial({ size: 0.06, map: glowTex, vertexColors: true, transparent: true, blending: THREE.AdditiveBlending, depthWrite: false, toneMapped: false }));
  lines.frustumCulled = heads.frustumCulled = false;
  rig.add(lines, heads);
  let sCursor = 0, emitAcc = 0;

  function emit(n, p) {
    for (let k = 0; k < n; k++) {
      const i = sCursor; sCursor = (sCursor + 1) % SP;
      let vx = (Math.random() - 0.5) * 2.4, vy = Math.random() * 2.4 - 0.5, vz = 0.25 + Math.random() * 1.5;
      const len = Math.hypot(vx, vy, vz) || 1, sp = 1.3 + Math.random() * 3.4;
      sPos[i * 3] = p.x; sPos[i * 3 + 1] = p.y; sPos[i * 3 + 2] = p.z;
      sVel[i * 3] = vx / len * sp; sVel[i * 3 + 1] = vy / len * sp; sVel[i * 3 + 2] = vz / len * sp;
      sLife[i] = sMax[i] = 0.35 + Math.random() * 1.15;
    }
  }
  function stepSparks(dt) {
    const drag = 1 - 0.85 * dt;
    for (let i = 0; i < SP; i++) {
      const i3 = i * 3, i6 = i * 6;
      if (sLife[i] <= 0) {
        hCol[i3] = hCol[i3 + 1] = hCol[i3 + 2] = 0;
        lCol[i6] = lCol[i6 + 1] = lCol[i6 + 2] = lCol[i6 + 3] = lCol[i6 + 4] = lCol[i6 + 5] = 0;
        continue;
      }
      sLife[i] -= dt;
      sVel[i3 + 1] -= 7.8 * dt;
      sVel[i3] *= drag; sVel[i3 + 1] *= drag; sVel[i3 + 2] *= drag;
      sPos[i3] += sVel[i3] * dt; sPos[i3 + 1] += sVel[i3 + 1] * dt; sPos[i3 + 2] += sVel[i3 + 2] * dt;
      if (sPos[i3 + 1] < FLOOR + 0.01) {
        sPos[i3 + 1] = FLOOR + 0.01;
        if (sVel[i3 + 1] < 0) { sVel[i3 + 1] *= -0.32; sVel[i3] *= 0.6; sVel[i3 + 2] *= 0.6; }
      }
      if (sPos[i3 + 2] < FRONT && Math.abs(sPos[i3]) < PW + GAP && sPos[i3 + 1] < PH / 2) { sPos[i3 + 2] = FRONT; sVel[i3 + 2] *= -0.3; }
      const k = Math.max(sLife[i] / sMax[i], 0);
      let r, g, b;
      if (k > 0.55) { const t = (k - 0.55) / 0.45; r = 1; g = 0.55 + 0.4 * t; b = 0.18 + 0.6 * t; }
      else { const t = k / 0.55; r = 0.55 + 0.45 * t; g = 0.08 + 0.47 * t; b = 0.1 * t; }
      const it = Math.min(1, k * 1.7);
      hCol[i3] = r * it; hCol[i3 + 1] = g * it; hCol[i3 + 2] = b * it;
      lPos[i6] = sPos[i3]; lPos[i6 + 1] = sPos[i3 + 1]; lPos[i6 + 2] = sPos[i3 + 2];
      lPos[i6 + 3] = sPos[i3] - sVel[i3] * 0.045; lPos[i6 + 4] = sPos[i3 + 1] - sVel[i3 + 1] * 0.045; lPos[i6 + 5] = sPos[i3 + 2] - sVel[i3 + 2] * 0.045;
      lCol[i6] = r * it; lCol[i6 + 1] = g * it; lCol[i6 + 2] = b * it;
      lCol[i6 + 3] = r * it * 0.08; lCol[i6 + 4] = g * it * 0.08; lCol[i6 + 5] = b * it * 0.08;
    }
    lineGeo.attributes.position.needsUpdate = true;
    lineGeo.attributes.color.needsUpdate = true;
    headGeo.attributes.position.needsUpdate = true;
    headGeo.attributes.color.needsUpdate = true;
  }

  /* ---------- Glutpartikel in der Luft ---------- */
  const EM = small ? 70 : 160;
  const ePos = new Float32Array(EM * 3), eCol = new Float32Array(EM * 3), eSeed = new Float32Array(EM), eVy = new Float32Array(EM);
  for (let i = 0; i < EM; i++) {
    ePos[i * 3] = (Math.random() - 0.5) * 16; ePos[i * 3 + 1] = -5 + Math.random() * 10; ePos[i * 3 + 2] = -5 + Math.random() * 8;
    eSeed[i] = Math.random() * 100; eVy[i] = 0.08 + Math.random() * 0.3;
  }
  const emGeo = new THREE.BufferGeometry();
  emGeo.setAttribute('position', new THREE.BufferAttribute(ePos, 3).setUsage(THREE.DynamicDrawUsage));
  emGeo.setAttribute('color', new THREE.BufferAttribute(eCol, 3).setUsage(THREE.DynamicDrawUsage));
  const embers = new THREE.Points(emGeo, new THREE.PointsMaterial({ size: 0.05, map: glowTex, vertexColors: true, transparent: true, blending: THREE.AdditiveBlending, depthWrite: false, toneMapped: false }));
  embers.frustumCulled = false;
  scene.add(embers);
  function stepEmbers(dt, t) {
    for (let i = 0; i < EM; i++) {
      const i3 = i * 3;
      ePos[i3 + 1] += eVy[i] * dt;
      ePos[i3] += Math.sin(t * 0.6 + eSeed[i]) * 0.0025;
      if (ePos[i3 + 1] > 5.5) ePos[i3 + 1] = -5;
      const fl = 0.35 + 0.65 * Math.max(0, Math.sin(t * 2.2 + eSeed[i] * 3.1));
      eCol[i3] = 1.0 * fl * 0.8; eCol[i3 + 1] = 0.45 * fl * 0.8; eCol[i3 + 2] = 0.12 * fl * 0.8;
    }
    emGeo.attributes.position.needsUpdate = true;
    emGeo.attributes.color.needsUpdate = true;
  }

  /* ---------- Rauch ---------- */
  const smoke = [];
  if (!small) {
    for (let i = 0; i < 12; i++) {
      const s = new THREE.Sprite(new THREE.SpriteMaterial({ map: glowTex, color: 0x9a948c, transparent: true, opacity: 0, depthWrite: false }));
      s.userData = { life: 0, max: 1, vx: 0 };
      rig.add(s); smoke.push(s);
    }
  }
  let smokeAcc = 0, smokeIdx = 0;
  function stepSmoke(dt, arcPos, on) {
    if (!smoke.length) return;
    smokeAcc += dt;
    if (on && smokeAcc > 0.28) {
      smokeAcc = 0;
      const s = smoke[smokeIdx]; smokeIdx = (smokeIdx + 1) % smoke.length;
      s.position.copy(arcPos); s.position.z += 0.15;
      s.userData.life = s.userData.max = 2.6 + Math.random() * 1.4;
      s.userData.vx = (Math.random() - 0.3) * 0.25;
    }
    smoke.forEach((s) => {
      const u = s.userData;
      if (u.life <= 0) { s.material.opacity = 0; return; }
      u.life -= dt;
      const k = 1 - u.life / u.max;
      s.position.y += dt * 0.55; s.position.x += u.vx * dt; s.position.z += dt * 0.12;
      s.scale.setScalar(0.4 + k * 2.6);
      s.material.opacity = Math.sin(Math.PI * k) * 0.075;
    });
  }

  /* ---------- Layout ---------- */
  let W = 1, H = 1;
  const base = new THREE.Vector3(0, 0.35, 12.5);
  function resize() {
    W = Math.max(stage.clientWidth, 1); H = Math.max(stage.clientHeight, 1);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, small ? 1.5 : 2));
    renderer.setSize(W, H, false);
    const aspect = W / H;
    camera.aspect = aspect;
    camera.fov = aspect < 0.75 ? 46 : aspect < 1.2 ? 38 : 30;
    camera.updateProjectionMatrix();
    const visH = 2 * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)) * base.z;
    const visW = visH * aspect;
    if (aspect >= 1.2) rig.position.set(visW * (withForm && W > 1080 ? 0.075 : 0.235), -0.1, 0);
    else if (aspect >= 0.75) rig.position.set(visW * 0.2, 0.2, 0);
    else rig.position.set(visW * 0.2, 1.6, -0.5);
    measureObstacles();
    if (reduced) renderStill();
  }

  /* Texte und Formular, die von den Hinweisen nicht verdeckt werden dürfen */
  let obstacles = [];
  const tagSize = new Map();
  function rectOf(el, text) {
    if (text) {
      const range = document.createRange();
      range.selectNodeContents(el);
      return range.getBoundingClientRect();
    }
    return el.getBoundingClientRect();
  }
  function measureObstacles() {
    const sr = stage.getBoundingClientRect();
    const list = [];
    hero.querySelectorAll('.eyebrow, .hero__l2, .hero__cta > *, .hero__form, .hero__facts > div, .crumbs').forEach((el) => list.push(rectOf(el, false)));
    hero.querySelectorAll('.hero__l1, .hero__lead').forEach((el) => list.push(rectOf(el, true)));
    obstacles = list.filter((r) => r.width > 0).map((r) => ({ l: r.left - sr.left - 12, t: r.top - sr.top - 12, r: r.right - sr.left + 12, b: r.bottom - sr.top + 12 }));
    hero.querySelectorAll('.tag').forEach((t) => { const b = t.querySelector('.tag__body'); tagSize.set(t, b ? [b.offsetWidth, b.offsetHeight] : [0, 0]); });
  }

  /* ---------- Hinweise (HTML) an 3D-Punkte heften ---------- */
  const tagArc = hero.querySelector('[data-tag="arc"]');
  const tagTemper = hero.querySelector('[data-tag="temper"]');
  const tempEl = hero.querySelector('[data-temp]');
  const tmp = new THREE.Vector3();
  function placeTag(el, x, y, z, show) {
    if (!el) return;
    tmp.set(x, y, z);
    rig.localToWorld(tmp);
    tmp.project(camera);
    const sx = (tmp.x * 0.5 + 0.5) * W, sy = (-tmp.y * 0.5 + 0.5) * H;
    let vis = show && tmp.z < 1 && sx > 40 && sx < W - 40 && sy > 110 && sy < H - 120;
    if (vis) {
      const sz = tagSize.get(el) || [0, 0];
      const left = el.classList.contains('tag--arc');
      const box = left ? { l: sx - 94 - sz[0], r: sx + 6 } : { l: sx - 6, r: sx + 94 + sz[0] };
      box.t = sy - sz[1] / 2; box.b = sy + sz[1] / 2;
      if (box.l < 8 || box.r > W - 8) vis = false;
      for (let i = 0; vis && i < obstacles.length; i++) {
        const o = obstacles[i];
        if (box.l < o.r && box.r > o.l && box.t < o.b && box.b > o.t) vis = false;
      }
    }
    el.classList.toggle('is-on', vis);
    if (vis) el.style.transform = `translate3d(${sx.toFixed(1)}px, ${sy.toFixed(1)}px, 0)`;
  }

  /* ---------- Steuerung ---------- */
  const mouse = { x: 0, y: 0, tx: 0, ty: 0 };
  addEventListener('pointermove', (e) => { mouse.tx = e.clientX / innerWidth - 0.5; mouse.ty = e.clientY / innerHeight - 0.5; }, { passive: true });

  const arcPos = new THREE.Vector3();
  const L0 = 1.9, L1 = 0.72;
  let time = T_IN + 2.6;           // Start mitten in der Naht: Anlauffarben sind sofort sichtbar
  let lastTemp = 0;
  const smooth = (x) => x * x * (3 - 2 * x);

  function update(dt, live) {
    if (live) time += dt;
    const tc = time % PERIOD;
    const uT = tc - T_IN;
    const k = Math.min(Math.max(uT / T_WELD, 0), 1);
    const on = uT >= 0 && uT <= T_WELD;
    const tf = tc - (T_IN + T_WELD + T_HOLD);
    const fade = tf > 0 ? 1 - smooth(Math.min(tf / T_FADE, 1)) : 1;

    const flick = 0.72 + 0.28 * Math.sin(time * 61) * Math.sin(time * 23.7) + (Math.random() - 0.5) * 0.3;
    const strike = on && uT < 0.14 ? 2.2 : 1;
    const arcY = Y0 + k * SEAM;
    arcPos.set(on ? Math.sin(time * 7) * 0.014 : 0, arcY, FRONT + 0.035);

    U.uT.value = uT;
    U.uFade.value = fade;
    U.uArcY.value = arcY;
    U.uArcOn.value = on ? Math.max(0.4, flick) : 0;

    // Lichtbogen
    const vis = on ? 1 : 0;
    [core, halo, bloom, streak].forEach((s) => { s.visible = !!vis; s.position.copy(arcPos); });
    core.scale.setScalar((0.42 + flick * 0.22) * strike);
    halo.scale.setScalar((1.9 + flick * 0.8) * strike);
    bloom.scale.setScalar((small ? 5 : 7) * (0.85 + flick * 0.2) * strike);
    streak.scale.set((small ? 4.5 : 7.5) * (0.8 + flick * 0.3) * strike, 0.05, 1);
    arcLight.position.copy(arcPos).add(new THREE.Vector3(0.1, 0, 0.4));
    arcBlue.position.copy(arcPos).add(new THREE.Vector3(0, 0, 0.25));
    arcLight.intensity = on ? (5 + flick * 4) * strike : 0;
    arcBlue.intensity = on ? (1.2 + flick * 1.2) * strike : 0;
    tip.visible = on;

    // Elektrode: fährt heran, brennt ab, zieht sich zurück
    let away = 0;
    if (uT < 0) away = 2.6 * (1 - smooth(Math.min(tc / T_IN, 1)));
    else if (uT > T_WELD) away = 3.2 * smooth(Math.min((uT - T_WELD) / 1.4, 1));
    const len = L0 - (L0 - L1) * k;
    rod.scale.y = len;
    holder.position.y = len;
    elec.position.copy(arcPos).addScaledVector(elecDir, 0.02 + away);
    elec.visible = fade > 0.02 || uT < 0;

    // Partikel
    if (live) {
      if (on) {
        emitAcc += dt * (small ? 150 : 300) * (strike > 1 ? 3 : 1);
        const n = Math.floor(emitAcc); emitAcc -= n;
        if (n) emit(n, arcPos);
        if (Math.random() < dt * 1.4) emit(small ? 10 : 24, arcPos);
      }
      stepSparks(dt);
      stepEmbers(dt, time);
      stepSmoke(dt, arcPos, on);
    }

    // Kamera
    mouse.x += (mouse.tx - mouse.x) * 0.04;
    mouse.y += (mouse.ty - mouse.y) * 0.04;
    const sp = Math.min(Math.max(-stage.getBoundingClientRect().top / Math.max(H, 1), 0), 1);
    camera.position.set(base.x + mouse.x * 1.3, base.y - mouse.y * 0.7 + sp * 1.2, base.z - sp * 2.6);
    camera.lookAt(0, 0.1 + sp * 0.6, 0);
    rig.rotation.y = -0.52 + Math.sin(time * 0.17) * 0.05 + mouse.x * 0.08 + sp * 0.3;
    canvas.style.opacity = (1 - sp * 0.75).toFixed(3);

    // HTML-Hinweise
    camera.updateMatrixWorld();
    rig.updateMatrixWorld();
    placeTag(tagArc, -0.2, arcY, FRONT, on && uT > 0.5);
    const ty = arcY - 1.5;
    placeTag(tagTemper, 0.3, ty, FRONT, fade > 0.9 && uT > 2.4 && ty > Y0 + 0.3);

    if (tempEl && time - lastTemp > 0.16) {
      lastTemp = time;
      tempEl.textContent = on ? '≈ ' + (1470 + Math.round(Math.random() * 70)).toLocaleString('de-DE') : '—';
    }
  }

  function renderStill() {
    time = T_IN + T_WELD * 0.66;
    update(0, false);
    renderer.render(scene, camera);
  }

  /* ---------- Schleife (pausiert außerhalb des Sichtfelds) ---------- */
  const clock = new THREE.Clock();
  let inView = true, running = false, frameNo = 0;
  function tick() {
    if (!canvas.isConnected) { dispose(); return; }
    if (++frameNo % 45 === 0) measureObstacles();
    const dt = Math.min(clock.getDelta(), 0.05);
    update(dt, true);
    renderer.render(scene, camera);
  }
  function setRunning(v) {
    if (reduced) return;
    if (v === running) return;
    running = v;
    if (v) clock.getDelta();
    renderer.setAnimationLoop(v ? tick : null);
  }

  function dispose() {
    renderer.setAnimationLoop(null);
    removeEventListener('resize', resize);
    if (ro) ro.disconnect();
    renderer.dispose();
  }

  resize();
  addEventListener('resize', resize);
  const ro = 'ResizeObserver' in window ? new ResizeObserver(() => resize()) : null;
  if (ro) ro.observe(stage);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(measureObstacles);
  canvas.addEventListener('webglcontextlost', (e) => { e.preventDefault(); setRunning(false); hero.classList.add('no-webgl'); });

  if (reduced) {
    renderStill();
  } else {
    // ein paar Funken vorweg, damit das erste Bild schon „lebt“
    for (let i = 0; i < 40; i++) { update(1 / 60, true); }
    renderer.render(scene, camera);
    new IntersectionObserver((en) => { inView = en[0].isIntersecting; setRunning(inView && !document.hidden); }).observe(hero);
    document.addEventListener('visibilitychange', () => setRunning(inView && !document.hidden));
    setRunning(true);
  }
  requestAnimationFrame(ready);
}
