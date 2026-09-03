/* ==========================================================================
   Candid Career Secondary School — main.js
   Vanilla JS, no dependencies. Everything is progressive enhancement: if this
   file fails to load the page still reads and the form still submits.

     1. Mobile nav (hamburger drawer)
     2. Sticky header shadow
     3. Active nav link on scroll
     4. Smooth scroll fallback for older browsers
     5. Scroll-reveal via IntersectionObserver
     6. Animated student counter
     7. Enquiry form validation
     8. Facebook page plugin width
     9. Footer year
   ========================================================================== */
(function () {
  'use strict';

  /* The stylesheet hides .reveal elements; drop the no-js guard now that we
     know JS is running, so those elements can be revealed by the observer. */
  document.documentElement.classList.remove('no-js');

  var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- 1. Mobile navigation --------------------------------------- */
  var header = document.getElementById('siteHeader');
  var navToggle = document.getElementById('navToggle');
  var primaryNav = document.getElementById('primaryNav');
  var navLinks = primaryNav ? Array.prototype.slice.call(primaryNav.querySelectorAll('a[href^="#"]')) : [];

  function isMobileNav() {
    return window.matchMedia('(max-width: 768px)').matches;
  }

  function setNav(open) {
    if (!navToggle || !primaryNav) return;
    navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    navToggle.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
    primaryNav.classList.toggle('is-open', open);
    document.body.classList.toggle('nav-open', open);
  }

  function closeNav() { setNav(false); }

  if (navToggle) {
    navToggle.addEventListener('click', function () {
      setNav(navToggle.getAttribute('aria-expanded') !== 'true');
    });
  }

  /* Close the drawer after tapping a link, and on Escape. */
  navLinks.forEach(function (link) {
    link.addEventListener('click', function () {
      if (isMobileNav()) closeNav();
    });
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && primaryNav && primaryNav.classList.contains('is-open')) {
      closeNav();
      navToggle.focus();
    }
  });

  /* Clicking outside the open drawer closes it. */
  document.addEventListener('click', function (e) {
    if (!primaryNav || !primaryNav.classList.contains('is-open')) return;
    if (primaryNav.contains(e.target) || (navToggle && navToggle.contains(e.target))) return;
    closeNav();
  });

  /* Resizing up to desktop should never leave the drawer state behind. */
  window.addEventListener('resize', function () {
    if (!isMobileNav()) closeNav();
  });

  /* ---------- 2. Sticky header shadow ------------------------------------ */
  function onScrollHeader() {
    if (!header) return;
    header.classList.toggle('is-scrolled', window.scrollY > 8);
  }
  onScrollHeader();

  /* ---------- 3. Active nav link on scroll ------------------------------- */
  var sections = navLinks
    .map(function (link) { return document.querySelector(link.getAttribute('href')); })
    .filter(Boolean);

  function onScrollActive() {
    if (!sections.length) return;
    var probe = window.scrollY + (header ? header.offsetHeight : 0) + 24;
    var current = sections[0];

    sections.forEach(function (section) {
      if (section.offsetTop <= probe) current = section;
    });

    /* At the very bottom of the page, highlight the last section instead. */
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 4) {
      current = sections[sections.length - 1];
    }

    navLinks.forEach(function (link) {
      var active = link.getAttribute('href') === '#' + current.id;
      link.classList.toggle('is-active', active);
      if (active) {
        link.setAttribute('aria-current', 'true');
      } else {
        link.removeAttribute('aria-current');
      }
    });
  }

  /* One throttled scroll handler for both header and nav highlighting. */
  var scrollQueued = false;
  window.addEventListener('scroll', function () {
    if (scrollQueued) return;
    scrollQueued = true;
    window.requestAnimationFrame(function () {
      onScrollHeader();
      onScrollActive();
      scrollQueued = false;
    });
  }, { passive: true });
  onScrollActive();

  /* ---------- 4. Smooth scroll fallback ---------------------------------- */
  /* Modern browsers handle this with `scroll-behavior: smooth` in the CSS.
     This covers older ones, and keeps the sticky header from covering headings. */
  var supportsScrollBehavior = 'scrollBehavior' in document.documentElement.style;

  document.addEventListener('click', function (e) {
    var link = e.target.closest ? e.target.closest('a[href^="#"]') : null;
    if (!link) return;

    var hash = link.getAttribute('href');
    if (!hash || hash === '#') return;

    var target = document.querySelector(hash);
    if (!target) return;

    if (!supportsScrollBehavior) {
      e.preventDefault();
      var offset = (header ? header.offsetHeight : 0) + 12;
      window.scrollTo(0, Math.max(target.getBoundingClientRect().top + window.scrollY - offset, 0));
    }

    /* Anchors do not move keyboard focus by themselves; do it explicitly so
       screen-reader and keyboard users land in the section they picked. */
    window.setTimeout(function () {
      if (!target.hasAttribute('tabindex')) {
        target.setAttribute('tabindex', '-1');
      }
      target.focus({ preventScroll: true });
    }, supportsScrollBehavior ? 480 : 0);
  });

  /* ---------- 5. Scroll reveal ------------------------------------------- */
  var revealTargets = Array.prototype.slice.call(document.querySelectorAll('.reveal'));

  function revealAll() {
    revealTargets.forEach(function (el) { el.classList.add('is-visible'); });
  }

  if (prefersReducedMotion || !('IntersectionObserver' in window)) {
    revealAll();
  } else {
    var revealObserver = new IntersectionObserver(function (entries, observer) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target); /* reveal once, then stop watching */
      });
    }, { rootMargin: '0px 0px -12% 0px', threshold: 0.12 });

    revealTargets.forEach(function (el) { revealObserver.observe(el); });
  }

  /* ---------- 6. Animated student counter -------------------------------- */
  var counters = Array.prototype.slice.call(document.querySelectorAll('[data-count-to]'));

  function runCounter(el) {
    var end = parseInt(el.getAttribute('data-count-to'), 10);
    var suffix = el.getAttribute('data-count-suffix') || '';
    if (isNaN(end)) return;

    if (prefersReducedMotion) {
      el.textContent = end + suffix;
      return;
    }

    var duration = 1200;
    var started = null;

    function step(now) {
      if (started === null) started = now;
      var progress = Math.min((now - started) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3); /* ease-out cubic */
      el.textContent = Math.round(end * eased) + (progress === 1 ? suffix : '');
      if (progress < 1) window.requestAnimationFrame(step);
    }
    window.requestAnimationFrame(step);
  }

  if (counters.length) {
    if (!('IntersectionObserver' in window)) {
      counters.forEach(runCounter);
    } else {
      var counterObserver = new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          runCounter(entry.target);
          observer.unobserve(entry.target);
        });
      }, { threshold: 0.6 });
      counters.forEach(function (el) { counterObserver.observe(el); });
    }
  }

  /* ---------- 7. Enquiry form validation --------------------------------- */
  /* Client-side only. See the wiring comment above the <form> in index.html
     for how to connect Formspree, Netlify Forms, or your own endpoint. */
  var form = document.getElementById('enquiryForm');

  if (form) {
    var statusEl = document.getElementById('formStatus');

    var RULES = {
      name: {
        test: function (v) { return v.length >= 2; },
        message: 'Please enter your full name.'
      },
      phone: {
        /* Deliberately permissive: allows +977, spaces, dashes and brackets,
           and only requires at least 7 digits overall. */
        test: function (v) { return (v.replace(/\D/g, '')).length >= 7; },
        message: 'Please enter a phone number we can reach you on.'
      },
      email: {
        optional: true,
        test: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v); },
        message: 'Please check the email address, or leave it blank.'
      },
      grade: {
        test: function (v) { return v !== ''; },
        message: 'Please choose the grade you are interested in.'
      },
      message: {
        test: function (v) { return v.length >= 10; },
        message: 'Please tell us a little more (at least 10 characters).'
      }
    };

    function fieldWrap(input) { return input.closest('.field'); }

    function showError(input, message) {
      var wrap = fieldWrap(input);
      var errorEl = document.getElementById('e-' + input.id.replace('f-', ''));
      if (wrap) wrap.classList.add('has-error');
      input.setAttribute('aria-invalid', 'true');
      if (errorEl) {
        errorEl.textContent = message;
        errorEl.hidden = false;
      }
    }

    function clearError(input) {
      var wrap = fieldWrap(input);
      var errorEl = document.getElementById('e-' + input.id.replace('f-', ''));
      if (wrap) wrap.classList.remove('has-error');
      input.removeAttribute('aria-invalid');
      if (errorEl) {
        errorEl.textContent = '';
        errorEl.hidden = true;
      }
    }

    function validateField(input) {
      var rule = RULES[input.name];
      if (!rule) return true;

      var value = (input.value || '').trim();

      if (!value) {
        if (rule.optional) { clearError(input); return true; }
        showError(input, rule.message);
        return false;
      }
      if (!rule.test(value)) {
        showError(input, rule.message);
        return false;
      }
      clearError(input);
      return true;
    }

    var fields = Array.prototype.slice.call(form.querySelectorAll('input, select, textarea'))
      .filter(function (el) { return RULES[el.name]; });

    /* Validate on blur, and clear the error as soon as the user fixes it. */
    fields.forEach(function (input) {
      input.addEventListener('blur', function () { validateField(input); });
      input.addEventListener('input', function () {
        if (fieldWrap(input) && fieldWrap(input).classList.contains('has-error')) {
          validateField(input);
        }
      });
      if (input.tagName === 'SELECT') {
        input.addEventListener('change', function () { validateField(input); });
      }
    });

    function setStatus(message, kind) {
      if (!statusEl) return;
      statusEl.textContent = message;
      statusEl.className = 'form-status' + (kind ? ' is-' + kind : '');
    }

    form.addEventListener('submit', function (e) {
      var firstInvalid = null;
      var valid = true;

      fields.forEach(function (input) {
        if (!validateField(input)) {
          valid = false;
          if (!firstInvalid) firstInvalid = input;
        }
      });

      if (!valid) {
        e.preventDefault();
        setStatus('Please correct the highlighted fields and try again.', 'error');
        if (firstInvalid) firstInvalid.focus();
        return;
      }

      /* A real backend is wired up (Formspree / Netlify / your own script):
         let the browser submit normally. */
      var action = form.getAttribute('action');
      if (action && action.trim() !== '' && action.trim() !== '#') {
        setStatus('Sending your enquiry…');
        return;
      }

      /* No backend yet — fall back to opening the visitor's email client. */
      e.preventDefault();

      var to = (form.getAttribute('data-mailto') || '').trim();
      if (!to || to.indexOf('[PLACEHOLDER') === 0) {
        /* [PLACEHOLDER — EMAIL] Set data-mailto on the <form> (or add a real
           action) so enquiries actually reach the school. */
        setStatus('This form is not connected yet. Please call or email the school office directly.', 'error');
        return;
      }

      var get = function (name) {
        var el = form.elements[name];
        return el ? (el.value || '').trim() : '';
      };

      var body = [
        'Name: ' + get('name'),
        'Phone: ' + get('phone'),
        'Email: ' + (get('email') || '—'),
        'Grade interested in: ' + get('grade'),
        '',
        'Message:',
        get('message')
      ].join('\r\n');

      window.location.href = 'mailto:' + to +
        '?subject=' + encodeURIComponent('Admission enquiry — ' + get('name')) +
        '&body=' + encodeURIComponent(body);

      setStatus('Opening your email app so you can send the enquiry…', 'success');
    });
  }

  /* ---------- 8. Facebook page plugin width ------------------------------ */
  /* The plugin takes a fixed pixel width, so match it to the container and
     re-render when the viewport changes size. Max 500px is Facebook's limit. */
  var fbPage = document.getElementById('fbPage');

  function sizeFacebookPlugin() {
    if (!fbPage || !fbPage.parentElement) return;
    var available = fbPage.parentElement.clientWidth;
    var width = Math.max(180, Math.min(500, Math.floor(available)));
    if (fbPage.getAttribute('data-width') === String(width)) return false;
    fbPage.setAttribute('data-width', String(width));
    return true;
  }

  sizeFacebookPlugin();

  var fbResizeTimer;
  window.addEventListener('resize', function () {
    window.clearTimeout(fbResizeTimer);
    fbResizeTimer = window.setTimeout(function () {
      if (sizeFacebookPlugin() && window.FB && window.FB.XFBML) {
        window.FB.XFBML.parse(fbPage.parentElement);
      }
    }, 250);
  });

  /* ---------- 9. Footer year --------------------------------------------- */
  var yearEl = document.getElementById('year');
  if (yearEl) yearEl.textContent = String(new Date().getFullYear());
})();
