<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Check extends Mage_Core_Model_Config_Data
{
    /**
     * Magento version check
     *
     */
    protected function _beforeSave()
    {
        if ($this->getValue()) {
            if (!version_compare(Mage::getVersion(), '1.4.1.0', '>=')) {
                Mage::throwException(
                    Mage::helper('core')->__('Your version of Magento store does not support Billing Agreements.')
                );
            }
        }
        return parent::_beforeSave();
    }
}
