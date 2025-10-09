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
});
