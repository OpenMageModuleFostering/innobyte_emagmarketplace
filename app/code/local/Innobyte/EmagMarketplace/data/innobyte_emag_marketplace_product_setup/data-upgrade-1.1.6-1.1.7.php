<?php
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$tableNameProducts = $installer->getTable('innobyte_emag_marketplace/product');
if ($installer->getConnection()->isTableExists($tableNameProducts)) {
    // Update emag_is_synced product attribute
    $collection = Mage::getModel('innobyte_emag_marketplace/product')->getCollection();
    foreach ($collection as $product) {

        Mage::getResourceSingleton('catalog/product_action')->updateAttributes(
            array($product->getProductId()),
            array('emag_is_synced' => $product->getIsSynced()),
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        Mage::getResourceSingleton('catalog/product_action')->updateAttributes(
            array($product->getProductId()),
            array('emag_is_synced' => $product->getIsSynced()),
            $product->getStoreId()
        );
    }
}
