<?php
class Sns_Producttabs_IndexController extends Mage_Core_Controller_Front_Action{

    public function IndexAction() {
	  $this->loadLayout();
      $this->renderLayout();
    }
	public function ajaxAction() {
		$layout   = Mage::getSingleton('core/layout');
		$block    = $layout->createBlock('producttabs/list');
		echo '{"listProducts":' . json_encode($block->toHtml()) .'}';
		$this->renderLayout();
    }
}