<?php
class Sns_producttabs_Model_System_Config_Source_LinkTargets{
	public function toOptionArray()
	{
		return array(
			array('value'=>'_self',	'label'=>Mage::helper('producttabs')->__('Same Window')),
        	array('value'=>'_blank','label'=>Mage::helper('producttabs')->__('New Window')),
			array('value'=>'_popup','label'=>Mage::helper('producttabs')->__('Popup Window'))
		);
	}
}
