<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_System_Config_Backend_Cron extends Mage_Core_Model_Config_Data
{
    /**
     * Cron settings after save
     *
     */
    protected function _afterSave()
    {
        $enabled = $this->getData('groups/foomandpspro/fields/cronactive/value');
        $time = $this->getData('groups/foomandpspro/fields/crontime/value');

        if ($enabled) {
            $cronExprArray = array(
                intval($time[1]), # Minute
                intval($time[0]), # Hour
                '*', # Day of the Month
                '*', # Month of the Year
                '*', # Day of the Week
            );
            $cronExprString = join(' ', $cronExprArray);
        } else {
            $cronExprString = '';
        }

        try {
            Mage::getModel('core/config_data')
                ->load('crontab/jobs/check_billing_agreements/schedule/cron_expr', 'path')
                ->setValue($cronExprString)
                ->setPath('crontab/jobs/check_billing_agreements/schedule/cron_expr')
                ->save();

            Mage::getModel('core/config_data')
                ->load('crontab/jobs/check_billing_agreements/run/model', 'path')
                ->setValue((string)Mage::getConfig()->getNode('crontab/jobs/check_billing_agreements/run/model'))
                ->setPath('crontab/jobs/check_billing_agreements/run/model')
                ->save();
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('adminhtml')->__('Unable to save the cron expression.'));
        }
    }
}
