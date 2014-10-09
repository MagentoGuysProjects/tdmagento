<?php

class Sns_Producttabs_Helper_Data extends Mage_Core_Helper_Abstract {
	public function __construct(){
		$this->defaults = array(
			/* General setting */
			'isenabled'						=> '1',
			'title' 						=> 'SNS Product Tabs',
			'show_number_loadmore'			=>'1',
			'effects'						=>'slideBottom',
			/* Filter options */
			'list_products_by'      		=>'order',
			'order_type'             		=> array(),
			'product_category' 				=> array(),
			'product_order_by'				=> '',
			'product_order_dir' 			=> 'desc',
			'number_per_display' 			=> '4',
			'number_per_loadmore'			=> '4',
			/* Fisplay options */
			'item_image_width'				=> '370',
			'item_image_height'				=> '370',
			'item_title_display'			=> '1',
			'item_title_max_characs'		=> '20',
			'item_desc_display' 			=> '0',
			'item_desc_max_characs'			=> '150',
			'item_description_striptags' 	=> '1',
			'item_description_keeptags' 	=> '',
			'item_price_disp'				=> '1',
			'item_review_disp'				=> '1',
			'item_cart_disp'				=> '1',
			'item_wishlist_disp'			=> '1',
			'item_compare_disp'				=> '1',
			/* Advanced */
			'pretext'						=> '',
			'posttext'						=> '',
			'devices_wide'					=>'4',
			'devices_normal' 				=> '4',
			'devices_tabletlandscape' 		=> '4',
			'devices_tabletportrait' 		=> '3',
			'devices_mobilelandscape' 		=> '2',
			'devices_mobileportrait' 		=> '1'
		);
	}

	function get($attributes=array())
	{
		$data = $this->defaults;
		$general 					= Mage::getStoreConfig("producttabs_cfg/general");
		$product_selection 			= Mage::getStoreConfig("producttabs_cfg/product_selection");
		$product_ordering			= Mage::getStoreConfig("producttabs_cfg/product_ordering");
		$tabs_options				= Mage::getStoreConfig("producttabs_cfg/tabs_options");
		$product_display_setting 	= Mage::getStoreConfig("producttabs_cfg/product_display_setting");
		$advanced 					= Mage::getStoreConfig("producttabs_cfg/advanced");
		if (!is_array($attributes)) {
			$attributes = array($attributes);
		}
		if (is_array($general))					$data = array_merge($data, $general);
		if (is_array($product_selection)) 		$data = array_merge($data, $product_selection);
		if (is_array($product_ordering))        $data = array_merge($data, $product_ordering);
		if (is_array($tabs_options))            $data = array_merge($data, $tabs_options);
		if (is_array($product_display_setting)) $data = array_merge($data, $product_display_setting);
		if (is_array($advanced)) 				$data = array_merge($data, $advanced);
		
		return array_merge($data, $attributes);;
	}
	
	public function truncate($string, $length, $etc='...'){
		return defined('MB_OVERLOAD_STRING')
		? self::_mb_truncate($string, $length, $etc)
		: self::_truncate($string, $length, $etc);
	}
	
