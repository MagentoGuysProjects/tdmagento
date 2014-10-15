<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Mysql4_Payment extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('foomandpspro/payment', 'payment_id');
    }
}
