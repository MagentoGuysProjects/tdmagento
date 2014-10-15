<?php

$installer = $this;
/* @var $installer Fooman_DpsPro_Model_Mysql4_Setup */

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('foomandpspro_payment')}`;
CREATE TABLE `{$this->getTable('foomandpspro_payment')}` (
  `payment_id` int(10) unsigned NOT NULL auto_increment,
  `payment_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `amount` decimal(12,4),
  `currency` varchar(10),
  `reference` varchar(64),
  `card_holder_name` varchar(64),
  `card_name` varchar(64),
  `dps_transaction_reference` varchar(255),
  PRIMARY KEY  (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);
$installer->startSetup();
if (version_compare(Mage::getVersion(), '1.5.0.0', '>=')) {
    $newOrderStatusses =  array();
    $newOrderStatusses[] =  array(
        'status'=> 'fraud_dps',
        'label'=> 'Pending Payment Review (DPS)',
        'state'=> 'payment_review'
    );

    foreach ($newOrderStatusses as $newOrderStatus) {
        $status = Mage::getModel('sales/order_status')->load($newOrderStatus['status']);
        if ($status->getStatus()) {
            //skip existing
            continue;
        }
        $status = Mage::getModel('sales/order_status');
        $status->setStatus($newOrderStatus['status']);
        $status->setLabel($newOrderStatus['label']);
        $status->save();
        $status->assignState($newOrderStatus['state'], 0);
    }
}
$installer->endSetup();
