<?php
/**
 * Plugin Name: Amazon affiliate products
 * Plugin URI: http://www.tonichaz.com
 * Description: This plugin add products of amazon and updates the prices for affiliates
 * Version: 1.0.7
 * Author: Toni Chaz
 * Author URI: http://www.tonichaz.com
 * License: MIT
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

//TODO make config.php as options to save in database
require_once( dirname( __FILE__ ) . '/config.php' );
require_once( 'classes/amazon-api.php' );
require_once( 'classes/db.php' );
require_once( 'classes/utils.php' );

if ( ! class_exists( 'Amz_Affiliate' ) ) {
	class Amz_Affiliate {

		/**
		 * Construct the plugin object
		 */
		public function __construct() {
			// Initialize Settings
			require_once( dirname( __FILE__ ) . '/settings.php' );
			new Amz_Settings();

			// Initialize Apis
			require_once( dirname( __FILE__ ) . '/classes/product-api.php' );
			new Amz_Product_Api();

			// Initialize Apis
			require_once( dirname( __FILE__ ) . '/classes/table-api.php' );
			new Amz_Table_Api();

			// Initialize Apis
			require_once( dirname( __FILE__ ) . '/classes/short-code.php' );
			new Amz_ShortCode();

			// Initialize Cron
			require_once( dirname( __FILE__ ) . '/classes/amz-cron.php' );
			new Amz_Cron();

		} // End public function __construct

		/**
		 * Activate the plugin
		 *
		 * @return void
		 */
		public static function activate() {
			Amz_Utils::setLog('AMZ plugin activate');

			/**
			 * Create de table in database.
			 */
			global $wpdb;
			$wpdb->show_errors();
			$amz_products_table = $wpdb->prefix . AMZ_PRODUCTS_TABLE;
			$amz_tables_table   = $wpdb->prefix . AMZ_TABLES_TABLE;
			$haveProductsTable  = $wpdb->get_var( "show tables like '$amz_products_table'" ) != $amz_products_table;
			$haveTablesTable    = $wpdb->get_var( "show tables like '$amz_tables_table'" ) != $amz_tables_table;


			// create the products and tables database tables
			if ( $haveProductsTable && $haveTablesTable ) {
				$charset_collate = $wpdb->get_charset_collate();

				$productsSQL = "CREATE TABLE " . $amz_products_table . " (
                  product_id mediumint(9) NOT NULL AUTO_INCREMENT,
                  product_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                  product_asin tinytext NOT NULL,
                  product_price tinytext NOT NULL,
                  product_data text NOT NULL,
                  product_link text NOT NULL
                  PRIMARY KEY  (product_id)
                ) " . $charset_collate . ";";

				$tablesSQL = "CREATE TABLE " . $amz_tables_table . " (
                  table_id mediumint(9) NOT NULL AUTO_INCREMENT, 
                  table_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                  table_body text NOT NULL,
                  table_head text NOT NULL,
                  table_name text NULL,
                  PRIMARY KEY  (table_id)
                ) " . $charset_collate . ";";

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

				dbDelta( $productsSQL );
				dbDelta( $tablesSQL );

				Amz_Utils::setLog('AMZ Database created');

				if ( $wpdb->last_error ) {
					Amz_Utils::setLog('WP Database Error ' . $wpdb->last_error);
					new WP_Error( $wpdb->last_error );
				};
			}

			$wpdb->hide_errors();

			//Amz_Cron::activated();

		} // End public static function activate

		/**
		 * Deactivate the plugin
		 *
		 * @return void
		 */
		public static function deactivate() {
			Amz_Cron::deactivated();
			Amz_Utils::setLog('AMZ plugin deactivate');
			// Do nothing
			// TODO Delete table from database if user check it
		} // End public static function deactivate
	} // End class Amz_Affiliate
} // End if(!class_exists('Amz_Affiliate'))

if ( class_exists( 'Amz_Affiliate' ) ) {
	// Installation and uninstallation hooks
	register_activation_hook( __FILE__, array( 'Amz_Affiliate', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'Amz_Affiliate', 'deactivate' ) );

	// instantiate the plugin class
	new Amz_Affiliate();
}
