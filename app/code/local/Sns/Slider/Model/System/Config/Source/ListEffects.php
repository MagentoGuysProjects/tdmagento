<?php
class Sns_Slider_Model_System_Config_Source_ListEffects{
	public function toOptionArray(){
		return array(
		array('value'=>'', 'label'=>Mage::helper('slider')->__('None')),
		array('value'=>'slideBottom', 'label'=>Mage::helper('slider')->__('slideBottom')),
		/*array('value'=>'slideLeft', 'label'=>Mage::helper('slider')->__('slideLeft')),
		array('value'=>'slideRight', 'label'=>Mage::helper('slider')->__('slideRight')),*/
		array('value'=>'bounceIn', 'label'=>Mage::helper('slider')->__('bounceIn')),
		array('value'=>'bounceInRight', 'label'=>Mage::helper('slider')->__('bounceInRight')),
		array('value'=>'zoomIn', 'label'=>Mage::helper('slider')->__('zoomIn')),
		array('value'=>'zoomOut', 'label'=>Mage::helper('slider')->__('zoomOut')),
		array('value'=>'pageTop', 'label'=>Mage::helper('slider')->__('pageTop')),
		array('value'=>'pageBottom', 'label'=>Mage::helper('slider')->__('pageBottom')),
		/*array('value'=>'pageLeft', 'label'=>Mage::helper('slider')->__('pageLeft')),
		array('value'=>'pageRight', 'label'=>Mage::helper('slider')->__('pageRight')),*/
		array('value'=>'starwars', 'label'=>Mage::helper('slider')->__('starwars'))
		);
	}
}
