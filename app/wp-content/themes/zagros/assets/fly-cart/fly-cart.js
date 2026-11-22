/**
 * Fly-to-cart: the product image lifts, then shrinks and arcs into the cart button.
 * Used by the theme bundle (cards, quick-add sheet, product page) and the lookbook.
 *
 *   window.LC_FLY(img, target, done)
 *   img    – the product image the shopper added from
 *   target – the cart button to land in (defaults to the first visible [data-cart-open])
 *   done   – called when it lands (or straight away when there is nothing to animate)
 */
(() => {
	const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
	const visibleCart = () => [...document.querySelectorAll('[data-cart-open]')].find((el) => el.offsetParent !== null);

	window.LC_FLY = (img, target, done = () => {}) => {
		const to = target || visibleCart();
		const a = img && img.getBoundingClientRect ? img.getBoundingClientRect() : null;
		const b = to && to.getBoundingClientRect();
		const onScreen = a && a.width > 0 && a.bottom > 0 && a.top < window.innerHeight;
		if (!onScreen || !b || reduced.matches || !document.body.animate) {
			done();
			return;
		}
		// keep the flying copy a sensible size even when it starts from a full-width product photo
		const k = Math.min(1, 320 / Math.max(a.width, a.height));
		const w = a.width * k;
		const h = a.height * k;
		const x = a.left + (a.width - w) / 2;
		const y = a.top + (a.height - h) / 2;

		const box = document.createElement('div');
		box.setAttribute('aria-hidden', 'true');
		box.style.cssText = `position:fixed;z-index:900;left:${x}px;top:${y}px;width:${w}px;height:${h}px;pointer-events:none;`;
		const lift = document.createElement('div');
		lift.style.cssText = 'width:100%;height:100%;';
		const pic = new Image();
		pic.alt = '';
		pic.src = img.currentSrc || img.src;
		pic.style.cssText = 'display:block;width:100%;height:100%;object-fit:cover;border-radius:16px;box-shadow:0 20px 50px -10px rgba(0,0,0,.6),0 0 0 1px rgba(255,255,255,.1);';
		lift.appendChild(pic);
		box.appendChild(lift);
		document.body.appendChild(box);

		const dx = b.left + b.width / 2 - (x + w / 2);
		const dy = b.top + b.height / 2 - (y + h / 2);
		const end = 22 / Math.max(w, h);
		const D = 880;
		// sideways: smooth in and out
		box.animate([{ transform: 'translateX(0)' }, { transform: `translateX(${dx}px)` }], { duration: D, easing: 'cubic-bezier(.4,0,.6,1)', fill: 'forwards' });
		// up/down: eases out against the sideways move, so the path curves like a throw
		lift.animate([{ transform: 'translateY(0)' }, { transform: `translateY(${dy}px)` }], { duration: D, easing: 'cubic-bezier(.5,-0.35,.7,.9)', fill: 'forwards' });
		// a small lift, then it shrinks to a dot as it lands
		const shrink = pic.animate([
			{ transform: "scale(1)", borderRadius: '16px', opacity: 1 },
			{ transform: 'scale(1.06)', borderRadius: '16px', opacity: 1, offset: 0.12 },
			{ transform: `scale(${end})`, borderRadius: '50%', opacity: 0.9 },
		], { duration: D, easing: 'cubic-bezier(.45,0,.75,.55)', fill: 'forwards' });

		let finished = false;
		const finish = () => {
			if (finished) return;
			finished = true;
			box.remove();
			to.animate?.([
				{ transform: 'none' },
				{ transform: 'scale(1.22) rotate(-8deg)', offset: 0.3 },
				{ transform: 'scale(.94) rotate(3deg)', offset: 0.6 },
				{ transform: 'none' },
			], { duration: 550, easing: 'cubic-bezier(.16,1,.3,1)' });
			done();
		};
		shrink.onfinish = finish;
		shrink.oncancel = finish;
	};
})();
