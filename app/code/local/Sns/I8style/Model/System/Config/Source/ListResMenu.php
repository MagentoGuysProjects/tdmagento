<?php

class Sns_I8style_Model_System_Config_Source_ListResMenu
{
	public function toOptionArray()
	{
		return array(
			array('value'=>'sidebar', 'label'=>Mage::helper('i8style')->__('SideBar')),
			array('value'=>'collapse', 'label'=>Mage::helper('i8style')->__('Collapse'))
		);
	}
}
