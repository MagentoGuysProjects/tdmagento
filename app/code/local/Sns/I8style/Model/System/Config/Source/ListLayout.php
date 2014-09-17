<?php

class Sns_I8style_Model_System_Config_Source_ListLayout
{
	public function toOptionArray()
	{
		return array(
			array('value'=>'1', 'label'=>Mage::helper('i8style')->__('Full width')),
			array('value'=>'2', 'label'=>Mage::helper('i8style')->__('Boxed')),
		);
	}
}
