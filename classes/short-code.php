<?php
/**
 * Short Codes
 *
 * @package Amazon affiliate products
 * @subpackage Amz_ShortCode
 * @author Toni Chaz
 * @since 1.0.5
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if ( ! class_exists( 'Amz_ShortCode' ) ) {
	class Amz_ShortCode {

		private $amz_db;
		private $amz_utils;
		private $tables;

		/**
		 * Construct
		 */
		public function __construct() {

			$this->amz_db    = new Amz_Db();
			$this->amz_utils = new Amz_Utils();
			self::generate_short_codes();

		} // End public function __construct

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
		 * Init short code
		 *
		 * @void
		 */

		public function generate_short_codes() {

			$this->tables = self::find_tables();

			if ( $this->tables ) {
				foreach ( $this->tables as $table ) {
					add_shortcode( 'amztable_' . $table->table_id, array( $this, 'amztable_func' ) );
				}
			}
		}

		/**
		 * Init short code
		 *
		 * @return String
		 */

		public function amztable_func( $atts, $content = "", $tag ) {

			$result    = "";
			$array_idx = array();
			$id        = substr( $tag, strrpos( $tag, '_' ) + 1 );

			foreach ( $this->tables as $table ) {
				if ( $table->table_id == $id ) {
					$result = "<table class='display cell-border' cellspacing='0' width='100%'>";
					$result .= "<thead>";
					$result .= "<tr>";
					foreach ( array_slice( $table->table_head, 1 ) as $idx => $cell ) {
						if ( preg_match( '/(Marca y Modelo)|(Precio)|(Info)|(Comprar)/', $cell, $matches ) ) {
							$result .= "<th>$cell</th>";
							array_push( $array_idx, $idx );
						} else {
							$result .= "<th class='mobile-hidden'>$cell</th>";
						}
					}
					$result .= "</tr>";
					$result .= "</thead>";
					$result .= "<tbody>";
					foreach ( $table->table_body as $row ) {
						$result .= "<tr>";
						foreach ( array_slice( $row, 1 ) as $idx => $cell ) {
							if ( preg_match( '/EUR/', $cell ) ) {
                                $price  = ltrim( $cell, 'EUR ' );
							    $matches = array();
                                $has_old_price = preg_match('/\[(.*?)\]/s', $price, $matches);
                                if ($has_old_price){
                                    $price = str_replace($matches[0], '&euro;', $price);
                                    $result .= "<td><b>$price</b><br><span class='old-price'>$matches[1]&euro;</span></td>";
                                } else {
                                    $result .= "<td><b>$price&euro;</b></td>";
                                }
							} else {
								if ( in_array( $idx, $array_idx ) ) {
									$result .= "<td>$cell</td>";
								} else {
									$result .= "<td class='mobile-hidden'>$cell</td>";
								}

							}
						}
						$result .= "</tr>";
					}
					$result .= "</tbody>";
					$result .= "</table>";
				}
			}

			return $result;
		}

	} // End class Amz_ShortCode
} // End if(!class_exists('Amz_ShortCode'))

