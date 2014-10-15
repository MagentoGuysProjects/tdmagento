<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_DpsPro_Model_Observer
{
    public function paymentMethodIsActive($observer)
    {
        if (Mage::getStoreConfig('payment_services/foomandpspro/maxmind_hide_payment_methods_on_fraud')) {
            $methodInstance = $observer->getEvent()->getMethodInstance();
            if (Mage::getStoreConfig('payment/' . $methodInstance->getCode() . '/use_maxmind_fraud_detection')) {
                $result = $observer->getEvent()->getResult();
                $quote = $observer->getEvent()->getQuote();
                $maxmind = Mage::getModel('foomandpspro/method_common_maxmind');
                try {
                    $maxmindResult = $maxmind->getMaxmindFraudScore($quote, $methodInstance->getTransactionId());
                } catch (Exception $e) {
                    Mage::logException($e);
                }

                if (isset($maxmindResult['riskScore'])) {
                    $methodInstance->setFoomanDpsProMaxmindResult(json_encode($maxmindResult));
                    if ($maxmind->isFraud($maxmindResult['riskScore'], true)) {
                        $result->isAvailable = false;
                    }
                }
            }
        }
    }

    public function salesModelServiceQuoteSubmitBefore($observer)
    {
        $this->_processObserver($observer, true, false);
    }

    public function salesModelServiceQuoteSubmitAfter($observer)
    {
        $this->_processObserver($observer, false, true);
    }

    protected function _processObserver($observer, $before, $sendEmails)
    {
        $order = $observer->getEvent()->getOrder();
        $orderPayment = $order->getPayment();
        $quote = $observer->getEvent()->getQuote();
        $quotePayment = $quote->getPayment();
        $storeId = $quote->getStoreId();

        if (Mage::getStoreConfig('payment/' . $orderPayment->getMethod() . '/use_maxmind_fraud_detection', $storeId)) {
            $maxmind = Mage::getModel('foomandpspro/method_common_maxmind');
            /*
             * PxFusion integration
             */
            $maxmindResult = json_decode($quotePayment->getAdditionalData(), true);
            $saveQuotePayment = true;
            if (!empty($maxmindResult['riskScore'])) {
                $saveQuotePayment = false;
            }

            $orderPayment->setAdditionalInformation(
                'OrigTxnType',
                Mage::getStoreConfig('payment/' . $orderPayment->getMethod() . '/payment_action', $storeId)
            );

            if ($saveQuotePayment) {
                $methodInstance = $quotePayment->getMethodInstance();
                if ($methodInstance->getFoomanDpsProMaxmindResult()) {
                    $maxmindResult = json_decode($methodInstance->getFoomanDpsProMaxmindResult(), true);
                } else {
                    try {
                        $maxmindResult = $maxmind->getMaxmindFraudScore(
                            $quotePayment->getQuote(), $quotePayment->getTransactionId()
                        );
                        if ($maxmindResult) {
                            $methodInstance->setFoomanDpsProMaxmindResult(json_encode($maxmindResult));
                        }
                    } catch (Exception $e) {
                        Mage::logException($e);
                        return;
                    }
                }
            }
            if (Mage::getStoreConfig('payment/' . $orderPayment->getMethod() . '/debug', $storeId)) {
                Mage::helper('magebasedps')->debug($maxmindResult);
            }
            if ($before) {
                foreach ($maxmindResult as $key => $value) {
                    $orderPayment->setAdditionalInformation($key, $value);
                }
                /*
                 * Somehow quote payment additional data gets into order payment additional data and that's causes
                 * dps data helper to generate notices when trying to unserialize jsone_encoded data.
                 */
                $orderPayment->setAdditionalData('');
                if ($saveQuotePayment) {
                    if ($quotePayment->getAdditionalData()) {
                        $existingData = json_decode($quotePayment->getAdditionalData(), true);
                        $quotePayment->setAdditionalData(json_encode(array_merge($existingData, $maxmindResult)));
                    } else {
                        $quotePayment->setAdditionalData(json_encode($maxmindResult));
                    }
                }
            }

            if (isset($maxmindResult['riskScore']) && $maxmind->isFraud($maxmindResult['riskScore'])) {
                if($orderPayment->getMethod() == 'foomandpsprofusion') {
                    return;
                }
                $order = $orderPayment->getOrder();
                $fraudStatus = Mage::getStoreConfig(
                    'payment_services/foomandpspro/order_status_suspected_fraud',
                    $storeId
                );
                if (version_compare(Mage::getVersion(), '1.4.1.0', '>=')) {
                    $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, $fraudStatus, '');
                } else {
                    $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $fraudStatus, '');
                }
                if (!$before) {
                    $order->save();
                    if ($sendEmails) {
                        Mage::helper('foomandpspro')->sendFraudEmails($quote, $order);
                    }
                }
            }
        }
    }
}