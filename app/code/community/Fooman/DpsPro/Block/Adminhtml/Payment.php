<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_DpsPro_Block_Adminhtml_Payment extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'foomandpspro';
        $this->_controller = 'adminhtml_payment';
        $this->_headerText = Mage::helper('foomandpspro')->__(
            'DPS Manual Payments (only listing payments without order affiliation)'
        );
        parent::__construct();

        // Remove the add button
        $this->_removeButton('add');
    }

}
