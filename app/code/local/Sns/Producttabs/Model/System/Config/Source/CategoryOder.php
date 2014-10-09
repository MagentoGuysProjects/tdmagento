<?php
class Sns_Producttabs_Model_System_Config_Source_CategoryOder{
	public function toOptionArray(){
		return array(
		array('value'=>'title', 'label'=>Mage::helper('producttabs')->__('Title')),
		array('value'=>'random', 'label'=>Mage::helper('producttabs')->__('Random')),
		);
	}
}
