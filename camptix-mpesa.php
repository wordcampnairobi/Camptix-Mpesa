
<?php
/**
* Plugin Name: CampTix Mpesa Payment gateway
* Plugin URI: https://nabaleka.com
* Description: Mpesa Payment Gateway for CampTix
* Author: Emmanuel Ammanulah
* Author URI: https://nabaleka.com
* Version: 1.0.0
* Licence: MIT
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Add KES currency
add_filter( 'camptix_currencies', 'camptix_add_kes_currency' );
function camptix_add_kes_currency( $currencies ) {
  $currencies['KES'] = array(
    'label' => __( 'Kenyan Shilling', 'camptix' ),
    'format' => 'Ksh %s',
  );
  return $currencies;
}

// Load the Mpesa Payment Method
add_action( 'camptix_load_addons', 'camptix_mpesa_load_payment_method' );
function camptix_mpesa_load_payment_method() {
  if ( ! class_exists( 'Mpesa_Camptix' ) )
    require_once plugin_dir_path( __FILE__ ) . 'classes/payment-mpesa.php';
  camptix_register_addon( 'Mpesa_Camptix' );
}

 ?>
