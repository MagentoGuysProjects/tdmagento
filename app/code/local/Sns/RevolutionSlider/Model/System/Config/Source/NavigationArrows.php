<?php

class Sns_RevolutionSlider_Model_System_Config_Source_NavigationArrows{
	public function toOptionArray(){
		return array(
		array('value'=>'solo', 'label'=>Mage::helper('revolutionslider')->__('solo')),
		array('value'=>'nextto', 'label'=>Mage::helper('revolutionslider')->__('nextto')),
		array('value'=>'none', 'label'=>Mage::helper('revolutionslider')->__('none')),
		);
	}
}
