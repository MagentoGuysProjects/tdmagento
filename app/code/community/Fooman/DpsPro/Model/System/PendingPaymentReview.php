<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_System_PendingPaymentReview
    extends Mage_Adminhtml_Model_System_Config_Source_Order_Status
{
    protected $_stateStatuses;

    public function __construct()
    {
        if (version_compare(Mage::getVersion(), '1.4.1.0', '>=')) {
            $this->_stateStatuses = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
        } else {
            $this->_stateStatuses = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        }
    }
}
