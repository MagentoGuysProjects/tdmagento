<?php
class Sns_I8style_Block_Adminhtml_System_Config_Form_Field_AddSocial extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('sns/i8style/system/config/form/field/array.phtml');
        }
        $this->addColumn('title', array(
            'label' => Mage::helper('adminhtml')->__('Title '),
            'style' => 'width:120px',
            'type' => 'input'
        ));
        $this->addColumn('link', array(
            'label' => Mage::helper('adminhtml')->__('Link'),
            'style' => 'width:120px',
            'type' => 'input',
        ));
        $this->addColumn('icon', array(
            'label' => Mage::helper('adminhtml')->__('Icon'),
            'style' => 'width:80px',
            'type' => 'input',
        ));
        $this->addColumn('target', array(
            'label' => Mage::helper('adminhtml')->__('Target'),
            'style' => 'width:70px',
            'type' => 'select',
            'values' => array(
            				'_self'		=> '_self',
            				'_blank'	=> '_blank',
				            '_parent'	=> '_parent',
				            '_top'		=> '_top'
        				)
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Item');
        parent::__construct();
    }
    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        $column     = $this->_columns[$columnName];
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        if ($column['renderer']) {
            return $column['renderer']->setInputName($inputName)->setColumnName($columnName)->setColumn($column)
                ->toHtml();
        }
		if ($column['type'] == 'input') {
	        $html = '<input type="text" name="' . $inputName . '" value="#{' . $columnName . '}" ' .
		            ($column['size'] ? 'size="' . $column['size'] . '"' : '') . ' class="' .
		            (isset($column['class']) ? $column['class'] : 'input-text') . '"'.
		            (isset($column['style']) ? ' style="'.$column['style'] . '"' : '') . '/>';
		}
		if ($column['type'] == 'select') {
			$html = '<select name="' . $inputName . '" ' .
		            ($column['size'] ? 'size="' . $column['size'] . '"' : '') . ' class="' .
		            (isset($column['class']) ? $column['class'] : 'input') . '"'.
		            (isset($column['style']) ? ' style="'.$column['style'] . '"' : '') . '>';
			
			$options = $column['values'];
			foreach ($options as $key => $option) {
				$html .= '<option data-selected="#{' . $columnName . '}" value="'.$key.'">'.$option.'</option>';
			}
			$html .= '</select>';
		}
		return $html;
    }
    public function addColumn($name, $params) {
        $this->_columns[$name] = array(
            'label'     => empty($params['label']) ? 'Column' : $params['label'],
            'size'      => empty($params['size'])  ? false    : $params['size'],
            'style'     => empty($params['style'])  ? null    : $params['style'],
            'class'     => empty($params['class'])  ? null    : $params['class'],
            'type'    	=> empty($params['type'])  ? null    : $params['type'],
            'values'   	=> empty($params['values'])  ? null    : $params['values'],
            'renderer'  => false,
        );
        if ((!empty($params['renderer'])) && ($params['renderer'] instanceof Mage_Core_Block_Abstract)) {
            $this->_columns[$name]['renderer'] = $params['renderer'];
        }
    }
}