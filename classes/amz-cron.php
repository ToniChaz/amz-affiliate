<?php
/**
 * Amz Cron
 *
 * @package Amazon affiliate products
 * @subpackage Amz_Cron
 * @author Toni Chaz
 * @since 1.0.7
 */

if ( ! class_exists( 'Amz_Cron' ) ) {
	class Amz_Cron {

		private $amz_utils;
		private $amz_db;
		private $amz_amazon_api;

		/**
		 * Construct
		 */
		public function __construct() {
			add_action( 'refresh_product_data', array( $this, 'callback_refresh_product_data' ) );
			add_action( 'wp', array( $this, 'activated' ) );

			$this->amz_utils      = new Amz_Utils();
			$this->amz_db         = new Amz_Db();
			$this->amz_amazon_api = new Amz_Amazon_Api();
		}

		/**
		 * activated
		 */
		public static function activated() {
			if ( ! wp_next_scheduled( 'refresh_product_data' ) ) {
				Amz_Utils::setLog( 'Amz_Cron schedule event added' );
				wp_schedule_event( time(), 'daily', 'refresh_product_data' );
			}
		}

		/**
		 * deactivated
		 */
		public static function deactivated() {
			Amz_Utils::setLog( 'Amz_Cron deactivated' );
			// find out when the last event was scheduled
			$timestamp = wp_next_scheduled( 'refresh_product_data' );
			// unschedule previous event if any
			wp_unschedule_event( $timestamp, 'refresh_product_data' );
		}

		/**
		 * Refresh product data
		 *
		 * @void
		 */
		public function callback_refresh_product_data() {

			$products_asin = $this->amz_db->query( "product_asin", AMZ_PRODUCTS_TABLE );

			if ( !empty( $products_asin ) ) {

				$products_chunk = array_chunk($products_asin, 10);

				foreach ( $products_chunk as $product_chunk ) {

					$product_map = array_map( 'array_pop', $product_chunk );
					$imploded    = implode( ',', $product_map );
					$url         = $this->amz_amazon_api->generate_amazon_url( $imploded );
					$response    = wp_remote_get( $url );

					if ( $response['response']['code'] === 200 ) {

						$simple_xml = simplexml_load_string( $response['body'] );

						if ( isset( $simple_xml->Items->Request->Errors ) ) {
							$error_message = $simple_xml->Items->Request->Errors->Error->Message->__toString();
							Amz_Utils::setLog( 'Refresh product error ' . $error_message );
						} else {
							foreach ( $product_map as $key => $item ) {
								$product_price = $this->amz_utils->getProductPriceXML( $simple_xml->Items->Item[ $key ] );
								Amz_Utils::setLog( 'The ' . $item . ' price is: ' . $product_price );

								$time = new DateTime();
								$time->setTimezone( new DateTimeZone( 'Europe/Madrid' ) );
								$time_formatted = $time->format( 'Y-m-d H:i:s' );

								$data = array(
									'product_price' => $product_price,
									'product_time'  => $time_formatted
								);

								$result = $this->amz_db->update( AMZ_PRODUCTS_TABLE, $data, 'product_asin', $item );

								if ( ! $result ) {
									Amz_Utils::setLog( 'The ' . $item . ' NOT UPDATED' );
								}
							}
							Amz_Utils::setLog( 'Database updated' );
						}
					} else {
						Amz_Utils::setLog( 'Service Error: [' . $response['response']['code'] . '] ' . $response['response']['message'] );
					}
				}
			} else {

				Amz_Utils::setLog( 'No products to update' );
			}
		}

	} // End class Amz_Cron
} // End if(!class_exists('Amz_Cron'))
