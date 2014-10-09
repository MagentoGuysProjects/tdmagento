<?php

class Sns_Producttabs_Model_System_Config_Source_OrderDirection{
	public function toOptionArray(){
		return array(
			array('value' => 'asc',			'label' => Mage::helper('producttabs')->__('Asc')),
			array('value' => 'desc', 		'label' => Mage::helper('producttabs')->__('Desc'))
		);
	}
}
