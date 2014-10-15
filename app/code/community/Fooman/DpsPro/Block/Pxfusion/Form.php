<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Block_Pxfusion_Form extends Mage_Payment_Block_Form_Cc
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('fooman/dpspro/pxfusion/form.phtml');
    }

    public function getLogosToDisplay()
    {
        $_logos = Mage::getStoreConfig('payment/foomandpsprofusion/display_logos');
        if (!empty($_logos)) {
            return explode(",", $_logos);
        }
        return array();
    }

    public function displayAddCcSave()
    {
        return (Mage::getSingleton('customer/session')->isLoggedIn()
            || Mage::getSingleton('checkout/session')->getQuote()->getCheckoutMethod()
            == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER)
        && Mage::helper('foomandpspro')->shouldOfferRebillForPxFusion();
    }
}
