<?php

class Sns_I8style_Model_System_Config_Source_Category_Types
	extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{	
	/**
     * Get list of available type
     */
	public function getAllOptions()
	{
		if (!$this->_options)
		{
            return array(
                array('value'=>'0', 'label'=>Mage::helper('adminhtml')->__('Subcategories')),
                array('value'=>'1', 'label'=>Mage::helper('adminhtml')->__('Blocks')),
                array('value'=>'2', 'label'=>Mage::helper('adminhtml')->__('Subcategories and Blocks'))
            );
        }
		return $this->_options;
    }
}
