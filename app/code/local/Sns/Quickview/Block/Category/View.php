<?php
class Sns_Quickview_Block_Category_View extends Mage_Catalog_Block_Category_View {
    public function getProductListHtml(){
        return $this->getChildHtml('product_list');
    }
}