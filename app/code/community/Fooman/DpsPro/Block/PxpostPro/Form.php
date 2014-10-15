<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Block_PxpostPro_Form extends MageBase_DpsPaymentExpress_Block_Pxpost_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('fooman/dpspro/pxpostpro/form.phtml');
    }

    public function displayAddCcSave()
    {
        return (Mage::getSingleton('customer/session')->isLoggedIn()
            || Mage::getSingleton('checkout/session')->getQuote()->getCheckoutMethod()
            == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER)
        && Mage::helper('foomandpspro')->shouldOfferRebillForPxPost();
    }
}
