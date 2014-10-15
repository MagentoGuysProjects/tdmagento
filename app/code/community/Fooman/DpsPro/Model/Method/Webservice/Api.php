<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Method_Webservice_Api extends Mage_Centinel_Model_Api
{

    public function callLookup($data)
    {
        $result = new Varien_Object();
        $dpsClient = $this->getDpsClient();

        $transactionDetails = array(
            'amount'         => trim(htmlentities(sprintf("%9.2f", $data->getAmount()))),
            'cardNumber'     => $data->getCardNumber(),
            'dateExpiry'     => str_pad($data->getCardExpMonth(), 2, '0', STR_PAD_LEFT) .
                substr($data->getCardExpYear(), 2, 2),
            'txnDescription' => $data->getOrderNumber(),
            'txnRef'         => Mage::helper('foomandpspro')->getTxnId($data),
            'currency'       => $data->getCurrencyCode()
        );

        try {
            $enrolledResult = $dpsClient->Check3dsEnrollment($transactionDetails);
        } catch (Fooman_DpsPro_Model_Method_Webservice_Timeout $e) {
            //when timing out proceed with no 3d secure
            Mage::logException($e);
            $result->setEnrolled('N');
            $result->setAcsUrl('');
            $result->setPayload('');
            $result->setTransactionId($transactionDetails['txnRef']);
            $result->setErrorNo(0);
            $result->setErrorDesc($e->getMessage());
            //$result->setEciFlag(7);
        }

        /*
         * enrolled 	Indicates if the card holder is or can enroll for 3D secure
         * paReq 	Payer authentication request value
         * acsURL 	URL at which the card holder can be authenticated
        */

        if ($enrolledResult) {
            /*
            * values for enrolled
            * -1 	The call has failed for technical reasons
            * 0 	The card is not enrolled for 3D secure
            * 1 	The card is enrolled for 3D secure
            * 2 	The card is not enrolled for 3D secure however the user can be given the opportunity to do so.
            */
            if ($enrolledResult->Check3dsEnrollmentResult->enrolled == 1) {
                $result->setEnrolled('Y');
                $result->setAcsUrl($enrolledResult->Check3dsEnrollmentResult->acsURL);
                $result->setPayload($enrolledResult->Check3dsEnrollmentResult->paReq);
            } elseif ($enrolledResult->Check3dsEnrollmentResult->enrolled == -1) {
                Mage::throwException(Mage::helper('foomandpspro')->__('Payment method is not available.'));
            } else {
                $result->setEnrolled('N');
                $result->setAcsUrl('');
                $result->setPayload('');
            }
            $result->setTransactionId($transactionDetails['txnRef']);
            $result->setErrorNo(0);
            //$result->setEciFlag(6);
        }
        return $result;
    }


    public function callAuthentication($data)
    {
        /*
        *  There is no additional call with DPS to authenticate a result
         * use the lookup results directly
        */

        $result = new Varien_Object();
        $result->setEnrolled($data->getLookupEnrolled());
        $result->setAcsUrl($data->getLookupAcsUrl());
        $result->setPayload($data->getLookupPayload());
        $result->setErrorNo($data->getLookupErrorNo());

        return $result;
    }

}
