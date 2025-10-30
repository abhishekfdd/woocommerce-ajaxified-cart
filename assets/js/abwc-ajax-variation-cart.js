/**
 * WC Add to Cart JS for variable product
 *
 * @link        https://wordpress.org/plugins/ajaxified-cart-woocommerce/
 * @since       1.0.0
 * @package     ABWC_Ajax_Cart
 */

jQuery(($) => {
	// Ensure wc_add_to_cart_params exists
	if (typeof wc_add_to_cart_params === 'undefined') return false;

	// Ajax add to cart
	$(document).on('click', '.variations_form .single_add_to_cart_button', function (e) {
		e.preventDefault();

		const $variationForm = $(this).closest('.variations_form');
		const varId = $variationForm.find('input[name=variation_id]').val();
		const productId = $variationForm.find('input[name=product_id]').val();
		const quantity = $variationForm.find('input[name=quantity]').val();

		$('.ajaxerrors').remove();
		const item = {};
		let check = true;

		let $variations = $variationForm.find('select[name^=attribute]');
		if (!$variations.length) {
			$variations = $variationForm.find('[name^=attribute]:checked');
		}
		if (!$variations.length) {
			$variations = $variationForm.find('input[name^=attribute]');
		}

		$variations.each(function () {
			const $this = $(this);
			const attributeName = $this.attr('name');
			const attributeValue = $this.val();

			$this.removeClass('error');

			if (!attributeValue) {
				const index = attributeName.lastIndexOf('_');
				const attributeTaxName = attributeName.substring(index + 1);

				$this
					.addClass('required error')
					.before(`<div class="ajaxerrors"><p>Please select ${attributeTaxName}</p></div>`);
				check = false;
			} else {
				item[attributeName] = attributeValue;
			}
		});

		if (!check) return false;

		const $thisButton = $(this);

		if ($thisButton.is('.variations_form .single_add_to_cart_button')) {
			$thisButton.removeClass('added').addClass('loading');

			const data = {
				action: 'woocommerce_add_to_cart_variable_rc',
				product_id: productId,
				quantity,
				variation_id: varId,
				variation: item,
				nonce: ( typeof abwc_ajax_frontend !== 'undefined' ? abwc_ajax_frontend.nonce : '' )
			};

			$('body').trigger('adding_to_cart', [$thisButton, data]);

			$.post(wc_add_to_cart_params.ajax_url, data, (response) => {
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
			});

			return false;
		}
		return true;
	});
});
