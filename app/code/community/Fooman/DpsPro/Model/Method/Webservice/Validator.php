<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Method_Webservice_Validator extends Mage_Centinel_Model_Service
{

    /**
     * Return validation api model
     *
     * @return Fooman_DpsPro_Model_Method_Webservice_Api
     */
    protected function _getApi()
    {
        if (is_null($this->_api)) {
            $this->_api = Mage::getSingleton('foomandpspro/method_webservice_api');
            $this->_api->setDpsClient($this->getMethodInstance()->getDpsClient());
        }
        return $this->_api;
    }

    public function validate($data)
    {
        Mage::helper('foomandpspro')->debug('validate');
        Mage::helper('foomandpspro')->debug($data);
        parent::validate($data);
    }

    public function authenticate($data)
    {
        Mage::helper('foomandpspro')->debug('authenticate');
        Mage::helper('foomandpspro')->debug($data);
        Mage::helper('foomandpspro')->debug($this->_getValidationState()->debug());
        $data->setLookupAcsUrl($this->_getValidationState()->getLookupAcsUrl());
        $data->setLookupEnrolled($this->_getValidationState()->getLookupEnrolled());
        $data->setLookupPayload($this->_getValidationState()->getLookupPayload());
        $data->setLookupErrorNo($this->_getValidationState()->getLookupErrorNo());

        $this->_getValidationState()->setTransactionId($data->getTransactionId());
        $this->_getValidationState()->setPaRes($data->getPaResPayload());
        return parent::authenticate($data);

    }

    protected function _getConfig()
    {
        $config = Mage::getSingleton('foomandpspro/method_webservice_validator_config')->setStore($this->getStore());
        return $config;
    }

    public function getValidatorTransactionId()
    {
        return $this->_getValidationState()->getTransactionId();
    }

    public function getValidatorPaRes()
    {
        return $this->_getValidationState()->getPaRes();
    }

    public function getIsEnrolled()
    {
        return $this->_getValidationState()->getLookupEnrolled() == 'Y';
    }

    public function checkedEnrollmentStatus()
    {
        return $this->_getValidationState()->getLookupEnrolled();
    }

}
