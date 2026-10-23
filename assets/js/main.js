/* Traverse Asia – menu, header, map hover, scroll reveal, count-up, form redirect. */
(function () {
  var root = document.documentElement;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* menu (phone) */
  var btn = document.querySelector('.menu-btn'), nav = document.getElementById('c-nav');
  if (btn && nav) btn.addEventListener('click', function () {
    var open = nav.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    btn.textContent = open ? '閉じる' : 'メニュー';
  });

  /* links to other sites (in articles, menus …) open in a new tab */
  document.querySelectorAll('a[href^="http"]').forEach(function (a) {
    if (a.host && a.host !== location.host && !a.hasAttribute('target')) {
      a.target = '_blank';
      a.rel = (a.rel ? a.rel + ' ' : '') + 'noopener';
    }
  });

  /* Contact Form 7: go to the thanks page after a successful send */
  var form = document.querySelector('.ta-form-wrap');
  if (form) document.addEventListener('wpcf7mailsent', function () {
    window.location.href = form.getAttribute('data-thanks');
  });

  /* map hover link: list <-> map */
  var fig = document.querySelector('.map-fig');
  function setActive(city) {
    if (!fig) return;
    if (city) fig.setAttribute('data-active', city); else fig.removeAttribute('data-active');
    document.querySelectorAll('[data-city]').forEach(function (el) {
      el.classList.toggle('on', !!city && el.getAttribute('data-city') === city);
    });
  }
  document.querySelectorAll('.ct[data-city], .map-fig .lab[data-city], .map-fig .dot[data-city]').forEach(function (el) {
    el.addEventListener('mouseenter', function () { setActive(el.getAttribute('data-city')); });
    el.addEventListener('mouseleave', function () { setActive(null); });
  });

  /* header: translucent after scrolling; hide on scroll down, show on scroll up */
  var header = document.querySelector('.site-header'), lastY = window.scrollY, ticking = false;
  function onScroll() {
    var y = window.scrollY;
    header.classList.toggle('scrolled', y > 24);
    if (!reduce && !(nav && nav.classList.contains('open'))) header.classList.toggle('hidden', y > 240 && y > lastY);
    lastY = y; ticking = false;
  }
  if (header) {
    window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
    onScroll();
  }

  if (!root.classList.contains('m')) return;
  window.taMotion = 1;

  function delay(el, ms) { if (el) el.style.transitionDelay = ms + 'ms'; }
  function mark(sel, cls, base, step, scope) {
    if (!scope) return;
    Array.prototype.forEach.call(scope.querySelectorAll(sel), function (el, i) {
      el.classList.add(cls); delay(el, base + Math.min(i, 6) * step);
    });
  }
  var sections = Array.prototype.slice.call(document.querySelectorAll('[data-sec]'));
  var fv = document.querySelector('[data-sec="fv"]');

  /* first view: plays on load */
  if (fv) {
    mark('.fv-a', 'a', 0, 0, fv);
    mark('.fv-l', 'a', 80, 80, fv);
    mark('.fv-b', 'a', 360, 0, fv);
    mark('.fv-c', 'a', 440, 0, fv);
    delay(fv.querySelector('.fv-img'), 120);
    mark('.fv-m', 'ax', 1000, 80, fv);
  }

  /* other sections: heading first, then their items in a short stagger */
  var ITEMS = '.st, .en-card, .row, .fl, .wy, .ct, .card';
  sections.forEach(function (s) {
    if (s === fv) return;
    mark('h2', 'a', 0, 0, s);
    var panel = s.querySelector('.why-panel'); if (panel) panel.classList.add('af');
    var n = 0;
    Array.prototype.forEach.call(s.querySelectorAll(ITEMS), function (el) {
      if (el.parentElement.closest(ITEMS) || el.closest('.map-fig')) return;
      el.classList.add('a'); delay(el, 100 + Math.min(n, 6) * 80);
      var a = el.querySelector('.arw'); if (a) delay(a, 100 + Math.min(n, 6) * 80 + 650);
      var rule = el.querySelector('.fl-rule'); if (rule) delay(rule, 100 + Math.min(n, 6) * 80 + 250);
      n++;
    });
  });

  /* map: arcs draw from Japan city by city */
  var mp = fig && fig.closest('[data-sec]');
  if (mp) {
    delay(mp.querySelector('.jp-dot'), 100);
    ['bj', 'tp', 'hk', 'mnl', 'hcm', 'bkk', 'sg'].forEach(function (c, i) {
      var t = 400 + i * 120;
      mp.querySelectorAll('.arc[data-city="' + c + '"]').forEach(function (el) { el.style.transitionDelay = t + 'ms, 0ms'; });
      mp.querySelectorAll('.dot[data-city="' + c + '"], .lab[data-city="' + c + '"]').forEach(function (el) { delay(el, t + 800); });
    });
  }

  /* numbers count up once (only when the markup carries data-to) */
  function countUp(s) {
    var nums = s.querySelectorAll('.num[data-to]'), t0 = performance.now();
    if (!nums.length) return;
    function frame(t) {
      var k = Math.min(1, (t - t0) / 1200), e = 1 - Math.pow(1 - k, 3);
      nums.forEach(function (n) { n.textContent = Math.round(+n.getAttribute('data-to') * e).toLocaleString('en-US'); });
      if (k < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }
  document.querySelectorAll('.num[data-to]').forEach(function (n) { n.textContent = '0'; });

  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (!e.isIntersecting) return;
      e.target.classList.add('in');
      countUp(e.target);
      io.unobserve(e.target);
    });
  }, { threshold: 0, rootMargin: '0px 0px -15% 0px' });

  void root.offsetWidth; root.classList.remove('pre');
  var played = false;
  function playFv() {
    if (played || !fv) return; played = true;
    requestAnimationFrame(function () { requestAnimationFrame(function () { fv.classList.add('in'); }); });
  }
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(playFv);
  setTimeout(playFv, 800);
  sections.forEach(function (s) { if (s !== fv) io.observe(s); });
})();
