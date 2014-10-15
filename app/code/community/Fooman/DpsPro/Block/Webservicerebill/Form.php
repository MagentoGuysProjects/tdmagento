<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_DpsPro_Block_Webservicerebill_Form extends Fooman_DpsPro_Block_Pxpostrebill_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setMethodCode(Mage::getModel('foomandpspro/method_webserviceRebill')->getCode());
    }

    public function getBillingAgreements()
    {
        $activeWebserviceRebill = Mage::helper('foomandpspro')->isWebserviceRebillActive();
        if ((Mage::getSingleton('customer/session')->isLoggedIn()
                || Mage::getSingleton('checkout/session')->getQuote()->getCheckoutMethod()
                == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER) && $activeWebserviceRebill) {
            return Mage::getModel('foomandpspro/method_webservicerebill')->getBillingAgreements()->getItems();
        }
        return array();
    }
}
