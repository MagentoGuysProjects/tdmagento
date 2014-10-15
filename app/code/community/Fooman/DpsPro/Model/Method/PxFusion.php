<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Method_PxFusion extends Mage_Payment_Model_Method_Abstract
{
    const CREATE_BILLING_AGREEMENT = 1;

    const URL_PXFUSION = 'https://sec.paymentexpress.com/pxf/pxf.svc';
    const URL_WSDL_PXFUSION = 'https://sec.paymentexpress.com/pxf/pxf.svc?wsdl';
    const URL_FORM_SUBMIT = 'https://sec.paymentexpress.com/pxmi3/pxfusionauth';
    const URL_PXFUSION_GET_TRANSACTION = 'foomandpspro/pxfusion/getTransaction';
    const URL_PXFUSION_GET_SESSION = 'foomandpspro/pxfusion/getSessionId';

    const DPS_LOG_FILENAME = 'foomandpspro_pxfusion.log';

    protected $_code = 'foomandpsprofusion';
    protected $_formBlockType = 'foomandpspro/pxfusion_form';
    protected $_infoBlockType = 'foomandpspro/pxfusion_info';
    protected $_canSaveCc     = false;

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_isInitializeNeeded = true;
    protected $_canReviewPayment = true;

    protected $_order;

    /**
     * Error Codes
     * Code =>  Description
     */
    public $statusCodes = array(
               0 => 'Transaction approved.',
               1 => 'Transaction declined.',
               2 => 'Transaction declined due to transient error (retry advised).',
               3 => 'Invalid data submitted in form post (alert site admin).',
               4 => 'Transaction result cannot be determined at this time (re-run GetTransaction).',
               5 => 'Transaction did not proceed due to being attempted after timeout timestamp or having been cancelled
                     by a CancelTransaction call.',
               6 => 'No transaction found (SessionId query failed to return a transaction record - transaction not yet
                     attempted).',
           );

    protected function _isActive()
    {
        return Mage::getStoreConfigFlag('payment/' . $this->_code . '/active', $this->getStore());
    }

    public function isTestMode()
    {
        return Mage::getStoreConfigFlag('payment/' . $this->_code . '/testmode', $this->getStore());
    }

    public function getCode()
    {
        return $this->_code;
    }

    /**
     * @return Fooman_DpsPro_Model_Method_PxFusion_SoapClient
     */
    public function getDpsClient()
    {
        $client = Mage::getModel(
            'foomandpspro/method_pxFusion_soapClient',
            array(
                 'soapUrl'  => self::URL_WSDL_PXFUSION,
                 'user'     => htmlentities($this->getFusionUsername($this->getStore())),
                 'password' => htmlentities($this->getFusionPassword($this->getStore()))
            )
        );
        /* @var $client Fooman_DpsPro_Model_Method_PxFusion_SoapClient */
        return $client;
    }

    /**
     * retrieve PostUsername from database
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getFusionUsername($storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        return Mage::helper('core')->decrypt(
            Mage::getStoreConfig('payment/' . $this->_code . '/pxfusionuserid', $storeId)
        );
    }

    /**
     * Retrieve PostPassword from database
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getFusionPassword($storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        return Mage::helper('core')->decrypt(
            Mage::getStoreConfig('payment/' . $this->_code . '/pxfusionkey', $storeId)
        );
    }

    /**
     * check if current currency code is allowed for this payment method
     *
     * @param string $currencyCode
     *
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        return Mage::helper('magebasedps')->canUseCurrency($currencyCode);
    }

    public function getOrderPlaceRedirectUrl()
    {
        $quote = $this->getInfoInstance()->getQuote();
        $grandTotal = trim(sprintf("%9.2f", Mage::getModel('core/store')->roundPrice($quote->getBaseGrandTotal())));
        $currencyCode = $quote->getBaseCurrencyCode();
        $returnUrl = Mage::getUrl(
            Fooman_DpsPro_Model_Method_PxFusion::URL_PXFUSION_GET_TRANSACTION,
            array('_nosid' => true, '_secure'=>true)
        );

        /*
         * Here we are getting txnType value for the requested to be posted based on the data from current
         * store's config.
         * Since DPS accepts only values of "Purchase, Auth" we have to map store's values to DPS's ones.
         */
        $txnType = '';
        $paymentAction = Mage::getStoreConfig(
            'payment/foomandpsprofusion/payment_action',
            $quote->getStoreId()
        );

        //We have to make maxmind check here prior to submitting actual payment to determine either it should be AUTH or
        //CAPTURE
        $quotePayment = $quote->getPayment();

        if (Mage::getStoreConfig('payment/foomandpsprofusion/use_maxmind_fraud_detection')){
            try{
                $quotePayment->setAdditionalInformation('OrigTxnType', $paymentAction);
                $maxmind = Mage::getModel('foomandpspro/method_common_maxmind');
                $maxmindResult = $maxmind->getMaxmindFraudScore(
                    $quote, $quotePayment->getTransactionId()
                );

                if (isset($maxmindResult['riskScore'])) {
                    if (Mage::getModel('foomandpspro/method_common_maxmind')->isFraud($maxmindResult['riskScore'])) {
                        $paymentAction = 'authorize';
                    }
                }
                //The following we do to prevent double call later in observer for other methods.
                if ($quotePayment->getAdditionalData()) {
                    $existingData = json_decode($quotePayment->getAdditionalData(), true);
                    $quotePayment->setAdditionalData(json_encode(array_merge($existingData, $maxmindResult)));
                } else {
                    $quotePayment->setAdditionalData(json_encode($maxmindResult));
                }
                $quotePayment->save();
            } catch (Exception $e){
                Mage::logException($e);
            }
        }

        switch($paymentAction) {
            case 'authorize':
                $txnType = MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_AUTHORIZE;
                break;
            case 'authorize_capture':
                $txnType = MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_PURCHASE;
                break;
        }
        $txnRef = Mage::helper('foomandpspro')->generateTxnId();

        $dpsClient = $this->getDpsClient();
        $params = array(
            'amount'            => $grandTotal,
            'currency'          => $currencyCode,
            'returnUrl'         => $returnUrl,
            'txnRef'            => $txnRef,
            'txnType'           => $txnType,
            'merchantReference' => htmlentities(
                Mage::helper('foomandpspro')->getMerchantReference($this->getInfoInstance()), ENT_QUOTES, 'UTF-8'
            ),
            'txnData1'          => htmlentities($quote->getStore()->getName(), ENT_QUOTES, 'UTF-8'),
            'txnData2'          => $quote->getId(),
            'enableAddBillCard' => Mage::helper('foomandpspro')->getCcSaveFromPost() ? 'True' : 'False',
            'txnData3'          => Mage::helper('foomandpspro')->getCcSaveFromPost()
        );

        $transport = new Varien_Object();
        $transport->setParams($params);
        Mage::dispatchEvent(
            'foomandpspro_pxfusion_params_before', array('method_instance' => $this, 'transport' => $transport)
        );
        $transactionIdResult = $dpsClient->GetTransactionId($transport->getParams());

        Mage::log(
            'getSessionId: Submitting to DPS ' . "\n" . var_export($params, true),
            null,
            Fooman_DpsPro_Model_Method_PxFusion::DPS_LOG_FILENAME
        );

        return $transactionIdResult->GetTransactionIdResult->sessionId;
    }

    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus(
            Mage::getStoreConfig('payment/' . $this->_code . '/unpaid_order_status', $this->getStore())
        );
        $stateObject->setIsNotified(false);
    }

    /**
     * Determines whether refund is available
     *
     * @return boolean
     */
    public function canRefund()
    {
        return $this->_isOnlineCreditmemoAvailable();
    }

    /**
     * Determines whether partial refund is available
     *
     * @return boolean
     */
    public function canRefundPartialPerInvoice()
    {
        return $this->_isOnlineCreditmemoAvailable();
    }

    /**
     * Determines whether online creditmemo is available
     *
     * @return boolean
     */
    protected function _isOnlineCreditmemoAvailable()
    {
        $pxpostPaymentModel = Mage::getModel('magebasedps/method_pxpost');
        $creditmemo = Mage::registry('current_creditmemo');
        $invoice = Mage::registry('current_invoice');
        if ($invoice) {
            $origDpsTxnRef = Mage::helper('magebasedps')->getAdditionalData(
                $invoice->getOrder()->getPayment(), 'DpsTxnRef'
            );
            return ($origDpsTxnRef && $pxpostPaymentModel->getPostUsername() && $pxpostPaymentModel->getPostPassword());
        } elseif ($creditmemo) {
            $origDpsTxnRef = Mage::helper('magebasedps')->getAdditionalData(
                $creditmemo->getOrder()->getPayment(), 'DpsTxnRef'
            );
            return ($origDpsTxnRef && $pxpostPaymentModel->getPostUsername() && $pxpostPaymentModel->getPostPassword());
        } else {
            return false;
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

    public function refund(Varien_Object $payment, $amount)
    {
        $pxpostPaymentModel = Mage::getModel('magebasedps/method_pxpost');
        $pxpostPaymentModel->setData('info_instance', $this->getInfoInstance());
        return $pxpostPaymentModel->refund($payment, $amount);
    }

    public function authorize(Varien_Object $payment, $amount)
    {
        $payment->setAdditionalInformation('payment_type', $this->getConfigData('payment_action'));
        $this->setAdditionalData($this->getPxFusionSession(), $payment);
    }

    /**
     * Processes the data taken from the session
     *
     * @param Varien_Object $payment
     * @param float         $amount
     *
     * @return Fooman_DpsPro_Model_Method_PxFusion
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if (Mage::helper('magebasedps')->getAdditionalData($payment, 'TxnType')
            == MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_AUTHORIZE
        ) {
            //This is the Complete Action to capture the funds
            $pxpostPaymentModel = Mage::getModel('magebasedps/method_pxpost');
            $pxpostPaymentModel->setData('info_instance', $this->getInfoInstance());
            return $pxpostPaymentModel->capture($payment, $amount);
        } else {
            if (!$this->_validateResponse($this->getPxFusionSession())) {
                $error = $this->getError();
                if (isset($error['message'])) {
                    $message = Mage::helper('core')->__('There has been an error processing your payment.')
                        . ' ' . $error['message'];
                } else {
                    $message = Mage::helper('core')->__(
                        'There has been an error processing your payment. Please try later or contact us for help.'
                    );
                }
                Mage::throwException($message);
            }
            $this->setAdditionalData($this->getPxFusionSession(), $payment);
            $dpsTxnRef = Mage::helper('magebasedps')->getAdditionalData($payment, 'DpsTxnRef');
            $payment->setStatus(self::STATUS_APPROVED)
                ->setTransactionId($dpsTxnRef)
                ->setLastTransId($dpsTxnRef);

            $riskScore = Mage::helper('magebasedps')->getAdditionalData($payment, 'riskScore');
            if (Mage::getStoreConfig('payment/' . $this->_code . '/use_maxmind_fraud_detection') && isset($riskScore)) {
                if (Mage::getModel('foomandpspro/method_common_maxmind')->isFraud($riskScore)) {
                    $payment->setIsTransactionPending(true);
                    $payment->setIsFraudDetected(true);
                }
            }

            $this->createBillingAgreements($this->getPxFusionSession()->GetTransactionResult, $payment->getOrder());
            $this->setPxFusionSession(NULL);
        }
        return $this;

    }

    public function createBillingAgreements($responseXml, $order)
    {
        Mage::log(
            "createBillingAgreements. " . var_export($responseXml, true) . var_export(get_class($order), true) . var_export($order->getStoreId(), true) . var_export($order->getId(), true) . var_export($responseXml->dpsBillingId, true) . var_export($responseXml->txnData3, true),
            null,
            self::DPS_LOG_FILENAME
        );

        if (!empty($responseXml->dpsBillingId) && !empty($responseXml->txnData3)
            && (int)$responseXml->txnData3 == self::CREATE_BILLING_AGREEMENT
        ) {
            $billingAgreement = Mage::getModel('sales/billing_agreement')->load(
                (string)$responseXml->dpsBillingId, 'reference_id'
            );
            $billingAgreement->setCustomerId($order->getCustomerId())
                ->setMethodCode($this->_code)
                ->setReferenceId((string)$responseXml->dpsBillingId)
                ->setStatus(Mage_Sales_Model_Billing_Agreement::STATUS_ACTIVE)
                ->setAgreementLabel(
                    substr((string)$responseXml->cardNumber, 0, 4)."............"
                    .substr((string)$responseXml->cardNumber, -2)
                    . ' ' . substr((string)$responseXml->dateExpiry, 0, 2) . '/'
                    .'20'. substr((string)$responseXml->dateExpiry, 2)
                )
                ->setStoreId($order->getStoreId())
                ->addOrderRelation($order->getId())
                ->save();

            if ($billingAgreement->getId()) {
                if ($billingAgreement->isValid()) {
                    $message = Mage::helper('sales')->__('Created billing agreement #%s.', $billingAgreement->getId());
                } else {
                    $message = Mage::helper('sales')->__('Failed to create billing agreement for this order.');
                }
                $order->addStatusHistoryComment($message);
                $order->save();
            }
        }

    }

    /**
     * Validates returned response
     * checks: Success Response
     *         Authorized Response
     *         amount = base grand total
     *         currency settled = base currency
     *         order exists
     *
     * @param stdClass $result
     *
     * @return bool
     */
    protected function _validateResponse($result)
    {
        try {
            if ($result) {
                if (!$result->GetTransactionResult) {
                    Mage::log(
                        "Error in DPS Response Validation: No Transaction Data. " . var_export($result, true),
                        null,
                        self::DPS_LOG_FILENAME
                    );
                    return false;
                }
                $result = $result->GetTransactionResult;
                if ($result->status != 0) {
                    Mage::log(
                        "Error in DPS Response Validation: Transaction failed. " . $this->statusCodes[$result->status],
                        null,
                        self::DPS_LOG_FILENAME
                    );
                    return false;
                }
                if (empty($result->authCode)) {
                    $common = Mage::getModel('magebasedps/method_common');
                    if (isset($result->responseCode)) {
                        Mage::log(
                            "Error in DPS Response Validation: " .
                            $common->returnErrorExplanation($result->responseCode), null, self::DPS_LOG_FILENAME
                        );
                    } else {
                        Mage::log(
                            "Error in DPS Response Validation: No response code.", null, self::DPS_LOG_FILENAME
                        );
                    }
                    if ($result->responseText) {
                        $this->setError(array('message' => $result->responseText));
                    }
                    return false;
                }
                $order = $this->_getOrder();
                if (!$order) {
                    Mage::log("Error in DPS Response Validation: No Order", null, self::DPS_LOG_FILENAME);
                    return false;
                }
                if ($result->txnType == 'Purchase') {
                    if (abs((float)$result->amount - $order->getBaseGrandTotal()) > 0.0005) {
                        Mage::log("Error in DPS Response Validation: Mismatched totals", null, self::DPS_LOG_FILENAME);
                        return false;
                    }
                }
                if ($result->currencyName != $order->getBaseCurrencyCode() && $result->currencyName != 'DFLT') {
                    Mage::log("Error in DPS Response Validation: Mismatched currencies", null, self::DPS_LOG_FILENAME);
                    return false;
                }
                return true;
            } else {
                Mage::log(__METHOD__);
                Mage::log($result);
                Mage::log("Error in DPS Response Validation: Not a valid response.", null, self::DPS_LOG_FILENAME);
                return false;
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::log("Error in DPS Response Validation " . $e->getMessage(), null, self::DPS_LOG_FILENAME);
            return false;
        }

    }

    /**
     * Saves all useful returned info from DPS to additional information field
     * on order payment object
     *
     * @param StdClass                       $response
     * @param Mage_Sales_Model_Order_Payment $payment ?
     *
     * @return void
     */
    public function setAdditionalData($response, $payment)
    {
        $response = $response->GetTransactionResult;
        $data = array(
            'ReCo'   => $response->responseCode,
            'AuthCode'       => $response->authCode,
            'CardName'       => $response->cardName,
            'CurrencyName'   => $response->currencyName,
            'Amount'         => $response->amount,
            'CardHolderName' => $response->cardHolderName,
            'CardNumber'     => $response->cardNumber,
            'CardNumber2'    => $response->cardNumber2,
            'TxnType'        => $response->txnType,
            'TransactionId'  => $response->txnRef,
            'DpsTxnRef'      => $response->dpsTxnRef,
            'BillingId'      => $response->billingId,
            'DpsBillingId'   => $response->dpsBillingId,
            'TxnMac'         => $response->txnMac,
            'ResponseText'   => $response->responseText,
            'Cvc2ResultCode' => $response->cvc2ResultCode,
            'DateExpiry'     => $response->dateExpiry
        );

        foreach ($data as $key => $value) {
            $payment->setAdditionalInformation($key, $value);
        }
    }

    /*
     * Saves an array with sessionId + all additional information regarding the payment into Mage session
     *
     * @param object $paymentInfo
     * @return void
     */
    public function setPxFusionSession($paymentInfo)
    {
        Mage::log(
            'setPxfusionSession: ' . "\n" . var_export($paymentInfo, true),
            null,
            self::DPS_LOG_FILENAME
        );
        Mage::getSingleton('core/session')->setPxFusionPayment($paymentInfo);
    }

    /*
     * Retrieves an array with sessionId + all additional information regarding the payment into Mage session
     *
     * @return object
     */
    public function getPxFusionSession()
    {
        Mage::log(
            'getPxFusionSession ',
            null,
            self::DPS_LOG_FILENAME
        );
        return Mage::getSingleton('core/session')->getPxFusionPayment();
    }


    /**
     * checks whether order has been placed
     *
     * @return boolean
     */
    protected function _isPlaceOrder()
    {
        $info = $this->getInfoInstance();
        if ($info instanceof Mage_Sales_Model_Quote_Payment) {
            return false;
        } elseif ($info instanceof Mage_Sales_Model_Order_Payment) {
            return true;
        }
    }

    /**
     * retrieve current order
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        if (!$this->_order) {
            $this->_order = $this->getInfoInstance()->getOrder();
        }
        return $this->_order;
    }

    /**
     * Can be used in regular checkout
     *
     * @return bool
     */
    public function canUseCheckout()
    {
        $this->_canUseCheckout = Mage::getStoreConfigFlag(
            'payment/' . $this->_code . '/frontend_checkout', $this->getStore()
        );
        return $this->_canUseCheckout;
    }
}
