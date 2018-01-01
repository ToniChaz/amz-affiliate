<?php
/**
 * Products API
 *
 * @package Amazon affiliate products
 * @subpackage Amz_Product_Api
 * @author Toni Chaz
 * @since 1.0.7
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if ( ! class_exists( 'Amz_Product_Api' ) ) {
	class Amz_Product_Api {

		private $amz_utils;
		private $amz_db;
		private $amz_amazon_api;

		/**
		 * Construct
		 */
		public function __construct() {

			add_action( 'admin_enqueue_scripts', array( $this, 'init_enqueue' ) );
			add_action( 'wp_ajax_find_product', array( $this, 'find_amazon_product_callback' ) );
			add_action( 'wp_ajax_save_product', array( $this, 'save_product_callback' ) );
			add_action( 'wp_ajax_delete_product', array( $this, 'delete_product_callback' ) );
			add_action( 'wp_ajax_update_product', array( $this, 'update_product_callback' ) );

			$this->amz_utils      = new Amz_Utils();
			$this->amz_db         = new Amz_Db();
			$this->amz_amazon_api = new Amz_Amazon_Api();

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
		 * Find a product by asin code
		 *
		 * @void
		 */
		public function find_amazon_product_callback() {

			if ( ! empty( $_POST['product_asin'] ) ) {

				$url = $this->amz_amazon_api->generate_amazon_url( $_POST['product_asin'] );


				$response = wp_remote_get( $url );

				if ( $response['response']['code'] === 200 ) {
					$simple_xml = simplexml_load_string( $response['body'] );

					if ( isset( $simple_xml->Items->Request->Errors ) ) {
						$error_message = $simple_xml->Items->Request->Errors->Error->Message->__toString();
						Amz_Utils::setLog( 'Find product error ' . $error_message );
						Amz_Utils::returnBadRequest( $error_message );
					}

					$this->amz_utils->setXML_product_data( $simple_xml->Items->Item );
					$json = $this->amz_utils->getJSON_product_data();
					Amz_Utils::returnGoodRequest( 'We find your product.', $json );
				} else {
					Amz_Utils::setLog( 'Service error: [' . $response['response']['code'] . '] ' . $response['response']['message'] );
					Amz_Utils::returnBadRequest( 'Service error: [' . $response['response']['code'] . '] ' . $response['response']['message'] );
				}
			} else {
				Amz_Utils::returnBadRequest( 'The product ASIN code is undefined' );
			}

		} // End public function find_product()

		/**
		 * Find all products from database
		 *
		 * @return array or null
		 */
		public function find_products() {

			$result = $this->amz_db->find_all( AMZ_PRODUCTS_TABLE );

			if ( $result ) {
				$products = array();

				foreach ( $result as $key => $value ) {
					$parsed_data       = json_decode( $value->product_data, true );
					$product           = new stdClass();
					$product->id       = $value->product_id;
					$product->asin     = $value->product_asin;
					$product->data     = $parsed_data;
					$product->price    = $value->product_price;
					$product->time     = $value->product_time;
					$product->link     = $value->product_link;
					array_push( $products, $product );
				}

				return $products;
			} else {
				return null;
			}


		} // End public function find_products()

		/**
		 * Save the product callback
		 *
		 * @return void
		 */
		public function save_product_callback() {

			if ( ! empty( $_POST['product_asin'] ) ) {

				$product_asin = $_POST['product_asin'];

				$JSON_product_data = $this->amz_utils->getJSON_product_data();
				$this->amz_utils->deleteJSON_product_data();

				$current_product = json_decode( $JSON_product_data, true );
				$product_price   = $this->amz_utils->getProductPrice( $current_product );

				$product_link = 'https://www.amazon.es/gp/product/' . $product_asin . '/ref=as_li_tf_tl?ie=UTF8&camp=3626&creative=24790&creativeASIN=' . $product_asin . '&linkCode=as2&tag=' . AFFILIATE_ID;

				$time = new DateTime();
				$time->setTimezone( new DateTimeZone( 'Europe/Madrid' ) );
				$time_formatted = $time->format( 'Y-m-d H:i:s' );

				$product_exist = $this->amz_db->find( AMZ_PRODUCTS_TABLE, 'product_asin', $product_asin );

				$result = null;

				if ( $product_exist == null ) {
					$data = array(
						'product_asin'  => $product_asin,
						'product_time'  => $time_formatted,
						'product_data'  => $JSON_product_data,
						'product_price' => $product_price,
						'product_link'  => stripslashes( $product_link )
					);

					$result = $this->amz_db->save( AMZ_PRODUCTS_TABLE, $data );

				} else {
					Amz_Utils::returnBadRequest( 'This product already exist in your database.' );
				}

				if ( $result ) {
					Amz_Utils::returnGoodRequest( 'Your product has been saved.' );
				} else {
					Amz_Utils::setLog( 'Product with ASIN ' . $product_asin . ' error on save ' . $result );
					Amz_Utils::returnBadRequest( 'An error occurred, please try again letter.' );
				}

			} else {
				Amz_Utils::returnBadRequest( 'The current product is undefined' );
			}

		}

		/**
		 * Delete the product callback
		 *
		 * @void
		 */
		public function delete_product_callback() {

			if ( ! empty( $_POST['product_id'] ) ) {

				$result = $this->amz_db->delete( AMZ_PRODUCTS_TABLE, 'product_id', $_POST['product_id'] );

				if ( $result ) {
					Amz_Utils::returnGoodRequest( 'Your product/s has been deleted.' );
				} else {
					Amz_Utils::setLog( 'Product with id ' . $_POST['product_id'] . ' error on delete ' . $result );
					Amz_Utils::returnBadRequest( 'An error occurred, please try again letter.' );
				}

			} else {
				Amz_Utils::returnBadRequest( 'The ID of product is undefined' );
			}

		}

		/**
		 * Update the product callback
		 *
		 * @void
		 */
		public function update_product_callback() {

			if ( ! empty( $_POST['product_asin'] ) ) {

				$url = $this->amz_amazon_api->generate_amazon_url( $_POST['product_asin'] );

				$response = wp_remote_get( $url );

				if ( $response['response']['code'] === 200 ) {
					$simple_xml = simplexml_load_string( $response['body'] );

					if ( isset( $simple_xml->Items->Request->Errors ) ) {
						$error_message = $simple_xml->Items->Request->Errors->Error->Message->__toString();
						Amz_Utils::setLog( 'Find product error ' . $error_message );
						Amz_Utils::returnBadRequest( $error_message );
					}

					$current_product = json_encode( $simple_xml->Items->Item );
					$product_price   = $this->amz_utils->getProductPrice( json_decode( $current_product, true ) );
					if ( $current_product ) {

						$time = new DateTime();
						$time->setTimezone( new DateTimeZone( 'Europe/Madrid' ) );
						$time_formatted = $time->format( 'Y-m-d H:i:s' );

						$data = array(
							'product_data'  => $current_product,
							'product_price' => $product_price,
							'product_time'  => $time_formatted
						);

						$result = $this->amz_db->update( AMZ_PRODUCTS_TABLE, $data, 'product_asin', $_POST['product_asin'] );

						if ( $result ) {
							Amz_Utils::returnGoodRequest( 'Your product has been updated.' );
						} else {
							Amz_Utils::setLog( 'Product with ASIN ' . $_POST['product_asin'] . ' error on update ' . $result );
							Amz_Utils::returnBadRequest( 'An error occurred. Can&#8217;t save in database.' );
						}

					} else {
						Amz_Utils::returnBadRequest( 'An error occurred. Can&#8217;t parse product JSON.' );
					}
				} else {
					Amz_Utils::setLog( 'Service error: [' . $response['response']['code'] . '] ' . $response['response']['message'] );
					Amz_Utils::returnBadRequest( 'Service error: [' . $response['response']['code'] . '] ' . $response['response']['message'] );
				}
			} else {
				Amz_Utils::returnBadRequest( 'The product ASIN code is undefined' );
			}

		}

	} // End class Amz_Product_Api
} // End if(!class_exists('Amz_Product_Api'))

