<?php

class Sns_RevolutionSlider_Helper_Data extends Mage_Core_Helper_Abstract {
	public function __construct(){
		$this->defaults = array(
			/* General options */
			'title' 		=> 'SNS Revolution Slider',
			'autoplay'		=> '1',
			'html_slides'	=>'',
			'include_jquery'	=> '1',
			'pretext'			=> '',
			'posttext'			=> ''

			/**config_fields**/
		);
	}

	function get($attributes=array())
	{
		$data = $this->defaults;
		$general 					= Mage::getStoreConfig("revolutionslider_cfg/general");
		$slideshow_effect				= Mage::getStoreConfig("revolutionslider_cfg/slideshow_effect");
		$advanced 					= Mage::getStoreConfig("revolutionslider_cfg/advanced");
		if (!is_array($attributes)) {
			$attributes = array($attributes);
		}
		if (is_array($general))					$data = array_merge($data, $general);
		if (is_array($slideshow_effect)) 		$data = array_merge($data, $slideshow_effect);
		if (is_array($advanced)) 				$data = array_merge($data, $advanced);

		return array_merge($data, $attributes);;
	}
	public function getJQquery(){
		if (Mage::getStoreConfigFlag('revolutionslider_cfg/advanced/include_jquery')){
			if (null == Mage::registry('sns.jquery')){
				Mage::register('sns.jquery', 1);
				return 'sns/revolutionslider/js/jquery.min.js';
			}
		}
		return;
	}
	public function getJQqueryNoconflict(){
		if (Mage::getStoreConfigFlag('revolutionslider_cfg/advanced/include_jquery')){
			if (null == Mage::registry('sns.jquerynoconflict')){
				Mage::register('sns.jquerynoconflict', 1);
				return 'sns/revolutionslider/js/sns.noconflict.js';
			}
		}
		return;
	}
	public function getJSRevolutionSlider(){
		if (null == Mage::registry('sns.revolutionslider')){
			Mage::register('sns.revolutionslider', 1);
			return 'sns/revolutionslider/js/jquery.themepunch.revolution.min.js';
		}
		return;
	}
	public function getJSThemepunchPlugins(){
		if (null == Mage::registry('sns.themepunchplugins')){
			Mage::register('sns.themepunchplugins', 1);
			return 'sns/revolutionslider/js/jquery.themepunch.plugins.min.js';
		}
		return;
	}
}
?>