/**
 * Back/forward returns to where you were.
 *
 * The theme router swaps pages in place and starts new pages at the top, but on back/forward it
 * kept whatever scroll the previous page had. This remembers the position on each history entry
 * and puts it back once the router has finished drawing the page (`lc:navigated`).
 */
(() => {
	if (!window.history || !('replaceState' in history)) return;

	const save = () => {
		try {
			history.replaceState({ ...(history.state || {}), lcY: Math.round(window.scrollY) }, '');
		} catch (e) { /* some browsers rate-limit replaceState; the next save catches up */ }
	};
	let timer = 0;
	window.addEventListener('scroll', () => {
		clearTimeout(timer);
		timer = setTimeout(save, 150);
	}, { passive: true });
	// make sure the entry we are leaving has the latest position
	document.addEventListener('click', (e) => {
		if (e.target.closest && e.target.closest('a[href]')) save();
	}, true);

	let restoreTo = null;
	window.addEventListener('popstate', (e) => {
		restoreTo = e.state && typeof e.state.lcY === 'number' ? e.state.lcY : null;
	});

	const jump = (y) => window.scrollTo({ top: y, left: 0, behavior: 'instant' });
	let settle = 0;
	let userMoved = false;
	const stop = () => {
		userMoved = true;
	};
	['wheel', 'touchstart', 'keydown', 'pointerdown'].forEach((t) => window.addEventListener(t, stop, { passive: true }));

	document.addEventListener('lc:navigated', () => {
		if (restoreTo === null) return;
		const y = restoreTo;
		restoreTo = null;
		userMoved = false;
		clearInterval(settle);
		jump(y);
		// The page can still be growing (images, pinned scroll sections), which clamps the jump short.
		// Keep putting it back for a moment, unless the shopper starts scrolling themselves.
		const until = performance.now() + 1500;
		settle = setInterval(() => {
			if (userMoved || performance.now() > until) {
				clearInterval(settle);
				return;
			}
			if (Math.abs(window.scrollY - y) > 2) jump(y);
		}, 120);
	});
})();
