<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Helper_Data extends MageBase_DpsPaymentExpress_Helper_Data
{

    public function debug($msg)
    {
        Mage::log($msg, null, 'foomandpspro.log');
    }

    public function generateTxnId()
    {
        return substr(uniqid(rand()), 0, 16);
    }

    public function getTxnId($payment)
    {
        if (!$payment->getTxnId()) {
            $txnId = $this->generateTxnId();
            $payment->setTxnId($txnId);
        }
        return $payment->getTxnId();
    }

    public function getClientIp()
    {
        if (Mage::helper('core/string')->cleanString(Mage::app()->getRequest()->getServer('X-HTTP_CLIENT_IP-FOR'))) {
            return Mage::helper('core/string')->cleanString(
                Mage::app()->getRequest()->getServer('X-HTTP_CLIENT_IP-FOR')
            );
        }
        if (Mage::helper('core/string')->cleanString(Mage::app()->getRequest()->getServer('X-FORWARDED-FOR'))) {
            return Mage::helper('core/string')->cleanString(
                Mage::app()->getRequest()->getServer('X-FORWARDED-FOR')
            );
        }
        return Mage::helper('core/http')->getRemoteAddr();
    }

    public function getMaxmindData($info)
    {
        $maxmindKeys = array(
            'err'              => 'Error',
            'distance'         => 'Distance',
            'ip_accuracyRadius'=> 'IP Accuracy',
            'countryMatch'     => 'Country Match',
            'countryCode'      => 'Country Code',
            'ip_countryName'   => 'Country Name',
            'anonymousProxy'   => 'Anonymous Proxy',
            'isTransProxy'     => 'Transparent Proxy',
            'proxyScore'       => 'Proxy Score',
            'ip_userType'      => 'User Type',
            'ip_regionName'    => 'IP Region',
            'ip_city'          => 'IP City',
            'ip_isp'           => 'IP ISP',
            'ip_org'           => 'IP Organisation',
            'freeMail'         => 'Freemailer Service',
            'carderEmail'      => 'High Risk Email',
            'highRiskUsername' => 'High Risk Username',
            'highRiskPassword' => 'High Risk Password',
            'highRiskCountry'  => 'High Risk Country',
            'shipForward'      => 'High Risk Shipping Address',
            'riskScore'        => 'Risk Score',
        );
        $fraudDataAvailable = false;
        $returnArray = array();
        $returnArray['Suspected Fraud'] = 'No Fraud Suspected';
        foreach ($maxmindKeys as $maxmindKey => $maxmindLabel) {
            $data = $this->getAdditionalData($info, $maxmindKey);
            if ($data) {
                $fraudDataAvailable = true;
                $returnArray[$maxmindLabel] = $data;
            }
        }
        if (isset($returnArray['Risk Score'])) {
            if (Mage::getModel('foomandpspro/method_common_maxmind')->isFraud($returnArray['Risk Score'])) {
                $returnArray['Suspected Fraud'] = 'Fraud Suspected - Review Pending';
            }
            if ($this->getAdditionalData($info, 'status')) {
                $returnArray['Suspected Fraud'] = $this->getAdditionalData($info, 'status');
            }
        }
        if (!$fraudDataAvailable) {
            return false;
        }
        return $returnArray;
    }

    public function isPxPostRebillActive()
    {
        $codeRebill = Mage::getModel('foomandpspro/method_pxpostProRebill')->getCode();

        $activeRebill = Mage::getStoreConfigFlag(
            'payment/' . $codeRebill . '/active', Mage::app()->getStore()->getId()
        );

        return $activeRebill;
    }

    public function isWebserviceRebillActive()
    {
        $codeRebill = Mage::getModel('foomandpspro/method_webserviceRebill')->getCode();

        $activeRebill = Mage::getStoreConfigFlag(
            'payment/' . $codeRebill . '/active', Mage::app()->getStore()->getId()
        );

        return $activeRebill;
    }

    public function shouldOfferRebillForPxPay()
    {
        $codePxPay = Mage::getModel('magebasedps/method_pxpay')->getCode();
        $activePxPay = Mage::getStoreConfigFlag(
            'payment/' . $codePxPay . '/active', Mage::app()->getStore()->getId()
        );
        $frontendCheckoutPxpay= Mage::getStoreConfigFlag(
            'payment/' . $codePxPay . '/frontend_checkout', Mage::app()->getStore()->getId()
        );
        if ($activePxPay && $frontendCheckoutPxpay && $this->isPxPostRebillActive()) {
            //PxPay uses PxPost credentials for rebilling
            $codePxPost = Mage::getModel('magebasedps/method_pxpost')->getCode();
            $postusername = Mage::getStoreConfig(
                'payment/' . $codePxPost . '/postusername', Mage::app()->getStore()->getId()
            );
            $postpassword = Mage::getStoreConfig(
                'payment/' . $codePxPost . '/postpassword', Mage::app()->getStore()->getId()
            );
            if ($postusername && $postpassword) {
                return true;
            }
        }
        return false;
    }

    public function shouldOfferRebillForPxFusion()
    {
        $codePxFusion = Mage::getModel('foomandpspro/method_pxFusion')->getCode();
        $activePxPay = Mage::getStoreConfigFlag(
            'payment/' . $codePxFusion . '/active', Mage::app()->getStore()->getId()
        );
        $frontendCheckoutPxFusion= Mage::getStoreConfigFlag(
            'payment/' . $codePxFusion . '/frontend_checkout', Mage::app()->getStore()->getId()
        );
        if ($activePxPay && $frontendCheckoutPxFusion && $this->isPxPostRebillActive()) {
            //PxFusion uses PxPost credentials for rebilling
            $codePxPost = Mage::getModel('magebasedps/method_pxpost')->getCode();
            $postusername = Mage::getStoreConfig(
                'payment/' . $codePxPost . '/postusername', Mage::app()->getStore()->getId()
            );
            $postpassword = Mage::getStoreConfig(
                'payment/' . $codePxPost . '/postpassword', Mage::app()->getStore()->getId()
            );
            if ($postusername && $postpassword) {
                return true;
            }
        }
        return false;
    }

    public function shouldOfferRebillForPxPost()
    {
        $codePxPost = Mage::getModel('magebasedps/method_pxpost')->getCode();
        $activePxPost = Mage::getStoreConfigFlag(
            'payment/' . $codePxPost . '/active', Mage::app()->getStore()->getId()
        );
        $frontendCheckoutPxPost = Mage::getStoreConfigFlag(
            'payment/' . $codePxPost . '/frontend_checkout', Mage::app()->getStore()->getId()
        );
        if ($activePxPost && $frontendCheckoutPxPost && $this->isPxPostRebillActive()) {
            $postusername = Mage::getStoreConfig(
                'payment/' . $codePxPost . '/postusername', Mage::app()->getStore()->getId()
            );
            $postpassword = Mage::getStoreConfig(
                'payment/' . $codePxPost . '/postpassword', Mage::app()->getStore()->getId()
            );
            if ($postusername && $postpassword) {
                return true;
            }
        }
        return false;
    }

    public function shouldOfferRebillForWebservice()
    {
        $code = Mage::getModel('foomandpspro/method_webservice')->getCode();
        $active = Mage::getStoreConfigFlag(
            'payment/' . $code . '/active', Mage::app()->getStore()->getId()
        );
        $frontendCheckout = Mage::getStoreConfigFlag(
            'payment/' . $code . '/frontend_checkout', Mage::app()->getStore()->getId()
        );
        if ($active && $frontendCheckout && $this->isWebserviceRebillActive()) {
            $postusername = Mage::getStoreConfig(
                'payment/' . $code . '/postusername', Mage::app()->getStore()->getId()
            );
            $postpassword = Mage::getStoreConfig(
                'payment/' . $code . '/postpassword', Mage::app()->getStore()->getId()
            );
            if ($postusername && $postpassword) {
                return true;
            }
        }
        return false;
    }

    public function getCcSaveFromPost()
    {
        $payment = Mage::app()->getRequest()->getPost('payment');
        if (isset($payment['cc_save'])) {
            return (int)$payment['cc_save'];
        }
        /*
         * Needed for PxFusion only.
         */
        if(Mage::app()->getRequest()->getParam('cc_save')) {
            return (int)Mage::app()->getRequest()->getParam('cc_save');
        }
    }

    public function getAgreementIdFromPost($code)
    {
        $payment = Mage::app()->getRequest()->getPost('payment');
        if (isset($payment[$code . '_billing_agreement'])) {
            return $payment[$code . '_billing_agreement'];
        }
    }

    public function getDpsBillingId($code)
    {
        $customerBillingAgreementIds = Mage::getModel('sales/billing_agreement')->getAvailableCustomerBillingAgreements(
            Mage::getSingleton('customer/session')->getCustomer()->getId()
        )->getAllIds();
        $chosenAgreementId = Mage::helper('foomandpspro')->getAgreementIdFromPost($code);
        if (!in_array($chosenAgreementId, $customerBillingAgreementIds)) {
            Mage::throwException(Mage::helper('foomandpspro')->__('Please select a different card'));
        }
        return Mage::getModel('sales/billing_agreement')->load($chosenAgreementId)->getReferenceId();
    }

    /**
     * Accepts the payment previously authorized
     *
     * @param Mage_Payment_Model_Info $payment
     * @param Mage_Payment_Model_Method_Abstract $paymentMethod
     * @return bool
     */
    public function acceptPayment(Mage_Payment_Model_Info $payment, $paymentMethod)
    {
        if (Mage::helper('magebasedps')->getAdditionalData($payment, 'TxnType')
            == MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_AUTHORIZE
        ) {
            $payment->setIsFraudDetected(false);
            if (Mage::helper('magebasedps')->getAdditionalData($payment, 'OrigTxnType')
                == MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_AUTHORIZE_CAPTURE
            ) {
                $paymentMethod->capture($payment, $payment->getBaseAmountAuthorized());
                $payment->registerCaptureNotification($payment->getBaseAmountAuthorized());
            }

            $payment->setAdditionalInformation('status', 'Fraud Suspected - Accepted');
            $payment->save();

            $order = $payment->getOrder();
            if (Mage::getStoreConfig('payment_services/foomandpspro/email_customer_on_fraud', $order->getStoreId())) {
                $mailer = Mage::getModel('core/email_template_mailer');
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo(
                    $order->getCustomerEmail(),
                    $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname()
                );
                $mailer->addEmailInfo($emailInfo);
                // Set all required params and send emails
                $mailer->setSender(
                    Mage::getStoreConfig('sales_email/order/identity')
                );
                $mailer->setStoreId(Mage::app()->getStore()->getStoreId());
                $mailer->setTemplateId('customer_approved');
                $mailer->setTemplateParams(
                    array(
                        'name'        => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
                        'ordernumber' => $order->getIncrementId()
                    )
                );
                $mailer->send();
            }
        }
        return true;
    }

    /**
     * Denies the payment previously authorized
     *
     * @param Mage_Payment_Model_Info $payment
     * @param Mage_Payment_Model_Method_Abstract $paymentMethod
     * @return bool
     */
    public function denyPayment(Mage_Payment_Model_Info $payment, $paymentMethod)
    {
        //there is no void or cancelling with DPS so we accept and
        //immediately refund to release the held funds back to the card
        $amount = $payment->getBaseAmountAuthorized();
        if (Mage::helper('magebasedps')->getAdditionalData($payment, 'TxnType')
            == MageBase_DpsPaymentExpress_Model_Method_Common::ACTION_AUTHORIZE
        ) {
            $paymentMethod->capture($payment, $amount);
        }
        $paymentMethod->refund($payment, $amount);
        $payment->setAdditionalInformation('status', 'Fraud Suspected - Rejected');
        $payment->save();

        $order = $payment->getOrder();
        if (Mage::getStoreConfig('payment_services/foomandpspro/email_customer_on_fraud', $order->getStoreId())) {
            $mailer = Mage::getModel('core/email_template_mailer');
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo(
                $order->getCustomerEmail(),
                $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname()
            );
            $mailer->addEmailInfo($emailInfo);
            // Set all required params and send emails
            $mailer->setSender(
                Mage::getStoreConfig('sales_email/order/identity')
            );
            $mailer->setStoreId(Mage::app()->getStore()->getStoreId());
            $mailer->setTemplateId('customer_denied');
            $mailer->setTemplateParams(
                array(
                    'name'        => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
                    'ordernumber' => $order->getIncrementId()
                )
            );
            $mailer->send();
        }

        return true;
    }

    /**
     * Sends fraud warning emails to store owner and customers
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order $order
     */
    public function sendFraudEmails($quote, $order)
    {
        $this->sendFraudStoreEmail($order);
        if (Mage::getStoreConfig('payment_services/foomandpspro/email_customer_on_fraud', $order->getStoreId())) {
            $this->sendFraudCustomerEmail($quote, $order);
        }
    }

    /**
     * Sends fraud warning emails to store owner
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function sendFraudStoreEmail($order)
    {
        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo(
            Mage::getStoreConfig('trans_email/ident_general/email', $order->getStoreId()),
            Mage::getStoreConfig('trans_email/ident_general/name', $order->getStoreId())
        );
        $mailer->addEmailInfo($emailInfo);
        // Set all required params and send emails
        $mailer->setSender(
            Mage::getStoreConfig('sales_email/order/identity', $order->getStoreId())
        );
        $mailer->setStoreId($order->getStoreId());
        $mailer->setTemplateId('store_owner_fraud');
        $mailer->setTemplateParams(
            array(
                'ordernumber' => $order->getIncrementId()
            )
        );
        $mailer->send();
    }

    /**
     * Sends fraud warning emails to customer
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order $order
     */
    public function sendFraudCustomerEmail($quote, $order)
    {
        $mailer = Mage::getModel('core/email_template_mailer');
        // Email copies are sent as separated emails
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo(
            $quote->getCustomerEmail(),
            $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname()
        );
        $mailer->addEmailInfo($emailInfo);
        // Set all required params and send emails
        $mailer->setSender(
            Mage::getStoreConfig('sales_email/order/identity', $order->getStoreId())
        );
        $mailer->setStoreId($order->getStoreId());
        $mailer->setTemplateId('customer_fraud');
        $mailer->setTemplateParams(
            array(
                'name'        => $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname(),
                'ordernumber' => $order->getIncrementId()
            )
        );
        $mailer->send();
    }

    /**
     * Order increment ID getter (either real from order or a reserved from quote)
     *
     * @param $info
     *
     * @return string
     */
    public function getMerchantReference($info)
    {

        if ($this->_isPlaceOrder($info)) {
            return $info->getOrder()->getIncrementId();
        } else {
            if (!$info->getQuote()->getReservedOrderId()) {
                $info->getQuote()->reserveOrderId();
            }
            return $info->getQuote()->getReservedOrderId();
        }
    }

    /**
     * Whether current operation is order placement
     *
     * @param $info
     *
     * @return bool
     */
    protected function _isPlaceOrder($info)
    {
        if ($info instanceof Mage_Sales_Model_Quote_Payment) {
            return false;
        } elseif ($info instanceof Mage_Sales_Model_Order_Payment) {
            return true;
        }
    }

}
