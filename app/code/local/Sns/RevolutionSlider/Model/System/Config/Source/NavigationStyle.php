<?php

class Sns_RevolutionSlider_Model_System_Config_Source_NavigationStyle{
	public function toOptionArray(){
		return array(
		array('value'=>'round', 'label'=>Mage::helper('revolutionslider')->__('round')),
		array('value'=>'square', 'label'=>Mage::helper('revolutionslider')->__('square')),
		array('value'=>'navbar', 'label'=>Mage::helper('revolutionslider')->__('navbar')),
		array('value'=>'round-old', 'label'=>Mage::helper('revolutionslider')->__('round-old')),
		array('value'=>'square-old', 'label'=>Mage::helper('revolutionslider')->__('square-old')),
		array('value'=>'navbar-old', 'label'=>Mage::helper('revolutionslider')->__('navbar-old')),
		);
	}
}
