<?php
/**
 * Add emag_is_synced product attribute
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 *
 * @author   Valentin Sandu <valentin.sandu@innobyte.com>
 */
?>
<?php
/** @var $installer Mage_Catalog_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$this->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'emag_is_synced', array(
    'group' => 'General',
    'backend' => '',
    'frontend' => '',
    'type' => 'int',
    'input' => 'text',
    'label' => 'eMAG Synced',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'default' => '0',
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'required' => false,
    'visible' => false,
    'user_defined' => false,
    'used_in_product_listing' => false,
    'unique' => false
));

$installer->endSetup();
