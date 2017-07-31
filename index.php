<?php
file_put_contents( './aMget.txt', 'POST-:' . print_r( $_POST, true ) . "\n", FILE_APPEND );

include( '../wp-config.php' );


function connectDB( )
        {
                $link = mysqli_connect( DB_HOST, DB_USER, DB_PASSWORD );
                if ( !$link )
                        {
                                die( 'Could not connect: ' . mysqli_error() );
                        } //!$link
                mysqli_select_db( $link, DB_NAME );
                return $link;
        }

function logTransation( $sender_phone, $amount, $service_name, $business_number, $transaction_reference, $internal_transaction_id, $transaction_type, $account_number, $first_name, $middle_name, $last_name )
        {
                
                $link                    = connectDB();
                $sender_phone            = mysqli_real_escape_string( $link, $sender_phone );
                $amount                  = mysqli_real_escape_string( $link, $amount );
                $business_number         = mysqli_real_escape_string( $link, $business_number );
                $transaction_reference   = mysqli_real_escape_string( $link, $transaction_reference );
                $internal_transaction_id = mysqli_real_escape_string( $link, $internal_transaction_id );
                $transaction_type        = mysqli_real_escape_string( $link, $transaction_type );
                $account_number          = mysqli_real_escape_string( $link, $account_number );
                $first_name              = mysqli_real_escape_string( $link, $first_name );
                $middle_name             = mysqli_real_escape_string( $link, $middle_name );
                $last_name               = mysqli_real_escape_string( $link, $last_name );
                
                $query  = "insert into wp_transation (sender_phone, amount, service_name, business_number, transaction_reference, internal_transaction_id, transaction_type, account_number, first_name, middle_name, last_name, transacton_date) values('$sender_phone', '$amount', '$service_name', '$business_number', '$transaction_reference', '$internal_transaction_id', '$transaction_type', '$account_number', '$first_name', '$middle_name', '$last_name', sysdate())";
                $result = mysqli_query( $link, $query );
                return $result;
        }

$sender_phone            = $_POST[ 'sender_phone' ];
$amount                  = $_POST[ 'amount' ];
$service_name            = $_POST[ 'service_name' ];
$business_number         = $_POST[ 'business_number' ];
$transaction_reference   = $_POST[ 'transaction_reference' ];
$internal_transaction_id = $_POST[ 'internal_transaction_id' ];
$transaction_type        = $_POST[ 'transaction_type' ];
$account_number          = $_POST[ 'account_number' ];
$first_name              = $_POST[ 'first_name' ];
$middle_name             = $_POST[ 'middle_name' ];
$last_name               = $_POST[ 'last_name' ];

logTransation( $sender_phone, $amount, $service_name, $business_number, $transaction_reference, $internal_transaction_id, $transaction_type, $account_number, $first_name, $middle_name, $last_name );

file_put_contents( './aMget.txt', "[$sender_phone][$amount][$service_name][$business_number][$transaction_reference][$internal_transaction_id][$transaction_type][$account_number][$first_name][$middle_name][$last_name]\n", FILE_APPEND );
?>
