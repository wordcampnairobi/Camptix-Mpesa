<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// add this to use the Guzzle HTTP client library
$plugi_dir = plugin_dir_path(__FILE__);
include_once '/../vendor/autoload.php';
use GuzzleHttp\Client;

class Mpesa_Camptix extends CampTix_Payment_Method
{
	public $id = 'mpesa';
	public $name = 'Lipa Na Mpesa';
	public $description = 'Pay for your Ticket Using Mpesa Mobile Money.';
	public $supported_currencies = array( 'KES','USD');

	/* Payment method options will be stored here.*/
	protected $options = array();

	/*When the class is first initialized*/
        function camptix_init() {
		$this->options = array_merge( array(
			'consumer_key' => '',
			'consumer_secret' => '',
			'paybill_no' => '',
			'sandbox' => true
		), $this->get_payment_options() );
                
                
                //add_action( 'template_redirect', array( $this, 'template_redirect' ) );
	}
        
        public function payment_checkout($payment_token){
            
            if (!$payment_token || empty($payment_token)){
                //Return false if there is no payment token
                return false;
            }
                       
            if (!in_array(
                    $this->camptix_options['currency'],
                    $this->supported_currencies)){
                        die(__('The selected curency is not supported','camptix'));
            }
            
            /* Add a return url*/
            $return_url = add_query_arg(array(
                'tix_action' => 'payment_return',
                'tix_payment_method'=>'camptix_mpesa',
                'tix_payment_token'=>$payment_token,
            ), $this->get_tickets_url());
            
            /* Add a cancel url*/
            $cancel_url = add_query_arg(array(
                'tix_action' => 'payment_cancel',
                'tix_payment_method'=>'camptix_mpesa',
                'tix_payment_token'=>$payment_token,
            ), $this->get_tickets_url());
            
            /* Add a notify url*/
            $notify_url = add_query_arg(array(
                'tix_action' => 'payment_notify',
                'tix_payment_method'=>'camptix_mpesa',
                'tix_payment_token'=>$payment_token,
            ), $this->get_tickets_url());
            
            $info_url = add_query_arg(array(
                'tix_action' => 'payment_notify',
                'tix_payment_method'=>'camptix_mpesa',
                'tix_payment_token'=>$payment_token,
            ), $this->get_tickets_url());
            
            //save the order in a variable
            
            $order = $this->get_order($payment_token);
            
            /* Create a payload */
            $payload = array(
                //Merchant details
                'consumer_key' => $this->options['consumer_key'],
                'consumer_secret'=> $this->options['consumer_secret'],
                'paybill_no' => $this->options['paybill_no'],
                'info_url'=>$info_url,
                'return_url'=>$return_url,
                'cancel_url'=>$cancel_url,
                
                //Items details
                'm_payment_id'=>$payment_token,
                'amount' => $order['total'],
                'item_name' => get_bloginfo('name'). ' purchase, Order '.$payment_token,
                
                
                //Any other custom string
                'source'=> 'WordCamp-CampTix-Plugin',
                
                //
            );
            
            
            /* Check if the sandbox option is enabled and load the sandbox 
             * credentials
             */
            
            if($this->options['sandbox']){
                $payload['consumer_key'] = 'B5pGWsCLYZmoYz9iRQeWnijdRQlw29Ph';
                $payload['consumer_secret'] = 'B5pGWsCLYZmoYz9iRQeWnijdRQlw29Ph';
            }
            
            //Tell user what to do
            
            $this->show_payment_info();
            
            //$this->generateAothKey();
           // var_dump($payload);

            //Communicate with safaricom here
            
            
            return;
        }
            
            
        //Configures the payment method screen
        function payment_settings_fields(){
            $this->add_settings_field_helper( 'consumer_key', __( 'consumer key', 'API Consumer Key' ), array( $this, 'field_text' ) );
            $this->add_settings_field_helper( 'consumer_secret', __( 'consumer secret', 'API Consumer Secret' ), array( $this, 'field_text' ) );
            $this->add_settings_field_helper( 'paybill_no','Business Pay Bill Number', array( $this, 'field_text' ) );
            $this->add_settings_field_helper( 'sandbox', __( 'Sandbox', 'Developer Sandbox' ), array( $this, 'field_yesno' ) );
        }
        
        // Called by CampTix When your payment methods are being called
        function validate_options( $input ) {
		$output = $this->options;

		if ( isset( $input['consumer_key'] ) )
			$output['consumer_key'] = $input['consumer_key'];

		if ( isset( $input['consumer_secret'] ) )
			$output['consumer_secret'] = $input['consumer_secret'];

		if ( isset( $input['sandbox'] ) )
			$output['sandbox'] = (bool) $input['sandbox'];

		return $output;
	}
        
        
  
        


        /* Mpesa validation and verification should begin here*/
        
        //Steps:
        
        /**
         * Get what the user has bought
         * Proceed to checkout
         * 
         */
        
        /**
         * During checkout:
         * ===============
         * connect to mpesa using the mpesa api
         * request for an authentication access token
         * recieve the authentication token
         * 
         * check for payment confirmation
         * user entering his number
         * 
        */
       function show_payment_info(){
           //Guide the user on how to complete payment
           //Format the message
           
           $message = "You have chosen Lipa na Mpesa.<br>"
                   ." 1. On your phone, go to Mpesa menu<br>"
                   . "2. Go to <em>Lipa na mpesa.</em><br> "
                   . "3. Go to <em>Buy Goods and Services</em><br>"
                   . "4. Enter the <em>Till Number:</em>". $this->options['paybill_no']
                   . "<br>5. Enter the amount:"
                   . "<br>6. Enter <em>PIN</em> to complete transactions"
                   . "<br>7. Wait for confirmation message<br>";
           
           
           
           echo '<div id="tix" class="tix-info"';
           echo $message;
           echo '</div>';
           
           
       }
       
       public function generateAothKey(){
           //Generate the access token for the session

            

            define('CONSUMER_KEY', 'B5pGWsCLYZmoYz9iRQeWnijdRQlw29Ph');
            define('SECRET_KEY', 'InAVKm8MNJpqHQ5C');
            //define('SECRET_KEY', 'InAVKm8MN');

            $data = CONSUMER_KEY.":".SECRET_KEY;

            $credentials = base64_encode($data);

            $API_url = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";

            $client = new Client();

            $response = $client->request('GET', $API_url, [
                'headers' => [
                    'User-Agent' => 'testing/1.0',
                    'Accept'     => 'application/json',
                    'Authorization'      => ['Basic '.$credentials]
                ]
            ]);
            //$client->request('GET', 'https://sandbox.safaricom.co.ke/oauth/v1/generate', [
            //    'query' => ['foo' => 'bar']
            //]);

            //var_dump($response);
            $code = $response->getStatusCode(); // 200
            $reason = $response->getReasonPhrase(); // OK

              // Parse the response object, e.g. read the headers, body, etc.
              $headers = $response->getHeaders();
              $body = $response->getBody();
              // Output headers and body for debugging purposes


              foreach ($response->getHeaders() as $name => $values) {
                echo $name . ': ' . implode(', ', $values) . "\r\n";
            }
           
       }
       
       public function sendAPIRequest(){
           
       }
       
       function processAPIRequest(){
           //Process the response and return appropriate action
           
    
       }
        
}
