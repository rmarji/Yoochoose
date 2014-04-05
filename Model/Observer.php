<?php

/**
 * @category   AvS
 * @package    AvS_Yoochoose
 * @author     Raed (Thunder) Marji <raed@raed@jogeeks.com>
 */

class AvS_Yoochoose_Model_Observer
{
    const YOOCHOOSE_LICENSE_URL = 'https://admin.yoochoose.net/ebl/%customer_id%/license.json';
    const YOOCHOOSE_SUMMARY_URL = 'https://admin.yoochoose.net/rest/%customer_id%/counter/summary.json';

	const SR_API_URL = 'http://demo.easyrec.org:8080/api/1.0/json/';
	
	const SR_API_ACTION_VIEW = 'view';
	const SR_API_ACTION_BUY = 'buy';
	const SR_API_ACTION_rate = 'rate';
	
	//TODO: add a shortcode functionality.
	
	//TODO: add custom actions fucntion
	//TODO: add an observer on the add product event
	//TODO: import rules from google analytics.. advanced!

    /**
     * Update field "yoochoose_user_id" from session to
     * customer object (database) or vice verse, if customer info already exists
     *
     * @param Varien_Event_Observer $observer
     */
    public function onCustomerLogin($observer)
    {
    	//TODO : print the ids.
    	Mage::log("Customer Logged in ", null, 'custom.log', true);
        $customer = $observer->getEvent()->getCustomer();
        Mage::helper('yoochoose')->mergeUserIdOnLogin();
    	Mage::log("id Merged", null, 'custom.log', true);
	}

    /**
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminSystemConfigChangedSectionYoochoose($observer)
    {
        
        $clientId = Mage::getStoreConfig('yoochoose/api/client_id');
        $licenseKey = Mage::getStoreConfig('yoochoose/api/license_key');

        if (!$clientId && !$licenseKey) return;

        $licenseType = $this->_getLicenseType($clientId, $licenseKey);

        $this->_displayMessage($licenseType);

        if ($licenseType != Mage::getStoreConfig('yoochoose/api/license_type')) {
            Mage::helper('yoochoose') ->setConfigData('yoochoose/api/license_type', $licenseType);
        }
    }

    /**
     * Display success or error message, depending on license type
     *
     * @param string $licenseType
     */
    protected function _displayMessage($licenseType)
    {
        if ($licenseType && $licenseType != Mage::getStoreConfig('yoochoose/api/license_type')) {
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('yoochoose')->__('License successfully verified.')
            );
        } else if (!$licenseType) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('yoochoose')->__('License could not be verified.')
            );
        }
    }

    /**
     * Get License Type base on Client Id and License Key
     *
     * @param string $clientId
     * @param string $licenseKey
     * @return string
     */
    protected function _getLicenseType($clientId, $licenseKey)
    {
        $url = str_replace('%customer_id%', $clientId, self::YOOCHOOSE_LICENSE_URL);
        try {
            $rawResponse = Mage::helper('yoochoose')->_getHttpsPage($url, $clientId, $licenseKey);
            $response = Zend_Json::decode($rawResponse);
            return $response['license']['type'];
        }
        catch(Exception $e) {

            // authentication failed
            return '';
        }
    }

/*    public function updateStats()
    {
        $clientId = Mage::getStoreConfig('yoochoose/api/client_id');
        $licenseKey = Mage::getStoreConfig('yoochoose/api/license_key');

        if (!$clientId && !$licenseKey) return;

        $stats = $this->_getStats($clientId, $licenseKey);

        $statsHtml = $this->_generateStatsHtml($stats);
        if ($statsHtml) {
            Mage::helper('yoochoose') ->setConfigData('yoochoose/general/stats', $statsHtml);
        }

        return $statsHtml;
    }
*/
    /**
     * Get Statistics bases on Client Id and License Key
     *
     * @param string $clientId
     * @param string $licenseKey
     * @return array
     */
/*    protected function _getStats($clientId, $licenseKey)
    {
        $url = str_replace('%customer_id%', $clientId, self::YOOCHOOSE_SUMMARY_URL);
        try {
            $rawResponse = Mage::helper('yoochoose')->_getHttpsPage($url, $clientId, $licenseKey);
            $response = Zend_Json::decode($rawResponse);
            return $response;
        }
        catch(Exception $e) {

            // authentication failed
            return array();
        }
    }
*/
    /**
     * Generate Statistics HTML for display in configuration
     *
     * @param array $stats
     * @return string
     */
