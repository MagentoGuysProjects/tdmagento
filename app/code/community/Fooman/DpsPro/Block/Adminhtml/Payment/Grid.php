<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_DpsPro_Block_Adminhtml_Payment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('foomandpsproPaymentGrid');
        $this->setDefaultSort('payment_id');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('foomandpspro/payment')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn(
            'payment_id', array(
                               'header' => Mage::helper('foomandpspro')->__('Id'),
                               'width'  => '30px',
                               'index'  => 'payment_id',
                          )
        );

        $this->addColumn(
            'payment_at', array(
                               'header' => Mage::helper('foomandpspro')->__('Payment Made On'),
                               'index'  => 'payment_at',
                               'type'   => 'datetime',
                               'width'  => '150px',
                          )
        );

        $this->addColumn(
            'dps_transaction_reference', array(
                                              'header' => Mage::helper('foomandpspro')->__('DPS Transaction Ref'),
                                              'width'  => '80px',
                                              'index'  => 'dps_transaction_reference',
                                         )
        );
        $this->addColumn(
            'currency', array(
                             'header' => Mage::helper('foomandpspro')->__('Currency'),
                             'width'  => '80',
                             'index'  => 'currency',
                        )
        );
        $this->addColumn(
            'amount', array(
                           'header'   => Mage::helper('foomandpspro')->__('Amount'),
                           'width'    => '80',
                           'index'    => 'amount',
                           'type'     => 'currency',
                           'currency' => 'currency',
                      )
        );
        $this->addColumn(
            'reference', array(
                              'header' => Mage::helper('foomandpspro')->__('Reference'),
                              'width'  => '160px',
                              'index'  => 'reference',
                         )
        );

        $this->addColumn(
            'card_holder_name', array(
                                     'header' => Mage::helper('foomandpspro')->__('Card Holder'),
                                     'width'  => '160px',
                                     'index'  => 'card_holder_name',
                                )
        );

        $this->addColumn(
            'card_name', array(
                              'header' => Mage::helper('foomandpspro')->__('Card Name'),
                              'width'  => '80px',
                              'index'  => 'card_name',
                         )
        );

        return parent::_prepareColumns();
    }

}
