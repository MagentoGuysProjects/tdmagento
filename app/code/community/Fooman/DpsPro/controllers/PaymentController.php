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

class Fooman_DpsPro_PaymentController extends MageBase_DpsPaymentExpress_PxpayController
{

    public function startAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('simplePxpay')
            ->setAmount(
                Mage::app()->getStore()->roundPrice(
                    Mage::helper('core')->htmlEscape($this->getRequest()->getParam('amount'))
                )
            )
            ->setReference(Mage::helper('core')->htmlEscape($this->getRequest()->getParam('reference')))
            ->setFormAction(Mage::getUrl('*/*/startPost'));
        $this->renderLayout();
    }

    public function startPostAction()
    {
        $data = array();
        $data['amountInput'] = $this->escapeHtmlByVersion($this->getRequest()->getParam('amount'));
        $data['currencyCode'] = Mage::app()->getStore()->getBaseCurrencyCode();
        $data['merchantReference'] = $this->escapeHtmlByVersion(
            substr($this->getRequest()->getParam('reference'), 0, 64)
        );
        $pxpay = Mage::getModel('foomandpspro/method_simplePxpay');
        $redirectUrl = $pxpay->getSimplePxPayUrl($data);
        $this->_redirectUrl($redirectUrl);
    }

    public function successAction()
    {
        if ($this->_validateUserId($this->getRequest()->getParam('userid'))) {
            $result = $this->getRequest()->getParam('result');
            $resultXml = $this->_getRealResponse($result);
            if ((int)$resultXml->Success == 1) {
                $pxpaySimple = Mage::getModel('foomandpspro/method_simplePxpay');
                $data = $pxpaySimple->resultXmlToArray($resultXml);
                $pxpaySimple->processSuccess($data);
                Mage::log(
                    "Payment Received: " . $data['amount'] . " from " . $data['card_holder_name'] . " with reference "
                    . $data['reference'], null, Fooman_DpsPro_Model_Method_SimplePxpay::DPS_LOG_FILENAME
                );
                $this->loadLayout();
                $this->getLayout()->getBlock('simplePxpayResult')
                    ->setSuccess(true)
                    ->setAmount($this->escapeHtmlByVersion($data['amount']))
                    ->setReference($data['reference'])
                    ->setCardHolderName($this->escapeHtmlByVersion($data['card_holder_name']))
                    ->setCardName($this->escapeHtmlByVersion($data['card_name']));
                $this->renderLayout();
            } else {
                $this->_redirect('*/*/fail');
            }

        } else {
            $this->_redirect('*/*/fail');
        }
    }

    public function failAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('simplePxpayResult')
            ->setSuccess(false);
        $this->renderLayout();
    }

    protected function escapeHtmlByVersion($string)
    {
        $helper = Mage::helper('core');
        if (method_exists($helper, 'escapeHtml')) {
            return $helper->escapeHtml($string);
        } else {
            return $helper->htmlEscape($string);
        }
    }

}