/*    protected function _generateStatsHtml($stats)
    {
	
        $statsHtml = '<table>';
        $statsLines = array();
        $baseSorting = 6;
        
        foreach($stats as $key => $singleStat) {

            switch ($key) {

                case 'EVENT_1':

                    $label = Mage::helper('yoochoose')->__('Registered Clicks');
                    $sorting = 0;
                    break;

                case 'EVENT_2':

                    $label = Mage::helper('yoochoose')->__('Registered Buys');
                    $sorting = 1;
                    break;

                case 'RECO_related_products':

                    $label = Mage::helper('yoochoose')->__('"Related products" recommendations');
                    $sorting = 3;
                    break;

                case 'RECO_cross_selling':

                    $label = Mage::helper('yoochoose')->__('"Cross selling" recommendations');
                    $sorting = 4;
                    break;

                case 'RECO_up_selling':

                    $label = Mage::helper('yoochoose')->__('"Up selling" recommendations');
                    $sorting = 5;
                    break;

                case 'DELIVERED_RECOS_related_products':
                case 'DELIVERED_RECOS_cross_selling':
                case 'DELIVERED_RECOS_up_selling':

                    continue;

                default:

                    $label = $key;
                    $sorting = $baseSorting;
                    $baseSorting++;
                    break;
            }

            $statsLines[$sorting] = '<tr><td>' . $label . ':&nbsp;</td><td>' . $singleStat['count'] . '</td></tr>';
        }

        $statsLines[2] = '<tr><td>----------</td><td>&nbsp;</td></tr>';

        ksort($statsLines);
        $statsHtml .= implode("\n", $statsLines);

        $statsHtml .= '</table>';

       return $statsHtml;
	
    }
*/

 	/**
	 * Saves $value in store config based on $configPath
	 *
	 * @param string $configPath
	 * @param string $value
	 */
    /*protected function _setConfigData($configPath, $value)
    {
        $setup = new Mage_Core_Model_Resource_Setup('core_setup');
        $setup->startSetup();
        $setup->setConfigData($configPath, $value);
        $setup->endSetup();
        Mage::getSingleton('core/config')->reinit();
    }*/

	/**
	 *  Observer Function that activiates on product view to register the view with the API
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function eventPostDispatchProductView($observer) {
		//TODO: if (!$this->isActive()) return;
		
		$_product = Mage::registry('current_product');
		$this->_sendAction($_product , self::SR_API_ACTION_VIEW);
		Mage::log("Product Viewed",null,'custom.log',true);
	}

	/**
	 *  Observer Function that activiates on checkout to register the products bought with the API
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function eventPlaceOrderEnd($observer)
	{
		//TODO: Make sure everything is working with the api and for all products
		//TODO: if (!$this->isActive()) return;
		
		Mage::log("Order Placed and Done",null,'custom.log',true);
		$order = $observer->getEvent()->getOrder();
		Mage::log($order->getId(),null,'custom.log',true);
		
		$items = $order->getItemsCollection();
		foreach ($items as $item) {
			Mage::log($item->getProductId(),null,'custom.log',true);
			Mage::log($item->getName(),null,'custom.log',true);
			$_product = $item->getProduct();
			$this->_sendAction($_product, self::SR_API_ACTION_BUY);
		}		
		
		//TODO: check what the hell this means mabbe its useful
		/*
		foreach ($items as $item) {
			if ($item->getHasChildren()) {
					
				if ($item->getParentItem() != null) {
					continue;
				} else {
					continue;
				}
			} else {
				if ($item->getParentItem() != null)	{
					$itemsUrl .= "&item=" . urlencode($item->getParentItem()->getProductId() .
					 "::" . $item->getParentItem()->getPriceInclTax() . "::" . $item->getParentItem()->getQtyOrdered());		
				} else {
					$itemsUrl .= "&item=" . urlencode($item->getProductId() . "::" . $item->getPriceInclTax() . "::" . $item->getQtyOrdered());
				}
			}	
		}*/
		
	}
	public function eventPayInvoice($observer)
	{
		Mage::log("Payment made",null,'custom.log',true);
			 
	}
	
	// TODO: might need to change the params so that it can be more generic
	protected function _sendAction($_product , $event){
		// echo $_product->getShortDescription(); //product's short description
		// echo $_product->getDescription(); // product's long description
		// echo $_product->getName(); //product name
		// echo $_product->getPrice(); //product's regular Price
		// echo $_product->getSpecialPrice(); //product's special Price
		// echo $_product->getProductUrl(); //product url
		// echo $_product->getImageUrl(); //product's image url
		// echo $_product->getSmallImageUrl(); //product's small image url
		// echo $_product->getThumbnailUrl(); //product's thumbnail image url
		
		// $event = $observer -> getEvent();
		// $product = $event -> getProduct();

		$_productId = $_product -> getId();
		$_productSDesc = $_product -> getName();
		$_productURL = $_product -> getProductUrl();
		$_productImgURL = $_product -> getImageUrl();
		
		//TODO: change this to the item category VIP
		$_productCategory = 'ITEM';
		
  /* 		$categoryIds = $_product ->  getCategoryIds();

     if(count($categoryIds) ){
            $firstCategoryId = $categoryIds[0];
            $_category = Mage::getModel('catalog/category')->load($firstCategoryId);

            echo $_category->getName();
			
		print_r($category); 
	 */
   
		$_userId = Mage::helper('yoochoose')->getUserId();

		$session = Mage::getSingleton("customer/session");
		$_userSessionId = $session -> getEncryptedSessionId();

		Mage::log($_userId, null, 'custom.log', true);

		$url = self::SR_API_URL.$event;

		$params = array('apikey' => '62ffa549a74ba0eea4e7502c48e54c13',
		 'tenantid' => 'EASYREC_DEMO',
		  'itemid' => $_productId,
		   'itemdescription' => $_productSDesc,
		    'itemurl' => $_productURL,
		    'itemimageurl' => $_productImgURL,
		     'userid' => $_userId,
		     'sessionid' => $_userSessionId,
		     'itemtype' => $_productCategory,
		        );

		//$this->_getRecommendationUrlParams($maxCount);
		
		 try {
		 $rawResponse = Mage::helper('yoochoose') -> _getHttpPage($url, $params);
		 $response = Zend_Json::decode($rawResponse);
	 
		 Mage::log($response, null, 'custom.log', true);
		 } catch(Exception $e) {
		 echo 'error';
		 Mage::logException($e);
		 // authentication failed
		 return array();
		}
	}
} 
