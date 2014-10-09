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
 * @author     Lloyd Hazlett
 * @author     Chris Norton
 * @author     Peter Spiller
 * @copyright  Copyright (c) 2008 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fontis_Dps_Model_Pxpay extends Mage_Payment_Model_Method_Abstract
{
    const CGI_URL = 'https://sec2.paymentexpress.com/pxpay/pxaccess.aspx';
    const REQUEST_AMOUNT_EDITABLE = 'N';

    protected $_code  = 'pxpay';
    protected $_formBlockType = 'dps/pxpay_form';
    protected $_allowCurrencyCode = array('AUD', 'EUR', 'GBP', 'NZD', 'USD');
    
    public function assignData($data)
    {
        $details = array();
        if ($this->getUsername())
        {
            $details['username'] = $this->getUsername();
        }
        if (!empty($details)) 
        {
            $this->getInfoInstance()->setAdditionalData(serialize($details));
        }
        return $this;
    }

    public function getUsername()
    {
        return $this->getConfigData('username');
    }
    
    public function getPassword()
    {
        return $this->getConfigData('password');
    }
    
    public function getUrl()
    {
    	$url = $this->getConfigData('cgi_url');
    	
    	if(!$url)
    	{
    		$url = self::CGI_URL;
    	}

    	if($this->getDebug())
		{
			$writer = new Zend_Log_Writer_Stream($this->getLogPath());
			$logger = new Zend_Log($writer);
			$logger->info("entering getUrl()");
		}
		
        $session = Mage::getSingleton('checkout/session');
		$transaction_id = $session->getQuote()->getReservedOrderId();
        
		if($this->getDebug())
		{
			$logger->info(var_export($session->getQuote()->getData(), TRUE));
		}
		
		// Build the XML request
		$doc = new SimpleXMLElement('<GenerateRequest></GenerateRequest>');
		
		$doc->addChild('PxPayUserId', htmlentities($this->getUsername()));
		$doc->addChild('PxPayKey', htmlentities($this->getPassword()));
		$doc->addChild('AmountInput', htmlentities(sprintf("%01.2f", $session->getQuote()->getBaseGrandTotal())));
		$doc->addChild('CurrencyInput', htmlentities($session->getQuote()->getBaseCurrencyCode()));
		$doc->addChild('MerchantReference', htmlentities($transaction_id));
		$doc->addChild('EmailAddress', '');
		$doc->addChild('TxnData1', '');
		$doc->addChild('TxnData2', '');
		$doc->addChild('TxnData3', '');
		$doc->addChild('TxnType', htmlentities('Purchase'));
		$doc->addChild('TxnId', '');
		$doc->addChild('BillingId', '');
		$doc->addChild('EnableAddBillCard', '0');
		$doc->addChild('UrlSuccess', htmlentities(Mage::getUrl('dps/pxpay/success')));
		$doc->addChild('UrlFail', htmlentities(Mage::getUrl('dps/pxpay/fail')));

		$xml = $doc->asXML();
        
        if($this->getDebug()) { $logger->info($xml); }
		
		// Send the data via HTTP POST and get the response
		$http = new Varien_Http_Adapter_Curl();
		$http->setConfig(array('timeout' => 30));
		
		$http->write(Zend_Http_Client::POST, $url, '1.1', array(), $xml);
		
		$response = $http->read();
     
		if ($http->getErrno()) {
			$http->close();
			$this->setError(array(
				'message' => $http->getError()
			));
            return htmlentities(Mage::getUrl('dps/pxpay/fail'));
		}
		
		if($this->getDebug()) { $logger->info($response); }
        
        $http->close();

		// Strip out header tags
        $response = preg_split('/^\r?$/m', $response, 2);
        $response = trim($response[1]);
		
		// Parse the XML object
		$xmlObj = simplexml_load_string($response);
		
        // Determine if the request was successful
        if ($xmlObj['valid'] == 1) {
            return strval($xmlObj->URI);
        } else {
            return htmlentities(Mage::getUrl('dps/pxpay/fail'));
        }
    }
    
    public function getSession()
    {
        return Mage::getSingleton('dps/pxpay_session');
    }

    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

	public function getCheckoutFormFields()
	{
		$a = $this->getQuote()->getShippingAddress();
		$b = $this->getQuote()->getBillingAddress();
		$currency_code = $this->getQuote()->getBaseCurrencyCode();
		$cost = $a->getBaseSubtotal() - $a->getBaseDiscountAmount();
		$shipping = $a->getBaseShippingAmount();

		$_shippingTax = $this->getQuote()->getShippingAddress()->getBaseTaxAmount();
		$_billingTax = $this->getQuote()->getBillingAddress()->getBaseTaxAmount();
		$tax = sprintf('%.2f', $_shippingTax + $_billingTax);
		$cost = sprintf('%.2f', $cost + $tax);
		
		$fields = array(
			'mid'					=> $this->getUsername(),
			'amt'					=> sprintf('%.2f', $cost + $shipping),
			'amt_editable'			=> self::REQUEST_AMOUNT_EDITABLE,
			'currency'				=> $currency_code,
			'ref'					=> $this->getCheckout()->getLastRealOrderId(),
			'pmt_sender_email'		=> $b->getEmail(),
			'pmt_contact_firstname'	=> $b->getFirstname(),
			'pmt_contact_surname'	=> $b->getLastname(),
			'pmt_contact_phone'		=> $b->getTelephone(),
			'pmt_country'			=> $b->getCountry(),
			'regindi_address1'		=> $b->getStreet(1),
			'regindi_address2'		=> $b->getStreet(2),
			'regindi_sub'			=> $b->getCity(),
			'regindi_state'			=> $b->getRegion(),		// Returns full state name
			'regindi_pcode'			=> $b->getPostcode(),
			'return'				=> Mage::getUrl('dps/pxpay/success'),
			'popup'					=> 'N',
		);

		// Run through fields and replace any occurrences of & with the word 
		// 'and', as having an ampersand present will conflict with the HTTP
		// request.
		$filtered_fields = array();
        foreach ($fields as $k=>$v) {
            $value = str_replace("&","and",$v);
            $filtered_fields[$k] =  $value;
        }
        
        return $filtered_fields;
	}

    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('dps/pxpay_form', $name)
            ->setMethod('pxpay')
            ->setPayment($this->getPayment())
            ->setTemplate('fontis/dps/pxpay/form.phtml');

        return $block;
    }

    public function validate()
    {
        parent::validate();
        $currency_code = $this->getQuote()->getBaseCurrencyCode();
        if (!in_array($currency_code,$this->_allowCurrencyCode)) {
            Mage::throwException(Mage::helper('dps')->__('Selected currency code ('.$currency_code.') is not compatible with Payment Express'));
        }
        return $this;
    }

    public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment)
    {
       return $this;
    }

    public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment)
    {

    }

    public function canCapture()
    {
        return true;
    }

    public function getOrderPlaceRedirectUrl()
    {
          return Mage::getUrl('dps/pxpay/redirect');
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
}
