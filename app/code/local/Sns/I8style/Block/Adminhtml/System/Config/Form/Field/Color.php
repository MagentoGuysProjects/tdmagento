<?php
class Sns_I8style_Block_Adminhtml_System_Config_Form_Field_Color extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $time = $element->getHtmlId();
        $html = parent::_getElementHtml($element);
        
        $html .= $element->getAfterElementHtml();
        
        $html .= '<style>#'.$time.' + .minicolors-swatch {left: auto; right: 0; top: 0;} #'.$time.' {height: auto; padding-left: 2px;}</style>';
        
        $html .= '<script>';
        $html .= 	'jQuery(document).ready(function(){';
        $html .= 		'jQuery("#'.$time.'").minicolors();';
        $html .= 	'});';
        $html .= '</script>';

        return $html;
    }
}