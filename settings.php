<?php
/**
 * Settings
 *
 * @package Amazon affiliate products
 * @subpackage Amz_Settings
 * @author Toni Chaz
 * @since 1.0.0
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if ( ! class_exists( 'Amz_Settings' ) ) {
	class Amz_Settings {
		/**
		 * Construct the plugin object
		 */
		public function __construct() {
			// register actions
			add_action( 'admin_menu', array( $this, 'add_menu' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'load_front_resources' ) );
		} // End public function __construct

		/**
		 * add a menu
		 *
		 * @return void
		 */
		public function add_menu() {
			add_menu_page(
				'Amazon Affiliate | Products',
				'Products',
				'manage_options',
				'amz-affiliate/pages/product.php',
				array( &$this, 'load_product_page' ),
				plugins_url( 'amz-affiliate/img/icon.png' ),
				50
			);
			add_submenu_page(
				'amz-affiliate/pages/product.php',
				'Amazon Affiliate | Tables',
				'Tables',
				'manage_options',
				'amz-affiliate/pages/table.php',
				array( &$this, 'load_table_page' )
			);
		} // End public function add_menu()

		/**
		 * Menu Callback
		 *
		 * @return void
		 */
		public function load_product_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}

			// Render the settings template
			require_once( dirname( __FILE__ ) . '/pages/product.php' );
			wp_enqueue_script( 'amz-commons', '/wp-content/plugins/amz-affiliate/js/commons.js', 'jquery' );
			wp_enqueue_script( 'amz-product', '/wp-content/plugins/amz-affiliate/js/product.js', 'jquery' );

		} // End public function load_product_page()

		public function load_table_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}

			// Render the settings template
			require_once( dirname( __FILE__ ) . '/pages/table.php' );
			wp_enqueue_script( 'data-tables', '/wp-content/plugins/amz-affiliate/js/vendor/data-tables.js', 'jquery' );
			wp_enqueue_script( 'amz-commons', '/wp-content/plugins/amz-affiliate/js/commons.js', 'jquery' );
			wp_enqueue_script( 'amz-table', '/wp-content/plugins/amz-affiliate/js/table.js', 'jquery' );
			wp_enqueue_style( 'amz-tables-styles', '/wp-content/plugins/amz-affiliate/css/table-admin.css' );
			wp_enqueue_style( 'data-tables-styles', '/wp-content/plugins/amz-affiliate/css/vendor/data-tables.css' );

		} // End public function load_table_page()

		public function load_front_resources() {
			global $post;

			$amz_db      = new Amz_Db();
			$tables      = $amz_db->find_all( AMZ_TABLES_TABLE );

			if ( $tables ) {

				foreach ( $tables as $table ) {
					if ( has_shortcode( $post->post_content, 'amztable_' . $table->table_id ) ) {
						wp_enqueue_script( 'data-tables', '/wp-content/plugins/amz-affiliate/js/vendor/data-tables.js', 'jquery' );
						wp_enqueue_script( 'amz-table', '/wp-content/plugins/amz-affiliate/js/amz-tables.js', 'jquery' );
						wp_enqueue_style( 'data-tables-styles', '/wp-content/plugins/amz-affiliate/css/vendor/data-tables.css' );
						wp_enqueue_style( 'amz-tables-styles', '/wp-content/plugins/amz-affiliate/css/amz-tables.css' );
						break;
					}
				}
			}

		} // End public function load_table_page()
	} // End class Amz_Settings
} // End if(!class_exists('Amz_Settings'))