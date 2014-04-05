<?php

/**
 * @category   AvS
 * @package    AvS_Yoochoose
 * @author     Raed (Thunder) Marji <raed@raed@jogeeks.com>
 */


class AvS_Yoochoose_Model_Api_Recommendation extends AvS_Yoochoose_Model_Api {

    // TODO see what we can about these
    const SCENARIO_CROSS_SELLING = 'cross_selling';
    const SCENARIO_RELATED_PRODUCTS = 'related_products';
    const SCENARIO_UP_SELLING = 'up_selling';

    protected $_recommendedProductIds = array();
    protected $_numberProducts = 10;

    /**
     * Get Product Recommendations based on Client Id and License Key
     *
     * @param int $maxCount
     * @return array
     */
    public function getRecommendedProductsSR($scenario, $maxCount = 10) {
    
        // The url that we want to get recs from
        $url = $this -> _getRecommendationBaseUrlSR($scenario);
        // Filter the recs by these params
        $params = $this -> _getRecommendationUrlParamsSR($maxCount);
        // try to contact the server and get the rec based on the criteria and url
        try {
            $rawResponse = Mage::helper('yoochoose') -> _getHttpPage($url, $params);
            $response = Zend_Json::decode($rawResponse);
            Mage::log($response, null, 'custom.log', true);
            // if the limit has been reached then return with nothing
            if ($response['error']['@code'] == "909") {
                Mage::log('its ' . $response['error']['@code'], null, 'custom.log', true);
                // TODO: check if its already disabled
                // TODO maybe do a cron job to check if the license is valid every hours
                Mage::helper('yoochoose') ->setConfigData('yoochoose/api/license_type', 'No More Actions This Month');
            } else {
                //print_r($response);
                // Parse the response to get the products in form of an array
                return $this -> _getRecommendedProductsArraySR($response);
            }
        } catch(Exception $e) {
            echo 'error';
            Mage::logException($e);
            // authentication failed
            return array();
        }

        // for some reason nothing works return an empty array
        return array();
    }

    /**
     * Transform Response Array to Array of Products
     *
     * @param array $response
     * @return array
     */
    protected function _getRecommendedProductsArraySR($response) {

        $responseArray = $response['recommendeditems']['item'];
        // TODO: custom option to choose how to order the recs generated.
        //$responseArray = Mage::helper('yoochoose')->getArraySortedBySubkey($responseArray, 'relevance');
        //print_r($responseArray);
        $recommendedProductsArray = array();
        foreach ($responseArray as $singleRecommendation) {

            if ($singleRecommendation['item'] == 1 || TRUE) {

                $product = Mage::getModel('catalog/product') -> load($singleRecommendation['id']);
                if ($product -> getId()) {
                    $recommendedProductsArray[] = $product;
                }
            }
        }

        return $recommendedProductsArray;
    }

    protected function _getRecommendedProductsArray($response) {
        $responseArray = $response['recommendationResponseList'];
        $responseArray = Mage::helper('yoochoose') -> getArraySortedBySubkey($responseArray, 'relevance');

        $recommendedProductsArray = array();
        foreach ($responseArray as $singleRecommendation) {

            if ($singleRecommendation['itemType'] == 1) {

                $product = Mage::getModel('catalog/product') -> load($singleRecommendation['itemId']);
                if ($product -> getId()) {

                    $recommendedProductsArray[] = $product;
                }
            }
        }

        return $recommendedProductsArray;
    }

    /**
     * return an array of products for testing
     *
     * @return array
     */
    protected function _getRecommendedProductsArrayDummy() {
        // $responseArray = $response['recommendationResponseList'];
        // $responseArray = Mage::helper('yoochoose')->getArraySortedBySubkey($responseArray, 'relevance');

        $recommendedProductsArray = array();
        $dummyData = array(159, 160, 161);
        foreach ($dummyData as $singleRecommendation) {
            if ($singleRecommendation) {
                $product = Mage::getModel('catalog/product') -> load($singleRecommendation);
                if ($product -> getId()) {
                    $recommendedProductsArray[] = $product;
                }
            }
        }
        return $recommendedProductsArray;
    }

    /*	protected function _getRecommendationBaseUrl($scenario) {
     $url = self::YOOCHOOSE_RECOMMENDATION_URL;
     $path = array(self::PRODUCT_ID, self::EVENT_TYPE_RECOMMENDATION, Mage::getStoreConfig('yoochoose/api/client_id'), $this -> _getUserId(), $scenario . '.json', );

     return $url . implode('/', $path);
     }
     */

    /**
     * Generate Base Url for Recommendation Request
     *
     * @param string $scenario
     * @return string
     */
    protected function _getRecommendationBaseUrlSR($scenario) {
        $url = self::SR_API_BASE_URL;
        //$path = array(self::PRODUCT_ID, self::EVENT_TYPE_RECOMMENDATION, Mage::getStoreConfig('yoochoose/api/client_id'), $this -> _getUserId(), $scenario . '.json', );
        $path = $url . $scenario;

        return $path;
    }

    /**
     * Generate Parameters for Recommendation URL
     *
     * @param int $maxCount
     * @return array
     */
    protected function _getRecommendationUrlParamsSR($maxCount) {
        $tenantId = Mage::getStoreConfig('yoochoose/api/client_id');
        $apiKey = Mage::getStoreConfig('yoochoose/api/license_key');
        $itemType = $this -> _getCategoryPath();
        
        $product = Mage::registry('product');
        $itemId = $product -> getId();

        //TODO: change the item id to get it from the current page or from the total
        $params = array('itemid' => $itemId, 'numberOfResults' => min(10, $maxCount),
        //'apikey' => '62ffa549a74ba0eea4e7502c48e54c13',
        'apikey' => $apiKey, 'tenantid' => $tenantId, 'requesteditemtype' => $itemType, 'numberOfResults' => min(10, $maxCount), );

        return $params;
    }

    /**
     * Merge two array of products; don't add duplicates
     *
     * @param array $itemArray1
     * @param array $itemArray2
     * @return array
     */
    public function mergeItemArrays($itemArray1, $itemArray2) {
        foreach ($itemArray2 as $item) {
            // check this item is not already in the recommended products ids
            if (!in_array($item -> getId(), $this -> _recommendedProductIds)) {
                // add the item to the final list
                $itemArray1[] = $item;
                // if we reached the limit we need we can stop looking
                if (count($itemArray1) >= $this -> getMaxNumberProducts()) {
                    break;
                }
            }
        }
        return $itemArray1;
    }

    /**
     * Gets configured maximum number of recommended products
     *
     * @return int
     */
    public function getMaxNumberProducts() {
        return $this -> _numberProducts;
    }

    /**
     * Gets configured maximum number of recommended products
     *
     * @return int
     */
    public function setMaxNumberProducts($numberProducts) {
        $this -> _numberProducts = $numberProducts;
    }

    /**
     * Converts item collection to array
     *
     * @return array
     */
    public function getArrayFromItemCollection($itemCollection) {
        $itemArray = array();
        foreach ($itemCollection as $item) {

            $itemArray[] = $item;
            $this -> _recommendedProductIds[] = $item -> getId();
        }

        return $itemArray;
    }

}
