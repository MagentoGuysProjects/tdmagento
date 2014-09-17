<?php
class Sns_Producttabs_Block_List extends Mage_Catalog_Block_Product_Abstract{
	protected $_config = null;
	protected $products_viewed = null;

	public function __construct($attributes = array()){
		parent::__construct();
		$this->_config = Mage::helper('producttabs/data')->get($attributes);
	}
	public function getConfig($name=null, $value=null){
		if (is_null($this->_config)){
			$this->_config = Mage::helper('producttabs/data')->get(null);
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
			Mage::log($name);
			$this->_config = array_merge($this->_config, $name);
			return;
		}
		if (!empty($name)){
			$this->_config[$name] = $value;
		}
		return true;
	}
	public function getConfigObject(){
        return (object)$this->getConfig();
	}
	public function generateHash(){
		$config = $this->getConfig();
		$this->hash = md5( serialize($config) );
		return $this->hash;
	}
	public function _beforeHtml(){
		$this->generateHash();
	}
	protected function _toHtml(){
		if(!$this->getConfig('isenabled')) return;
		$type      = Mage::app()->getRequest()->getParam('pdt_type');
		if( $type!='' ){
			$template_file = 'sns/producttabs/items.phtml';
		}else{
			$template_file = 'sns/producttabs/default.phtml';
		}
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
	protected function getProductCollection($product_sort_by=''){
		$collection = Mage::getSingleton('catalog/product')->getCollection();
		$collection->addAttributeToSelect('*');
		$collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
		$visibility = array(
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
		);
		$collection->addAttributeToFilter('visibility', $visibility);
		// add price data
		$collection->addPriceData();
		$sort_by = explode(',', $this->_config['order_type']);
		if($product_sort_by==""){
			if( !empty($sort_by[0]) ){
				$product_sort_by = $sort_by[0];
			}else{
				$product_sort_by = 'created_at';
			}
		} //echo $product_sort_by.'<br/>';
		// add more attribute
		if ( $product_sort_by=='top_rating' || $product_sort_by=='most_reviewed' ){ //echo "dung<br/>";
			$this->_addReviewsCount($collection);
		}
		if ( $product_sort_by=='most_viewed' ){
			$this->_addViewsCount($collection);
		}
		if ( $product_sort_by=='best_sales' ){
			$this->_addOrderedCount($collection);
		}
		return $collection;
	}
	public function setProductCollection($collection=null){
		$this->_productCollection = $collection;
	}
	public function getTotalProduct($orderby=''){
		if ($this->getConfig('product_category')==''){
			return;
		}
		$params = Mage::app()->getRequest()->getParams();
		if(!empty($params['orderby'])){
			$orderby = $params['orderby'];
		}else{ //echo $orderby."<br/>"; die();
			$orderby = $orderby;
		}

		return $this->getProductsOrder($orderby, 1);
	}
	public function getListByOrder(){
		$order_by = preg_split("/\,/", $this->_config['order_type']);
		$tabs = array();
		while( count($order_by) ){
			$tab = array();
			$tab = trim(array_shift($order_by));
			array_push($tabs, $tab);
		}
		//Zend_Debug::dump($tabs); die('dd');
		$labels  = Mage::getModel('producttabs/system_config_source_orderBy')->toOptionArray(true);
		$list = array();
		$params = Mage::app()->getRequest()->getParams();

		$orderby = "";
		if ( $this->getConfig('product_category')=='' ){
			return array();
		}
		$i = 0;
		foreach ( $tabs as $tab ) { $i++;
			$itemTab		 = new stdClass();
			foreach ($labels as $title){
				if ( $tab == $title['value'] ){
					$itemTab->title = $title['label'];
				}
			}
			$itemTab->id 	 = $tab;
			$itemTab->orderid  = $tab;
			$total = count($this->getTotalProduct($tab)); //echo $total."<br/>";
			$itemTab->count = $total;
			if ( $type!='' ){
				$itemTab->child    = $this->getProductsOrder($tab);
			}
			if( $i == 1 ){
				$itemTab->child    = $this->getProductsOrder($tab);
				$itemTab->id       = $tabs['0'];
				$itemTab->active  = 1;
				$itemTab->orderid  = $tabs['0'];
			}
			$list[]= $itemTab;
		}
		return $list;
	}

	public function getProductsOrder($orderby, $isTotal = 0){
		$params = Mage::app()->getRequest()->getParams();
		$type = 'order';
		$orderby = (!empty($params['orderby']))?$params['orderby']:$orderby;
		$catids = (!empty($params['catids']))?$params['catids']:'';
		//echo $orderby."<br/>";
		$collection = $this->getProductCollection($orderby);
		$order_by = preg_split("/\,/", $this->_config['order_type']);
		$cat = array();
		while( count($order_by) ){
			$item = array();
			$item = trim(array_shift($order_by));
			array_push($cat, $item);
		}
		$items = array();
		$this->addReviewSummaryTemplate('sns', 'sns/producttabs/summary.phtml');
		$items = array();
		if ( Mage::registry('current_category') ){
			//is category view page.
			$current_category = Mage::registry('current_category');
			$current_category_id = $current_category->getId();
			$product_ids = $current_category->getProductCollection()->getAllIds();
			$collection->addIdFilter($product_ids);
			$category_ids = array();
		} else {
			if( $catids!='' ){
				$category_ids = preg_split("/[,\s\D]+/", $catids);
			} else {
				$category_ids = preg_split("/[,\s\D]+/", $this->_config['product_category']);
			}
			if (is_array($category_ids)){
				foreach ($category_ids as $i => $id) {
					if (!is_numeric($id)){
						unset($category_ids[$i]);
					}
				}
			}
		}
		//Zend_Debug::dump($category_ids); die();
		if (isset($category_ids) && count($category_ids)>0) $this->_addCategoryFilter($collection, $category_ids);

		$dir = strtolower( $this->_config['product_order_dir'] );
		if (!in_array($dir, array('asc', 'desc'))){
			$dir = 'asc';
		}

		$attribute_to_sort = $orderby;

		switch ($attribute_to_sort){
			case 'name':
			case 'created_at':
			case 'price':
				$collection->addAttributeToSort($attribute_to_sort, $dir);
				break;
			case 'position':
				break;
			case 'random':
				$collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
				break;
			case 'is_featured':
					$collection->addAttributeToFilter('is_featured', 1);
					break;
			case 'top_rating':
				$collection->getSelect()->order('sns_rating_summary '.$dir);
				break;
			case 'most_reviewed':
				$collection->getSelect()->order('sns_reviews_count '.$dir); //Zend_Debug::dump($collection->getSelect());
				break;
			case 'most_viewed':
				$collection->getSelect()->order('sns_views_count '.$dir);
				break;
			case 'best_sales':
				$collection->getSelect()->order('sns_ordered_count '.$dir);
				break;
		}
		//echo ((string)$collection->getSelect()).'<br/>'; //die();
		if( $catids!='' ){
			$numberstart = intval(Mage::app()->getRequest()->getParam('numberstart'));
			$number = ($numberstart==0)?$this->_config['number_per_display']:$this->_config['number_per_loadmore'];
			$collection->getSelect()->limit( $number, $numberstart );
		} else {
			if ( $this->_config['number_per_display'] > 0 && $isTotal == 0){
				$collection->setPageSize($this->_config['number_per_display']);
			}
		}
		$maxtitle = $this->getConfig('item_title_max_characs',-1);
		foreach( $collection as $k => $product ) {
			$product_obj = new stdClass();
			$product_obj->id = $product->getId();

			if ( $maxtitle  > 0 ){
				$product_obj->title = Mage::helper('producttabs/data')->truncate($product->getName(), $maxtitle, '');
			} else {
				$product_obj->title = $product->getName();
			}
			$description = $product->getShortDescription();
			if ( (int)$this->getConfig('item_description_striptags') == 1 ){
				$keep_tags = $this->getConfig('item_description_keeptags', '');
				$keep_tags = str_replace(array(' '), array(''), $keep_tags);
				$tmp_desc = strip_tags($description ,$keep_tags );
				$product_obj->description = $tmp_desc;
			} else {
				$product_obj->description = $description;
			}
			if (($maxchars=$this->getConfig('item_desc_max_characs',-1))>0){
				$product_obj->description = Mage::helper('producttabs/data')->truncate($product_obj->description, $maxchars, '');
			}
			$product_obj->image = (string)Mage::helper('catalog/image')->init($product, 'image')->resize($this->getConfig('item_image_width'), $this->getConfig('item_image_height'));
			$product_obj->link = $product->getProductUrl();
			$product_obj->price_html = $this->getPriceHtml($product, true);
			$product_obj->review_html = $this->getReviewsSummaryHtml($product, 'sns', true);
			$items[$product_obj->id] = $product_obj;
		}
		return $items;
	}
	public function getListByCategory(){
		$list = array();
		$params = Mage::app()->getRequest()->getParams();
		$orderid = $this->_config['product_order_by'];
		$all = new stdClass();
		$all->child = $this->getProductsCat('*', 0);
		$all->id   = '*';
		$all->count = count($all->child); //echo $all->count; die('ddd');
		$all->title = 'All Product';
		$all->active   = 1;
		$all->orderid   = $orderid;
		//array_unshift($list, $all);
		if ( $this->getConfig('product_category')=='' ){
			return array();
		}
		$storeId = Mage::app()->getStore()->getId();
		$categories = Mage::getModel('catalog/category')->getCollection();
		$categories->setStoreId($storeId);
		$categories->addIsActiveFilter();
		$categories->addAttributeToSelect('*');
		$categories->addIdFilter( $this->getConfig('product_category') );
		$i = 0;
		foreach ($categories as $category) {
			$i++;
			$itemTab		= new stdClass();
			$itemTab->id 	= $category->getId();
			$itemTab->title = $category->getName();
			$itemTab->link	= $category->getUrl();
			$products     = $category->getProductCollection();
			$items = array();
			foreach( $products as $product ){
				$item = new stdClass();
				$item->id = $product->getId();
				$stocklevel = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
				$stock = $product->getStockItem();
				$status = $stock->getIsInStock();
				if( $stocklevel > 0 && $status == '1' ){
					$items[$item->id] = $itemTab;
				}
			}
			$itemTab->orderid   = $orderid;
			if($i==1){
				$itemTab->child    = $this->getProductsCat($category->getId(), 0);
				$itemTab->active   = 1;
			} else {
				$itemTab->child    = "";
			}
			
			$itemTab->count    = count($this->getProductsCat($itemTab->id, 1));
			$list[]= $itemTab;
		} //Zend_Debug::dump($list); //die();
		return $list;
	}
	public function getProductsCat($catid , $isTotal=0){
		$params = Mage::app()->getRequest()->getParams(); //Zend_Debug::dump($params);
		$type = 'category';
		$catid = (!empty($params['catid']))?$params['catid']:$catid;

		$collection = $this->getProductCollection($this->_config['product_order_by']);
		$items = array();
		$this->addReviewSummaryTemplate('sns', 'sns/producttabs/summary.phtml');
		$items = array();
		if ( Mage::registry('current_category') ){
			//is category view page.
			$current_category = Mage::registry('current_category');
			$current_category_id = $current_category->getId();
			$product_ids = $current_category->getProductCollection()->getAllIds();
			$collection->addIdFilter($product_ids);
			$category_ids = array();
		} else {
			if( $catid != '*' ){
				$category_ids = preg_split("/[,\s\D]+/", $catid);
			} else {
				$category_ids = preg_split("/[,\s\D]+/", $this->_config['product_category']);
			}

			if (is_array($category_ids)){
				foreach ($category_ids as $i => $id) {
					if (!is_numeric($id)){
						unset($category_ids[$i]);
					}
				}
			}
		}

		if (isset($category_ids) && count($category_ids)>0) $this->_addCategoryFilter($collection, $category_ids); //Zend_Debug::dump($category_ids); //die();
		// Sort products in collection
		$dir = strtolower( $this->_config['product_order_dir'] );
		if (!in_array($dir, array('asc', 'desc'))){
			$dir = 'asc';
		}

		$attribute_to_sort = $this->_config['product_order_by'];
		switch ($attribute_to_sort){
			case 'name':
			case 'created_at':
			case 'price':
				$collection->addAttributeToSort($attribute_to_sort, $dir);
				break;
			case 'position':
				break;
			case 'random':
				$collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
				break;
			case 'is_featured':
					$collection->addAttributeToFilter('is_featured', 1);
					break;
			case 'top_rating':
				$collection->getSelect()->order('sns_rating_summary '.$dir);
				break;
			case 'most_reviewed':
				$collection->getSelect()->order('sns_reviews_count '.$dir);
				break;
			case 'most_viewed':
				$collection->getSelect()->order('sns_views_count '.$dir);
				break;
			case 'best_sales':
				$collection->getSelect()->order('sns_ordered_count '.$dir);
				break;
		}
		// Zend_Debug::dump((string)$collection->getSelect());
		if( !empty($params['catid']) ){
			$numberstart = intval(Mage::app()->getRequest()->getParam('numberstart'));
			$number = ($numberstart==0)?$this->_config['number_per_display']:$this->_config['number_per_loadmore'];
			$collection->getSelect()->limit( $number, $numberstart );
		} else {
			if ( $this->_config['number_per_display'] > 0 && $isTotal == 0){
				$collection->setPageSize($this->_config['number_per_display']);
			}
		}

		$maxtitle = $this->getConfig('item_title_max_characs',-1);
		foreach( $collection as $k => $product ) {
			$product_obj = new stdClass();
			$product_obj->id = $product->getId();

			if ( $maxtitle  > 0 ){
				$product_obj->title = Mage::helper('producttabs/data')->truncate($product->getName(), $maxtitle, '');
			} else {
				$product_obj->title = $product->getName();
			}
			$description = $product->getShortDescription();
			if ( (int)$this->getConfig('item_description_striptags') == 1 ){
				$keep_tags = $this->getConfig('item_description_keeptags', '');
				$keep_tags = str_replace(array(' '), array(''), $keep_tags);
				$tmp_desc = strip_tags($description ,$keep_tags );
				$product_obj->description = $tmp_desc;
			} else {
				$product_obj->description = $description;
			}
			if (($maxchars=$this->getConfig('item_desc_max_characs',-1))>0){
				$product_obj->description = Mage::helper('producttabs/data')->truncate($product_obj->description, $maxchars, '');
			}
			$product_obj->image = (string)Mage::helper('catalog/image')->init($product, 'image')->resize($this->getConfig('item_image_width'), $this->getConfig('item_image_height'));
			$product_obj->link = $product->getProductUrl();
			$product_obj->price_html = $this->getPriceHtml($product, true);
			$product_obj->review_html = $this->getReviewsSummaryHtml($product, 'sns', true);
			$product_obj->orderid =  $attribute_to_sort;
			$stocklevel = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
			$stock = $product->getStockItem();
			$status = $stock->getIsInStock();
			if( $status == '1' ){
				$items[$product_obj->id] = $product_obj;
			}
			//$items[$product_obj->id] = $product_obj;
		}
		return $items;
	}

	private function _addCategoryFilter(& $collection, $category_ids){
		$category_collection = Mage::getModel('catalog/category')->getCollection();
		$category_collection->addAttributeToSelect('*');
		$category_collection->addIsActiveFilter();
		if (count($category_ids)>0){
			$category_collection->addIdFilter($category_ids);
		}
		if (!Mage::helper('catalog/category_flat')->isEnabled()) {
		    $category_collection->groupByAttribute('entity_id');
		}
		$category_products = array();
		foreach ($category_collection as $category){
			$cid = $category->getId();
			if (!array_key_exists( $cid, $category_products)){
				$category_products[$cid] = $category->getProductCollection()->getAllIds();
				//Mage::log("ID: " . $cid );
				//Mage::log("collection->count(): " . count($category_products[$cid]) );
			}
		}
		$product_ids = array(); 
		//echo "-------- Begin ---------<br/>";
		//Zend_Debug::dump($category_products);
		if (count($category_products)){
			foreach ($category_products as $cp) {
				$product_ids = array_merge($product_ids, $cp);//Zend_Debug::dump($product_ids);
			}
		}
		//Zend_Debug::dump($product_ids); //die();
		//Mage::log("merged_count: " . count($product_ids));
		$collection->addIdFilter($product_ids);
	}
	private function _addViewsCount(& $collection, $views_count_alias="sns_views_count"){
		// add views_count
		$reports_event_table		= Mage::getSingleton('core/resource')->getTableName('reports/event');
		$reports_event_types_table 	= Mage::getSingleton('core/resource')->getTableName('reports/event_type');
		$collection->getSelect()
		->joinLeft(
			array("re_table" => $reports_event_table),
			"e.entity_id = re_table.object_id",
			array(
				$views_count_alias => "COUNT(re_table.event_id)"
			)
		)->joinLeft(
			array("ret_table" => $reports_event_types_table),
			"re_table.event_type_id = ret_table.event_type_id AND ret_table.event_name = 'catalog_product_view'",
			array()
		)->group('e.entity_id');
	}
	private function _addReviewsCount(& $collection, $reviews_count_alias="sns_reviews_count", $rating_summary_alias="sns_rating_summary" ){
		// add reviews_count and rating_summary
		$review_summary_table = Mage::getSingleton('core/resource')->getTableName('review/review_aggregate');
		$collection->getSelect()->joinLeft(
			array("rs_table" => $review_summary_table),
			"e.entity_id = rs_table.entity_pk_value AND rs_table.store_id=" . $this->getStoreId(),
			array(
				$reviews_count_alias  => "rs_table.reviews_count",
				$rating_summary_alias => "rs_table.rating_summary"
			)
		);
	}
	private function _addOrderedCount(& $collection, $ordered_qty_alias="sns_ordered_count"){
		$order_table = Mage::getSingleton('core/resource')->getTableName('sales/order');
		$read = Mage::getSingleton('core/resource')->getConnection ('core_read');
		$orders_active_query = $read->select()->from(array('o_table'=>$order_table), 'o_table.entity_id')->where("o_table.state<>'" . Mage_Sales_Model_Order::STATE_CANCELED . "'");
		$res = $orders_active_query->query()->fetchAll();
		$order_ids = array();
		if ( count($res) ){
			foreach($res as $row){
				array_key_exists('entity_id', $row) && array_push($order_ids, $row['entity_id']);
			}
		}
		$order_item_table = Mage::getSingleton('core/resource')->getTableName('sales/order_item');
		$collection->getSelect()->join(
				array('oi_table' => $order_item_table),
				'e.entity_id=oi_table.product_id'.(count($order_ids) ? ' AND oi_table.order_id IN('.implode(',', $order_ids).')' : ''),
				array(
						$ordered_qty_alias => 'SUM(oi_table.qty_ordered)'
				)
		);
		$collection->getSelect()->group('e.entity_id');
		return $this;
	}

}
