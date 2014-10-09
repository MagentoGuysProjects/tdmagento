<?php

class Sns_I8style_Model_System_Config_Source_ListStaticBlock {
    public function toOptionArray()
    {
		$staticBlocks = array();
		$staticBlocks[] = Mage::helper('adminhtml')->__('Please select static block ...');
		$cmsBlocksCollection = Mage::getModel('cms/block')->getCollection()->addFieldToFilter('is_active', 1);
		foreach($cmsBlocksCollection as $key => $cmsBlock){
			$staticBlocks[$cmsBlock->getIdentifier()] = $cmsBlock->getTitle();
		}
		return $staticBlocks;
    }
}
