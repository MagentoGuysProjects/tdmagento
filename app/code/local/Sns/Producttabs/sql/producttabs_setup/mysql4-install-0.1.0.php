<?php
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('catalog_product', 'is_featured', array(
	'label' => 'Is Featured',
	'type' => 'int',
	'input' => 'select',
	'source' => 'eav/entity_attribute_source_boolean',
	'visible' => true,
	'required' => false,
	'position' => 6,
));