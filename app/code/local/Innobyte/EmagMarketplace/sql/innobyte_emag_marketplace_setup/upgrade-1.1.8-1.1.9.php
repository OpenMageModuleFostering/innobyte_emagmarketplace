<?php
/**
 * Add eMAG entity types for invoice and creditmemo
 * Update source model  for attribute "emag_is_vat_payer"
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

$installer->addEntityType(Innobyte_EmagMarketplace_Model_Sales_Invoice::ENTITY_TYPE_CODE_INVOICE, array(
    'entity_model' => 'sales/order_invoice',
    'table' => 'sales/invoice',
    'increment_model' => 'eav/entity_increment_numeric',
    'increment_per_store' => true
));

$installer->addEntityType(Innobyte_EmagMarketplace_Model_Sales_Invoice::ENTITY_TYPE_CODE_CREDITMEMO, array(
    'entity_model' => 'sales/order_creditmemo',
    'table' => 'sales/creditmemo',
    'increment_model' => 'eav/entity_increment_numeric',
    'increment_per_store' => true
));


$installer->updateAttribute(
    'customer_address',
    'emag_is_vat_payer',
    'source_model',
    'eav/entity_attribute_source_boolean'
);

$installer->endSetup();
