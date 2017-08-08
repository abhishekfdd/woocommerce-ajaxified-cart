/**
 * Admin js for ajax cart
 * 
 * @since       1.2.0
 * @package     ABWC_Ajax_Cart
 */

jQuery( document ).ready( function () {

	//Temporary function to remove admin notice once cart expires 
	jQuery( '.wrap' ).on( 'click', '.notice-dismiss', function () {

		if ( 'undefined' != jQuery( this ).parent().data( 'dismiss' ) ) {
			jQuery.ajax( {
				url: abwc_ajax_data.ajax_url,
				type: 'post',
				data: {
					action: 'abwc_dismiss_notice',
					dismiss: 'true'
				},
				success: function ( response ) {
					console.log( 'Notice dismissed' );
				}
			} );
		}

	} ); //end()

} );