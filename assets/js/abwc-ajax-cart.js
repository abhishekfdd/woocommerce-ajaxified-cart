/**
 * WC Add to Cart JS
 *
 * @link        https://wordpress.org/plugins/ajaxified-cart-woocommerce/
 * @since       1.0.0
 * @package     ABWC_Ajax_Cart
 */

jQuery(($) => {
	if (typeof wc_add_to_cart_params === 'undefined') return false;

	$(document).on('click', '.product-type-simple form .single_add_to_cart_button', function (e) {
		e.preventDefault();

		const $thisButton = $(this);
		const $dataButton = $('.abwc-ajax-btn');
		const $qtyInput = $('.quantity .qty');

		if (!$dataButton.attr('data-product_id')) return true;

		$thisButton.removeClass('added').addClass('loading');

		const data = {
			product_id: $dataButton.data('product_id'),
			product_sku: $dataButton.data('product_sku'),
			quantity: $qtyInput.val()
		};

		$(document.body).trigger('adding_to_cart', [$thisButton, data]);

		$.post(
			wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
			data,
			(response) => {
				if (!response) return;

				let thisPage = window.location.toString().replace('add-to-cart', 'added-to-cart');

				if (response.error && response.product_url) {
					window.location = response.product_url;
					return;
				}

				if (wc_add_to_cart_params.cart_redirect_after_add === 'yes') {
					window.location = wc_add_to_cart_params.cart_url;
					return;
				}

				$thisButton.removeClass('loading').addClass('added');

				const {fragments, cart_hash} = response;

				if (fragments) {
					$.each(fragments, (key) => {
						$(key).addClass('updating');
					});
				}

				$('.shop_table.cart, .updating, .cart_totals').fadeTo('400', '0.6').block({
					message: null,
					overlayCSS: {opacity: 0.6}
				});

				if (!wc_add_to_cart_params.is_cart && $thisButton.parent().find('.added_to_cart').length === 0) {
					$thisButton.after(
						` <a href="${wc_add_to_cart_params.cart_url}" class="added_to_cart wc-forward" title="${wc_add_to_cart_params.i18n_view_cart}">${wc_add_to_cart_params.i18n_view_cart}</a>`
					);
				}

				if (fragments) {
					$.each(fragments, (key, value) => {
						$(key).replaceWith(value);
					});
				}

				$('.widget_shopping_cart, .updating').stop(true).css('opacity', '1').unblock();

				$('.shop_table.cart').load(`${thisPage} .shop_table.cart:eq(0) > *`, function () {
					$('.shop_table.cart').stop(true).css('opacity', '1').unblock();
					$(document.body).trigger('cart_page_refreshed');
				});

				$('.cart_totals').load(`${thisPage} .cart_totals:eq(0) > *`, function () {
					$('.cart_totals').stop(true).css('opacity', '1').unblock();
				});

				$(document.body).trigger('added_to_cart', [fragments, cart_hash, $thisButton]);
			}
		);
	});

	// Support for block-based Product Collection variable products.
	$(document).on('click', 'a[data-abwc-variable="1"]', function (e) {
		const $btn = $(this); abwcLastTrigger = $btn;
		// Already loaded; just show modal again.
		if ($btn.data('abwc-loaded')) {
			e.preventDefault();
			abwcShowModal();
			return false;
		}
		e.preventDefault();
		if ( typeof abwc_ajax_frontend === 'undefined' ) {
			return true;
		}
		const productId = $btn.data('product_id');
		if ( ! productId ) {
			return true;
		}
		$btn.addClass('loading');
		$.post( abwc_ajax_frontend.ajax_url, {
			action: 'abwc_get_variable_form',
			product_id: productId,
			nonce: abwc_ajax_frontend.nonce
		}).done( function ( resp ) {
			$btn.removeClass('loading');
			if ( ! resp || ! resp.success || ! resp.data || ! resp.data.html ) {
				return;
			}
			// Create a lightweight inline modal container if not present.
			let $modal = $('#abwc-variable-modal');
			if ( ! $modal.length ) {
				$('body').append('<div id="abwc-variable-modal" class="abwc-variable-modal" style="position:fixed;z-index:9999;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.5);"><div class="abwc-variable-modal__inner" style="max-width:520px;width:100%;background:#fff;padding:20px;position:relative;overflow:auto;max-height:90%;"></div></div>');
				$modal = $('#abwc-variable-modal');
				$modal.on('click', function(ev){ if ( ev.target === this ) { $modal.hide(); }});
			}
			$modal.find('.abwc-variable-modal__inner').html('<button type="button" class="abwc-close" style="position:absolute;top:8px;right:8px;">'+ (abwc_ajax_frontend.i18n ? abwc_ajax_frontend.i18n.close : '×') +'</button>' + resp.data.html);
			$modal.show();
			$modal.find('.abwc-close').on('click', function(){ $modal.hide(); });
			// Mark button to avoid refetch if reopened (optional: keep dynamic).
			$btn.data('abwc-loaded', true);
			// Initialize variation form JS if present (WC script triggers on found forms automatically but we ensure).
			$modal.find('.variations_form').wc_variation_form();
		}).fail(function(){
			$btn.removeClass('loading');
		});
	});

	// Intercept block button elements too (buttons or anchors with data-abwc-variable) & support keyboard ESC close.
	$(document).on('click', '[data-abwc-variable="1"].wp-block-button__link, button[data-abwc-variable="1"]', function(e){
		const $el = $(this); abwcLastTrigger = $el;
		if ($el.data('abwc-loaded')) {
			e.preventDefault();
			abwcShowModal();
			return false;
		}
		e.preventDefault();
		if ( typeof abwc_ajax_frontend === 'undefined' ) { return true; }
		const productId = $el.data('product_id');
		if ( ! productId ) { return true; }
		$el.addClass('loading');
		$.post( abwc_ajax_frontend.ajax_url, { action: 'abwc_get_variable_form', product_id: productId, nonce: abwc_ajax_frontend.nonce } )
			.done( function( resp ){
				$el.removeClass('loading');
				if ( ! resp || ! resp.success || ! resp.data || ! resp.data.html ) { return; }
				let $modal = $('#abwc-variable-modal');
				if ( ! $modal.length ) {
					$('body').append('<div id="abwc-variable-modal" class="abwc-variable-modal" role="dialog" aria-modal="true" style="position:fixed;z-index:9999;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.5);"><div class="abwc-variable-modal__inner" style="max-width:520px;width:100%;background:#fff;padding:20px;position:relative;overflow:auto;max-height:90%;"></div></div>');
					$modal = $('#abwc-variable-modal');
					$modal.on('click', function(ev){ if ( ev.target === this ) { $modal.hide(); } });
					$(document).on('keyup.abwcModal', function(ev){ if ( ev.key === 'Escape' ) { $modal.hide(); } });
				}
				$modal.find('.abwc-variable-modal__inner').html('<button type="button" class="abwc-close" aria-label="'+ (abwc_ajax_frontend.i18n ? abwc_ajax_frontend.i18n.close : 'Close') +'" style="position:absolute;top:8px;right:8px;">'+ (abwc_ajax_frontend.i18n ? abwc_ajax_frontend.i18n.close : '×') +'</button>' + resp.data.html);
				$modal.show();
				$modal.find('.abwc-close').on('click', function(){ $modal.hide(); });
				$el.data('abwc-loaded', true);
				$modal.find('.variations_form').wc_variation_form();
				// Focus first select for accessibility.
				const firstSelect = $modal.find('.variations_form select:first');
				if ( firstSelect.length ) { firstSelect.focus(); }
			})
			.fail( function(){ $el.removeClass('loading'); });
	});

	// Capture-phase interception for block buttons to prevent early navigation by WC Blocks interactive handler.
	document.addEventListener('click', function(ev){
		const target = ev.target.closest('a[data-abwc-variable="1"], button[data-abwc-variable="1"], a[data-abwc-variable][data-product_slug]');
		if (!target) return;
		abwcLastTrigger = $(target);
		// Always intercept to manage modal.
		ev.preventDefault();
		if (target.dataset.abwcLoaded === 'true') {
			abwcShowModal();
			return;
		}
		// Trigger jQuery handler for first-time load.
		$(target).trigger('click');
	}, true);

	// Mutation observer: tag newly added product buttons lacking data-product_id but showing Select Options text.
	const observeButtons = () => {
		const target = document.body;
		if (!target) return;
		const observer = new MutationObserver((mutations) => {
			mutations.forEach((m) => {
				if (!m.addedNodes) return;
				$(m.addedNodes).find('a,button').each(function(){
					const $candidate = $(this);
					if ($candidate.attr('data-product_id')) return;
					const txt = $candidate.text().trim().toLowerCase();
					const ref = (abwc_ajax_frontend && abwc_ajax_frontend.i18n ? abwc_ajax_frontend.i18n.selectOptionsText : 'select options').toLowerCase();
					if ( txt === ref ) {
						// Try to derive product slug from href.
						const href = $candidate.attr('href') || '';
						let slug = '';
						try {
							const url = new URL(href, window.location.origin);
							slug = url.pathname.replace(/\/$/, '').split('/').filter(Boolean).pop();
						} catch(e) {}
						if ( slug ) {
							$candidate.attr('data-abwc-variable','1').attr('data-product_slug', slug);
						}
					}
				});
			});
		});
		observer.observe(target, { childList: true, subtree: true });
	};
	observeButtons();

	// Fallback handler when we only have product_slug.
	$(document).on('click', '[data-abwc-variable="1"][data-product_slug]:not([data-product_id])', function(e){
		const $el = $(this); abwcLastTrigger = $el;
		if ($el.data('abwc-loaded')) {
			e.preventDefault();
			abwcShowModal();
			return false;
		}
		e.preventDefault();
		if ( typeof abwc_ajax_frontend === 'undefined' ) return true;
		const slug = $el.attr('data-product_slug');
		if (!slug) return true;
		$el.addClass('loading');
		$.post(abwc_ajax_frontend.ajax_url, { action: 'abwc_get_variable_form_by_slug', product_slug: slug, nonce: abwc_ajax_frontend.nonce })
			.done(resp => {
				$el.removeClass('loading');
				if (!resp || !resp.success || !resp.data || !resp.data.html) return;
				let $modal = $('#abwc-variable-modal');
				if (!$modal.length) {
					$('body').append('<div id="abwc-variable-modal" class="abwc-variable-modal" role="dialog" aria-modal="true" style="position:fixed;z-index:9999;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.5);"><div class="abwc-variable-modal__inner" style="max-width:520px;width:100%;background:#fff;padding:20px;position:relative;overflow:auto;max-height:90%;"></div></div>');
					$modal = $('#abwc-variable-modal');
					$modal.on('click', ev => { if (ev.target === ev.currentTarget) { $modal.hide(); } });
					$(document).on('keyup.abwcModalSlug', ev => { if (ev.key === 'Escape') { $modal.hide(); } });
				}
				$modal.find('.abwc-variable-modal__inner').html('<button type="button" class="abwc-close" aria-label="'+ (abwc_ajax_frontend.i18n ? abwc_ajax_frontend.i18n.close : 'Close') +'" style="position:absolute;top:8px;right:8px;">'+ (abwc_ajax_frontend.i18n ? abwc_ajax_frontend.i18n.close : '×') +'</button>' + resp.data.html);
				$modal.show();
				$modal.find('.abwc-close').on('click', () => { $modal.hide(); });
				$el.data('abwc-loaded', true);
				$modal.find('.variations_form').wc_variation_form();
				const firstSelect = $modal.find('.variations_form select:first');
				if (firstSelect.length) firstSelect.focus();
			})
			.fail(() => { $el.removeClass('loading'); });
	});

	// Refresh button inside modal (delegated) to re-fetch form.
	$(document).on('click', '#abwc-variable-modal .abwc-refresh', function(e){
		e.preventDefault();
		const pid = $('#abwc-variable-modal .variations_form input[name="product_id"]').val();
		if (!pid || typeof abwc_ajax_frontend === 'undefined') return;
		const $modal = $('#abwc-variable-modal');
		$modal.attr('data-loading','true');
		$.post(abwc_ajax_frontend.ajax_url, { action: 'abwc_get_variable_form', product_id: pid, nonce: abwc_ajax_frontend.nonce })
			.done(resp => {
				if (resp && resp.success && resp.data && resp.data.html) {
					$modal.find('.abwc-variable-modal__inner').html('<button type="button" class="abwc-close" aria-label="'+ (abwc_ajax_frontend.i18n ? abwc_ajax_frontend.i18n.close : 'Close') +'" style="position:absolute;top:8px;right:8px;">'+ (abwc_ajax_frontend.i18n ? abwc_ajax_frontend.i18n.close : '×') +'</button>' + resp.data.html + '<p style="margin-top:12px;text-align:right;"><a href="#" class="abwc-refresh">'+ (abwc_ajax_frontend.i18n ? abwc_ajax_frontend.i18n.refresh : 'Refresh') +'</a></p>');
					$modal.attr('data-loading','false');
					$modal.find('.variations_form').wc_variation_form();
					abwcShowModal();
				}
			})
			.always(()=>{ $modal.attr('data-loading','false'); });
	});

	// Helper to show existing modal and refocus.
	let abwcLastTrigger = null;
	function abwcShowModal() {
		const $modal = $('#abwc-variable-modal');
		if ($modal.length) {
			$modal.attr('aria-hidden','false');
			$('body').addClass('abwc-modal-open');
			const focusable = $modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])').filter(':visible');
			const first = focusable.first();
			const last = focusable.last();
			if (first.length) first.focus();
			$modal.off('keydown.abwcTrap').on('keydown.abwcTrap', function(e){
				if ( e.key === 'Escape' ) { abwcCloseModal(); return; }
				if ( e.key === 'Tab' ) {
					if ( e.shiftKey && document.activeElement === first[0] ) { e.preventDefault(); last.focus(); }
					else if ( ! e.shiftKey && document.activeElement === last[0] ) { e.preventDefault(); first.focus(); }
				}
			});
			return true;
		}
		return false;
	}
	function abwcCloseModal() {
		const $modal = $('#abwc-variable-modal');
		$modal.attr('aria-hidden','true').hide();
		$('body').removeClass('abwc-modal-open');
		if (abwcLastTrigger && abwcLastTrigger.length) {
			abwcLastTrigger.focus();
		}
	}
});
