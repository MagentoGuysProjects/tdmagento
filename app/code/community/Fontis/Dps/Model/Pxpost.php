<?php
/**
 * Fontis Direct Payment Solutions Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Fontis
 * @package    Fontis_Dps
 * @author     Chris Norton
 * @copyright  Copyright (c) 2008 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Fontis_Dps_Model_Pxpost extends Mage_Payment_Model_Method_Cc
{

    protected $_code  = 'pxpost';
    protected $_formBlockType = 'dps/pxpost_form';
    protected $_infoBlockType = 'dps/pxpost_info';

    protected $_isGateway               = true;
    protected $_canAuthorize            = false;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc               = false;
    
    const URL = 'https://www.paymentexpress.com/pxpost.aspx';
    
    const STATUS_APPROVED = 'Approved';

	const PAYMENT_ACTION_AUTH_CAPTURE = 'authorize_capture';
	const PAYMENT_ACTION_AUTH = 'authorize';

	/**
	 *
	 */
	public function getGatewayUrl()
	{
		return self::URL;
	}
	
	public function getDebug()
	{
		return Mage::getStoreConfig('payment/pxpost/debug');
	}
	
	public function getLogPath()
	{
		return Mage::getBaseDir() . '/var/log/pxpost.log';
	}
	
	public function getUsername()
	{
		return Mage::getStoreConfig('payment/pxpost/username');
	}
	
	public function getPassword()
	{
		return Mage::getStoreConfig('payment/pxpost/password');
	}

	public function isAvailable($quote = null)
	{
		if($this->getDebug())
		{
	    	$writer = new Zend_Log_Writer_Stream($this->getLogPath());
			$logger = new Zend_Log($writer);
			$logger->info("entering isAvailable()");
		}
	
		$groupAccess = $this->getConfigData('customer_group_access');
		$group = $this->getConfigData('customer_group');
		
		if($this->getDebug())
		{
			$logger->info("Customer Group Access: " . $groupAccess);
			$logger->info("Customer Group: " . $group);
			$logger->info("Quoted Customer Group: " . $quote->getCustomerGroupId());
		}
		
		if($groupAccess == 0 || $group === '')
		{
			// No restrictions on access
			return true;
		}
		elseif($groupAccess == 1)
		{
			// Only allow customer to access this method if they are part of the
			// specified group
			if($quote->getCustomerGroupId() == $group)
			{
				return true;
			}
		}
		elseif($groupAccess == 2)
		{
			// Only allow customer to access this method if they are NOT part
			// of the specified group
			if($quote->getCustomerGroupId() != $group)
			{
				return true;
			}
		}
		
		// Default, restrict access
		return false;
	}

	/**
	 *
	 */
	public function validate()
    {
    	if($this->getDebug())
		{
	    	$writer = new Zend_Log_Writer_Stream($this->getLogPath());
			$logger = new Zend_Log($writer);
			$logger->info("entering validate()");
		}
		
        parent::validate();
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $currency_code = $paymentInfo->getOrder()->getBaseCurrencyCode();
        } else {
            $currency_code = $paymentInfo->getQuote()->getBaseCurrencyCode();
        }
        return $this;
    }

	public function authorize(Varien_Object $payment, $amount)
	{
		if($this->getDebug())
		{
			$writer = new Zend_Log_Writer_Stream($this->getLogPath());
			$logger = new Zend_Log($writer);
			$logger->info("entering authorize()");
		}
	}
	
	/**
	 *
	 */
	public function capture(Varien_Object $payment, $amount)
	{
		if($this->getDebug())
		{
			$writer = new Zend_Log_Writer_Stream($this->getLogPath());
			$logger = new Zend_Log($writer);
			$logger->info("entering capture()");
		}
	
		$this->setAmount($amount)
			->setPayment($payment);

		$result = $this->_call($payment);
		
		if($this->getDebug()) { $logger->info(var_export($result, TRUE)); }

		if($result === false)
		{
			$e = $this->getError();
			if (isset($e['message'])) {
				$message = Mage::helper('dps')->__('There has been an error processing your payment.') . $e['message'];
			} else {
				$message = Mage::helper('dps')->__('There has been an error processing your payment. Please try later or contact us for help.');
			}
			Mage::throwException($message);
		}
		else
		{
			if ($result['Authorized']) {
				$payment->setStatus(self::STATUS_APPROVED)
					->setLastTransId($this->getTransactionId());
			}
			else
			{
				Mage::throwException($result['HelpText']);
			}
		}
		return $this;
	}

	/**
	 *
	 */
	protected function _call(Varien_Object $payment)
	{
		if($this->getDebug())
		{
			$writer = new Zend_Log_Writer_Stream($this->getLogPath());
			$logger = new Zend_Log($writer);
			$logger->info("entering _call()");
		}
		
		// Generate any needed values
		$date_expiry = str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT) . 
						substr($payment->getCcExpYear(), 2, 2);
		
		$transaction_id = $payment->getOrder()->getStoreId() . 
						str_pad($payment->getOrder()->getQuoteId(), 9, '0', STR_PAD_LEFT);
		
		if($this->getDebug())
		{
			$logger->info( var_export($payment->getOrder()->getData(), TRUE) );
		}
		
		// Build the XML request
		$doc = new SimpleXMLElement('<Txn></Txn>');
		
		$doc->addChild('PostUsername', htmlentities( $this->getUsername() ));
		$doc->addChild('PostPassword', htmlentities( $this->getPassword() ));
		$doc->addChild('CardHolderName', htmlentities($payment->getCcOwner()));
		$doc->addChild('CardNumber', htmlentities($payment->getCcNumber()));
		$doc->addChild('Amount', htmlentities($this->getAmount()));
		$doc->addChild('DateExpiry', htmlentities($date_expiry));
		$doc->addChild('Cvc2', htmlentities($payment->getCcCid()));
		$doc->addChild('InputCurrency', htmlentities($payment->getOrder()->getBaseCurrencyCode()));
		$doc->addChild('TxnType', htmlentities('Purchase'));
		$doc->addChild('TxnId', htmlentities($transaction_id));

		$xml = $doc->asXML();
		
		// DEBUG
		if($this->getDebug()) { $logger->info($xml); }
		
		// Send the data via HTTP POST and get the response
		$http = new Varien_Http_Adapter_Curl();
		$http->setConfig(array('timeout' => 30));
		
		$http->write(Zend_Http_Client::POST, $this->getGatewayUrl(), '1.1', array(), $xml);
		
		$response = $http->read();
		
		if ($http->getErrno()) {
			$http->close();
			$this->setError(array(
				'message' => $http->getError()
			));
			return false;
		}
		
		// DEBUG
		if($this->getDebug()) { $logger->info($response); }
        
        $http->close();

		// Strip out header tags
        $response = preg_split('/^\r?$/m', $response, 2);
        $response = trim($response[1]);
		
		// Parse the XML object
		$xmlObj = simplexml_load_string($response);
		
		$result = array();
		
		// Determine if the payment was successful
		$xpath = $xmlObj->xpath('/Txn/Transaction/Authorized');
		$result['Authorized'] = ($xpath !== FALSE && $xpath[0] == 1);
		
		$xpath = $xmlObj->xpath('/Txn/ResponseText');
		$result['ResponseText'] = ($xpath !== FALSE) ? $xpath[0] : '';
		
		$xpath = $xmlObj->xpath('/Txn/HelpText');
		$result['HelpText'] = ($xpath !== FALSE) ? $xpath[0] : '';
		
		$xpath = $xmlObj->xpath('/Txn/ReCo');
		$result['ReCo'] = ($xpath !== FALSE) ? $xpath[0] : '';
		
		$xpath = $xmlObj->xpath('/Txn/DpsTxnRef');
		$result['DpsTxnRef'] = ($xpath !== FALSE) ? $xpath[0] : '';
				
		return $result;
	}
}
