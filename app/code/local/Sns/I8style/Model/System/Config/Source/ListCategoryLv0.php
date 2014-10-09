<?php

class Sns_I8style_Model_System_Config_Source_ListCategoryLv0
{
	public function toOptionArray($addEmpty = true)
    {
        $options = array();
               
        $collection = Mage::getResourceModel('catalog/category_collection');
        $collection->addAttributeToSelect('name')->addPathFilter('^1/[0-9/]+')->load();
        $cats = array();
        
        foreach ($collection as $category) {
        	$c = new stdClass();
        	$c->label = $category->getName();
        	$c->value = $category->getId();
        	$c->level = $category->getLevel();
        	$c->parentid = $category->getParentId();
            $cats[$c->value] = $c;
        }
        $options['first'] = 'Before Navigation Menu';
        foreach($cats as $id => $c){
			if($c->level == 2){
				$options[$c->value] = 'After '.$c->label;
			}
        }
        $options['last'] = 'After Navigation Menu';
        return $options;
    }
}