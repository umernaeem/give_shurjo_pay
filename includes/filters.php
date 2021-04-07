<?php


function give_Shurjo_cc_form_callback( $form_id ) {

	if ( give_is_setting_enabled( give_get_option( 'Shurjo_billing_details' ) ) ) {
		give_default_cc_address_fields( $form_id );

		return true;
	}

	return false;
}

add_action( 'give_shurjo_cc_form', 'give_Shurjo_cc_form_callback' );



function give_Shurjo_show_error( $content ) {
	if (
		! isset( $_GET['give-Shurjo-payment'] )
		|| 'failed' !== $_GET['give-Shurjo-payment']
		|| ! isset( $_GET['give-Shurjo-error-message'] )
		|| empty( $_GET['give-Shurjo-error-message'] )
		|| ! give_is_failed_transaction_page()
	) {
		return $content;
	}

	return Give_Notices::print_frontend_notice(
		sprintf(
			'Payment Error: %s',
			base64_decode( $_GET['give-Shurjo-error-message'] )
		),
		false,
		'error'
	) . $content;
}

add_filter( 'the_content', 'give_Shurjo_show_error' );



