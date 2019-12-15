<?php
/**
 * Utils
 *
 * @package Amazon affiliate products
 * @subpackage Amz_Utils
 * @author Toni Chaz
 * @since 1.0.5
 */

if ( ! class_exists( 'Amz_Utils' ) ) {
	class Amz_Utils {

		private $amz_db;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->amz_db = new Amz_Db();
		}

		/**
		 * Public method to set current product data
		 *
		 * @param XML_product_data
		 *
		 * @return void
		 */
		public function setXML_product_data( $XML_product_data ) {

			$product_dataJSON = json_encode( $XML_product_data );

			if ( self::getJSON_product_data() == false ) {
				add_option( 'AMZ_product_data', $product_dataJSON, $deprecated = '', $autoload = 'no' );
			} else {
				update_option( 'AMZ_product_data', $product_dataJSON );
			}

		}

		/**
		 * Public method to get current product data
		 *
		 * @return String XML_product_data variable
		 */
		public function getJSON_product_data() {
			return get_option( 'AMZ_product_data', $default = false );
		}

		/**
		 * Public method to delete current product data
		 *
		 * @return void
		 */
		public function deleteJSON_product_data() {
			delete_option( 'AMZ_product_data' );
		}

		/**
		 * Get product price if is used or not
		 *
		 * @param Array_product_data
		 *
		 * @return string
		 */
		public function getProductPrice( $Array_product_data ) {

			$price = null;

			if ( array_key_exists( 'LowestNewPrice', $Array_product_data['OfferSummary'] ) ) {
				$price = $Array_product_data['OfferSummary']['LowestNewPrice']['FormattedPrice'];
			} else if ( array_key_exists( 'LowestUsedPrice', $Array_product_data['OfferSummary'] ) ) {
				$price = $Array_product_data['OfferSummary']['LowestUsedPrice']['FormattedPrice'] . ' (Only used)';
			} else {
				$price = 'No disponible';
			}

			return $price;
		}

		/**
		 * Get product price if is used or not
		 *
		 * @param XML_product_data
		 *
		 * @return string
		 */
		public function getProductPriceXML( $XML_product_data ) {

			$price = null;

			if ( isset( $XML_product_data->OfferSummary->LowestNewPrice ) ) {
				$price = $XML_product_data->OfferSummary->LowestNewPrice->FormattedPrice;
			} else if ( isset( $XML_product_data->OfferSummary->LowestUsedPrice ) ) {
				$price = $XML_product_data->OfferSummary->LowestUsedPrice->FormattedPrice . ' (Only used)';
			} else {
				$price = 'No disponible';
			}

			return $price;
		}

		/**
		 * Get product image from json
		 *
		 * @param product_data
		 *
		 * @return string
		 */
		public function getProductImage( $product_data ) {

			$image            = null;
			$Obj_product_data = json_decode( $product_data );

			if ( isset( $Obj_product_data->SmallImage->URL ) ) {
				$src = $Obj_product_data->SmallImage->URL;
				preg_match( '/^(?>\w+\s*){1,3}/', $Obj_product_data->ItemAttributes->Title, $match );
				$alt   = rtrim( $match[0] );
				$image = "<img src='" . $src . "' alt='" . $alt . "'/><br/>$alt";
			}

			return $image;
		}

        /**
         * Get product list price
         *
         * @param product_data
         *
         * @return string
         */
        public function getProductListPrice( $product_data ) {

            $listPrice        = null;
            $Obj_product_data = json_decode( $product_data );

            if ( isset( $Obj_product_data->ItemAttributes->ListPrice ) ) {
                $listPrice = $Obj_product_data->ItemAttributes->ListPrice->FormattedPrice;
            }

            return $listPrice;
        }

		/**
		 * Clean table body from slashes
		 *
		 * @param table_body
		 *
		 * @return array
		 */
		public function cleanTableBody( $table_body ) {

			$clean_table_body = array();

			foreach ( $table_body as $row ) {
				$clean_row = array();

				foreach ( $row as $value ) {
					$clean_value = stripslashes( $value );
					array_push( $clean_row, $clean_value );
				}

				array_push( $clean_table_body, $clean_row );
			}

			return $clean_table_body;
		}

		/**
		 * Set elements on table
		 *
		 * @param $table_body
		 * @param $table_head
		 * @param $force_update
		 *
		 * @return Array
		 */
		public function setElementsOnTable( $table_body, $table_head, $force_update = false ) {

			$new_table_body = array();

			foreach ( $table_body as $key => $value ) {
				$asinCode       = array_values( $value )[0];
				$currentProduct = $this->amz_db->find( AMZ_PRODUCTS_TABLE, 'product_asin', $asinCode );

				if ( $currentProduct == null ) {
					unset( $table_body[ $key ] );
				} else {
					$imagePosition = array_search( 'Marca y Modelo', $table_head );
					$pricePosition = array_search( 'Precio', $table_head );
					$infoPosition  = array_search( 'Info', $table_head );
					$buyPosition   = array_search( 'Comprar', $table_head );

					if ( $imagePosition && $value[ $imagePosition ] == '' ) {
						$value[ $imagePosition ] = self::getProductImage( $currentProduct->product_data );
					}

					if ( $pricePosition && ( $value[ $infoPosition ] == '' || $force_update ) ) {
                        $price = $currentProduct->product_price;
                        if (!preg_match('/\bOnly used|No disponible\b/', $price)) {
                            $listPrice = self::getProductListPrice( $currentProduct->product_data );
                            $cleanPrice = floatval(ltrim( $price, 'EUR ' ));
                            $cleanListPrice = floatval(ltrim( $listPrice, 'EUR ' ));
                            if ($cleanListPrice > $cleanPrice){
                                $price = $price . '[' .  ltrim( $listPrice, 'EUR ' ) . ']';
                            }
                        }
                        $value[ $pricePosition ] = $price;
					}

					if ( $infoPosition && $value[ $infoPosition ] == '' ) {
						$value[ $infoPosition ] = "<a rel='nofollow' href='" . $currentProduct->product_link . "' target='_blank'>Más detalles</a>";
					}

					if ( $buyPosition && $value[ $buyPosition ] == '' ) {
						$value[ $buyPosition ] = "<a rel='nofollow' href='" . $currentProduct->product_link . "' class='button-small' target='_blank'>Ver Ahora</a>";
					}

					array_push( $new_table_body, $value );
				}
			}

			return $new_table_body;
		}

		/**
		 * Return good request from Ajax
		 *
		 * @param $message
		 * @param $data
		 *
		 * @void
		 */
		public static function returnGoodRequest( $message, $data = null ) {
			header( 'HTTP/1.1 200 OK' );
			header( 'Content-Type: application/json; charset=UTF-8' );

			$response = array(
				'message' => $message
			);

			if ( $data ) {
				$response['data'] = $data;
			}

			echo json_encode( $response );
			wp_die();
		}

		/**
		 * Return bad request from Ajax
		 *
		 * @param $message
		 *
		 * @void
		 */
		public static function returnBadRequest( $message ) {
			header( 'HTTP/1.1 400 Bad Request' );
			header( 'Content-Type: application/json; charset=UTF-8' );
			wp_die( $message );
		}

		/**
		 * Set logs function
		 *
		 * @param $message (String)
		 *
		 * @void
		 */
		public static function setLog( $message ) {
			$new_date = date( 'Y-m-d H:i:s', strtotime( '+1 hour' ) );
			file_put_contents( dirname( __DIR__ ) . '/logs.txt', '[' . $new_date . '] ' . $message . "\n" );
		}

	} // End class Amz_Utils
} // End if(!class_exists('Amz_Utils'))