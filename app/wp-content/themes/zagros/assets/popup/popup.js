/**
 * Promo popup (window.LC_POPUPS from inc/popups.php).
 * Picks the first popup this visitor qualifies for (first visit / returning, how often), waits for
 * its delay (after the home intro), and never interrupts an open story, cart or menu sheet.
 */
(() => {
	const list = window.LC_POPUPS || [];
	if (!list.length) return;

	const mk = (store) => ({
		get: (k) => { try { return store.getItem(k); } catch (e) { return null; } },
		set: (k, v) => { try { store.setItem(k, v); } catch (e) { /* private mode */ } },
	});
	const ls = mk(window.localStorage);
	const ss = mk(window.sessionStorage);
	const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
	const phone = window.matchMedia('(max-width: 640px)');
	const html = document.documentElement;
	const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

	// First visit = no earlier visit on this device, fixed for the whole browsing session.
	let isNew = ss.get('lc-new');
	if (isNew === null) {
		isNew = ls.get('lc-visited') ? '0' : '1';
		ss.set('lc-new', isNew);
		ls.set('lc-visited', String(Date.now()));
	}
	isNew = isNew === '1';

	const key = (p) => 'lc-pop-' + p.id;
	const eligible = (p) => {
		if (p.audience === 'new' && !isNew) return false;
		if (p.audience === 'returning' && isNew) return false;
		if (p.freq === 'once') return !ls.get(key(p));
		if (p.freq === 'session') return !ss.get(key(p));
		return Date.now() - (+ls.get(key(p)) || 0) > p.days * 864e5;
	};
	const popup = list.find(eligible);
	if (!popup) return;

	const onRightPage = () => popup.where !== 'home' || !!document.querySelector('#main[data-template="home"]');
	const busy = () =>
		html.classList.contains('lc-intro') ||
		html.classList.contains('sv-open') ||
		!!document.querySelector('[data-cart].is-open, .sheet.is-open, .lc-sort.is-open') ||
		document.body.classList.contains('lc-checkout-page') ||
		document.body.classList.contains('lc-cart-page');

	/* ----------------------------------------------------------- timing */
	let due = false;
	let shown = false;
	const tryShow = () => {
		if (shown || !due) return;
		if (busy() || !onRightPage()) { setTimeout(tryShow, 1000); return; }
		show();
	};
	const arm = () => {
		if (html.classList.contains('lc-intro')) { setTimeout(arm, 300); return; } // the delay starts after the intro
		setTimeout(() => { due = true; tryShow(); }, Math.max(0, popup.delay) * 1000);
	};
	arm();
	document.addEventListener('lc:navigated', tryShow);

	/* --------------------------------------------------------------- UI */
	function show() {
		shown = true;
		if (popup.freq === 'session') ss.set(key(popup), '1');
		else ls.set(key(popup), String(Date.now()));

		const el = document.createElement('div');
		el.className = 'lpop';
		el.setAttribute('role', 'dialog');
		el.setAttribute('aria-modal', 'true');
		el.setAttribute('aria-labelledby', 'lpop-title');
		const hasText = !!(popup.eyebrow || popup.title || popup.text || popup.code);
		const hasBtn = !!(popup.btn && popup.url);
		const imageOnly = !!popup.img && !hasText;
		const kind = imageOnly ? ' is-image-only' : popup.img ? ' has-img' : ' is-text-only';
		const pic = popup.img
			? imageOnly && popup.url
				? `<a class="lpop__media lpop__cta" href="${esc(popup.url)}" aria-label="${esc(popup.btn || 'مشاهده')}"><img src="${esc(popup.img)}" alt=""></a>`
				: `<figure class="lpop__media"><img src="${esc(popup.img)}" alt=""></figure>`
			: '';
		const closeBtn = '<button class="lpop__close" type="button" data-lpop-close aria-label="بستن"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg></button>';
		el.innerHTML = `
			<div class="lpop__scrim" data-lpop-close></div>
			<div class="lpop__card${kind}" tabindex="-1"${popup.title ? '' : ' aria-label="پیام"'}>
				<span class="lpop__grip" aria-hidden="true"></span>
				${closeBtn}
				${pic}
				${imageOnly && !hasBtn ? '' : `<div class="lpop__body">
					${popup.eyebrow ? `<p class="lpop__eyebrow">${esc(popup.eyebrow)}</p>` : ''}
					${popup.title ? `<h2 class="lpop__title" id="lpop-title">${esc(popup.title)}</h2>` : ''}
					${popup.text ? `<p class="lpop__text">${esc(popup.text)}</p>` : ''}
					${popup.code ? `<div class="lpop__code"><code dir="ltr">${esc(popup.code)}</code><button type="button" data-lpop-copy>کپی کد</button></div>` : ''}
					<div class="lpop__actions">
						${hasBtn ? `<a class="btn btn--red lpop__cta" href="${esc(popup.url)}">${esc(popup.btn)}</a>` : ''}
						${imageOnly ? '' : '<button class="lpop__later" type="button" data-lpop-close>بعدا</button>'}
					</div>
				</div>`}
			</div>`;
		if (!popup.title) el.removeAttribute('aria-labelledby');
		document.body.appendChild(el);
		const card = el.querySelector('.lpop__card');
		const scrim = el.querySelector('.lpop__scrim');
		const last = document.activeElement;
		html.classList.add('lpop-open');
		card.focus({ preventScroll: true });

		if (!reduced.matches) {
			scrim.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 320, easing: 'ease' });
			card.animate(
				phone.matches
					? [{ transform: 'translateY(100%)' }, { transform: 'none' }]
					: [{ opacity: 0, transform: 'translateY(12px) scale(.96)' }, { opacity: 1, transform: 'none' }],
				{ duration: phone.matches ? 480 : 420, easing: 'cubic-bezier(.32,.72,0,1)' }
			);
		}

		let closing = false;
		const close = (instant = false) => {
			if (closing) return;
			closing = true;
			document.removeEventListener('keydown', onKey);
			const done = () => {
				el.remove();
				html.classList.remove('lpop-open');
				if (last && last.isConnected) last.focus({ preventScroll: true });
			};
			if (instant || reduced.matches) { done(); return; }
			scrim.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 220, easing: 'ease', fill: 'forwards' });
			const from = card.style.transform || 'none';
			card.animate(
				phone.matches
					? [{ transform: from }, { transform: 'translateY(100%)' }]
					: [{ opacity: 1, transform: from }, { opacity: 0, transform: 'translateY(8px) scale(.97)' }],
				{ duration: 220, easing: 'cubic-bezier(.23,1,.32,1)', fill: 'forwards' }
			).onfinish = done;
		};
		const onKey = (e) => {
			if (e.key === 'Escape') { e.preventDefault(); close(); return; }
			if (e.key !== 'Tab') return;
			const f = [...el.querySelectorAll('a[href], button')].filter((x) => x.offsetParent);
			const i = f.indexOf(document.activeElement);
			if (e.shiftKey && i <= 0) { e.preventDefault(); f[f.length - 1].focus(); }
			else if (!e.shiftKey && i === f.length - 1) { e.preventDefault(); f[0].focus(); }
		};
		document.addEventListener('keydown', onKey);

		el.addEventListener('click', (e) => {
			if (e.target.closest('[data-lpop-close]')) { close(); return; }
			if (e.target.closest('.lpop__cta')) { close(true); return; } // the router follows the link
			const copy = e.target.closest('[data-lpop-copy]');
			if (copy) {
				const ok = () => {
					copy.textContent = 'کپی شد';
					copy.classList.add('is-done');
					setTimeout(() => { copy.textContent = 'کپی کد'; copy.classList.remove('is-done'); }, 1800);
				};
				if (navigator.clipboard) navigator.clipboard.writeText(popup.code).then(ok, () => {});
				else {
					const r = document.createRange();
					r.selectNodeContents(el.querySelector('.lpop__code code'));
					getSelection().removeAllRanges();
					getSelection().addRange(r);
				}
			}
		});

		// phone: drag the sheet down to dismiss (a quick flick is enough)
		let drag = null;
		card.addEventListener('pointerdown', (e) => {
			if (!phone.matches || e.target.closest('a, button, code')) return;
			drag = { id: e.pointerId, y: e.clientY, t: performance.now() };
		});
		card.addEventListener('pointermove', (e) => {
			if (!drag || e.pointerId !== drag.id) return;
			const dy = e.clientY - drag.y;
			card.style.transform = `translateY(${dy > 0 ? dy : dy * 0.1}px)`;
			if (Math.abs(dy) > 4) try { card.setPointerCapture(e.pointerId); } catch (err) { /* released */ }
		});
		const end = (e) => {
			if (!drag || e.pointerId !== drag.id) return;
			const dy = e.clientY - drag.y;
			const v = dy / Math.max(1, performance.now() - drag.t);
			drag = null;
			if (dy > 110 || (dy > 20 && v > 0.11 * 4)) { close(); return; }
			card.animate([{ transform: card.style.transform || 'none' }, { transform: 'none' }], { duration: 300, easing: 'cubic-bezier(.23,1,.32,1)' });
			card.style.transform = '';
		};
		card.addEventListener('pointerup', end);
		card.addEventListener('pointercancel', end);
	}
})();
