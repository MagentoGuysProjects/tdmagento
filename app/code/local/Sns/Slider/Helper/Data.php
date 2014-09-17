<?php

class Sns_Slider_Helper_Data extends Mage_Core_Helper_Abstract {
	public function getJQquery(){
		if (Mage::getStoreConfigFlag('slider_cfg/advanced/include_jquery')){
			if (null == Mage::registry('sns.jquery')){
				Mage::register('sns.jquery', 1);
				return 'sns/slider/jquery-1.7.2.min.js';
			}
		}
		return;
	}
	public function getJQqueryNoconflict(){
		if (Mage::getStoreConfigFlag('slider_cfg/advanced/include_jquery')){
			if (null == Mage::registry('sns.jquerynoconflict')){
				Mage::register('sns.jquerynoconflict', 1);
				return 'sns/slider/sns.noconflict.js';
			}
		}
		return;
	}
	public function getJSOWL(){
		if (null == Mage::registry('sns.owlcarousel')){
			Mage::register('sns.owlcarousel', 1);
			return 'sns/slider/owl.carousel.min.js';
		}
		return;
	}
}
?>