<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Method_PxpostProRebill extends Fooman_DpsPro_Model_Method_PxpostPro
{
    protected $_code = 'foomandpspropxpostrebill';
    protected $_formBlockType = 'foomandpspro/pxpostrebill_form';
    protected $_infoBlockType = 'foomandpspro/pxpostrebill_info';


    public function getBillingAgreements()
    {
        $collection = Mage::getModel('sales/billing_agreement')->getAvailableCustomerBillingAgreements(
            Mage::getSingleton('customer/session')->getCustomer()->getId()
        );

        $codePxPay = Mage::getModel('magebasedps/method_pxpay')->getCode();
        $activePxPay = Mage::getStoreConfigFlag(
            'payment/' . $codePxPay . '/active', Mage::app()->getStore()->getId()
        );
        $frontendPxPay = Mage::getStoreConfigFlag(
            'payment/' . $codePxPay . '/frontend_checkout', Mage::app()->getStore()->getId()
        );

        $conditions = array();
        if ($activePxPay && $frontendPxPay) {
            $conditions = array(
                array('eq' => Mage::getModel('magebasedps/method_pxpost')->getCode()),
                array('eq' => Mage::getModel('magebasedps/method_pxpay')->getCode())
            );
        } else {
            $conditions = array(
                array('eq' => Mage::getModel('magebasedps/method_pxpost')->getCode())
            );
        }

        $collection->addFieldToFilter('method_code', $conditions);

        //$collection->load();
        return $collection;
    }

    public function canUseCheckout()
    {
        $this->_canUseCheckout = false;
        if ((Mage::getSingleton('customer/session')->isLoggedIn()
            || Mage::getSingleton('checkout/session')->getQuote()->getCheckoutMethod()
            == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER)) {
            if (Mage::helper('foomandpspro')->isPxPostRebillActive()) {
                $collection = $this->getBillingAgreements();
                if ($collection->count()) {
                    $this->_canUseCheckout = true;
                }
            } else {
                $this->_canUseCheckout = false;
            }
        }
        return $this->_canUseCheckout;
    }

    public function validate()
    {
        if (!Mage::helper('foomandpspro')->getAgreementIdFromPost($this->_code)) {
            Mage::throwException(Mage::helper('foomandpspro')->__('Please select your payment details'));
        }
        return $this;
    }

    /**
     * retrieve PostUsername from database
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getPostUsername($storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        return Mage::helper('core')->decrypt(
            Mage::getStoreConfig(
                'payment/' . Mage::getModel('magebasedps/method_pxpost')->getCode()
                . '/postusername', $storeId
            )
        );
    }

    /**
     * retrieve PostPassword from database
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getPostPassword($storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        return Mage::helper('core')->decrypt(
            Mage::getStoreConfig(
                'payment/' . Mage::getModel('magebasedps/method_pxpost')->getCode()
                . '/postpassword', $storeId
            )
        );
    }

    /**
     * create transaction object in xml and submit to server
     *
     * @return bool
     */
    public function buildRequestAndSubmitToDps()
    {
        $payment = $this->getPayment();
        if ($this->getPaymentAction() == MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_COMPLETE
        ) {
            $dpsBillingId = Mage::helper('magebasedps')->getAdditionalData($payment, 'DpsBillingId');
        } elseif ($this->getPaymentAction() == MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_REFUND) {
            $dpsBillingId = false;
        } else {
            $dpsBillingId = Mage::helper('foomandpspro')->getDpsBillingId($this->_code);
        }

        $responseXml = $this->getRequestToDpsResult($payment, false, $dpsBillingId);

        if ($responseXml && $this->_validateResponse($responseXml)) {
            $this->unsError();
            //update payment information with last transaction unless we are refunding or completing
            if ($this->getPaymentAction() != MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_REFUND
            ) {
                $this->setAdditionalData($responseXml, $payment);
            }
            return true;
        }
        return false;
    }

}
