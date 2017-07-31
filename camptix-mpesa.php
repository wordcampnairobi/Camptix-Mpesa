<?php
/**
 * Plugin Name: CampTix Mpesa Payment gateway
 * Plugin URI: https://nabaleka.com
 * Description: Mpesa Payment Gateway for CampTix
 * Author: Emmanuel Ammanulah
 * Author URI: https://nabaleka.com
 * Version: 1.0.0
 * Licence: GPLv3
 */

if ( !defined( 'ABSPATH' ) )
                exit; // Exit if accessed directly
register_activation_hook( __FILE__, 'camptix_db_install' );
global $camptix_db_version;
$camptix_db_version = 38590;

// Add KES currency
add_filter( 'camptix_currencies', 'camptix_add_kes_currency' );
function camptix_add_kes_currency( $currencies )
        {
                $currencies[ 'KES' ] = array(
                                 'label' => __( 'Kenyan Shilling', 'camptix' ),
                                'format' => 'Ksh %s' 
                );
                return $currencies;
        }

// Load the Mpesa Payment Method
add_action( 'camptix_load_addons', 'camptix_mpesa_load_payment_method' );
function camptix_mpesa_load_payment_method( )
        {
                if ( !class_exists( 'Mpesa_Camptix' ) )
                        {
                                
                        } //!class_exists( 'Mpesa_Camptix' )
                
                require_once dirname( __FILE__ ) . '/classes/payment-mpesa.php';
                
                camptix_register_addon( 'Mpesa_Camptix' );
        }

function camptix_db_install( )
        {
                global $wpdb, $camptix_db_version;
                
                $table_name      = $wpdb->prefix . "transation";
                $charset_collate = $wpdb->get_charset_collate();
                $sql             = "CREATE TABLE $table_name (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `sender_phone` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `amount` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `service_name` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `business_number` bigint(20) DEFAULT NULL,
    `transaction_reference` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `internal_transaction_id` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `transaction_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `account_number` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `first_name` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `middle_name` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `last_name` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `transacton_date` datetime DEFAULT NULL,
    `status` varchar(20) COLLATE utf8mb4_unicode_520_ci DEFAULT 'UNVERIFIED',
    PRIMARY KEY (`id`)
    )$charset_collate;";
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta( $sql );
                add_option( "camptix_db_version", $camptix_db_version );
                $path = realpath( __DIR__ . '/../../..' ) . '/transaction/';
                mkdir( $path, 0777, true );
                $file    = dirname( __FILE__ ) . '/index.php';
                $newfile = $path . 'index.php';
                copy( $file, $newfile );
        }
