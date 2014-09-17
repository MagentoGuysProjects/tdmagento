<?php

class Sns_I8style_Block_Adminhtml_Button_Import_Config extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $el)
    {
		$data = $el->getOriginalData();
		if (isset($data['process']))
			$process = $data['process'];
		else
			return '<div>Action was not specified</div>';
		
		$buttonSuffix = '';
		if (isset($data['label']))
			$buttonSuffix = ' ' . $data['label'];

		$url = $this->getUrl('i8style/adminhtml_config/' . $process);
		
		$html = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setType('button')
			->setClass('import-config')
			->setLabel('' . $buttonSuffix)
			->setOnClick("setLocation('$url')")
			->toHtml();
			
        return $html;
    }
}
