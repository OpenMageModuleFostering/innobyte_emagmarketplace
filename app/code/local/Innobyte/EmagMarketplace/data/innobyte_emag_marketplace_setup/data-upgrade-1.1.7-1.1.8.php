<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$tableNameProducts = $installer->getTable('innobyte_emag_marketplace/product');
if ($installer->getConnection()->isTableExists($tableNameProducts)) {
    // Populate sku column for eMAG products table
    $collection = Mage::getModel('innobyte_emag_marketplace/product')->getCollection();
    foreach ($collection as $product) {

        /** @var $magentoProduct Mage_Catalog_Model_Product */
        $magentoProduct = Mage::getModel('catalog/product')->load($product->getProductId());
        $product->setSku($magentoProduct->getSku());
        $product->save();
    }
}
