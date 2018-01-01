<?php
/**
 * Tables API
 *
 * @package Amazon affiliate products
 * @subpackage Amz_Table_Api
 * @author Toni Chaz
 * @since 1.0.7
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if ( ! class_exists( 'Amz_Table_Api' ) ) {
	class Amz_Table_Api {

		private $amz_utils;
		private $amz_db;

		/**
		 * Construct
		 */
		public function __construct() {

			add_action( 'admin_enqueue_scripts', array( $this, 'init_enqueue' ) );
			add_action( 'wp_ajax_delete_table', array( $this, 'delete_table_callback' ) );
			add_action( 'wp_ajax_save_table', array( $this, 'save_table_callback' ) );
			add_action( 'wp_ajax_update_table', array( $this, 'update_table_callback' ) );

			$this->amz_utils = new Amz_Utils();
			$this->amz_db    = new Amz_Db();

		} // End public function __construct

		/**
		 * Init enqueue
		 *
		 * @param $hook
		 *
		 * @void
		 */
		public function init_enqueue( $hook ) {
			if ( 'index.php' != $hook ) {
				// Only applies to dashboard panel
				return;
			}

			// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
			wp_localize_script(
				'ajax-script',
				'ajax_object',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' )
				)
			);
		}

		/**
		 * Find all products from database
		 *
		 * @return array or null
		 */
		public function find_tables() {

			$result = $this->amz_db->find_all( AMZ_TABLES_TABLE );

			if ( $result ) {

				foreach ( $result as $table ) {
					$unserialize_body  = unserialize( $table->table_body );
					$unserialize_head  = unserialize( $table->table_head );
					$table->table_body = $this->amz_utils->setElementsOnTable( $unserialize_body, $unserialize_head, true );
					$table->table_head = $unserialize_head;
				}

				return $result;
			} else {
				return null;
			}


		} // End public function find_tables()

		/**
		 * Save the product callback
		 *
		 * @void
		 */
		public function save_table_callback() {

			if ( ! empty( $_POST['table_body'] ) && ! empty( $_POST['table_head'] ) ) {

				$table_head     = $_POST['table_head'];
				$table_body     = $this->amz_utils->cleanTableBody( $_POST['table_body'] );
				$new_table_body = $this->amz_utils->setElementsOnTable( $table_body, $table_head );
				$time           = new DateTime();
				$time->setTimezone( new DateTimeZone( 'Europe/Madrid' ) );
				$time_formatted = $time->format( 'Y-m-d H:i:s' );

				$data = array(
					'table_time' => $time_formatted,
					'table_body' => serialize( $new_table_body ),
					'table_head' => serialize( $table_head ),
					'table_name' => $_POST['table_name']
				);

				$result = $this->amz_db->save( AMZ_TABLES_TABLE, $data );

				if ( $result ) {
					$data['table_head'] = $table_head;
					$data['table_body'] = $new_table_body;
					$data['itemId']     = $result;

					Amz_Utils::returnGoodRequest( 'Your table has been saved.', $data );
				} else {
					Amz_Utils::setLog( 'Table with id ' . $result . ' error on save ' . $result );
					Amz_Utils::returnBadRequest( 'An error occurred, please try again letter.' );
				}

			} else {
				Amz_Utils::returnBadRequest( 'The table data is undefined' );
			}

		}

		/**
		 * Delete the product callback
		 *
		 * @void
		 */
		public function delete_table_callback() {

			if ( ! empty( $_POST['table_id'] ) ) {

				$result = $this->amz_db->delete( AMZ_TABLES_TABLE, 'table_id', $_POST['table_id'] );

				if ( $result ) {
					Amz_Utils::returnGoodRequest( 'Your table has been deleted.' );
				} else {
					Amz_Utils::setLog( 'Table with id ' . $_POST['table_id'] . ' error on delete ' . $result );
					Amz_Utils::returnBadRequest( 'An error occurred, please try again letter.' );
				}

			} else {
				Amz_Utils::returnBadRequest( 'The ID of table is undefined' );
			}

		}

		/**
		 * Update the table callback
		 *
		 * @void
		 */
		public function update_table_callback() {

			if ( ! empty( $_POST['table_id'] ) && ! empty( $_POST['table_head'] ) && ! empty( $_POST['table_body'] ) ) {

				$table_head     = $_POST['table_head'];
				$table_body     = $this->amz_utils->cleanTableBody( $_POST['table_body'] );
				$new_table_body = $this->amz_utils->setElementsOnTable( $table_body, $table_head );
				$time           = new DateTime();
				$time->setTimezone( new DateTimeZone( 'Europe/Madrid' ) );
				$time_formatted = $time->format( 'Y-m-d H:i:s' );


				$data = array(
					'table_time' => $time_formatted,
					'table_body' => serialize( $new_table_body ),
					'table_head' => serialize( $table_head ),
					'table_name' => $_POST['table_name']
				);

				$result = $this->amz_db->update( AMZ_TABLES_TABLE, $data, 'table_id', $_POST['table_id'] );

				if ( $result ) {
					$data['table_head'] = $table_head;
					$data['table_body'] = $new_table_body;

					Amz_Utils::returnGoodRequest( 'Your table has been updated.', $data );
				} else {
					Amz_Utils::setLog( 'Table with id ' . $_POST['table_id'] . ' error on update ' . $result );
					Amz_Utils::returnBadRequest( 'An error occurred. Can&#8217;t save in database.' );
				}

			} else {
				Amz_Utils::returnBadRequest( 'The table id or data is undefined' );
			}

		}


	} // End class Amz_Table_Api
} // End if(!class_exists('Amz_Table_Api'))

