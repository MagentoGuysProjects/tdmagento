<?php

$this->startSetup();

$categoryEntityTypeId = $this->getEntityTypeId('catalog_category');

$this->removeAttribute($categoryEntityTypeId, 'i8style_menulink');
$this->addAttribute($categoryEntityTypeId, 'i8style_menulink', array(
    'group'				=> 'I8style Menu',
    'label'				=> 'Hide link',
    'note'				=> "This field is applicable only for top-level categories.",
    'type'				=> 'varchar',
    'input'				=> 'select',
    'source'			=> 'i8style/system_config_source_category_status',
    'visible'			=> true,
    'required'			=> false,
    'backend'			=> '',
    'frontend'			=> '',
    'searchable'		=> false,
    'filterable'		=> false,
    'comparable'		=> false,
    'user_defined'		=> true,
    'visible_on_front'	=> true,
    'wysiwyg_enabled'	=> false,
    'is_html_allowed_on_front'	=> false,
    'global'			=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
 ));

$this->removeAttribute($categoryEntityTypeId, 'i8style_groupsubcat');
$this->addAttribute($categoryEntityTypeId, 'i8style_groupsubcat', array(
    'group'				=> 'I8style Menu',
    'label'				=> 'Group Subcategories',
    'note'				=> "This field is applicable only for top-level categories.",
    'type'				=> 'varchar',
    'input'				=> 'select',
    'source'			=> 'i8style/system_config_source_category_status',
    'visible'			=> true,
    'required'			=> false,
    'backend'			=> '',
    'frontend'			=> '',
    'searchable'		=> false,
    'filterable'		=> false,
    'comparable'		=> false,
    'user_defined'		=> true,
    'visible_on_front'	=> true,
    'wysiwyg_enabled'	=> false,
    'is_html_allowed_on_front'	=> false,
    'global'			=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
 ));

$this->removeAttribute($categoryEntityTypeId, 'i8style_subcat_w');
$this->addAttribute($categoryEntityTypeId, 'i8style_subcat_w', array(
    'group'				=> 'I8style Menu',
    'label'				=> 'Width for subcategories block',
    'note'				=> "This field is applicable only for top-level categories and drop with subcategories and blocks.",
    'type'				=> 'varchar',
    'input'				=> 'select',
    'source'			=> 'i8style/system_config_source_category_colWidth',
    'visible'			=> true,
    'required'			=> false,
    'backend'			=> '',
    'frontend'			=> '',
    'searchable'		=> false,
    'filterable'		=> false,
    'comparable'		=> false,
    'user_defined'		=> true,
    'visible_on_front'	=> true,
    'wysiwyg_enabled'	=> false,
    'is_html_allowed_on_front'	=> false,
    'global'			=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
 ));

$this->removeAttribute($categoryEntityTypeId, 'i8style_subcat_colw');
$this->addAttribute($categoryEntityTypeId, 'i8style_subcat_colw', array(
    'group'				=> 'I8style Menu',
    'label'				=> 'Col width for subcategories',
    'note'				=> "This field is applicable only for top-level categories and group subcategories.",
    'type'				=> 'varchar',
    'input'				=> 'select',
    'source'			=> 'i8style/system_config_source_category_colWidth',
    'visible'			=> true,
    'required'			=> false,
    'backend'			=> '',
    'frontend'			=> '',
    'searchable'		=> false,
    'filterable'		=> false,
    'comparable'		=> false,
    'user_defined'		=> true,
    'visible_on_front'	=> true,
    'wysiwyg_enabled'	=> false,
    'is_html_allowed_on_front'	=> false,
    'global'			=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
 ));

$this->removeAttribute($categoryEntityTypeId, 'i8style_menutype');
$this->addAttribute($categoryEntityTypeId, 'i8style_menutype', array(
    'group'				=> 'I8style Menu',
    'label'				=> 'Dropdown with',
    'note'				=> "This field is applicable only for top-level categories.",
    'type'				=> 'varchar',
    'input'				=> 'select',
    'source'			=> 'i8style/system_config_source_category_types',
    'visible'			=> true,
    'required'			=> false,
    'backend'			=> '',
    'frontend'			=> '',
    'searchable'		=> false,
    'filterable'		=> false,
    'comparable'		=> false,
    'user_defined'		=> true,
    'visible_on_front'	=> true,
    'wysiwyg_enabled'	=> false,
    'is_html_allowed_on_front'	=> false,
    'global'			=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
 ));

$this->removeAttribute($categoryEntityTypeId, 'i8style_block_r');
$this->addAttribute($categoryEntityTypeId, 'i8style_block_r', array(
	'group'				=> 'I8style Menu',
	'label'				=> 'Block Right',
	'note'				=> "This field is applicable only for top-level categories.",
	'input'         	=> 'textarea',
	'type'          	=> 'text',
	'visible'       	=> true,
	'required'      	=> false,
	'user_defined'  	=> true,
	'global'        	=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
 ));

$this->removeAttribute($categoryEntityTypeId, 'i8style_block_t');
$this->addAttribute($categoryEntityTypeId, 'i8style_block_t', array(
	'group'				=> 'I8style Menu',
	'label'				=> 'Block Top',
	'note'				=> "This field is applicable only for top-level categories.",
	'input'         	=> 'textarea',
	'type'          	=> 'text',
	'visible'       	=> true,
	'required'      	=> false,
	'user_defined'  	=> true,
	'global'        	=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
 ));

$this->removeAttribute($categoryEntityTypeId, 'i8style_block_b');
$this->addAttribute($categoryEntityTypeId, 'i8style_block_b', array(
	'group'				=> 'I8style Menu',
	'label'				=> 'Block Bottom',
	'note'				=> "This field is applicable only for top-level categories.",
	'input'         	=> 'textarea',
	'type'          	=> 'text',
	'visible'       	=> true,
	'required'      	=> false,
	'user_defined'  	=> true,
	'global'        	=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
 ));

/*Adds WYSIWYG Support*/
$this->updateAttribute($categoryEntityTypeId, 'i8style_block_l', 'is_wysiwyg_enabled', 1);
$this->updateAttribute($categoryEntityTypeId, 'i8style_block_l', 'is_html_allowed_on_front', 1);
$this->updateAttribute($categoryEntityTypeId, 'i8style_block_r', 'is_wysiwyg_enabled', 1);
$this->updateAttribute($categoryEntityTypeId, 'i8style_block_r', 'is_html_allowed_on_front', 1);
$this->updateAttribute($categoryEntityTypeId, 'i8style_block_t', 'is_wysiwyg_enabled', 1);
$this->updateAttribute($categoryEntityTypeId, 'i8style_block_t', 'is_html_allowed_on_front', 1);
$this->updateAttribute($categoryEntityTypeId, 'i8style_block_b', 'is_wysiwyg_enabled', 1);
$this->updateAttribute($categoryEntityTypeId, 'i8style_block_b', 'is_html_allowed_on_front', 1);

$this->endSetup();

?>