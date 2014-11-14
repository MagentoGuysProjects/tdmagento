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
 
class Jtech_Changeattributeset_Model_Observer
{

	public function addMassactionToProductGrid($observer)
	{
		$block = $observer->getBlock();
		if($block instanceof Mage_Adminhtml_Block_Catalog_Product_Grid || $block instanceof TBT_Enhancedgrid_Block_Catalog_Product_Grid){
			
			$sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
				->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
				->load()
				->toOptionHash();
			//was '*/*/massAttributeSet' but fails with TBT but now 'adminhtml/catalog_product/massAttributeSet'
			$block->getMassactionBlock()->addItem('commerceextensions_changeattributeset', array(
				'label'=> Mage::helper('catalog')->__('Change attribute set'),
				'url'  => $block->getUrl('adminhtml/catalog_product/massAttributeSet', array('_current'=>true)),
				'additional' => array(
					'visibility' => array(
						'name' => 'attribute_set',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('catalog')->__('Attribute Set'),
						'values' => $sets
					)
				)
			)); 			
		}
	}
	
}
