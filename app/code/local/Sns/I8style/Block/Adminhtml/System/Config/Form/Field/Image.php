<?php
class Sns_I8style_Block_Adminhtml_System_Config_Form_Field_Image extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $time = $element->getHtmlId();
 //       $unique = rand().time();
        // Get the default HTML for this option
        //$html = parent::_getElementHtml($element);
         $url = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index', array(
                    'static_urls_allowed'   => 1,
                    'target_element_id'     => $time
                ));
         
        $html = '<input data-url="'.$url.'" id="'.$element->getHtmlId().'" name="'.$element->getName()
             .'" style="width: 220px;" value="'.$element->getEscapedValue().'" '.$element->serialize($element->getHtmlAttributes()).'/>'."\n";
        
        $html .= '<button title="Click to browser media" type="button" class="scalable " onclick="popup_wysiwyg('.$time.')" style=""><span><span><span>...</span></span></span></button>';
        $html .= '<button title="Click to clear value" type="button" class="scalable " onclick="on_clear_click('.$time.');" style=""><span><span><span>x</span></span></span></button>';

        $html .= '';
        
        $html.= $element->getAfterElementHtml();
        
        return $html;
    }
}