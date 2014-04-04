<?php

/**
 * @category   AvS
 * @package    AvS_Yoochoose
 * @author     Andreas von Studnitz <avs@avs-webentwicklung.de>
 */

class AvS_Yoochoose_Model_Api
{
    const YOOCHOOSE_RECOMMENDATION_URL  = 'http://reco.yoochoose.net/';
    const YOOCHOOSE_EVENT_URL           = 'http://event.yoochoose.net/';
	
	const SR_API_BASE_URL				= 'http://demo.easyrec.org:8080/api/1.0/json/';
	
	const SR_REC_ALSO_VIEWED 			= 'otherusersalsoviewed';
	const SR_REC_ALSO_BOUGHT			= 'otherusersalsobought';
	const SR_REC_ALSO_RATED_GOOD		= 'itemsratedgoodbyotherusers';
	const SR_REC_RELATED_ITEMS			= 'relateditems';
	
	const SR_REC_FOR_USER	 			= 'recommendationsforuser';
	const SR_USER_ACTION_HISTORY		= 'actionhistoryforuser';

	/* TODO event stuf check them later */
    const ITEM_TYPE_CATEGORY            = 0;
    const ITEM_TYPE_PRODUCT             = 1;

    const EVENT_TYPE_CLICK              = 'click';
    const EVENT_TYPE_BUY                = 'buy';
    const EVENT_TYPE_RECOMMENDATION     = 'recommendation';
    const EVENT_TYPE_TRANSFER           = 'transfer';

    const PRODUCT_ID                    = 'ebl';

    /**
     * Get User Id from Cookie, Session or Customer Object (if logged in)
     *
     * @return string
     */
    protected function _getUserId()
    {
    	// get the userID either from the session or customerobject or from the cookie
        return Mage::helper('yoochoose')->getUserId();
    }

    /**
     * return comma seperated category ids of current item (category or product)
     *
     * @return string
     */
    protected function _getCategoryPath() {
		// get the category for the current product
        $category = Mage::registry('current_category');
        if (!$category) return '';
		
		// return string the reverse order with commas in between.
        $categoryPath = $category->getPathInStore();
        return implode(',', array_reverse(explode(',', $categoryPath)));
    }
} 