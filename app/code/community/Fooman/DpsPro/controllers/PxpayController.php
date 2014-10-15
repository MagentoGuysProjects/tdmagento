<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once Mage::getModuleDir('controllers', 'MageBase_DpsPaymentExpress') . DS . 'PxpayController.php';

class Fooman_DpsPro_PxpayController extends MageBase_DpsPaymentExpress_PxpayController
{

    public function successAction()
    {
        if (
            !Mage::getStoreConfig('payment/' . Mage::getModel('magebasedps/method_pxpay')->getCode() . '/test_locally')
        ) {
            return parent::successAction();
        }

        Mage::log(
            'MageBaseDps successAction userid '.$this->getRequest()->getParam('userid'),
            null,
            MageBase_DpsPaymentExpress_Model_Method_Pxpay::DPS_LOG_FILENAME
        );
        Mage::log(
            'MageBaseDps successAction result '.$this->getRequest()->getParam('result'),
            null,
            MageBase_DpsPaymentExpress_Model_Method_Pxpay::DPS_LOG_FILENAME
        );
        if ($this->_validateUserId($this->getRequest()->getParam('userid'))) {

            /* successAction is called twice
             * once by DPS's FailProofNotification
             * and also by the customer when returning
             * DPS has no session
             * only the DPS response is processed to prevent double handling / order locking
             */
            $session = Mage::getSingleton('checkout/session');
            //session exists = user with browser
            if ($session->getLastOrderId()) {
                Mage::log(
                    $session->getLastRealOrderId().' MageBaseDps User returned to Success Url',
                    null,
                    MageBase_DpsPaymentExpress_Model_Method_Pxpay::DPS_LOG_FILENAME
                );
                $resultXml = $this->_processSuccessResponse($this->getRequest()->getParam('result'));
                //we have a reponse from DPS
                if ($resultXml) {
                    if ((int)$resultXml->Success == 1) {
                        $session->setLastQuoteId((int)$resultXml->TxnData2)
                            ->setLastOrderId(
                                Mage::getModel('sales/order')->loadByIncrementId((string)$resultXml->MerchantReference)
                                ->getId()
                            )
                            ->setLastRealOrderId((string)$resultXml->MerchantReference)
                            ->setLastSuccessQuoteId((int)$resultXml->TxnData2);
                        $this->_redirect('checkout/onepage/success', array('_secure'=>true));
                    } else {
                        if ((int) $resultXml->TxnData2) {
                            $session->setLastQuoteId((int) $resultXml->TxnData2);
                        }
                        if ((string) $resultXml->MerchantReference) {
                            $session->setLastOrderId(
                                Mage::getModel('sales/order')->loadByIncrementId((string) $resultXml->MerchantReference)
                                ->getId()
                            )
                            ->setLastRealOrderId((string) $resultXml->MerchantReference);
                        }
                        $this->_redirect('checkout/onepage/failure', array('_secure' => true));
                    }
                //we didn't get a succesful response
                } else {
                    //we don't have a proper response - fail but we don't know why
                    Mage::log(
                        $session->getLastRealOrderId() .
                        ' MageBaseDps User returned to Success Url but we were unable to retrieve a positive ' .
                        'response from DPS',
                        null,
                        MageBase_DpsPaymentExpress_Model_Method_Pxpay::DPS_LOG_FILENAME
                    );
                    // display success problem template, which will reload this URL.
                    $this->loadLayout();
                    $this->renderLayout(); // magebasedps_pxpay_success -> magebase/dps/pxpay/successproblem.phtml
                    // previously we displayed a failure page, but this could be a successful order
                }
            //session doesn't exist = DPS notification
            } else {
                try {
                    $result = $this->getRequest()->getParam('result');
                    Mage::log(
                        'DPS result from url: '.$result,
                        null,
                        MageBase_DpsPaymentExpress_Model_Method_Pxpay::DPS_LOG_FILENAME
                    );
                    if (empty ($result)) {
                        throw new Exception(
                            "Can't retrieve result from GET variable result. Check your server configuration."
                        );
                    }
                    $this->_processSuccessResponse($this->getRequest()->getParam('result'));
                }catch (Exception $e){
                    Mage::logException($e);
                    Mage::log(
                        'MageBaseDps failed with exception - see exception.log',
                        null,
                        MageBase_DpsPaymentExpress_Model_Method_Pxpay::DPS_LOG_FILENAME
                    );
                    // At this point, something's failed at our end - we should emit a 50x error so that
                    // DPS will continue to retry, rather than assuming we've succeeded and giving up.
                    // Perhaps we have a transient error connecting back.
                    Mage::app()->getResponse()
                      ->setHttpResponseCode(503)
                      ->sendResponse();
                    exit;
                    // was: $this->_redirect('checkout/onepage/failure', array('_secure'=>true));
                }
                Mage::app()->getResponse()
                  ->setHttpResponseCode(200)
                  ->sendResponse();
                exit;
                // was: $this->_redirect('checkout/onepage/success', array('_secure'=>true));
            }
        //url tampering = wrong PxPayUserId
        } else {
            Mage::log(
                'MageBaseDps successAction, but wrong PxPayUserId',
                null,
                MageBase_DpsPaymentExpress_Model_Method_Pxpay::DPS_LOG_FILENAME
            );
            $this->_redirect('checkout/onepage/failure', array('_secure'=>true));
        }
    }

    public function failAction()
    {
        Mage::log(
            'MageBaseDps failAction',
            null,
            MageBase_DpsPaymentExpress_Model_Method_Pxpay::DPS_LOG_FILENAME
        );
        if (!$this->_validateUserId($this->getRequest()->getParam('userid'))) {
            Mage::log(
                'MageBaseDps failAction - wrong PxPayUserId',
                null,
                MageBase_DpsPaymentExpress_Model_Method_Pxpay::DPS_LOG_FILENAME
            );
        }
        $resultXml = $this->_processFailResponse($this->getRequest()->getParam('result'));
        //reactivate quote
        $session = Mage::getSingleton('checkout/session');
        if (    $session
                && (int)$resultXml->TxnData2
                && (string)$resultXml->MerchantReference
                && $session->getLastRealOrderId() == (string)$resultXml->MerchantReference) {
            Mage::getModel('sales/quote')->load((int)$resultXml->TxnData2)->setIsActive(true)->save();
            $session->setLastQuoteId((int)$resultXml->TxnData2);
        }
        if ($session) {
            $this->_redirect('checkout/onepage/failure', array('_secure' => true));
        }
    }

    protected function _getRealResponse($result)
    {
        return Mage::getSingleton('magebasedps/method_pxpay')->getRealResponse($result);
    }

    protected function _processSuccessResponse($result)
    {
        return Mage::getSingleton('magebasedps/method_pxpay')->processSuccessResponse($result);
    }

    protected function _processFailResponse($result)
    {
        return Mage::getSingleton('magebasedps/method_pxpay')->processFailResponse($result);
    }

    protected function _validateUserId($userId)
    {
        return Mage::getSingleton('magebasedps/method_pxpay')->validateUserId($userId);
    }

}
