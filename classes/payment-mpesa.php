<?php

if ( !defined( 'ABSPATH' ) )
                exit; // Exit if accessed directly

class Mpesa_Camptix extends CampTix_Payment_Method
    {
        public $id = 'mpesa';
        public $name = 'Lipa Na Mpesa';
        public $description = 'Pay for your Ticket Using Mpesa Mobile Money.';
        public $supported_currencies = array( 'KES', 'USD' );
        
        /* Payment method options will be stored here.*/
        protected $options = array( );
            
        /*When the class is first initialized*/
        function camptix_init( )
                {
                        //    global $wp;
                        $url           = site_url() . '/transaction/';
                        $this->options = array_merge( array(
                                         'paybill_no' => '',
                                        'trans_url' => $url 
                        ), $this->get_payment_options() );
                        
                        
                        //add_action( 'template_redirect', array( $this, 'template_redirect' ) );
                }
        
        public function payment_checkout( $payment_token )
                {
                        
                        if ( !$payment_token || empty( $payment_token ) )
                                {
                                        //Return false if there is no payment token
                                        return false;
                                } //!$payment_token || empty( $payment_token )
                        
                        if ( !in_array( $this->camptix_options[ 'currency' ], $this->supported_currencies ) )
                                {
                                        die( __( 'The selected curency is not supported', 'camptix' ) );
                                } //!in_array( $this->camptix_options[ 'currency' ], $this->supported_currencies )
                        
                        /* Add a return url*/
                        $return_url = add_query_arg( array(
                                         'tix_action' => 'payment_return',
                                        'tix_payment_method' => 'camptix_mpesa',
                                        'tix_payment_token' => $payment_token 
                        ), $this->get_tickets_url() );
                        
                        /* Add a cancel url*/
                        $cancel_url = add_query_arg( array(
                                         'tix_action' => 'payment_cancel',
                                        'tix_payment_method' => 'camptix_mpesa',
                                        'tix_payment_token' => $payment_token 
                        ), $this->get_tickets_url() );
                        
                        /* Add a notify url*/
                        $notify_url = add_query_arg( array(
                                         'tix_action' => 'payment_notify',
                                        'tix_payment_method' => 'camptix_mpesa',
                                        'tix_payment_token' => $payment_token 
                        ), $this->get_tickets_url() );
                        
                        $info_url = add_query_arg( array(
                                         'tix_action' => 'payment_notify',
                                        'tix_payment_method' => 'camptix_mpesa',
                                        'tix_payment_token' => $payment_token 
                        ), $this->get_tickets_url() );
                        
                        //save the order in a variable
                        
                        $order   = $this->get_order( $payment_token );
                        /* Create a payload */
                        $payload = array(
                                        //Merchant details
                                         'paybill_no' => $this->options[ 'paybill_no' ],
                                        'info_url' => $info_url,
                                        'return_url' => $return_url,
                                        'cancel_url' => $cancel_url,
                                        
                                        //Items details
                                        'm_payment_id' => $payment_token,
                                        'amount' => $order[ 'total' ],
                                        'item_name' => get_bloginfo( 'name' ) . ' purchase, Order ' . $payment_token,
                                        
                                        
                                        //Any other custom string
                                        'source' => 'WordCamp-CampTix-Plugin' 
                                        
                                        //
                        );
                        
                        
                        /* Check if the sandbox option is enabled and load the sandbox
                         * credentials
                         */
                        
                        
                        //Tell user what to do
                        
                        $return = $this->show_payment_info();
                        
                        //$this->generateAothKey();
                        // var_dump($payload);
                        
                        //Communicate with safaricom here
                        
                        
                        return $return;
                }
        
        
        //Configures the payment method screen
        function payment_settings_fields( )
                {
                        $this->add_settings_field_helper( 'paybill_no', 'Business Pay Bill Number', array(
                                         $this,
                                        'field_text' 
                        ) );
                        $this->add_settings_field_helper( 'trans_url', 'Tranasaction callback url', array(
                                         $this,
                                        'field_text' 
                        ) );
                }
        
        // Called by CampTix When your payment methods are being called
        function validate_options( $input )
                {
                        $output = $this->options;
                        
                        
                        if ( isset( $input[ 'paybill_no' ] ) )
                                        $output[ 'paybill_no' ] = $input[ 'paybill_no' ];
                        
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
                function show_payment_info( $details )
                        {
                                $total = $details[ 'total' ];
                                //Guide the user on how to complete payment
                                //Format the message
                                $_POST['amount'] = $total;
                                $message = "You have chosen Lipa na Mpesa.<br>" . " 1. On your phone, go to Mpesa menu<br>" . "2. Go to <em>Lipa na mpesa.</em><br> " . "3. Go to <em>Buy Goods and Services</em><br>" . "4. Enter the <em>Till Number:</em> " . $this->options[ 'paybill_no' ] . "<br>5. Enter the amount: Ksh $total"<br>6. Enter <em>PIN</em> to complete transactions" . "<br>7. Wait for confirmation message<br>" . "<br>After you receive the confirmation message, please input your phone number below using you have made payment .</br>" . "<br><form action ='' method='POST'><input type='number' placeholder='Enter your mobile number' name='msisdn'><br>
        <input type='hidden'  name='newPost' value='" . base64_encode( serialize( $_POST ) ) . "'><br>" . "<br><button type='submit'>Verify Payment</button></form><br>";
                                
                                //."<form action=''>"
                                
                                $data = '<div id="tix" >' . $message . '</div>';
                                return $data;
                                
                                
                        }
                
                
                function fill_payload_with_order( &$payload, $order )
                        {
                                /** @var $camptix CampTix_Plugin */
                                global $camptix;
                                
                                $event_name = 'Event';
                                if ( isset( $this->camptix_options[ 'event_name' ] ) )
                                        {
                                                $event_name = $this->camptix_options[ 'event_name' ];
                                        } //isset( $this->camptix_options[ 'event_name' ] )
                                
                                $i = 0;
                                foreach ( $order[ 'items' ] as $item )
                                        {
                                                $payload[ 'L_PAYMENTREQUEST_0_NAME' . $i ]   = $camptix->substr_bytes( strip_tags( $event_name . ': ' . $item[ 'name' ] ), 0, 127 );
                                                $payload[ 'L_PAYMENTREQUEST_0_DESC' . $i ]   = $camptix->substr_bytes( strip_tags( $item[ 'description' ] ), 0, 127 );
                                                $payload[ 'L_PAYMENTREQUEST_0_NUMBER' . $i ] = $item[ 'id' ];
                                                $payload[ 'L_PAYMENTREQUEST_0_AMT' . $i ]    = $item[ 'price' ];
                                                $payload[ 'L_PAYMENTREQUEST_0_QTY' . $i ]    = $item[ 'quantity' ];
                                                $i++;
                                        } //$order[ 'items' ] as $item
                                
                                /** @todo add coupon/reservation as a note. **/
                                
                                $payload[ 'PAYMENTREQUEST_0_ITEMAMT' ]      = $order[ 'total' ];
                                $payload[ 'PAYMENTREQUEST_0_AMT' ]          = $order[ 'total' ];
                                $payload[ 'PAYMENTREQUEST_0_CURRENCYCODE' ] = $this->camptix_options[ 'currency' ];
                                
                                return $payload;
                        }
                
                public function sendAPIRequest( )
                        {
                                
                        }
                
                function processAPIRequest( )
                        {
                                //Process the response and return appropriate action
                                
                                
                        }
                
        }
