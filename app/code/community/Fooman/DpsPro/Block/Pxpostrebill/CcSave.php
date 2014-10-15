<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Block_Pxpostrebill_CcSave extends Mage_Core_Block_Template
{
    /**
     * return array of method codes supported by DPS Pro for saving payment details
     *
     * @return array
     */
    public function getMethodCodes()
    {
        $codes = array();
        $codes[] = Mage::getModel('magebasedps/method_pxpost')->getCode();
        $codes[] = Mage::getModel('magebasedps/method_pxpay')->getCode();
        $codes[] = Mage::getModel('foomandpspro/method_webservice')->getCode();
        return $codes;
    }

    /**
     * we show the option to save payment details
     * if we have PxPost Username and Password
     * and the user is logged in
     *
     * @return bool
     */
    public function getActiveMethodCodes()
    {
        $returnArray = array();
        if (!Mage::getSingleton('customer/session')->isLoggedIn()
            && Mage::getSingleton('checkout/session')->getQuote()->getCheckoutMethod()
            != Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER
        ) {
            return $returnArray;
        }

        if (Mage::helper('foomandpspro')->shouldOfferRebillForPxPay()) {
            $returnArray[] = Mage::getModel('magebasedps/method_pxpay')->getCode();
        }

        if (Mage::helper('foomandpspro')->shouldOfferRebillForPxPost()) {
            $returnArray[] = Mage::getModel('magebasedps/method_pxpost')->getCode();
        }

        if (Mage::helper('foomandpspro')->shouldOfferRebillForWebservice()) {
            $returnArray[] = Mage::getModel('foomandpspro/method_webservice')->getCode();
        }
        return $returnArray;
    }
}
