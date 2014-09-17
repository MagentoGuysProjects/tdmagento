<?php
//$themeCfg = Mage::helper('i8style/data')->get();
//$themeCfg->getField
class Sns_I8style_Helper_Data extends Mage_Core_Helper_Abstract {
	public function __construct(){
		$this->defaults = array();
	}
	public function get($attributes=array()) {
		$data 						= $this->defaults;
		
		$config = array();
		
		foreach(Mage::getStoreConfig("sns_i8style_cfg") as $k => $group){
			$groupName = $k;
			foreach($group as $key => $value){
				$config[$groupName.'_'.$key] = $value;
			}
		}
		
		if (is_array($config)) 				$data = array_merge($data, $config);
		if (!is_array($attributes))			$attributes = array($attributes);

		$cookies = Mage::getModel('core/cookie')->get();
		if(!is_null(Mage::app()->getRequest()->getParam('sns_clearcookie'))){
			foreach($cookies as $key => $value) {
				if(preg_match('/^sns_i8style_/', $key)){
					Mage::getModel('core/cookie')->delete($key, '/');
				}
			}
		} else {
			if($data['advance_showCpanel'] == 1) {
				foreach($cookies as $key => $value) {
					$key = preg_replace('/^sns_i8style_/', '', $key);
					if(isset($data[$key])) {
						$data[$key] = $value;
					}
				}
			}
		}

		
		return array_merge($data, $attributes);;
	}
	public function getField($field) {
		$data = $this->get();
		return $data[$field];
	}
	public function getImgSize($size) {
		$size = strtoupper($size);
		
		$imgRate = 0.8;
		$imgS_w = 60; // small img
		$imgM_w = 90; // detail thumb img
		$imgL_w = 200; // grid product img
		$imgXL_w = 400; // detail big img
		$imgXXL_w = 600;
		
		if($size == 'S') return array($imgS_w, $imgS_w / $imgRate);
		if($size == 'M') return array($imgM_w, $imgM_w / $imgRate);
		if($size == 'L') return array($imgL_w, $imgL_w / $imgRate);
		if($size == 'XL') return array($imgXL_w, $imgXL_w / $imgRate);
		if($size == 'XXL') return array($imgXXL_w, $imgXXL_w / $imgRate);
		return;
		//	$imgSize = Mage::helper('i8style/data')->getImgSize(S);
		//	$imgSize[0], $imgSize[1]
	}
	public function getThemeVersion() {
		$version = (string) Mage::getConfig()->getNode()->modules->Sns_I8style->title;
		$version .= ' '.(string) Mage::getConfig()->getNode()->modules->Sns_I8style->version; 
		return $version;
	}
	public function getThemeCSS($attributes=array()) {
		$themeCssName = 'theme-' . str_replace("#", "", $this->getField('advance_themeColor'));
		return 'css/'.$themeCssName.'.css';
	}
	public function checkBrowser () {
		if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/chrome/i',$_SERVER['HTTP_USER_AGENT'])) return 'Chrome';
		if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/safari/i',$_SERVER['HTTP_USER_AGENT'])) return 'Safari';
		if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/opera/i',$_SERVER['HTTP_USER_AGENT'])) return 'OP';
		if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/msie/i',$_SERVER['HTTP_USER_AGENT'])) return 'IE';
	}
	public function getBrowser () {
		if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/chrome/i',$_SERVER['HTTP_USER_AGENT'])) return 'chrome';
		if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/safari/i',$_SERVER['HTTP_USER_AGENT'])) return 'safari';
		if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/opera/i',$_SERVER['HTTP_USER_AGENT'])) return 'op';
		if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/msie/i',$_SERVER['HTTP_USER_AGENT'])) {
			preg_match('/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT'], $reg); //Zend_Debug::dump($reg); die();
			if(!isset($reg[1])) {
				return 'ie';
			} else {
				return 'ie' . ' ie' . floatval($reg[1]);
			}
		}
	}
	
    public function getCfg($optionString) {
        return Mage::getStoreConfig('sns_i8style_cfg/' . $optionString);
    }
	public function delCacheCss ($directory, $minute) {
	    if ($directory != '.') {
	        $directory = rtrim($directory, '/') . '/';
	    }
	    if ($handle = opendir($directory)) {
	        while (false !== ($file = readdir($handle))) {
	            if ($file != '.' && $file != '..') {
					if(preg_match("/^theme-/i", $file) && preg_match("/css$/i", $file)) {
					    $filePath = $directory.$file;
					    $time_elapsed = (time() - filemtime($filePath)) / 60;
						if($time_elapsed > $minute){
							unlink($filePath);
						}
					}
	            }
	        }
	        closedir($handle);
	    }
	}
	public function compileLess () {
		$skin_path = Mage::getDesign()->getSkinBaseDir();
		
		$themeColor = $this->getField('advance_themeColor');
		
		$themeCssName = 'theme-' . str_replace("#", "", $themeColor);
		$themeCssPath = $skin_path.'/css/'.$themeCssName.'.css';
		
		$less = new lessc;
		 
		$less->setVariables(array(
			"color1" => $themeColor
		));
		$less->setFormatter("compressed");
		
		if($this->getField('advance_showCpanel')) {
		//	$this->delCacheCss($skin_path.'/css/', 10);
		}
		if($this->getField('advance_lessEnabled')) {
			$less->compileFile($skin_path . '/less/theme.less', $themeCssPath);
		} elseif(!file_exists($themeCssPath)) {
			$less->compileFile($skin_path . '/less/theme.less', $themeCssPath);
		}
	}
	public function getAttributeAdminLabel($attributeCode, $item){
	    ///trunk/app/code/core/Mage/Eav/Model/Config.php
	    $entityType = Mage::getModel('eav/config')->getEntityType('catalog_product');
	    //$entityTypeId = $entityType->getEntityTypeId();
	    $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode($entityType, $attributeCode);
	    $_collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
	                      ->setAttributeFilter($attributeModel->getId())
	                    ->setStoreFilter(0)
	                    ->load();
	 
	    foreach( $_collection->toOptionArray() as $_cur_option ) {
	        if ($_cur_option['value'] == $item->getValue()){ return $_cur_option['label']; }
	    }
	    return $item->getLabel();
	}
}















