<?php
class Sns_Quickview_Helper_Data extends Mage_Core_Helper_Abstract {
	public function getJQquery(){
		if (null == Mage::registry('sns.jquery')){
			Mage::register('sns.jquery', 1);
			return 'sns/quickview/js/jquery-1.7.2.min.js';
		}
		return;
	}
	public function getJQqueryNoconflict(){
		if (null == Mage::registry('sns.jquerynoconflict')){
			Mage::register('sns.jquerynoconflict', 1);
			return 'sns/quickview/js/sns.noconflict.js';
		}
		return;
	}
	public function getJSQuickview(){
		if (Mage::getStoreConfigFlag('quickview/general/enable')){
			if (null == Mage::registry('sns.quickview')){
				Mage::register('sns.quickview', 1);
				return 'sns/quickview/js/quickview.js';
			}
		}
		return;
	}
	public function getJSFancybox(){
		if (null == Mage::registry('sns.fancybox')){
			Mage::register('sns.fancybox', 1);
			return 'sns/quickview/js/jquery.fancybox-1.3.4.pack.js';
		}
		return;
	}
}