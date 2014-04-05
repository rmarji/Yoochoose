<?php

/**
 * @category   AvS
 * @package    AvS_Yoochoose
 * @author     Andreas von Studnitz <avs@avs-webentwicklung.de>
 */

class AvS_Yoochoose_Block_Upsell extends Mage_Catalog_Block_Product_List_Upsell
{
    protected $_itemArray = false;

    /**
     * Request Recommendations from Yoochoose Api and transform them to an array
     * of products
     *
     * @return array
     */
    public function getItemArray()
    {
    	// if the array isnt initialized already with something do this
        if ($this->_itemArray === false) {
			//get an instance of the upselling API
            /** @var $api AvS_Yoochoose_Model_Api_Recommendation_Upselling */
            $api = Mage::getSingleton('yoochoose/api_recommendation_upselling');
			// initialze it with an empty array
            $this->_itemArray = array();
			// check if the user hasnt chosen to prefer manual input when adding products
            if (Mage::getStoreConfig('yoochoose/upselling/prefer_manual_connections')) {
				// load manual set upselling products first
                $this->_itemArray = $api->getArrayFromItemCollection($this->getItems());
            }

			// checks id the plugin is active and if the user has a valid license.
           	//TODO: fix it in the helper when we build a custom function for retrieving the api limit
           	/*if (!Mage::helper('yoochoose')->isActive()) {
                return $this->_itemArray;
            }
			*/

            // check if the manual products are less than the max products defined
            if (count($this->_itemArray) < $api->getMaxNumberProducts()) {
            	// get the scenario
                $scenario = Mage::getStoreConfig('yoochoose/upselling/scenario');
				// merge the manualy added products with the recommendations
                $this->_itemArray = $api->mergeItemArrays(
                    $this->_itemArray,
                    $api->getRecommendedProductsSR($scenario) // get the recommendations using the api
                );
            }
        }
        
		// return the list of the products
        return $this->_itemArray;
    }

    public function getRowCount()
    {
        return ceil(count($this->_itemArray)/$this->getColumnCount());
    }
}
