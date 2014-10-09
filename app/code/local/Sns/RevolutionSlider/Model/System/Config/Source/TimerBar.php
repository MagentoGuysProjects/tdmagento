<?php

class Sns_RevolutionSlider_Model_System_Config_Source_TimerBar{
	public function toOptionArray(){
		return array(
		array('value'=>'top', 'label'=>Mage::helper('revolutionslider')->__('Top')),
		array('value'=>'bottom', 'label'=>Mage::helper('revolutionslider')->__('Bottom'))
		);
	}
}
