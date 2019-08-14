<?php
/**
 * Installer script.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 *
 * @author   Valentin Sandu <valentin.sandu@innobyte.com>
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */
?>
<?php
/** @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$emagSalesQuoteTable = $installer->getTable('innobyte_emag_marketplace/sales_quote');
$emagSalesOrderTable = $installer->getTable('innobyte_emag_marketplace/sales_order');
$emagSalesQuoteVoucherTable = $installer->getTable('innobyte_emag_marketplace/sales_quote_voucher');
$emagSalesOrderVoucherTable = $installer->getTable('innobyte_emag_marketplace/sales_order_voucher');


$installer->getConnection()->changeColumn(
    $emagSalesQuoteTable,
    'emag_order_id',
    'emag_order_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_BIGINT,
    )
);
$installer->getConnection()->changeColumn(
    $emagSalesQuoteTable,
    'emag_customer_id',
    'emag_customer_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_BIGINT,
    )
);
$installer->getConnection()->changeColumn(
    $emagSalesOrderTable,
    'emag_order_id',
    'emag_order_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_BIGINT,
    )
);
$installer->getConnection()->changeColumn(
    $emagSalesOrderTable,
    'emag_customer_id',
    'emag_customer_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_BIGINT,
    )
);
$installer->getConnection()->changeColumn(
    $emagSalesQuoteVoucherTable,
    'emag_id',
    'emag_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_BIGINT,
    )
);
$installer->getConnection()->changeColumn(
    $emagSalesQuoteVoucherTable,
    'emag_voucher_id',
    'emag_voucher_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_BIGINT,
    )
);
$installer->getConnection()->changeColumn(
    $emagSalesOrderVoucherTable,
    'emag_id',
    'emag_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_BIGINT,
    )
);
$installer->getConnection()->changeColumn(
    $emagSalesOrderVoucherTable,
    'emag_voucher_id',
    'emag_voucher_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_BIGINT,
    )
);



