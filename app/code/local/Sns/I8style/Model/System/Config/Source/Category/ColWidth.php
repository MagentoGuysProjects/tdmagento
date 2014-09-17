<?php

class Sns_I8style_Model_System_Config_Source_Category_ColWidth extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {	
	public function getAllOptions() {
		if (!$this->_options) {
            return array(
                array('value'=>'1', 'label'=>Mage::helper('adminhtml')->__('1 Columns')),
                array('value'=>'2', 'label'=>Mage::helper('adminhtml')->__('2 Columns')),
                array('value'=>'3', 'label'=>Mage::helper('adminhtml')->__('3 Columns')),
                array('value'=>'4', 'label'=>Mage::helper('adminhtml')->__('4 Columns')),
                array('value'=>'5', 'label'=>Mage::helper('adminhtml')->__('5 Columns')),
                array('value'=>'6', 'label'=>Mage::helper('adminhtml')->__('6 Columns')),
                array('value'=>'7', 'label'=>Mage::helper('adminhtml')->__('7 Columns')),
                array('value'=>'8', 'label'=>Mage::helper('adminhtml')->__('8 Columns')),
                array('value'=>'9', 'label'=>Mage::helper('adminhtml')->__('9 Columns')),
                array('value'=>'10', 'label'=>Mage::helper('adminhtml')->__('10 Columns')),
                array('value'=>'11', 'label'=>Mage::helper('adminhtml')->__('11 Columns')),
                array('value'=>'12', 'label'=>Mage::helper('adminhtml')->__('12 Columns')),
            );
        }
		return $this->_options;
    }
}
