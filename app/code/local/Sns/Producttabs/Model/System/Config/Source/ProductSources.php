<?php

class Sns_Producttabs_Model_System_Config_Source_ProductSources{
	public function toOptionArray(){
		return array(
			array('value'=>'catalog',	'label'=>Mage::helper('producttabs')->__('Catalog')),
        	array('value'=>'product',	'label'=>Mage::helper('producttabs')->__('Product'))
		);
	}
}
