<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Method_Webservice_SoapClient
{
    protected $_client = null;
    protected $_user = null;
    protected $_password = null;

    public function __construct($params = null)
    {
        $requiredVars = array('soapUrl' => 'URL', 'user' => 'User', 'password' => 'Password');
        foreach ($requiredVars as $var => $message) {
            if (!isset($params[$var]) && empty($params[$var])) {
                $msg = sprintf('A %s for the DPS webservice is required.', $message);
                throw new Fooman_DpsPro_Model_Method_Webservice_Exception($msg);
            }
        }
        try {
            //$options = array ('features' => SOAP_SINGLE_ELEMENT_ARRAYS);
            $options = array('connection_timeout' => 10, 'exceptions'=>1);
            $this->_client = new SoapClient($params['soapUrl'], $options);
            $this->_user = $params['user'];
            $this->_password = $params['password'];
            return $this->_client;
        } catch (Exception $e) {
            $msg = sprintf('DPS webservice could not be started: %s', $e->getMessage());
            throw new Fooman_DpsPro_Model_Method_Webservice_Exception($msg);
        }
    }

    public function Check3dsEnrollment($params)
    {
        try {
            return $this->_client->Check3dsEnrollment(
                array(
                     'postUsername'       => $this->_user,
                     'postPassword'       => $this->_password,
                     'transactionDetails' => $params
                )
            );
        } catch (SoapFault $e) {
            if ($e->getMessage() == 'Error Fetching http headers') {
                throw new Fooman_DpsPro_Model_Method_Webservice_Timeout('Connection timed out');
            } else {
                $msg = sprintf('DPS webservice Check3dsEnrollment errored with: %s', $e->getMessage());
                throw new Fooman_DpsPro_Model_Method_Webservice_Exception($msg);
            }
        }
    }

    public function SubmitTransaction($params)
    {
        try {
            return $this->_client->SubmitTransaction(
                array(
                     'postUsername'       => $this->_user,
                     'postPassword'       => $this->_password,
                     'transactionDetails' => $params
                )
            );
        } catch (SoapFault $e) {
            if ($e->getMessage() == 'Error Fetching http headers') {
                throw new Fooman_DpsPro_Model_Method_Webservice_Timeout('Connection timed out');
            } else {
                $msg = sprintf('DPS webservice SubmitTransaction errored with: %s', $e->getMessage());
                throw new Fooman_DpsPro_Model_Method_Webservice_Exception($msg);
            }
        }
    }

    public function getLastRequest()
    {
        return $this->_client->__getLastRequest();
    }

    public function getLastResponse()
    {
        return $this->_client->__getLastResponse();
    }

}
