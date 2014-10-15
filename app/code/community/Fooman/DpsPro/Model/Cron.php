<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Cron
{
    const PROCESS_AT_ONCE = 50;

    public function checkExpiredBillingAgreements()
    {
        if (!Mage::helper('foomandpspro')->isPxPostRebillActive()
            && !Mage::helper('foomandpspro')->isWebserviceRebillActive()
        ) {
            return;
        }
        $agreements = $this->loadAgreements();
        foreach ($agreements as $agreement) {
            $this->cancelAgreement($agreement);
        }
    }

    public function loadAgreements()
    {
        /**
         * Get the resource model
         */
        $resource = Mage::getSingleton('core/resource');

        /**
         * Retrieve the read connection
         */

        $resource = Mage::getSingleton('core/resource');

        $collection = Mage::getModel('sales/billing_agreement')->getCollection();
        $collection
            ->setPageSize(self::PROCESS_AT_ONCE)
            ->addFieldToFilter('status', Mage_Sales_Model_Billing_Agreement::STATUS_ACTIVE)
            ->addFieldToSelect('agreement_id');
        $select = $collection->getSelect();
        $select->joinLeft(
            array('sbao' => $resource->getTableName('sales/billing_agreement_order')),
            'main_table.agreement_id = sbao.agreement_id',
            null
        );
        $select->joinLeft(
            array('sopc' => $resource->getTableName('sales/order_payment')),
            'sbao.order_id = sopc.parent_id',
            array('cc_exp_month', 'cc_exp_year', 'method')
        );
        $select->where(
            '(cc_exp_month < ' . date('n') . ' AND cc_exp_year = ' . date('Y') . ') OR (cc_exp_year < ' .
            date('Y') . ' AND  cc_exp_year != 0)'
        );
        $select->where(
            'method in (?)',
            array(
                Mage::getModel('magebasedps/method_pxpay')->getCode(),
                Mage::getModel('magebasedps/method_pxpost')->getCode(),
                Mage::getModel('foomandpspro/method_webservice')->getCode()
            )
        );
        return $collection->getData();
    }

    public function cancelAgreement($agreement)
    {
        Mage::getModel('sales/billing_agreement')->load($agreement['agreement_id'])
            ->setStatus(Mage_Sales_Model_Billing_Agreement::STATUS_CANCELED)
            ->save();
    }
}
