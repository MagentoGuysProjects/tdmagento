<?php
class Sns_I8style_Model_System_Config_Source_ListBodyFont
{
	public function toOptionArray()
	{
		return array(
			array('value'=>'arial', 'label'=>Mage::helper('i8style')->__('Arial')),
			array('value'=>'arial-black', 'label'=>Mage::helper('i8style')->__('Arial black')),
			array('value'=>'courier new', 'label'=>Mage::helper('i8style')->__('courier New')),
			array('value'=>'georgia', 'label'=>Mage::helper('i8style')->__('Georgia')),
			array('value'=>'impact', 'label'=>Mage::helper('i8style')->__('Impact')),
			array('value'=>'lucida-console', 'label'=>Mage::helper('i8style')->__('Lucida Console')),
			array('value'=>'lucida-grande', 'label'=>Mage::helper('i8style')->__('Lucida-grande')),
			array('value'=>'palatino', 'label'=>Mage::helper('i8style')->__('Palatino')),
			array('value'=>'tahoma', 'label'=>Mage::helper('i8style')->__('Tahoma')),
			array('value'=>'times new roman', 'label'=>Mage::helper('i8style')->__('Times')),
			array('value'=>'Trebuchet', 'label'=>Mage::helper('i8style')->__('Trebuchet')),
			array('value'=>'Verdana', 'label'=>Mage::helper('i8style')->__('Verdana')),
			array('value'=>'segoe ui', 'label'=>Mage::helper('i8style')->__('Segoe UI'))
		);
	}
}
