<?php 
    class Sns_I8style_Adminhtml_ImportController extends Mage_Adminhtml_Controller_Action{ 
        public function indexAction() {
            $this->getResponse()->setRedirect($this->getUrl("adminhtml/system_config/edit/section/sns_i8style_cfg/"));
        }
        public function blocksAction() {
            $isoverwrite = Mage::helper('i8style')->getCfg('install/overwrite_blocks');
            Mage::getSingleton('i8style/import_cms')->importCms('cms/block', 'blocks', $isoverwrite);
            $this->getResponse()->setRedirect($this->getUrl("adminhtml/system_config/edit/section/sns_i8style_cfg/"));
        }
        public function pagesAction() {
            $isoverwrite = Mage::helper('i8style')->getCfg('install/overwrite_pages');
            Mage::getSingleton('i8style/import_cms')->importCms('cms/page', 'pages', $isoverwrite);
            $this->getResponse()->setRedirect($this->getUrl("adminhtml/system_config/edit/section/sns_i8style_cfg/")); 
        }
    }
    //$config = Mage::getStoreConfig('section_name/group/field'); //value
?>