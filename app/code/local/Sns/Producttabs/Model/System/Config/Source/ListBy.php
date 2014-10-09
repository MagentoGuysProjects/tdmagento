<?php

class Sns_Producttabs_Model_System_Config_Source_ListBy{
	public function toOptionArray(){
		return array(
		array('value'=>'order', 'label'=>Mage::helper('producttabs')->__('Order By')),
		array('value'=>'category', 'label'=>Mage::helper('producttabs')->__('Category'))
		);
	}
}
