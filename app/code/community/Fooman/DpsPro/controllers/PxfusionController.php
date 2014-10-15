<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Fooman_DpsPro_PxfusionController extends Mage_Core_Controller_Front_Action
{

    /**
     * Checks the transaction details by using the given sessionId value which is a part of the return url.
     *
     * @return string json encoded
     */
    public function getTransactionAction()
    {
        $session = Mage::getSingleton('checkout/session');
        //session exists = user with browser
        if ($session->getLastOrderId()) {
            $this->getResponse()->setHeader('Content-type', 'text/html');
            $dpsClient = Mage::getModel('foomandpspro/method_pxFusion')->getDpsClient();
            $transaction = $dpsClient->GetTransaction($this->getRequest()->getParam('sessionid'));
            if ($transaction) {
                $order = Mage::getModel('sales/order')->loadByIncrementId(
                    $transaction->GetTransactionResult->merchantReference
                );
                $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
                $paymentMethod = Mage::getModel('foomandpspro/method_pxFusion');
                $payment = $this->_saveTransactionDetails($order, $transaction, $paymentMethod);
            } else {
                $quote = false;
                $payment = false;
            }

            if ($payment && $transaction->GetTransactionResult->status == 0) {
                /*
                 * Invoice processing
                 */
                try {
                    if ($transaction->GetTransactionResult->txnType
                        == MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_PURCHASE
                    ) {
                        if (!$order->canInvoice()) {
                            Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
                        }
                        $payment->registerCaptureNotification($transaction->GetTransactionResult->amount);
                    } else {
                        $payment->setIsTransactionClosed(0);
                        $payment->registerAuthorizationNotification($transaction->GetTransactionResult->amount);
                        $order->setStatus(Mage::getStoreConfig('payment/' . $paymentMethod->getCode() . '/order_status'));
                    }
                    $order->sendNewOrderEmail()
                        ->setIsCustomerNotified(true)
                        ->save();
                } catch (Exception $e){
                    try {
                        $this->_cancelOrder($transaction);
                    } catch (Exception $e){
                        Mage::logException($e);
                    }

                    /*
                     * Idev_OneStepCheckout states it is necessary to recalculate shipping costs.
                     * Reason unclear.
                     * TODO investigate
                     */
                    //$quote->getShippingAddress()->setCollectShippingRates(true)->collectTotals();
                    $this->_reactivateQuote($quote);

                    Mage::logException($e);
                    $this->getResponse()->setBody($this->getErrorMessageResponse($transaction));
                    return;
                }

                Mage::log(
                    'getTransaction: ' . "\n" . var_export($transaction, true),
                    null,
                    Fooman_DpsPro_Model_Method_PxFusion::DPS_LOG_FILENAME
                );
                /*
                 * MaxMind integration
                 */
                $maxmind = Mage::getModel('foomandpspro/method_common_maxmind');
                $riskScore = $order->getPayment()->getAdditionalInformation('riskScore');
                if (isset($riskScore) && $maxmind->isFraud($riskScore)) {
                    $fraudStatus = Mage::getStoreConfig(
                        'payment_services/foomandpspro/order_status_suspected_fraud',
                        $order->getStoreId()
                    );
                    if (version_compare(Mage::getVersion(), '1.4.1.0', '>=')) {
                        $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, $fraudStatus, '');
                    } else {
                        $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $fraudStatus, '');
                    }
                    $order->save();
                    Mage::helper('foomandpspro')->sendFraudEmails($quote, $order);
                }
                /*
                 * Sending response back to the browser
                 */
                $quote->setIsActive(0)->save();
                Mage::getSingleton('checkout/session')->replaceQuote($quote)
                    ->setLastQuoteId($quote->getId())
                    ->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastSuccessQuoteId($quote->getId());
                $this->getResponse()->setBody(
                    '<html><head></head><body><script>
                    window.parent.location = window.parent.foomanSuccessUrl;
                    </script></body></html>'
                );

            } else {
                if ($quote) {
                    $this->_reactivateQuote($quote);
                    Mage::getSingleton('checkout/cart')->setQuote($quote);
                }

                $this->_cancelOrder($transaction, $quote);

                Mage::log(
                    'Transaction ERROR(dump): ' . var_export($transaction, true),
                    null,
                    Fooman_DpsPro_Model_Method_PxFusion::DPS_LOG_FILENAME
                );
                $this->getResponse()->setBody($this->getErrorMessageResponse($transaction));
                return;
            }
        }
    }

    protected function getErrorMessageResponse($transaction)
    {
        if (isset($transaction->GetTransactionResult->responseCode)) {
            $common = Mage::getModel('magebasedps/method_common');
            $errorMessage = sprintf(
                'There has been an error processing your payment (%s). Please try again later or contact us for help.',
                $common->returnErrorExplanation($transaction->GetTransactionResult->responseCode)
            );
        } else {
            $errorMessage = Mage::helper('foomandpspro')->__(
                'There has been an error processing your payment. Please try again later or contact us for help.'
            );
        }
        return '<html><head><body><script>
            alert("' . $errorMessage . '");
                if(typeof window.parent.review != "undefined") {
                    window.parent.review.resetLoadWaiting();
                }
                if(window.parent.document.getElementById("foomandpsprofusion_cc_owner")){
                    window.parent.document.getElementById("foomandpsprofusion_cc_owner").disabled = false;
                    window.parent.document.getElementById("foomandpsprofusion_cc_number").disabled = false;
                    window.parent.document.getElementById("foomandpsprofusion_expiration").disabled = false;
                    window.parent.document.getElementById("foomandpsprofusion_expiration_yr").disabled = false;
                    window.parent.document.getElementById("foomandpsprofusion_cc_cid").disabled = false;
                    window.parent.document.getElementById("foomandpsprofusion_cc_type").disabled = false;
                }
                if(typeof window.parent.checkout != "undefined") {
                    window.parent.checkout.gotoSection("payment");
                }
                if(typeof window.parent.awOSCForm != "undefined") {
                    window.parent.awOSCForm.enablePlaceOrderButton();
                    window.parent.awOSCForm.hidePleaseWaitNotice();
                }
                </script></body></head></html>';
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     */
    protected function _reactivateQuote(Mage_Sales_Model_Quote $quote)
    {
        $session =  Mage::getSingleton('checkout/session');
        $quote->removePayment();
        $quote->setIsActive(1)
            ->setReservedOrderId(NULL)
            ->save();
        $session->replaceQuote($quote);
        $session->unsLastRealOrderId();
    }

    /**
     * @param $transaction
     */
    protected function _cancelOrder($transaction)
    {
        //We need to reinitiate $order because of transaction rollbacks in the previous code.
        $order = Mage::getModel('sales/order')->loadByIncrementId(
            $transaction->GetTransactionResult->merchantReference
        );
        //We have to cancel it to roll back the inventory quantities
        $order->cancel();
        $order->save();

        /*
         * The following required by Mage_Sales_Model_Order::_beforeDelete()

        Mage::register('isSecureArea', true);
        $order->delete();
        Mage::unregister('isSecureArea');*/
    }

    /**
     * @param $order
     * @param $transaction
     * @param $paymentMethod
     *
     * @return mixed
     */
    protected function _saveTransactionDetails(Mage_Sales_Model_Order $order, $transaction, $paymentMethod)
    {
        $payment = $order->getPayment();
        $payment->setTransactionId($transaction->GetTransactionResult->txnRef);
        $paymentMethod->setAdditionalData($transaction, $payment);
        $payment->save();
        $paymentMethod->setPxFusionSession($transaction);
        return $payment;
    }
}
