<?php
class Sns_Producttabs_Model_System_Config_Source_ListEffects{
	public function toOptionArray(){
		return array(
		array('value'=>'slideBottom', 'label'=>Mage::helper('producttabs')->__('slideBottom')),
		array('value'=>'slideLeft', 'label'=>Mage::helper('producttabs')->__('slideLeft')),
		array('value'=>'slideRight', 'label'=>Mage::helper('producttabs')->__('slideRight')),
		array('value'=>'bounceIn', 'label'=>Mage::helper('producttabs')->__('bounceIn')),
		array('value'=>'bounceInRight', 'label'=>Mage::helper('producttabs')->__('bounceInRight')),
		array('value'=>'zoomIn', 'label'=>Mage::helper('producttabs')->__('zoomIn')),
		array('value'=>'zoomOut', 'label'=>Mage::helper('producttabs')->__('zoomOut')),
		array('value'=>'pageTop', 'label'=>Mage::helper('producttabs')->__('pageTop')),
		array('value'=>'pageBottom', 'label'=>Mage::helper('producttabs')->__('pageBottom')),
		array('value'=>'pageLeft', 'label'=>Mage::helper('producttabs')->__('pageLeft')),
		array('value'=>'pageRight', 'label'=>Mage::helper('producttabs')->__('pageRight')),
		array('value'=>'starwars', 'label'=>Mage::helper('producttabs')->__('starwars'))
		);
	}
}
