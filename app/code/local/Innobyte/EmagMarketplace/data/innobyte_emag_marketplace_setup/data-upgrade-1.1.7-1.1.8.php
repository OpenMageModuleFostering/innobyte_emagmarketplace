<?php

// Populate sku column for eMAG products table
$collection = Mage::getModel('innobyte_emag_marketplace/product')->getCollection();
foreach ($collection as $product) {

    /** @var $magentoProduct Mage_Catalog_Model_Product */
    $magentoProduct = Mage::getModel('catalog/product')->load($product->getProductId());
    $product->setSku($magentoProduct->getSku());
    $product->save();
}
