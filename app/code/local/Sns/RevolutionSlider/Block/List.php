<?php

class Sns_RevolutionSlider_Block_List extends Mage_Core_Block_Template
{
	protected $_config = null;
	protected $_storeId = null;
	
	public function __construct($attributes = array()){
		parent::__construct();
		$this->_config = Mage::helper('revolutionslider/data')->get($attributes);
	}

	public function getConfig($name=null, $value=null){
		if (is_null($this->_config)){
			$this->_config = Mage::helper('revolutionslider/data')->get(null);
		}
		if (!is_null($name) && !empty($name)){
			$valueRet = isset($this->_config[$name]) ? $this->_config[$name] : $value;
			return $valueRet;
		}
		return $this->_config;
	}
	
	public function setConfig($name, $value=null){
		if (is_null($this->_config)) $this->getConfig();
		if (is_array($name)){
			$this->_config = array_merge($this->_config, $name);
			return;
		}
		if (!empty($name)){
			$this->_config[$name] = $value;
		}
		return true;
	}
	
	protected function _toHtml(){
		$template_file = 'sns/revolutionslider/default.phtml';
		$this->setTemplate($template_file);
		return parent::_toHtml();
	}

	public function getStoreId(){
		if (is_null($this->_storeId)){
			$this->_storeId = Mage::app()->getStore()->getId();
		}
		return $this->_storeId;
	}
	public function setStoreId($storeId=null){
		$this->_storeId = $storeId;
	}

	public function getConfigObject(){
		return $this->_config;
	}
}
