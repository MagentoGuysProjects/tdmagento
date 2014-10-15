<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Method_Webservice extends Fooman_DpsPro_Model_Method_PxpostPro
{
    const URL_SERVICE_WSDL = 'https://sec.paymentexpress.com/WS/PXWS.asmx?WSDL';
    const TEST_URL_SERVICE_WSDL = 'https://qa4.paymentexpress.com/ws/pxws.asmx?WSDL';

    const DPS_LOG_FILENAME = 'fooman_dpspro_webservice.log';

    protected $_code = 'foomandpsproweb';
    protected $_validator = null;
    protected $_formBlockType = 'foomandpspro/webservice_form';

    //protected $_canSaveCc               = true;
    //protected $_isInitializeNeeded      = true;

    protected function _isActive()
    {
        return Mage::getStoreConfigFlag('payment/' . $this->_code . '/active', $this->getStore());
    }

    public function isTestMode()
    {
        return Mage::getStoreConfigFlag('payment/' . $this->_code . '/testmode', $this->getStore());
    }

    public function getIsCentinelValidationEnabled()
    {
        return Mage::getStoreConfigFlag('payment/' . $this->_code . '/secureactive', $this->getStore());
    }

    public function getCentinelValidator()
    {
        $validator = Mage::getSingleton('foomandpspro/method_webservice_validator');
        $validator
            ->setMethodInstance($this)
            ->setStore($this->getStore())
            ->setIsPlaceOrder($this->_isPlaceOrder());
        return $validator;
    }

    /**
     * @return Fooman_DpsPro_Model_Method_Webservice_SoapClient
     */
    public function getDpsClient()
    {
        $client = Mage::getModel(
            'foomandpspro/method_webservice_soapClient',
            array(
                'soapUrl'  => $this->isTestMode() ? self::TEST_URL_SERVICE_WSDL : self::URL_SERVICE_WSDL,
                'user'     => htmlentities($this->getPostUsername($this->getStore())),
                'password' => htmlentities($this->getPostPassword($this->getStore()))
            )
        );
        /* @var $client Fooman_DpsPro_Model_Method_Webservice_SoapClient */
        return $client;
    }

    /**
     * create transaction object in xml and submit to server
     *
     * @param Mage_Sales_Model_Order_Payment                         $payment
     * @param bool|\Fooman_DpsPro_Model_Method_Webservice_SoapClient $dpsClient
     * @param bool|string                                            $dpsBillingId
     *
     * @return SimpleXMLElement $responseXml
     */
    protected function getRequestToDpsResult($payment, $dpsClient = false, $dpsBillingId = false)
    {
        if (!$dpsClient) {
            $dpsClient = $this->getDpsClient();
        }

        $useThreeDSecure = $this->getIsCentinelValidationEnabled();
        if ($this->debugToDb()) {
            Mage::log(get_class($this) . ':' . 'buildRequestAndSubmitToDps', Zend_Log::DEBUG, self::DPS_LOG_FILENAME);
        }

        //Completing a previously authorized transaction
        //or refunding
        if ($this->getPaymentAction() == MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_COMPLETE
            || $this->getPaymentAction() == MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_REFUND
        ) {
            $transactionDetails = array(
                'amount'            => trim(sprintf("%9.2f", $this->getAmount())),
                'txnType'           => $this->getPaymentAction(),
                'merchantReference' => $this->_getOrderId(),
                'txnDescription'    => $this->_getOrderId(),
                'dpsTxnRef'         => Mage::helper('magebasedps')->getAdditionalData($payment, 'DpsTxnRef'),
                'inputCurrency'     => $this->_getCurrencyCode()
            );

            if ($dpsBillingId) {
                $this->setTransactionId(Mage::helper('magebasedps')->getAdditionalData($payment, 'DpsTxnRef'));
            } else {
                $txnId = Mage::helper('foomandpspro')->getTxnId($payment);
                $this->setTransactionId($txnId);
                $transactionDetails['txnRef'] = $txnId;
            }
        } else {
            //authorise or purchase
            if ($useThreeDSecure && $this->getCentinelValidator()->getValidatorTransactionId() && !$dpsBillingId) {
                $txnId = $this->getCentinelValidator()->getValidatorTransactionId();
            } else {
                $txnId = Mage::helper('foomandpspro')->getTxnId($payment);
            }
            $this->setTransactionId($txnId);
            if (MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_AUTHORIZE == $this->getPaymentAction()) {
                $payment->setIsTransactionClosed(0);
            }
            if ($dpsBillingId) {
                $payment->setTransactionId($txnId);
            }

            $transactionDetails = array(
                'amount'         => trim(sprintf("%9.2f", $this->getAmount())),
                'cardHolderName' => htmlspecialchars(trim($payment->getCcOwner()), ENT_QUOTES, 'UTF-8'),
                'cardNumber'     => $payment->getCcNumber(),
                'cvc2'           => htmlentities($payment->getCcCid()),
                'dateExpiry'     =>
                    str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT) . substr($payment->getCcExpYear(), 2, 2),
                'inputCurrency'  => $this->_getCurrencyCode(),
                'txnDescription' => $this->_getOrderId(),
                'txnType'        => $this->getPaymentAction(),
                'txnRef'         => $txnId,
            );
            if ($dpsBillingId) {
                $transactionDetails['dpsBillingId'] = $dpsBillingId;
            } else {
                $transactionDetails['merchantReference'] = $this->_getOrderId();
                $transactionDetails['enable3DSecure']
                    = $useThreeDSecure && $this->getCentinelValidator()->checkedEnrollmentStatus() ? 1 : 0;
            }

            //leave PaRes unset if we don't have a value for it
            if ($useThreeDSecure && $this->getCentinelValidator()->getValidatorPaRes()) {
                $transactionDetails['paRes'] = $this->getCentinelValidator()->getValidatorPaRes();
            }

            if (Mage::helper('foomandpspro')->getCcSaveFromPost()) {
                $transactionDetails['enableAddBillCard'] = Mage::helper('foomandpspro')->getCcSaveFromPost();
            }

            $payment->setAdditionalInformation('attempted3DSecure', $this->getIsCentinelValidationEnabled());
            $payment->setAdditionalInformation(
                'used3DSecure', $useThreeDSecure ? (int)$this->getCentinelValidator()->getValidatorPaRes() : 0
            );
        }

        if ($this->debugToDb()) {
            $debugData = $transactionDetails;
            //mask out card number in debug log
            if (isset($debugData['cardNumber'])) {
                $debugData['cardNumber']
                    = substr($debugData['cardNumber'], 0, 4) . "..........." . substr($debugData['cardNumber'], -1);
            }
            if (isset($debugData['cvc2'])) {
                $debugData['cvc2'] = str_replace(
                    array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'), '*', $debugData['cvc2']
                );
            }
            Mage::log($debugData, Zend_Log::DEBUG, self::DPS_LOG_FILENAME);
        }

        $response = $dpsClient->SubmitTransaction($transactionDetails);

        //check if we have to send another Post to request the status of the transaction
        if ($response && $response->SubmitTransactionResult && $response->SubmitTransactionResult->statusRequired) {
            $transactionDetails['txnType'] = 'Status';
            $response = $dpsClient->SubmitTransaction($transactionDetails);
        }

        if ($response && $this->debugToDb()) {
            Mage::log($dpsClient->getLastRequest());
            Mage::log($dpsClient->getLastResponse());
            Mage::log($response, null, self::DPS_LOG_FILENAME);
        }

        return $response;
    }

    /**
     * create transaction object in xml and submit to server
     *
     * @return bool
     */
    public function buildRequestAndSubmitToDps()
    {
        $payment = $this->getPayment();
        $dpsClient = $this->getDpsClient();

        $response = $this->getRequestToDpsResult($payment, $dpsClient, false);


        if ($response && $this->_validateResponse($response)) {
            $this->unsError();
            //update payment information with last transaction unless we are refunding
            if ($this->getPaymentAction() != MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_REFUND
            ) {
                $this->setAdditionalData($response, $payment);
                $payment->setAdditionalInformation(
                    'DateExpiry',
                    str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT) . substr($payment->getCcExpYear(), 2, 2)
                );
                if (Mage::helper('foomandpspro')->getCcSaveFromPost()
                    && (string)$response->SubmitTransactionResult->dpsBillingId
                ) {

                    $order = $this->getInfoInstance()->getOrder();
                    if (!$order->getId()) {
                        $order->save();
                    }
                    $billingAgreement = Mage::getModel('sales/billing_agreement')->load(
                        (string)$response->SubmitTransactionResult->dpsBillingId,
                        'reference_id'
                    );

                    if ((int)$payment->getCcExpMonth() < 10) {
                        $ccExpMonthFormatted = '0' . (string)$payment->getCcExpMonth();
                    }

                    $billingAgreement->setCustomerId($this->_getCustomerIdFromOrder())
                        ->setMethodCode($this->_code)
                        ->setReferenceId((string)$response->SubmitTransactionResult->dpsBillingId)
                        ->setStatus(Mage_Sales_Model_Billing_Agreement::STATUS_ACTIVE)
                        ->setAgreementLabel(
                            substr($response->SubmitTransactionResult->cardNumber, 0, 4) . "............"
                            . substr($response->SubmitTransactionResult->cardNumber, -2)
                            . ' ' . (string)$ccExpMonthFormatted . '/'
                            . (string)$payment->getCcExpYear()
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
     * validate returned response
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
                if (!$result->SubmitTransactionResult) {
                    Mage::log(
                        "Error in DPS Response Validation: No Transaction Data. " . $result,
                        null,
                        self::DPS_LOG_FILENAME
                    );
                    if ($result->SubmitTransactionResult->helpText) {
                        $this->setError(array('message' => $result->helpText));
                    }
                    return false;
                }
                $result = $result->SubmitTransactionResult;
                if ($result->statusRequired) {
                    Mage::log(
                        "Error in DPS Response Validation: No correct status even after retrying.",
                        null,
                        self::DPS_LOG_FILENAME
                    );
                    return false;
                }
                if (!isset($result->authorized)) {
                    Mage::log(
                        "Error in DPS Response Validation: No Success Data",
                        null,
                        self::DPS_LOG_FILENAME
                    );
                    return false;
                }
                if (!$result->authorized == 1) {
                    $common = Mage::getModel('magebasedps/method_common');
                    if (isset($result->reco)) {
                        Mage::log(
                            "Error in DPS Response Validation: " .
                            $common->returnErrorExplanation($result->reco), null, self::DPS_LOG_FILENAME
                        );
                    } else {
                        Mage::log(
                            "Error in DPS Response Validation: No reco code.", null, self::DPS_LOG_FILENAME
                        );
                    }
                    if ($result->helpText) {
                        $this->setError(array('message' => $result->helpText));
                    }
                    return false;
                }
                $order = $this->_getOrder();
                if (!$order) {
                    Mage::log("Error in DPS Response Validation: No Order", null, self::DPS_LOG_FILENAME);
                    return false;
                }
                if ($this->getPaymentAction() != MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_COMPLETE
                    && $this->getPaymentAction() != MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_REFUND
                ) {
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
     * save all useful returned info from DPS to additional information field
     * on order payment object
     *
     * @param StdClass                       $response
     * @param Mage_Sales_Model_Order_Payment $payment ?
     *
     * @return void
     */
    public function setAdditionalData($response, $payment)
    {
        $response = $response->SubmitTransactionResult;
        $data = array(
            'ReCo'           => $response->reco,
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
            'HelpText'       => $response->helpText,
        );
        foreach ($data as $key => $value) {
            $payment->setAdditionalInformation($key, $value);
        }
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
}
