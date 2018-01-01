<?php
/**
 * Operations Data Base
 *
 * @package Amazon affiliate products
 * @subpackage Amz_db
 * @author Toni Chaz
 * @since 1.0.0
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if ( ! class_exists( 'Amz_Db' ) ) {
	class Amz_Db {
		/**
		 * Construct
		 */
		public function __construct() { } // End public function __construct

		/**
		 * Find all in database
		 *
		 * @param $table_name
		 *
		 * @return array
		 */
		public function find_all( $table_name ) {
			global $wpdb;

			$table_name = $wpdb->prefix . $table_name;
			$results    = $wpdb->get_results( "SELECT * FROM $table_name" );

			return $results;
		}

		/**
		 * Find one in database
		 *
		 * @param $table_name
		 * @param $column_name
		 * @param $value
		 *
		 * @return array
		 */
		public function find( $table_name, $column_name, $value ) {
			global $wpdb;

			$table_name = $wpdb->prefix . $table_name;
			$results    = $wpdb->get_row( "select * from $table_name where $column_name='$value'" );

			return $results;
		}

		/**
		 * Custom query in database
		 *
		 * @param $query
		 * @param $table_name
		 * @param $column_name
		 * @param $value
		 *
		 * @return array
		 */
		public function query( $query, $table_name, $column_name = null, $value = null ) {
			global $wpdb;

			$table_name = $wpdb->prefix . $table_name;
			$results    = null;

			if ( is_null( $column_name ) && is_null( $value ) ) {
				$results = $wpdb->get_results( "select $query from $table_name", ARRAY_A );
			} else {
				$results = $wpdb->get_results( "select $query from $table_name where $column_name='$value'" );
			}

			return $results;
		}

		/**
		 * Save in database
		 *
		 * @param $table_name
		 * @param $table_data
		 *
		 * @return boolean
		 */
		public function save( $table_name, $table_data ) {
			global $wpdb;

			$table_name = $wpdb->prefix . $table_name;

			$wpdb->insert(
				$table_name,
				$table_data
			);


			return $wpdb->insert_id;
		}

		/**
		 * Delete in database
		 *
		 * @param $table_name
		 * @param $column_name
		 * @param $value
		 *
		 * @return boolean
		 */
		public function delete( $table_name, $column_name, $value ) {
			global $wpdb;

			$table_name = $wpdb->prefix . $table_name;
			$result = null;

			if ( is_array( $value ) ) {
				$ids = implode( ',', array_map( 'absint', $value ) );
				$result = $wpdb->query( "DELETE FROM $table_name WHERE $column_name IN($ids)" );
			} else {
				$result = $wpdb->delete( $table_name, array( $column_name => $value ) );
			}

			return $result;
		}

		/**
		 * Update the product in database
		 *
		 * @param $table_name
		 * @param $table_data
		 * @param $column_name
		 * @param $value
		 *
		 * @return boolean
		 */
		public function update( $table_name, $table_data, $column_name, $value ) {

			global $wpdb;

			$table_name = $wpdb->prefix . $table_name;

			$result = $wpdb->update(
				$table_name,
				$table_data,
				array( $column_name => $value )
			);

			return $result;
		}

		/**
		 * Update once in database
		 *
		 * @param $table_name
		 * @param $table_data
		 * @param $column_name
		 * @param $value
		 *
		 * @return boolean
		 */
		public function update_once( $table_name, $table_data, $column_name, $value ) {

			global $wpdb;

			$table_name = $wpdb->prefix . $table_name;

			$result = $wpdb->update(
				$table_name,
				$table_data,
				array( $column_name => $value )
			);

			return $result;
		}

	} // End class Amz_Db
} // End if(!class_exists('Amz_Db'))

