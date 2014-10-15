<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Method_PxFusion_SoapClient
{
    protected $_client = null;
    protected $_user = null;
    protected $_password = null;

    public function __construct($params = null)
    {

        $requiredVars = array('soapUrl' => 'URL', 'user' => 'User', 'password' => 'Password');
        foreach ($requiredVars as $var => $message) {
            if (!isset($params[$var]) && empty($params[$var])) {
                throw new Fooman_DpsPro_Model_Method_Webservice_Exception(
                    'A ' . $message . ' for the DPS webservice is required.'
                );
            }
        }
        try {
            //$options = array ('features' => SOAP_SINGLE_ELEMENT_ARRAYS);
            $options = array('connection_timeout' => 10);
            $this->_client = new SoapClient($params['soapUrl'], $options);
            $this->_user = $params['user'];
            $this->_password = $params['password'];
            return $this->_client;
        } catch (Exception $e) {
            throw new Fooman_DpsPro_Model_Method_Webservice_Exception(
                'DPS webservice could not be started: ' . $e->getMessage()
            );
        }
    }

    public function GetTransactionId($params)
    {
        try {
            return $this->_client->GetTransactionId(
                array(
                    'username'   => $this->_user,
                    'password'   => $this->_password,
                    'tranDetail' => $params
                )
            );
            Mage::log(
                'GetTransactionId(ERROR): Submitting to DPS ' . "\n" . $this->_client->__getLastRequest(),
                null,
                Fooman_DpsPro_Model_Method_PxFusion::DPS_LOG_FILENAME
            );
        } catch (Exception $e) {
            Mage::log(
                'GetTransactionId(ERROR): Submitting to DPS ' . "\n" . var_export($params, true),
                null,
                Fooman_DpsPro_Model_Method_PxFusion::DPS_LOG_FILENAME
            );
            throw new Fooman_DpsPro_Model_Method_PxFusion_Exception(
                'PxFusion GetTransactionId errored with: ' . $e->getMessage()
            );
        }
    }

    public function GetTransaction($transactionId)
    {
        try {
            return $this->_client->GetTransaction(
                array(
                    'username'      => $this->_user,
                    'password'      => $this->_password,
                    'transactionId' => $transactionId
                )
            );
        } catch (Exception $e) {
            throw new Fooman_DpsPro_Model_Method_PxFusion_Exception(
                'PxFusion  GetTransaction errored with: ' . $e->getMessage()
            );
        }
    }
}
