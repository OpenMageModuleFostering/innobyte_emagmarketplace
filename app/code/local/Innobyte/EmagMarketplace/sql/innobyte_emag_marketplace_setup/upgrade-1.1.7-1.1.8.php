<?php
/**
 * Add eMAG attribute for price attribute
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 *
 * @author   Valentin Sandu <valentin.sandu@innobyte.com>
 */
?>
<?php
/** @var $installer Mage_Sales_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$emagProductsTable = $installer->getTable('innobyte_emag_marketplace/product');

$installer->run("
    ALTER TABLE {$emagProductsTable}
    ADD COLUMN `sku` VARCHAR (255) NULL COMMENT 'Magento Product SKU' AFTER `product_id`
    ");

$installer->endSetup();
