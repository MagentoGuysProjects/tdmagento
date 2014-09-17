<?php

class Sns_I8style_Model_System_Config_Source_ListColor
{
	public function toOptionArray()
	{
		return array(
			array('value'=>'red', 'label'=>Mage::helper('i8style')->__('Red')),
			array('value'=>'brown', 'label'=>Mage::helper('i8style')->__('Brown')),
			array('value'=>'purple', 'label'=>Mage::helper('i8style')->__('Purple')),
			array('value'=>'yellow', 'label'=>Mage::helper('i8style')->__('Yellow')),
			array('value'=>'blue', 'label'=>Mage::helper('i8style')->__('Blue')),
			array('value'=>'green', 'label'=>Mage::helper('i8style')->__('Green')),
			array('value'=>'chocolate', 'label'=>Mage::helper('i8style')->__('Chocolate')),
			array('value'=>'slateblue', 'label'=>Mage::helper('i8style')->__('Slateblue')),
		);
	}
}
