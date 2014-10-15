<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Method_Common_Maxmind
{

    /**
     * is fraud score high enough to be fraud
     *
     * @param int  $fraudScore
     * @param bool $hide
     *
     * @return bool
     */
    public function isFraud($fraudScore = 0, $hide = false)
    {
        $isFraud = false;

        if ($hide) {
            $isFraud = (bool)($fraudScore >= Mage::getStoreConfig(
                    'payment_services/foomandpspro/maxmind_hidethreshold'
                )
            );
        } else {
            $isFraud = (bool)($fraudScore >= Mage::getStoreConfig(
                    'payment_services/foomandpspro/maxmind_fraudthreshold'
                )
            );
        }

        return $isFraud;
    }

    /**
     * retrieve maxmind fraud score
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param int                    $txnId
     *
     * @return float
     */
    public function getMaxmindFraudScore(Mage_Sales_Model_Quote $quote, $txnId)
    {
        //only use for quotes above a certain value
        if ($quote->getBaseGrandTotal() < Mage::getStoreConfig('payment_services/foomandpspro/maxmind_orderthreshold')
        ) {
            return false;
        }

        //country is required
        if (!$quote->getBillingAddress()->getCountryId()) {
            return false;
        }

        // Create a new CreditCardFraudDetection object
        $ccfs = new Maxmind_MinFraud_CreditCardFraudDetection();

        // change to 1 to enable - WARNING this debugs directly to screen and will break the checkout
        $ccfs->debug = 0;

        // Set inputs and store them in a hash
        // See http://www.maxmind.com/app/ccv for more details on the input fields

        // Enter your license key here (Required)
        $h["license_key"] = Mage::helper('core')->decrypt(
            Mage::getStoreConfig('payment_services/foomandpspro/maxmind_key')
        );

        // Required fields
        $h["i"] = Mage::helper('core/http')->getRemoteAddr(); // set the client ip address
        $h["city"] = $quote->getBillingAddress()->getCity(); // set the billing city
        $h["region"] = $quote->getBillingAddress()->getRegion(); // set the billing state
        $h["postal"] = $quote->getBillingAddress()->getPostcode(); // set the billing zip code
        $h["country"] = $quote->getBillingAddress()->getCountryId(); // set the billing country

        // Recommended fields
        $emailParts = explode('@', $quote->getCustomerEmail());
        if (isset($emailParts[1])) {
            $emailDomain = $emailParts[1];
        } else {
            $emailDomain = '';
        }
        $h["domain"] = $emailDomain; // Email domain
        $h["bin"] = ""; // bank identification number
        $h["forwardedIP"] = Mage::helper('foomandpspro')->getClientIp(); // X-Forwarded-For or Client-IP HTTP Header

        // CreditCardFraudDetection.php will take
        // MD5 hash of e-mail address passed to emailMD5 if it detects '@' in the string
        $h["emailMD5"] = strtolower(htmlentities($quote->getCustomerEmail()));

        // CreditCardFraudDetection.php will take the MD5 hash of the username/password if the length of the string is not 32
        if ($quote->getCustomerIsGuest()) {
            $h["usernameMD5"] = $quote->getCustomerEmail();
            $h["passwordMD5"] = "";
        } else {
            $h["usernameMD5"] = $quote->getCustomerEmail();
            $h["passwordMD5"] = substr($quote->getCustomer()->getPassword(), 0, 32);
        }

        // Optional fields
        $h["binName"] = ""; // bank name
        $h["binPhone"] = ""; // bank customer service phone number on back of credit card
        $h["custPhone"] = ""; // Area-code and local prefix of customer phone number
        $h["requested_type"] = "standard"; // Which level (free, city, premium) of CCFD to use

        if (!$quote->getIsVirtual()) {
            $h["shipAddr"] = $quote->getShippingAddress()->getStreetFull(); // Shipping Address
            $h["shipCity"] = $quote->getShippingAddress()->getCity(); // the City to Ship to
            $h["shipRegion"] = $quote->getShippingAddress()->getRegion(); // the Region to Ship to
            $h["shipPostal"] = $quote->getShippingAddress()->getPostcode(); // the Postal Code to Ship to
            $h["shipCountry"] = $quote->getShippingAddress()->getCountryId(); // the country to Ship to
        } else {
            $h["shipAddr"] = ""; // Shipping Address
            $h["shipCity"] = ""; // the City to Ship to
            $h["shipRegion"] = ""; // the Region to Ship to
            $h["shipPostal"] = ""; // the Postal Code to Ship to
            $h["shipCountry"] = ""; // the country to Ship to
        }

        if (empty($txnId)) {
            $h["txnID"] = $quote->getReservedOrderId();
        } else {
            $h["txnID"] = $txnId;
        }

        $h["sessionID"] = Mage::getSingleton('core/cookie')->get('frontend'); // Session ID

        $h["accept_language"] = Mage::helper('core/http')->getHttpAcceptLanguage();
        $h["user_agent"] = Mage::helper('core/http')->getHttpUserAgent();

        $ccfs->timeout = 10;

        $ccfs->isSecure = 1;

        // how many seconds to cache the ip addresses
        $ccfs->wsIpaddrRefreshTimeout = 3600 * 24;

        // file to store the ip address for minfraud3.maxmind.com, minfraud1.maxmind.com and minfraud2.maxmind.com
        $ccfs->wsIpaddrCacheFile = Mage::getBaseDir('var') . DS . 'maxmind.ws.cache';

        // if useDNS is 1 then use DNS, otherwise use ip addresses directly
        $ccfs->useDNS = 1;

        // next we set up the input hash
        $ccfs->input($h);

        // then we get the result from the server
        $ccfs->query();
        $result = $ccfs->output();

        if (isset($result['queriesRemaining'])) {
            //Update number of queries left for the current month
            //System > Configuration > Payment Services
            Mage::getModel('core/config')->saveConfig(
                'payment_services/foomandpspro/maxmind_queries_left', $result['queriesRemaining']
            );
        }

        if (isset($result['err']) && !empty($result['err'])
            && (!isset($result['riskScore'])
                || empty($result['riskScore']))
        ) {
            Mage::throwException('Maxmind error: ' . $result['err']);
        }
        return $result;
    }
}
