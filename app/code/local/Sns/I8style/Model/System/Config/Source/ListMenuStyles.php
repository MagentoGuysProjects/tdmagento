<?php

class Sns_I8style_Model_System_Config_Source_ListMenuStyles
{
	public function toOptionArray()
	{
		return array(
			//array('value'=>'base', 'label'=>Mage::helper('i8style')->__('Base')),
			array('value'=>'mega', 'label'=>Mage::helper('i8style')->__('Mega'))
		);
	}
}
