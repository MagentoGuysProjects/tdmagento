<?php

class Sns_I8style_Model_System_Config_Source_Category_Status extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {	
	public function getAllOptions() {
		if (!$this->_options) {
            return array(
            	array('value'=>'0', 'label'=>Mage::helper('adminhtml')->__('No')),
            	array('value'=>'1', 'label'=>Mage::helper('adminhtml')->__('Yes'))
            );
        }
		return $this->_options;
    }
}
