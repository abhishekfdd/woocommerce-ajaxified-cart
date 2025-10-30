/**
 * Admin js for ajax cart
 *
 * @since       1.2.0
 * @package     ABWC_Ajax_Cart
 */

jQuery(($) => {
	// Remove admin notice once cart expires
	$('.wrap').on('click', '.notice-dismiss', function () {
		const $parent = $(this).parent();
		if ($parent.data('dismiss') !== undefined) {
			$.post(abwc_ajax_data.ajax_url, {
				action: 'abwc_dismiss_notice',
				dismiss: 'true'
			}).done(() => {
				console.log('Notice dismissed');
			});
		}
	});
});
