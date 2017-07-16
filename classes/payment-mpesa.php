<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include_once dirname(__FILE__).'/vendor/autoload.php';

class Mpesa_Camptix extends CampTix_Payment_Method {
	public $id = 'mpesa';
	public $name = 'Lipa Na Mpesa';
	public $description = 'Pay for your Ticket Using Mpesa Mobile Money.';
	public $supported_currencies = array( 'KES');

	/**
	* This array is to store our options.
	* Use $this->get_payment_options() to retrieve them.
	*/

	protected $options = array();

	function camptix_init() {
		$this->options = array_merge( array(
			'consumer_key' => '',
			'consumer_secret' => '',
			'paybill_no' => '',
		), $this->get_payment_options() );

		// IPN Listener
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
	}

	function payment_settings_fields() {

		$this->add_settings_field_helper( 'consumer_key', __( 'Consumer Key', 'camptix' ), array( $this, 'field_text' ) );
		$this->add_settings_field_helper( 'consumer_secret', __( 'Consumer Secret', 'camptix' ), array( $this, 'field_text' ) );
		$this->add_settings_field_helper( 'paybill_no', __(' Paybill Number', 'camptix' ), array( $this, 'field_text' ) );

	}

	function validate_options( $input ) {
		$output = $this->options;

		if ( isset( $input['consumer_key'] ) )
			$output['consumer_key'] = $input['consumer_key'];
		if ( isset( $input['consumer_secret'] ) )
			$output['consumer_secret'] = $input['consumer_secret'];
		if ( isset( $input['paybill_no'] ) )
			$output['paybill_no'] = $input['paybill_no'];

		return $output;

 	}

	function template_redirect() {
		if ( ! isset( $_REQUEST['tix_payment_method'] ) || 'camptix_mpesa' != $_REQUEST['tix_payment_method'] )
			return;
		if ( isset( $_GET['tix_action'] ) ) {
			if ( 'payment_cancel' == $_GET['tix_action'] )
				$this->payment_cancel();
			if ( 'payment_return' == $_GET['tix_action'] )
				$this->payment_return();
			if ( 'payment_notify' == $_GET['tix_action'] )
				$this->payment_notify();
		}
	}
	function payment_return() {
		global $camptix;
		$this->log( sprintf( 'Running payment_return. Request data attached.' ), null, $_REQUEST );
		$this->log( sprintf( 'Running payment_return. Server data attached.' ), null, $_SERVER );
		$payment_token = ( isset( $_REQUEST['tix_payment_token'] ) ) ? trim( $_REQUEST['tix_payment_token'] ) : '';
		if ( empty( $payment_token ) )
			return;
		$attendees = get_posts(
			array(
				'posts_per_page' => 1,
				'post_type' => 'tix_attendee',
				'post_status' => array( 'draft', 'pending', 'publish', 'cancel', 'refund', 'failed' ),
				'meta_query' => array(
					array(
						'key' => 'tix_payment_token',
						'compare' => '=',
						'value' => $payment_token,
						'type' => 'CHAR',
					),
				),
			)
		);
		if ( empty( $attendees ) )
			return;
		$attendee = reset( $attendees );
		if ( 'draft' == $attendee->post_status ) {
			return $this->payment_result( $payment_token, CampTix_Plugin::PAYMENT_STATUS_PENDING );
		} else {
			$access_token = get_post_meta( $attendee->ID, 'tix_access_token', true );
			$url = add_query_arg( array(
				'tix_action' => 'access_tickets',
				'tix_access_token' => $access_token,
			), $camptix->get_tickets_url() );
			wp_safe_redirect( esc_url_raw( $url . '#tix' ) );
			die();
		}
	}

