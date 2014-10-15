<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Method_PxpostPro extends MageBase_DpsPaymentExpress_Model_Method_Pxpost
{

    protected $_formBlockType = 'foomandpspro/pxpostPro_form';
    protected $_canReviewPayment = true;

    protected function getPaymentAction()
    {
        //We don't need to check COMPLETE and REFUND transactions for fraud
        if (parent::getPaymentAction() == MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_COMPLETE
            || parent::getPaymentAction() == MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_REFUND
        ) {
            return parent::getPaymentAction();
        }
        $riskScore = Mage::helper('magebasedps')->getAdditionalData($this->getPayment(), 'riskScore');
        if (Mage::getStoreConfig('payment/' . $this->_code . '/use_maxmind_fraud_detection') && isset($riskScore)) {
            if (Mage::getModel('foomandpspro/method_common_maxmind')->isFraud($riskScore)) {
                $this->getPayment()->setIsTransactionPending(true);
                $this->getPayment()->setIsFraudDetected(true);
                return MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_AUTHORIZE;
            }
        }
        return parent::getPaymentAction();
    }

    /**
     * create transaction object in xml and submit to server
     *
     * @return bool
     */
    public function buildRequestAndSubmitToDps()
    {
        $payment = $this->getPayment();

        $responseXml = $this->getRequestToDpsResult($payment, Mage::helper('foomandpspro')->getCcSaveFromPost());

        if ($responseXml && $this->_validateResponse($responseXml)) {
            $this->unsError();
            //update payment information with last transaction unless we are refunding or completing
            if ($this->getPaymentAction() != MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_REFUND
            ) {
                $this->setAdditionalData($responseXml, $payment);

                if (Mage::helper('foomandpspro')->getCcSaveFromPost()) {
                    $order = $this->getInfoInstance()->getOrder();
                    if (!$order->getId()) {
                        $order->save();
                    }
                    $billingAgreement = Mage::getModel('sales/billing_agreement')->load(
                        (string)$responseXml->Transaction[0]->DpsBillingId,
                        'reference_id'
                    );
                    $billingAgreement->setCustomerId($this->_getCustomerIdFromOrder())
                        ->setMethodCode($this->_code)
                        ->setReferenceId((string)$responseXml->Transaction[0]->DpsBillingId)
                        ->setStatus(Mage_Sales_Model_Billing_Agreement::STATUS_ACTIVE)
                        ->setAgreementLabel(
                            substr((string)$responseXml->Transaction[0]->CardNumber, 0, 4)."............"
                            .substr((string)$responseXml->Transaction[0]->CardNumber, -2)
                            . ' ' .substr((string)$responseXml->Transaction[0]->DateExpiry, 0, 2) . '/'
                            . '20'.substr((string)$responseXml->Transaction[0]->DateExpiry, 2)
                        )
                        ->setStoreId($this->getStore())
                        ->addOrderRelation($order->getId())
                        ->save();

                    if ($billingAgreement->getId()) {
                        if ($billingAgreement->isValid()) {
                            $message = Mage::helper('sales')->__(
                                'Created billing agreement #%s.', $billingAgreement->getId()
                            );
                        } else {
                            $message = Mage::helper('sales')->__('Failed to create billing agreement for this order.');
                        }
                        $order->addStatusHistoryComment($message);
                    }
                }
            }

            return true;
        }

        return false;

    }

    /**
     * Customer ID getter (either real from order or from quote)
     *
     * @return string
     */
    protected function _getCustomerIdFromOrder()
    {
        $info = $this->getInfoInstance();

        if ($this->_isPlaceOrder()) {
            return $info->getOrder()->getCustomer()->getId();
        } else {
            return $info->getQuote()->getCustomer()->getId();
        }
    }

    /**
     * Accepts the payment previously authorized
     *
     * @param Mage_Payment_Model_Info $payment
     * @return bool
     */
    public function acceptPayment(Mage_Payment_Model_Info $payment)
    {
        parent::acceptPayment($payment);
        return Mage::helper('foomandpspro')->acceptPayment($payment, $this);
    }

    /**
     * Denies the payment previously authorized
     *
     * @param Mage_Payment_Model_Info $payment
     * @return bool
     */
    public function denyPayment(Mage_Payment_Model_Info $payment)
    {
        parent::denyPayment($payment);
        return Mage::helper('foomandpspro')->denyPayment($payment, $this);
    }

}
