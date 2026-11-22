/**
 * Instagram-style story viewer for the home circles ([data-story] + #lc-stories-data).
 *
 * Tap right = next photo, tap left = previous, hold = pause, swipe sideways = next/previous story,
 * swipe down (or a quick flick) = close. Keys: → ← Esc Space. Seen stories get a grey ring.
 */
(() => {
	const SEEN_KEY = 'lc-stories-seen';
	const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
	const store = {
		get() { try { return JSON.parse(localStorage.getItem(SEEN_KEY) || '{}'); } catch (e) { return {}; } },
		set(v) { try { localStorage.setItem(SEEN_KEY, JSON.stringify(v)); } catch (e) { /* private mode */ } },
	};
	const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
	const EASE_OUT = 'cubic-bezier(.23,1,.32,1)';
	const EASE_DRAWER = 'cubic-bezier(.32,.72,0,1)';

	let data = [];
	let duration = 5000;
	let ui = null;
	let g = 0; // story (group)
	let s = 0; // slide
	let fill = null; // running progress animation
	let paused = false;
	let origin = null; // circle the viewer opened from
	let lastFocus = null;

	/* ------------------------------------------------------------ seen rings */
	function markRings() {
		const seen = store.get();
		document.querySelectorAll('[data-story]').forEach((el) => el.classList.toggle('is-seen', !!seen[el.dataset.key]));
	}

	/* ------------------------------------------------------------------ DOM */
	function build() {
		const el = document.createElement('div');
		el.className = 'sv';
		el.setAttribute('role', 'dialog');
		el.setAttribute('aria-modal', 'true');
		el.setAttribute('aria-label', 'استوری');
		el.hidden = true;
		el.innerHTML = `
			<div class="sv__scrim" data-sv-close></div>
			<button class="sv__nav sv__nav--prev" type="button" data-sv-group="-1" aria-label="استوری قبلی"><svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></button>
			<div class="sv__card" dir="ltr">
				<div class="sv__media"><img class="sv__bg" alt="" aria-hidden="true"><img class="sv__img" alt=""></div>
				<div class="sv__shade" aria-hidden="true"></div>
				<div class="sv__bars" aria-hidden="true"></div>
				<header class="sv__head" dir="rtl">
					<img class="sv__avatar" alt="">
					<b class="sv__title"></b>
					<button class="sv__btn sv__pause" type="button" data-sv-pause aria-label="توقف"><svg viewBox="0 0 24 24"><path class="i-pause" d="M9 6v12M15 6v12"/><path class="i-play" d="M8 5.5v13l10.5-6.5z"/></svg></button>
					<button class="sv__btn" type="button" data-sv-close aria-label="بستن"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
				</header>
				<div class="sv__tap sv__tap--prev" data-sv-tap="-1"></div>
				<div class="sv__tap sv__tap--next" data-sv-tap="1"></div>
				<a class="sv__cta" dir="rtl" hidden>
					<span class="sv__cta-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 8h14l-1.2 12H6.2L5 8Z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/></svg></span>
					<span class="sv__cta-text"><b>مشاهده‌ی محصول</b><small></small></span>
				</a>
				<p class="sv__sr" aria-live="polite"></p>
			</div>
			<button class="sv__nav sv__nav--next" type="button" data-sv-group="1" aria-label="استوری بعدی"><svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></button>`;
		document.body.appendChild(el);
		const $ = (sel) => el.querySelector(sel);
		ui = { el, card: $('.sv__card'), img: $('.sv__img'), bg: $('.sv__bg'), bars: $('.sv__bars'), avatar: $('.sv__avatar'), title: $('.sv__title'), cta: $('.sv__cta'), ctaSmall: $('.sv__cta small'), sr: $('.sv__sr') };
		wire();
	}

	/* ------------------------------------------------------------- playback */
	function renderGroup() {
		const story = data[g];
		ui.avatar.src = story.cover || story.slides[0].src;
		ui.title.textContent = story.title;
		ui.bars.innerHTML = story.slides.map(() => '<span><i></i></span>').join('');
		const seen = store.get();
		seen[story.key] = 1;
		store.set(seen);
		ui.el.querySelector('.sv__nav--prev').disabled = g === 0;
	}

	function renderSlide() {
		const story = data[g];
		const slide = story.slides[s];
		[...ui.bars.children].forEach((bar, i) => {
			const f = bar.firstElementChild;
			f.getAnimations().forEach((a) => a.cancel());
			f.style.transform = i < s ? 'scaleX(1)' : 'scaleX(0)';
		});
		if (fill) fill.cancel();
		fill = null;
		ui.card.classList.add('is-loading');
		ui.bg.src = slide.bg || slide.src;
		ui.img.alt = story.title;
		const token = {};
		renderSlide.token = token;
		const start = () => {
			if (renderSlide.token !== token || ui.el.hidden) return;
			ui.card.classList.remove('is-loading');
			const f = ui.bars.children[s].firstElementChild;
			fill = f.animate([{ transform: 'scaleX(0)' }, { transform: 'scaleX(1)' }], { duration, easing: 'linear', fill: 'forwards' });
			fill.onfinish = () => { if (renderSlide.token === token) next(); };
			if (paused || document.hidden) fill.pause();
		};
		ui.img.src = slide.src;
		if (ui.img.decode) ui.img.decode().then(start, start);
		else if (ui.img.complete) start();
		else ui.img.onload = start;

		if (slide.product) {
			ui.cta.hidden = false;
			ui.cta.href = slide.product.url;
			ui.ctaSmall.textContent = slide.product.name + ' · ' + slide.product.price;
		} else {
			ui.cta.hidden = true;
			ui.cta.removeAttribute('href');
		}
		ui.sr.textContent = `${story.title}، عکس ${s + 1} از ${story.slides.length}`;
		// warm the next photo
		const up = story.slides[s + 1] || (data[g + 1] && data[g + 1].slides[0]);
		if (up) new Image().src = up.src;
	}

	function next() {
		if (s < data[g].slides.length - 1) { s++; renderSlide(); }
		else if (g < data.length - 1) switchGroup(1);
		else close();
	}
	function prev() {
		if (s > 0) { s--; renderSlide(); }
		else if (g > 0) switchGroup(-1, true);
		else renderSlide(); // restart the first photo
	}

	/** Slide the card to the next/previous story. */
	function switchGroup(dir, toLast = false) {
		const to = g + dir;
		if (to < 0 || to >= data.length) { if (dir > 0) close(); return; }
		const swap = () => {
			g = to;
			s = toLast ? data[g].slides.length - 1 : 0;
			renderGroup();
			renderSlide();
		};
		if (reduced.matches) { swap(); return; }
		const out = ui.card.animate([{ transform: 'none', opacity: 1 }, { transform: `translateX(${-dir * 6}%) scale(.96)`, opacity: 0, filter: 'blur(2px)' }], { duration: 160, easing: EASE_OUT });
		out.onfinish = () => {
			swap();
			ui.card.animate([{ transform: `translateX(${dir * 8}%) scale(.96)`, opacity: 0, filter: 'blur(2px)' }, { transform: 'none', opacity: 1, filter: 'blur(0)' }], { duration: 260, easing: EASE_OUT });
		};
	}

	function setPaused(v) {
		paused = v;
		ui.el.classList.toggle('is-paused', v);
		ui.el.querySelector('[data-sv-pause]').setAttribute('aria-label', v ? 'ادامه' : 'توقف');
		if (!fill) return;
		if (v || document.hidden) fill.pause();
		else fill.play();
	}

	/* --------------------------------------------------------- open / close */
	function open(index, from) {
		const el = document.getElementById('lc-stories-data');
		if (!el) return;
		try { data = JSON.parse(el.textContent); } catch (e) { return; }
		duration = (parseFloat(el.dataset.seconds) || 5) * 1000;
		if (!data[index]) return;
		if (!ui) build();
		origin = from || null;
		lastFocus = document.activeElement;
		g = index;
		s = 0;
		paused = false;
		ui.el.classList.remove('is-paused');
		ui.el.hidden = false;
		document.documentElement.classList.add('sv-open');
		renderGroup();
		renderSlide();
		ui.el.querySelector('[data-sv-close].sv__btn').focus({ preventScroll: true });
		if (!reduced.matches) {
			ui.el.querySelector('.sv__scrim').animate([{ opacity: 0 }, { opacity: 1 }], { duration: 300, easing: 'ease' });
			ui.card.animate([fromCircle(), { transform: 'none', opacity: 1, borderRadius: getComputedStyle(ui.card).borderRadius }], { duration: 420, easing: EASE_DRAWER });
		}
	}

	/** Transform that puts the card on top of the circle it came from (origin-aware open/close). */
	function fromCircle() {
		const ring = origin && origin.isConnected && origin.querySelector('.story__ring');
		if (!ring) return { transform: 'scale(.94)', opacity: 0 };
		const a = ring.getBoundingClientRect();
		const b = ui.card.getBoundingClientRect();
		const k = a.width / b.width;
		const dx = a.left + a.width / 2 - (b.left + b.width / 2);
		const dy = a.top + a.height / 2 - (b.top + b.height / 2);
		return { transform: `translate(${dx}px, ${dy}px) scale(${k})`, opacity: 0.4, borderRadius: '50%' };
	}

	function close() {
		if (!ui || ui.el.hidden) return;
		if (fill) fill.cancel();
		fill = null;
		renderSlide.token = null;
		const done = () => {
			ui.el.hidden = true;
			ui.card.style.transform = '';
			document.documentElement.classList.remove('sv-open');
			markRings();
			if (lastFocus && lastFocus.isConnected) lastFocus.focus({ preventScroll: true });
		};
		if (reduced.matches) { done(); return; }
		const scrim = ui.el.querySelector('.sv__scrim').animate([{ opacity: 1 }, { opacity: 0 }], { duration: 240, easing: 'ease', fill: 'forwards' });
		const from = getComputedStyle(ui.card).transform;
		const a = ui.card.animate([{ transform: from === 'none' ? 'none' : from, opacity: 1 }, fromCircle()], { duration: 260, easing: EASE_OUT, fill: 'forwards' });
		a.onfinish = () => {
			done();
			a.cancel();
			scrim.cancel();
		};
	}

	/* -------------------------------------------------------------- gestures */
	function wire() {
		const card = ui.card;
		let p = null; // active pointer gesture
		let holdT = 0;

		card.addEventListener('pointerdown', (e) => {
			if (p || e.button !== 0 || e.target.closest('a, button')) return;
			p = { id: e.pointerId, x: e.clientX, y: e.clientY, t: performance.now(), axis: null, held: false, zone: e.target.closest('[data-sv-tap]') };
			holdT = setTimeout(() => { if (p && !p.axis) { p.held = true; setPaused(true); } }, 220);
		});
		card.addEventListener('pointermove', (e) => {
			if (!p || e.pointerId !== p.id) return;
			const dx = e.clientX - p.x;
			const dy = e.clientY - p.y;
			if (!p.axis) {
				if (Math.hypot(dx, dy) < 10) return;
				p.axis = Math.abs(dy) > Math.abs(dx) ? 'y' : 'x';
				clearTimeout(holdT);
				try { card.setPointerCapture(e.pointerId); } catch (err) { /* released */ }
				if (fill) fill.pause();
			}
			if (p.axis === 'y') {
				const d = Math.max(0, dy) + Math.min(0, dy) * 0.15; // resist dragging up
				card.style.transform = `translateY(${d}px) scale(${1 - Math.max(0, d) / 2400})`;
			} else {
				const atEdge = (dx > 0 && g === 0) || (dx < 0 && g === data.length - 1);
				card.style.transform = `translateX(${dx * (atEdge ? 0.2 : 0.5)}px)`;
			}
		});
		const end = (e) => {
			if (!p || e.pointerId !== p.id) return;
			clearTimeout(holdT);
			const q = p;
			p = null;
			const dx = e.clientX - q.x;
			const dy = e.clientY - q.y;
			const v = (q.axis === 'y' ? Math.abs(dy) : Math.abs(dx)) / Math.max(1, performance.now() - q.t);
			if (q.axis === 'y') {
				if (dy > 120 || (dy > 30 && v > 0.5)) { close(); return; }
			} else if (q.axis === 'x') {
				if (Math.abs(dx) > 70 || (Math.abs(dx) > 25 && v > 0.45)) {
					card.style.transform = '';
					// swipe left (finger moves left) = next story, like Instagram
					switchGroup(dx < 0 ? 1 : -1);
					return;
				}
			}
			if (q.axis) {
				card.animate([{ transform: card.style.transform || 'none' }, { transform: 'none' }], { duration: 300, easing: EASE_OUT });
				card.style.transform = '';
				if (!paused && fill) fill.play();
				return;
			}
			if (q.held) { setPaused(false); return; }
			if (q.zone) (+q.zone.dataset.svTap > 0 ? next : prev)();
		};
		card.addEventListener('pointerup', end);
		card.addEventListener('pointercancel', end);
		card.addEventListener('contextmenu', (e) => e.preventDefault());

		ui.el.addEventListener('click', (e) => {
			if (e.target.closest('[data-sv-close]')) { close(); return; }
			if (e.target.closest('[data-sv-pause]')) { setPaused(!paused); return; }
			const grp = e.target.closest('[data-sv-group]');
			if (grp) { switchGroup(+grp.dataset.svGroup); return; }
			if (e.target.closest('.sv__cta')) {
				// leave instantly; the router takes over the link
				if (fill) fill.cancel();
				ui.el.hidden = true;
				document.documentElement.classList.remove('sv-open');
				markRings();
			}
		});

		document.addEventListener('keydown', (e) => {
			if (ui.el.hidden) return;
			if (e.key === 'Escape') { e.preventDefault(); close(); }
			else if (e.key === 'ArrowRight') { e.preventDefault(); next(); }
			else if (e.key === 'ArrowLeft') { e.preventDefault(); prev(); }
			else if (e.key === ' ' && !e.target.closest('a, button')) { e.preventDefault(); setPaused(!paused); }
			else if (e.key === 'Tab') {
				// keep focus inside the viewer
				const f = [...ui.el.querySelectorAll('button:not([disabled]), a[href]')].filter((x) => x.offsetParent);
				if (!f.length) return;
				const i = f.indexOf(document.activeElement);
				if (e.shiftKey && i <= 0) { e.preventDefault(); f[f.length - 1].focus(); }
				else if (!e.shiftKey && i === f.length - 1) { e.preventDefault(); f[0].focus(); }
			}
		});
		document.addEventListener('visibilitychange', () => {
			if (!fill || ui.el.hidden) return;
			if (document.hidden) fill.pause();
			else if (!paused) fill.play();
		});
	}

	/* ---------------------------------------------------------------- boot */
	document.addEventListener('click', (e) => {
		const c = e.target.closest('[data-story]');
		if (!c) return;
		e.preventDefault();
		open(+c.dataset.story, c);
	});
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', markRings);
	else markRings();
	document.addEventListener('lc:navigated', () => {
		if (ui && !ui.el.hidden) close();
		markRings();
	});
})();