	/**
	 * Runs when Mpesa sends an ITN signal.
	 * Verify the payload and use $this->payment_result
	 * to signal a transaction result back to CampTix.
	 */
	function payment_notify() {
		global $camptix;
		$this->log( sprintf( 'Running payment_notify. Request data attached.' ), null, $_REQUEST );
		$this->log( sprintf( 'Running payment_notify. Server data attached.' ), null, $_SERVER );
		$payment_token = ( isset( $_REQUEST['tix_payment_token'] ) ) ? trim( $_REQUEST['tix_payment_token'] ) : '';
		$payload = stripslashes_deep( $_POST );
		$data_string = '';
		$data_array = array();
		// Dump the submitted variables and calculate security signature
		foreach ( $payload as $key => $val ) {
			if ( $key != 'signature' ) {
				$data_string .= $key .'='. urlencode( $val ) .'&';
				$data_array[$key] = $val;
			}
		}
		$data_string = substr( $data_string, 0, -1 );
		$signature = md5( $data_string );
		$pfError = false;
		if ( 0 != strcmp( $signature, $payload['signature'] ) ) {
			$pfError = true;
			$this->log( sprintf( 'ITN request failed, signature mismatch: %s', $payload ) );
		}

		$order = $this->get_order( $payment_token );

		$parameters = array(
			'conusmer_key' 	=> $this->options['conusmer_key'],
			'consumer_secret' => $this->options['consumer_secret'],
			'paybill_no'    => $this->options['paybill_no'],
			'trans_id'  => $payload['trans_id'],
			'order_id'  => $order['attendee_id'],
			'amount'		=> $order['total'] / 10
		);

		$mpesa = new Mpesa_Payment();
		$result = $mpesa->verify_request($parameters);

		// Verify IPN came from Mpesa
		if ( $result == 0 ) {
			switch ( $payload['payment_status'] ) {
				case "COMPLETE" :
					$this->payment_result( $payment_token, CampTix_Plugin::PAYMENT_STATUS_COMPLETED );
					break;
				case "FAILED" :
					$this->payment_result( $payment_token, CampTix_Plugin::PAYMENT_STATUS_FAILED );
					break;
				case "PENDING" :
					$this->payment_result( $payment_token, CampTix_Plugin::PAYMENT_STATUS_PENDING );
					break;
			}
		} else {
			$this->payment_result( $payment_token, CampTix_Plugin::PAYMENT_STATUS_PENDING );
		}

		$access_token = get_post_meta( $attendee->ID, 'tix_access_token', true );
			$url = add_query_arg( array(
				'tix_action' => 'access_tickets',
				'tix_access_token' => $access_token,
			), $camptix->get_tickets_url() );
			wp_safe_redirect( esc_url_raw( $url . '#tix' ) );
			die();
	}
	public function payment_checkout( $payment_token ) {
		if ( ! $payment_token || empty( $payment_token ) )
			return false;
		if ( ! in_array( $this->camptix_options['currency'], $this->supported_currencies ) )
			die( __( 'The selected currency is not supported by this payment method.', 'camptix' ) );
		$return_url = add_query_arg( array(
			'tix_action' => 'payment_return',
			'tix_payment_token' => $payment_token,
			'tix_payment_method' => 'camptix_mpesa',
		), $this->get_tickets_url() );
		$cancel_url = add_query_arg( array(
			'tix_action' => 'payment_cancel',
			'tix_payment_token' => $payment_token,
			'tix_payment_method' => 'camptix_mpesa',
		), $this->get_tickets_url() );
		$notify_url = add_query_arg( array(
			'tix_action' => 'payment_notify',
			'tix_payment_token' => $payment_token,
			'tix_payment_method' => 'camptix_mpesa',
		), $this->get_tickets_url() );
		$order = $this->get_order( $payment_token );
		$payload = array(
			// Merchant details
			'consumer_key' => $this->options['consumer_key'],
			'consumer_secret' => $this->options['consumer_secret'],
			'return_url' => $return_url,
			'cancel_url' => $cancel_url,
			'notify_url' => $notify_url,
			// Item details
			'm_payment_id' => $payment_token,
			'amount' => $order['total'],
			'item_name' => get_bloginfo( 'name' ) .' purchase, Order ' . $payment_token,
			'item_description' => sprintf( __( 'New order from %s', 'woothemes' ), get_bloginfo( 'name' ) ),
			// Custom strings
			'custom_str1' => $payment_token,
			'source' => 'WordCamp-CampTix-Plugin'
		);

		$mpesa_args_array = array();
		foreach ( $payload as $key => $value ) {
			$mpesa_args_array[] = '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}
		//$url = $this->options['sandbox'] ? '' : '';
		$client = new GuzzleHttp\Client();
		$url = 'consumer_key'.":".'consumer_secret';
		$credentials = base64_encode($url);

		// Create a POST request
		try {
		$response = $client->request(
			'GET',
			'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
			[
          'Authorization' => ['Basic '.$credentials ]
      ]
  );

  // Parse the response object, e.g. read the headers, body, etc.
  $headers = $response->getHeaders();
  $body = $response->getBody();

  // Output headers and body for debugging purposes
  var_dump($headers, $body);
}catch (Exception $e) {
		echo $e->getMessage();
}



		echo '<div id="tix">
					<form action="' . $client . '" method="post" id="mpesa_payment_form">
						' . implode( '', $mpesa_args_array ) . '
						<script type="text/javascript">
							document.getElementById("mpesa_payment_form").submit();
						</script>
					</form>
				</div>';
		return;
	}
	/**
	 * Runs when the user cancels their payment during checkout at Lipa na Mpesa.
	 * This will simply tell CampTix to put the created attendee drafts into to Cancelled state.
	 **/
	function payment_cancel() {
		global $camptix;
		$this->log( sprintf( 'Running payment_cancel. Request data attached.' ), null, $_REQUEST );
		$this->log( sprintf( 'Running payment_cancel. Server data attached.' ), null, $_SERVER );
		$payment_token = ( isset( $_REQUEST['tix_payment_token'] ) ) ? trim( $_REQUEST['tix_payment_token'] ) : '';
		if ( ! $payment_token )
			die( 'empty token' );
		// Set the associated attendees to cancelled.
		return $this->payment_result( $payment_token, CampTix_Plugin::PAYMENT_STATUS_CANCELLED );
	}

}




 ?>
