<?php
class AvS_Yoochoose_Model_Config_Source_Scenarios
{
  public function toOptionArray()
  {
    return array(
      array('value' => 0, 'label' => Mage::helper()->__('Most Viewed Items')),
      array('value' => 1, 'label' => Mage::helper()->__('Other Users Also Viewed')),
      array('value' => 2, 'label' => Mage::helper()->__('Other Users Also Bought')),
     // and so on...
    );
  }
}