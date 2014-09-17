<?php

class Sns_RevolutionSlider_Model_System_Config_Source_NavigationType{
	public function toOptionArray(){
		return array(
		array('value'=>'bullet', 'label'=>Mage::helper('revolutionslider')->__('Bullet')),
		array('value'=>'thumb', 'label'=>Mage::helper('revolutionslider')->__('Thumb')),
		array('value'=>'none', 'label'=>Mage::helper('revolutionslider')->__('None')),
		);
	}
}