	/**
	 * Truncate string if it's size over $length
	 * @param string $string
	 * @param int $length
	 * @param string $etc
	 * @return string
	 */
	private static function _truncate($string, $length, $etc='...'){
		if ($length>0 && $length<strlen($string)){
			$buffer = '';
			$buffer_length = 0;
			$parts = preg_split('/(<[^>]*>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
			$self_closing_tag = split(',', 'area,base,basefont,br,col,frame,hr,img,input,isindex,link,meta,param,embed');
			$open = array();
	
			foreach($parts as $i => $s){
				if( false===strpos($s, '<') ){
					$s_length = strlen($s);
					if ($buffer_length + $s_length < $length){
						$buffer .= $s;
						$buffer_length += $s_length;
					} else if ($buffer_length + $s_length == $length) {
						if ( !empty($etc) ){
							$buffer .= ($s[$s_length - 1]==' ') ? $etc : " $etc";
						}
						break;
					} else {
						$words = preg_split('/([^\s]*)/', $s, - 1, PREG_SPLIT_DELIM_CAPTURE);
						$space_end = false;
						foreach ($words as $w){
							if ($w_length = strlen($w)){
								if ($buffer_length + $w_length < $length){
									$buffer .= $w;
									$buffer_length += $w_length;
									$space_end = (trim($w) == '');
								} else {
									if ( !empty($etc) ){
										$more = $space_end ? $etc : " $etc";
										$buffer .= $more;
										$buffer_length += strlen($more);
									}
									break;
								}
							}
						}
						break;
					}
				} else {
					preg_match('/^<([\/]?\s?)([a-zA-Z0-9]+)\s?[^>]*>$/', $s, $m);
					//$tagclose = isset($m[1]) && trim($m[1])=='/';
					if (empty($m[1]) && isset($m[2]) && !in_array($m[2], $self_closing_tag)){
						array_push($open, $m[2]);
					} else if (trim($m[1])=='/') {
						$tag = array_pop($open);
						if ($tag != $m[2]){
							// uncomment to to check invalid html string.
							// die('invalid close tag: '. $s);
						}
					}
					$buffer .= $s;
				}
			}
			// close tag openned.
			while(count($open)>0){
				$tag = array_pop($open);
				$buffer .= "</$tag>";
			}
			return $buffer;
		}
		return $string;
	}
	
	/**
	 * Truncate mutibyte string if it's size over $length
	 * @param string $string
	 * @param int $length
	 * @param string $etc
	 * @return string
	 */
	public function _mb_truncate($string, $length, $etc='...'){
		$encoding = mb_detect_encoding($string);
		if ($length>0 && $length<mb_strlen($string, $encoding)){
			$buffer = '';
			$buffer_length = 0;
			$parts = preg_split('/(<[^>]*>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
			$self_closing_tag = explode(',', 'area,base,basefont,br,col,frame,hr,img,input,isindex,link,meta,param,embed');
			$open = array();
	
			foreach($parts as $i => $s){
				if (false === mb_strpos($s, '<')){
					$s_length = mb_strlen($s, $encoding);
					if ($buffer_length + $s_length < $length){
						$buffer .= $s;
						$buffer_length += $s_length;
					} else if ($buffer_length + $s_length == $length) {
						if ( !empty($etc) ){
							$buffer .= ($s[$s_length - 1]==' ') ? $etc : " $etc";
						}
						break;
					} else {
						$words = preg_split('/([^\s]*)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
						$space_end = false;
						foreach ($words as $w){
							if ($w_length = mb_strlen($w, $encoding)){
								if ($buffer_length + $w_length < $length){
									$buffer .= $w;
									$buffer_length += $w_length;
									$space_end = (trim($w) == '');
								} else {
									if ( !empty($etc) ){
										$more = $space_end ? $etc : " $etc";
										$buffer .= $more;
										$buffer_length += mb_strlen($more);
									}
									break;
								}
							}
						}
						break;
					}
				} else {
					preg_match('/^<([\/]?\s?)([a-zA-Z0-9]+)\s?[^>]*>$/', $s, $m);
					//$tagclose = isset($m[1]) && trim($m[1])=='/';
					if (empty($m[1]) && isset($m[2]) && !in_array($m[2], $self_closing_tag)){
						array_push($open, $m[2]);
					} else if (trim($m[1])=='/') {
						$tag = array_pop($open);
						if ($tag != $m[2]){
							// uncomment to to check invalid html string.
							// die('invalid close tag: '. $s);
						}
					}
					$buffer .= $s;
				}
			}
			// close tag openned.
			while(count($open)>0){
				$tag = array_pop($open);
				$buffer .= "</$tag>";
			}
			return $buffer;
		}
		return $string;
	}
	public function getJQquery(){
		//if (Mage::getStoreConfigFlag('producttabs_cfg/advanced/include_jquery')){
			if (null == Mage::registry('sns.jquery')){
				Mage::register('sns.jquery', 1);
				return 'sns/producttabs/js/jquery-1.7.2.min.js';
			}
		//}
		return;
	}
	public function getJQqueryNoconflict(){
		//if (Mage::getStoreConfigFlag('producttabs_cfg/advanced/include_jquery')){
			if (null == Mage::registry('sns.jquerynoconflict')){
				Mage::register('sns.jquerynoconflict', 1);
				return 'sns/producttabs/js/sns.noconflict.js';
			}
		//}
		return;
	}
}
?>