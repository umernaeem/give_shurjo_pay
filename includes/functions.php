<?php


function give_Shurjo_is_test_mode() {
	return apply_filters( 'give_Shurjo_is_test_mode', give_is_test_mode() );
}

function give_Shurjo_get_api_url( $type = 'processTransaction' ) {
	
}
function shurjopay_submit_data($url = "", $postFields = "")
{
    if (empty($url) || empty($postFields)) return null;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function give_Shurjo_get_payment_method_label() {
	$checkout_label = give_get_option( 'Shurjo_checkout_label', '' );

	return ( empty( $checkout_label )
		? __( 'Shurjo', 'give-Shurjo' )
		: $checkout_label
	);
}


function Shurjo_Transaction_Failed($donation_id,$decryptValues)
{

	give_record_gateway_error(
		__( 'Shurjo Error', 'give-Shurjo' ),
		sprintf( __( 'Transaction Failed. Shurjo response message: %s', 'give-Shurjo' ), $decryptValues->spCodeDes ) . '<br><br>' . sprintf( esc_attr__( 'Details: %s', 'give-Shurjo' ), '<br>' . print_r( $decryptValues->spCodeDes, true ) ),
		$donation_id
	);

	give_update_payment_status( $donation_id, 'failed' );
	update_post_meta( $donation_id, 'Shurjo_donation_response', print_r( $decryptValues->spCodeDes, true ));
	give_insert_payment_note( $donation_id, sprintf( __( 'Transaction Failed.Shurjo response message:  %s', 'give-Shurjo' ), print_r( $decryptValues->spCodeDes, true ) ) );

	wp_redirect( give_get_failed_transaction_uri() . '?give-Shurjo-payment=failed&give-Shurjo-error-message=' . base64_encode( "Shurjo Transaction Failed" ) );
}


function Shurjo_Transaction_Success($donation_id,$decryptValues)
{
    echo $decryptValues->txID;
	give_insert_payment_note( $donation_id, sprintf( __( 'Transaction Successful. Shurjo Transaction ID: %s', 'give-Shurjo' ), $decryptValues->txID ) );
	give_set_payment_transaction_id( $donation_id,(string) $decryptValues->txID );
	update_post_meta( $donation_id, 'Shurjo_donation_response', sprintf( __( 'Transaction Successful. Shurjo Transaction ID: %s', 'give-Shurjo' ), (string)$decryptValues->txID ) );
	give_update_payment_status( $donation_id, 'complete' );

	give_send_to_success_page();
    
}



function Shurjo_get_form_payment_from_response($data = "", &$form_id, &$payment_id)
{
    if (empty($data)) return false;
    if (!isset($data->txID)) return false;
    $order_id_time = (string)$data->txID;
    $order_id = explode('_', $order_id_time);
    if(isset($order_id[1]) && isset($order_id[2]))
    {
    	$form_id = (int)$order_id[1];
    	$payment_id = (int)$order_id[2];


    }
}


function Shurjo_decrypt_and_validate($data = "")
{
    if (empty($data)) return false;
    $decryptValues = Shurjo_decrypt($data);
    if (empty($decryptValues)) return false;
    $decryptValues = simplexml_load_string($decryptValues) or die("Error: Cannot create object");
    
    if (!$decryptValues) return false;
    if (!isset($decryptValues->txID) || empty($decryptValues->txID)) return false;
    if (!isset($decryptValues->bankTxStatus) || empty($decryptValues->bankTxStatus)) return false;
    if (!isset($decryptValues->spCode) || empty($decryptValues->spCode)) return false;
    
    return $decryptValues;
}
function Shurjo_decrypt($encryptedText = "")
{
    if (empty($encryptedText)) return null;
    $Shurjo_decrypt_url = give_Shurjo_is_test_mode() ? 'http://shurjotest.com/merchant/decrypt.php': 'https://shurjopay.com/merchant/decrypt.php';
    $url = $Shurjo_decrypt_url . '?data=' . $encryptedText;
    $ch = curl_init();  
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);    
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $response_decrypted = curl_exec($ch);
    curl_close ($ch);
    return $response_decrypted;            
}
