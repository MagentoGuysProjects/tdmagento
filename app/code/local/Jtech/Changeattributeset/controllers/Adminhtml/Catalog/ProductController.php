<?php
/************************************************************************
 * 
 * jtechextensions @ J-Tech LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.jtechextensions.com/LICENSE-M1.txt
 *
 * @package    Inventory Report
 * @copyright  Copyright (c) 2012-2013 jtechextensions @ J-Tech LLC. (http://www.jtechextensions.com)
 * @license    http://www.jtechextensions.com/LICENSE-M1.txt
************************************************************************/
 
class Jtech_Changeattributeset_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Controller_Action
{
	
	
	public function massAttributeSetAction()
	{
		$productIds = $this->getRequest()->getParam('product');
		$storeId = (int)$this->getRequest()->getParam('store', 0);
		$arrayOfFieldsBefore = array();
		$arrayOfFieldsAfter = array();
		
		if (!is_array($productIds)) {
			$this->_getSession()->addError($this->__('Please select product(s)'));
		}
		else {
			try {
				foreach ($productIds as $productId) {
				
					$productModelBefore = Mage::getModel('catalog/product')->load($productId);
					foreach ($productModelBefore->getData() as $field => $value) {
						$arrayOfFieldsBefore[] = $field;
					}
					
					$product = Mage::getSingleton('catalog/product')
						->unsetData()
						->setStoreId($storeId)
						->load($productId)
						->setAttributeSetId($this->getRequest()->getParam('attribute_set'))
						->setIsMassupdate(true)
						->save();
					
					$allow_delete_orpahn_records = (bool)Mage::getStoreConfig('changeattributeset/changeattributeset/delete_orpahn_records');
					if ($allow_delete_orpahn_records == true) {
					
					$productModel = Mage::getModel('catalog/product')->load($productId);
					foreach ($productModel->getData() as $field => $value) {
						$arrayOfFieldsAfter[] = $field;
					}
					$arrayoforphanattributes = array_diff($arrayOfFieldsBefore, $arrayOfFieldsAfter);
					#print_r($arrayoforphanattributes);
					if(!empty($arrayoforphanattributes)) {
						foreach ($arrayoforphanattributes as $field) {
							if($field != "") {
							 //lets delete orphan records also
							 $resource = Mage::getSingleton('core/resource');
							 $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
							 $read = $resource->getConnection('core_read');
							 $EntityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
						//this deletes custom price attribute values, like any attributes you added manually that are of type price.. not core price
						 $select_qry =$read->query("DELETE FROM `".$prefix."catalog_product_entity_decimal` WHERE entity_id =\"".$productId."\" and attribute_id = (SELECT attribute_id FROM ".$prefix."eav_attribute eav WHERE eav.entity_type_id = '".$EntityTypeId."' AND eav.attribute_code = '".$field."')");
							 
						//this deletes date values
						 $select_qry =$read->query("DELETE FROM `".$prefix."catalog_product_entity_datetime` WHERE entity_id =\"".$productId."\" and attribute_id = (SELECT attribute_id FROM ".$prefix."eav_attribute eav WHERE eav.entity_type_id = '".$EntityTypeId."' AND eav.attribute_code = '".$field."')");
						 
						 //this deletes dropdowns/radio buttons/checkbox values
						 $select_qry =$read->query("DELETE FROM `".$prefix."catalog_product_entity_int` WHERE entity_id =\"".$productId."\" and attribute_id = (SELECT attribute_id FROM ".$prefix."eav_attribute eav WHERE eav.entity_type_id = '".$EntityTypeId."' AND eav.attribute_code = '".$field."')");
						 
						 //this deletes textfield/text areas values
						 $select_qry =$read->query("DELETE FROM `".$prefix."catalog_product_entity_varchar` WHERE entity_id =\"".$productId."\" and attribute_id = (SELECT attribute_id FROM ".$prefix."eav_attribute eav WHERE eav.entity_type_id = '".$EntityTypeId."' AND eav.attribute_code = '".$field."')");
							}
						}
					}
					
					}//end check if enabled
				}
				#exit;
				Mage::dispatchEvent('catalog_product_massupdate_after', array('products'=>$productIds));
				$this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', count($productIds)));
				
			}
			catch (Exception $e) {
				$this->_getSession()->addException($e, $e->getMessage());
			}
		}
		$this->_redirect('adminhtml/catalog_product/index/', array());
		#$this->_redirect('*/*/', array('store'=>(int)$this->getRequest()->getParam('store', 0)));
	}	
}
