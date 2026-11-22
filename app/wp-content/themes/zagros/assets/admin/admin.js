/* Theme admin: tabs, single image picker, sortable image galleries with an optional product per image. */
(function ($) {
	'use strict';

	/* Tabs (home settings screen) */
	const $tabs = $('.lcx-tab');
	if ($tabs.length) {
		const show = (key) => {
			if (!$('.lcx-panel[data-panel="' + key + '"]').length) key = $tabs.first().data('tab');
			$tabs.removeClass('is-active').attr('aria-selected', 'false').filter('[data-tab="' + key + '"]').addClass('is-active').attr('aria-selected', 'true');
			$('.lcx-panel').removeClass('is-active').filter('[data-panel="' + key + '"]').addClass('is-active');
			$('.lcx-current-tab').val(key);
		};
		$tabs.on('click', function (e) {
			e.preventDefault();
			const key = $(this).data('tab');
			history.replaceState(null, '', '#' + key);
			show(key);
		});
		show((location.hash || '').slice(1));
	}

	const thumb = (att) => (att.sizes && (att.sizes.thumbnail || att.sizes.medium) || att).url;

	/* Single image */
	$(document).on('click', '.lcx-image__pick', function () {
		const $box = $(this).closest('.lcx-image');
		const frame = wp.media({ title: 'انتخاب عکس', library: { type: 'image' }, multiple: false, button: { text: 'انتخاب' } });
		frame.on('select', () => {
			const att = frame.state().get('selection').first().toJSON();
			$box.find('input').val(att.id);
			$box.find('img').attr('src', (att.sizes && att.sizes.medium || att).url);
			$box.addClass('has-img');
		});
		frame.open();
	});
	$(document).on('click', '.lcx-image__clear', function () {
		const $box = $(this).closest('.lcx-image');
		$box.find('input').val('0');
		$box.find('img').attr('src', '');
		$box.removeClass('has-img');
	});

	/* Galleries */
	const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
	const row = (name, att, products) =>
		'<li class="lcx-row"><span class="lcx-row__handle" aria-hidden="true">⋮⋮</span>' +
		'<img src="' + esc(thumb(att)) + '" alt="" /><input type="hidden" name="' + esc(name) + '[img][]" value="' + att.id + '" />' +
		(products
			? '<select class="wc-product-search lcx-row__product" name="' + esc(name) + '[product][]" data-placeholder="لینک به محصول (اختیاری)" data-allow_clear="true" data-action="woocommerce_json_search_products" data-exclude_type="variation"><option value=""></option></select>'
			: '') +
		'<button type="button" class="lcx-row__remove" aria-label="برداشتن">×</button></li>';

	const sortable = ($list) => {
		if ($list.sortable) $list.sortable({ handle: '.lcx-row__handle, img', items: '> .lcx-row', placeholder: 'lcx-row lcx-row--ghost', tolerance: 'pointer' });
	};
	$('.lcx-gallery__list').each(function () { sortable($(this)); });

	$(document).on('click', '.lcx-gallery__add', function () {
		const $g = $(this).closest('.lcx-gallery');
		const frame = wp.media({ title: 'افزودن عکس', library: { type: 'image' }, multiple: 'add', button: { text: 'افزودن' } });
		frame.on('select', () => {
			const $list = $g.find('.lcx-gallery__list');
			frame.state().get('selection').each((m) => $list.append(row($g.data('name'), m.toJSON(), +$g.data('products') === 1)));
			$(document.body).trigger('wc-enhanced-select-init');
			sortable($list);
		});
		frame.open();
	});
	$(document).on('click', '.lcx-row__remove', function () {
		$(this).closest('.lcx-row').remove();
	});
})(jQuery);
