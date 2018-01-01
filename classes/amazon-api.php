<?php
/**
 * Amazon API
 *
 * @package Amazon affiliate products
 * @subpackage Amz_Amazon_Api
 * @author Toni Chaz
 * @since 1.0.7
 */

if (!class_exists('Amz_Amazon_Api')) {
    class Amz_Amazon_Api {

        protected $aws_secret_key;
        protected $endpoint;
        protected $uri;

        /**
         * Construct
         */
        public function __construct() { } // End public function __construct

        /**
         * Generate amazon url to find a product by ASIN
         * @return string
         */
        public function generate_amazon_url($product_asin) {
            // Your AWS Secret Key corresponding to the above ID, as taken from the AWS Your Account page
            $aws_secret_key = AWS_SECRET_KEY;

            // The region you are interested in
            $endpoint = "webservices.amazon.es";

            $uri = "/onca/xml";

            $params = array(
                "Service" => "AWSECommerceService",
                "Operation" => "ItemLookup",
                "AWSAccessKeyId" => AWS_ACCESS_KEY_ID,
                "AssociateTag" => "quepulsometro-21",
                "ItemId" => $product_asin,
                "IdType" => "ASIN",
                "ResponseGroup" => "Images,ItemAttributes,OfferSummary",
                "Version" => "2011-08-01"
            );

            // Set current timestamp if not set
            if (!isset($params["Timestamp"])) {
                $params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
            }

            // Sort the parameters by key
            ksort($params);

            $pairs = array();

            foreach ($params as $key => $value) {
                array_push($pairs, rawurlencode($key) . "=" . rawurlencode($value));
            }

            // Generate the canonical query
            $canonical_query_string = join("&", $pairs);

            // Generate the string to be signed
            $string_to_sign = "GET\n" . $endpoint . "\n" . $uri . "\n" . $canonical_query_string;

            // Generate the signature required by the Product Advertising API
            $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $aws_secret_key, true));

            // Generate the signed URL
            $request_url = 'https://' . $endpoint . $uri . '?' . $canonical_query_string . '&Signature=' . rawurlencode($signature);

            return $request_url;
        }


    } // End class Amz_Amazon_Api
} // End if(!class_exists('Amz_Amazon_Api'))

