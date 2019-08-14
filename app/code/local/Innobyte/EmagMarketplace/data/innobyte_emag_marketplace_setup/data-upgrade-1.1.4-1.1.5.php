<?php
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$emagSalesOrderTable = $installer->getTable('innobyte_emag_marketplace/sales_order');
if ($installer->getConnection()->isTableExists($emagSalesOrderTable)) {
    // Update weee tax for eMAG order items
    $collection = Mage::getModel('sales/order')->getCollection();
    $collection->getSelect()->joinInner(array(
        'emag_sales_order' => Mage::getSingleton('core/resource')->getTableName('innobyte_emag_marketplace/sales_order')),
        'main_table.entity_id = emag_sales_order.entity_id',
        array('emag_sales_order.emag_order_id'),
        null
    );

    foreach ($collection as $order) {
        if (!$order->getEmagOrderId()) {
            continue;
        }
        /** @var $item Mage_Sales_Model_Order_Item */
        foreach ($order->getAllItems() as $item) {
            $item->setGiftMessageAvailable(0)
                ->setBaseWeeeTaxAppliedAmount(0)
                ->setBaseWeeeTaxAppliedRowAmnt(0)
                ->setWeeeTaxAppliedAmount(0)
                ->setWeeeTaxAppliedRowAmount(0)
                ->setWeeeTaxApplied(serialize(array()))
                ->setWeeeTaxDisposition(0)
                ->setWeeeTaxRowDisposition(0)
                ->setBaseWeeeTaxDisposition(0)
                ->setBaseWeeeTaxRowDisposition(0);
            $item->save();
        }
    }
}