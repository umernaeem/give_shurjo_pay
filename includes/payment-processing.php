<?php

function give_Shurjo_listen_ipn() {

	if ( isset( $_GET['give-listener'] ) && $_GET['give-listener'] == 'Shurjo_IPN' ) {
		do_action( 'give_verify_Shurjo_gateway_ipn' );
		
	}
}

add_action( 'init', 'give_Shurjo_listen_ipn' );

function give_Shurjo_process_ipn() {
	if (!isset($_REQUEST['spdata']) || empty($_REQUEST['spdata'])) {
        wp_redirect( get_site_url() );
        exit();
    }
    $encResponse = $_REQUEST["spdata"];
    $decryptValues = Shurjo_decrypt_and_validate($encResponse);
    
    if ($decryptValues == false) {
        wp_redirect( get_site_url() );
        exit();
    }
    $donation_id = 0;
	$form_id     = 0;
	
    Shurjo_get_form_payment_from_response($decryptValues,$form_id,$donation_id);
    if((int)$donation_id==0 || (int)$form_id==0)
    {
    	exit();
    }
    switch (strtolower($decryptValues->bankTxStatus)) {
        case "success":
            Shurjo_Transaction_Success($donation_id,$decryptValues);
            break;
        case "cancel":
            Shurjo_Transaction_Failed($donation_id,$decryptValues);
            break;
        case "fail":
            Shurjo_Transaction_Failed($donation_id,$decryptValues);
            break;                                                  
    };
    
	
	exit();
}

add_action( 'give_verify_Shurjo_gateway_ipn', 'give_Shurjo_process_ipn' );


function give_Shurjo_process_payment( $purchase_data ) {

	//print_r($purchase_data);
	// check for any stored errors
	$errors = give_get_errors();
	if ( ! $errors ) {

		$payment_data = array(
			'price'           => $purchase_data['price'],
			'give_form_title' => $purchase_data['post_data']['give-form-title'],
			'give_form_id'    => (int) $purchase_data['post_data']['give-form-id'] ,
			'give_price_id'   => isset( $purchase_data['post_data']['give-price-id'] ) ? $purchase_data['post_data']['give-price-id'] : '',
			'date'            => $purchase_data['date'],
			'user_email'      => $purchase_data['user_email'],
			'purchase_key'    => $purchase_data['purchase_key'],
			'currency'        => give_get_currency(),
			'user_info'       => $purchase_data['user_info'],
			'status'          => 'pending',
			'gateway'         => $purchase_data['gateway'],
		);

		// Record the pending payment
		$payment = give_insert_payment( $payment_data );

		// Verify donation payment.
		if ( ! $payment ) {
			// Record the error.
			give_record_gateway_error(
				esc_html__( 'Payment Error', 'give-Shurjo' ),
				/* translators: %s: payment data */
				sprintf(
					esc_html__( 'Payment creation failed before process PayUmoney gateway. Payment data: %s', 'give-Shurjo' ),
					json_encode( $purchase_data )
				),
				$payment
			);

			// Problems? Send back.
			give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );
		}

		$redirect_to_form = $_SERVER['HTTP_HOST']. "/?give-redirect_form=Shurjo_redirect&price=".$purchase_data['price']."&payment-id={$payment}&form-id={$purchase_data['post_data']['give-form-id']}";
		
		if(isset($purchase_data['post_data']['give-cs-base-currency']) && isset($purchase_data['post_data']['give-cs-exchange-rate']) && isset($purchase_data['post_data']['give-cs-form-currency']))
		{
			$new_price = round($purchase_data['price']/$purchase_data['post_data']['give-cs-exchange-rate'],2);
			$redirect_to_form = $_SERVER['HTTP_HOST']. "/?give-redirect_form=Shurjo_redirect&price=".$new_price."&payment-id={$payment}&form-id={$purchase_data['post_data']['give-form-id']}";
		}

		wp_redirect( $redirect_to_form );
		exit();

		

	}
	give_send_back_to_checkout( "?payment-mode={$purchase_data['gateway']}&form-id={$purchase_data['post_data']['give-form-id']}" );
}

add_action( 'give_gateway_shurjo', 'give_Shurjo_process_payment' );


function give_Shurjo_listen_redirect() {
	
	if ( isset( $_GET['give-redirect_form'] ) && $_GET['give-redirect_form'] == 'Shurjo_redirect' ) {
		
		$price = $_GET['price'];
		$form_id = $_GET['form-id'];
		$payment_id = $_GET['payment-id'];
		$Shurjo_redirect = give_Shurjo_is_test_mode() ? 'http://shurjotest.com': 'https://shurjopay.com';
		$Shurjo_transaction_prefix = give_get_option( 'Shurjo_transaction_prefix', '' );
		$Shurjo_merchant_user_name = give_get_option( 'Shurjo_merchant_user_name', '' );
		$Shurjo_merchant_password = give_get_option( 'Shurjo_merchant_password', '' );

		$api_ip = $_SERVER['REMOTE_ADDR'];
		if($api_ip=='::1')
		{
			$api_ip = '127.0.0.1';
		}
		$api_return_url = get_site_url() . "/?give-listener=Shurjo_IPN";

		//$api_return_url = get_site_url();


		$uniq_transaction_key = $Shurjo_transaction_prefix . '_' .$form_id. '_' .$payment_id. '_' . date("ymds");


        $payload = 'spdata=<?xml version="1.0" encoding="utf-8"?>
                        <shurjoPay><merchantName>' . $Shurjo_merchant_user_name . '</merchantName>
                        <merchantPass>' . $Shurjo_merchant_password . '</merchantPass>
                        <userIP>' . $api_ip . '</userIP>
                        <uniqID>' . $uniq_transaction_key . '</uniqID>
                        <totalAmount>' . $price . '</totalAmount>
                        <paymentOption>shurjopay</paymentOption>
                        <returnURL>' . $api_return_url . '</returnURL></shurjoPay>';

        $gw_api_url = $Shurjo_redirect . "/sp-data.php";


        $response = shurjopay_submit_data($gw_api_url, $payload);
		
		print_r( $response );
		exit();
		
	}
}

add_action( 'init', 'give_Shurjo_listen_redirect' );