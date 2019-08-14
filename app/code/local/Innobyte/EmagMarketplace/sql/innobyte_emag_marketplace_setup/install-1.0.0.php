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
$emagSalesQuoteAddressTable = $installer->getTable('innobyte_emag_marketplace/sales_quote_address');
$emagSalesOrderAddressTable = $installer->getTable('innobyte_emag_marketplace/sales_order_address');

// Table 'innobyte_emag_marketplace/sales_quote'
if (!$installer->getConnection()->isTableExists($emagSalesQuoteTable)) {
    $table = $installer->getConnection()
        ->newTable($emagSalesQuoteTable)
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
            'default' => '0',
        ), 'Entity Id')
        ->addForeignKey(
            $installer->getFkName(
                'innobyte_emag_marketplace/sales_quote',
                'entity_id',
                'sales/order',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('sales/quote'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('eMAG Sales Flat Quote');
    $installer->getConnection()->createTable($table);
}

// Table 'innobyte_emag_marketplace/sales_quote_address'
if (!$installer->getConnection()->isTableExists($emagSalesQuoteAddressTable)) {
    $table = $installer->getConnection()
        ->newTable($emagSalesQuoteAddressTable)
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
                'default' => '0',
            )
            , 'Entity Id'
        )
        ->addForeignKey($installer->getFkName(
                'innobyte_emag_marketplace/sales_quote_address',
                'entity_id',
                'sales/quote_address',
                'address_id'
            ),
            'entity_id',
            $installer->getTable('sales/quote_address'),
            'address_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('eMAG Sales Flat Quote Address');

    // Create Table
    $installer->getConnection()->createTable($table);
}

// Table 'innobyte_emag_marketplace/sales_order'
if (!$installer->getConnection()->isTableExists($emagSalesOrderTable)) {
    $table = $installer->getConnection()
        ->newTable($emagSalesOrderTable)
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
            'default' => '0',
        ), 'Entity Id')
        ->addForeignKey(
            $installer->getFkName(
                'innobyte_emag_marketplace/sales_order',
                'entity_id',
                'sales/order',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('sales/order'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('eMAG Sales Flat Order');
    $installer->getConnection()->createTable($table);
}

// Table 'innobyte_emag_marketplace/sales_order_address'
if (!$installer->getConnection()->isTableExists($emagSalesOrderAddressTable)) {
    $table = $installer->getConnection()
        ->newTable($emagSalesOrderAddressTable)
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
                'default' => '0',
            )
            , 'Entity Id'
        )
        ->addForeignKey($installer->getFkName(
                'innobyte_emag_marketplace/sales_order_address',
                'entity_id',
                'sales/order_address',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('sales/order_address'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('eMAG Sales Flat Order Address');

    // Create Table
    $installer->getConnection()->createTable($table);
}


//====================== ADD CUSTOMER ADDRESS ATTRIBUTES =====================//

/** @var $eavConfig Mage_Eav_Model_Config */
$eavConfig = Mage::getSingleton('eav/config');

$attributes = array(
    'emag_order_id' => array(
        'frontend_label' => 'eMAG - Order ID',
        'frontend_input' => 'hidden',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 0,
        'is_required' => 0,
        'backend_type' => 'int',
        'sort_order' => 1,
        'note' => 'eMAG - Order ID.'
    ),
    'emag_customer_id' => array(
        'frontend_label' => 'eMAG - Customer ID',
        'frontend_input' => 'hidden',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 0,
        'is_required' => 0,
        'backend_type' => 'int',
        'sort_order' => 1,
        'note' => 'eMAG - Customer ID.'
    ),
    'emag_customer_comment' => array(
        'frontend_label' => 'eMAG - Customer Comment',
        'frontend_input' => 'text',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 0,
        'is_required' => 0,
        'backend_type' => 'text',
        'sort_order' => 1,
        'note' => 'eMAG - Customer Comment.'
    ),
    'emag_customer_gender' => array(
        'frontend_label' => 'eMAG - Customer Gender',
        'frontend_input' => 'hidden',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 0,
        'is_required' => 0,
        'backend_type' => 'varchar',
        'sort_order' => 1,
        'note' => 'eMAG - Customer Gender.'
    ),
    'emag_payment_status' => array(
        'frontend_label' => 'eMAG - Payment Status',
        'frontend_input' => 'hidden',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 0,
        'is_required' => 0,
        'backend_type' => 'varchar',
        'sort_order' => 1,
        'note' => 'eMAG - Payment Status.'
    ),
    'emag_order_date' => array(
        'frontend_label' => 'eMAG - Order Date',
        'frontend_input' => 'hidden',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 0,
        'is_required' => 0,
        'backend_type' => 'varchar',
        'sort_order' => 1,
        'note' => 'eMAG - Order Date.'
    )
);

// eMAG attributes
$addressAttributes = array(
    'emag_company_code' => array(
        'frontend_label' => 'eMAG - Company Registration Code',
        'frontend_input' => 'text',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 1,
        'is_required' => 0,
        'backend_type' => 'varchar',
        'validate_rules' => array(
            'max_text_length' => 255,
            'min_text_length' => 1
        ),
        'sort_order' => 61,
        'note' => 'eMAG - Company registration code.'
    ),
    'emag_company_reg_no' => array(
        'frontend_label' => 'eMAG - Company Registration Number',
        'frontend_input' => 'text',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 1,
        'is_required' => 0,
        'backend_type' => 'varchar',
        'validate_rules' => array(
            'max_text_length' => 255,
            'min_text_length' => 1
        ),
        'sort_order' => 62,
        'note' => 'eMAG - Company registration number.'
    ),
    'emag_bank' => array(
        'frontend_label' => 'eMAG - Bank',
        'frontend_input' => 'text',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 1,
        'is_required' => 0,
        'backend_type' => 'varchar',
        'validate_rules' => array(
            'max_text_length' => 255,
            'min_text_length' => 1
        ),
        'sort_order' => 63,
        'note' => 'eMAG - Bank.'
    ),
    'emag_iban' => array(
        'frontend_label' => 'eMAG - IBAN',
        'frontend_input' => 'text',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 1,
        'is_required' => 0,
        'backend_type' => 'varchar',
        'validate_rules' => array(
            'max_text_length' => 255,
            'min_text_length' => 1
        ),
        'sort_order' => 64,
        'note' => 'eMAG - IBAN.'
    ),
    'emag_legal_entity' => array(
        'frontend_label' => 'eMAG - Legal Entity',
        'frontend_input' => 'select',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 1,
        'is_required' => 0,
        'source_model' => 'innobyte_emag_marketplace/source_customer_address_attributes_legalentity',
        'backend_type' => 'int',
        'sort_order' => 58,
        'note' => 'eMAG - Legal Entity.'
    ),
    'emag_is_vat_payer' => array(
        'frontend_label' => 'eMAG - Is VAT Payer',
        'frontend_input' => 'boolean',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 1,
        'is_required' => 0,
        'source_model' => 'customer/attribute_backend_data_boolean',
        'backend_type' => 'static',
        'sort_order' => 59,
        'note' => 'eMAG - Is VAT Payer.'
    ),
    'emag_telephone_2' => array(
        'frontend_label' => 'eMAG - Telephone 2',
        'frontend_input' => 'text',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 1,
        'is_required' => 0,
        'backend_type' => 'varchar',
        'validate_rules' => array(
            'max_text_length' => 255,
            'min_text_length' => 1
        ),
        'sort_order' => 121,
        'note' => 'eMAG - Second Telephone Number.'
    ),
    'emag_telephone_3' => array(
        'frontend_label' => 'eMAG - Telephone 3',
        'frontend_input' => 'text',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 1,
        'is_required' => 0,
        'backend_type' => 'varchar',
        'validate_rules' => array(
            'max_text_length' => 255,
            'min_text_length' => 1
        ),
        'sort_order' => 122,
        'note' => 'eMAG - Third Telephone Number.'
    ),
    'emag_locality_id' => array(
        'frontend_label' => 'eMAG - Locality',
        'frontend_input' => 'hidden',
        'is_user_defined' => 1,
        'is_system' => 1,
        'is_visible' => 0,
        'is_required' => 0,
        'backend_type' => 'int',
        'sort_order' => 100,
        'note' => 'eMAG - Locality ID.'
    ),
);

// set forms to be used in; see table "customer_form_attribute" for form codes
$attributesForms = array(
    'adminhtml_customer'
);

$addressAttributesForms = array(
    'adminhtml_customer_address',
);

// add attributes
foreach ($attributes as $attributeCode => $data) {
    /** @var $attribute Mage_Customer_Model_Attribute */
    $attribute = $eavConfig->getAttribute('customer', $attributeCode);
    if (!$attribute) {
        continue;
    }

    $attribute->addData($data);
    $attribute->setData('used_in_forms', $attributesForms);
    $attribute->save();

    // add attribute to quote
    /** @var $quote Innobyte_EmagMarketplace_Model_Sales_Quote */
    $quote = Mage::getModel('innobyte_emag_marketplace/sales_quote');
    $quote->saveAttribute($attribute);

    // add attribute to order
    /** @var $order Innobyte_EmagMarketplace_Model_Sales_Order */
    $order = Mage::getModel('innobyte_emag_marketplace/sales_order');
    $order->saveAttribute($attribute);
}

// add address attributes
foreach ($addressAttributes as $attributeCode => $data) {
    /** @var $attribute Mage_Customer_Model_Attribute */
    $attribute = $eavConfig->getAttribute('customer_address', $attributeCode);
    if (!$attribute) {
        continue;
    }

    $attribute->addData($data);
    $attribute->setData('used_in_forms', $addressAttributesForms);
    $attribute->save();

    // add attribute to quote address
    /** @var $quoteAddress Innobyte_EmagMarketplace_Model_Sales_Quote_Address */
    $quoteAddress = Mage::getModel('innobyte_emag_marketplace/sales_quote_address');
    $quoteAddress->saveAttribute($attribute);

    // add attribute to order address
    /** @var $orderAddress Innobyte_EmagMarketplace_Model_Sales_Order_Address */
    $orderAddress = Mage::getModel('innobyte_emag_marketplace/sales_order_address');
    $orderAddress->saveAttribute($attribute);
}

$emagSalesInvoiceTable = $installer->getTable('innobyte_emag_marketplace/sales_invoice');
// Table 'innobyte_emag_marketplace/sales_invoice'
if (!$installer->getConnection()->isTableExists($emagSalesInvoiceTable)) {
    $table = $installer->getConnection()
        ->newTable($emagSalesInvoiceTable)
        ->addColumn(
            'id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
                'identity' => true,
            ),
            'ID'
        )
        ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            'default'   => '0',
        ), 'Order Id')
        ->addColumn(
            'emag_invoice_name',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            255,
            array(
                'nullable'  => false,
            ),
            'eMAG Invoice Name'
        )
        ->addColumn('emag_invoice_type', Varien_Db_Ddl_Table::TYPE_INTEGER, 5, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ), 'eMAG Invoice Type 0-normal, 1-storno')
        ->addForeignKey(
            $installer->getFkName(
                'innobyte_emag_marketplace/sales_invoice',
                'order_id',
                'sales/order',
                'entity_id'
            ),
            'order_id',
            $installer->getTable('sales/order'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('eMAG Third Party Invoice');
    $installer->getConnection()->createTable($table);
}

$emagSalesQuoteVoucherTable = $installer->getTable('innobyte_emag_marketplace/sales_quote_voucher');
$emagSalesOrderVoucherTable = $installer->getTable('innobyte_emag_marketplace/sales_order_voucher');

// Table 'innobyte_emag_marketplace/sales_quote_voucher'
if (!$installer->getConnection()->isTableExists($emagSalesQuoteVoucherTable)) {
    $table = $installer->getConnection()
        ->newTable($emagSalesQuoteVoucherTable)
        ->addColumn(
            'id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
                'identity' => true,
            ),
            'ID'
        )
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false
        ), 'Entity Id')
        ->addColumn('emag_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ), 'eMAG ID')
        ->addColumn('emag_voucher_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ), 'eMAG Voucher ID')
        ->addColumn(
            'emag_voucher_name',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            255,
            array(
                'nullable'  => false,
            ),
            'eMAG Voucher Name'
        )
        ->addColumn(
            'emag_sale_price',
            Varien_Db_Ddl_Table::TYPE_DECIMAL,
            '12,4',
            array(
                'nullable'  => false,
                'default' => 0
            ),
            'eMAG Sale Price'
        )
        ->addColumn(
            'base_emag_sale_price',
            Varien_Db_Ddl_Table::TYPE_DECIMAL,
            '12,4',
            array(
                'nullable'  => false,
                'default' => 0
            ),
            'Base eMAG Sale Price'
        )
        ->addColumn(
            'emag_sale_price_vat',
            Varien_Db_Ddl_Table::TYPE_DECIMAL,
            '12,4',
            array(
                'nullable'  => false,
                'default' => 0
            ),
            'eMAG Sale Price VAT'
        )
        ->addColumn(
            'base_emag_sale_price_vat',
            Varien_Db_Ddl_Table::TYPE_DECIMAL,
            '12,4',
            array(
                'nullable'  => false,
                'default' => 0
            ),
            'Base eMAG Sale Price VAT'
        )
        ->addColumn('emag_status', Varien_Db_Ddl_Table::TYPE_INTEGER, 5, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ), 'eMAG Status')
        ->addColumn(
            'emag_vat',
            Varien_Db_Ddl_Table::TYPE_DECIMAL,
            '4,2',
            array(
                'nullable'  => false,
                'default' => 0
            ),
            'eMAG VAT'
        )
        ->addColumn('emag_created', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'eMAG Creation Time')
        ->addColumn('emag_modified', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'eMAG Modification Time')
        ->addIndex(
            $installer->getIdxName(
                'innobyte_emag_marketplace/sales_quote_voucher',
                array('entity_id', 'emag_voucher_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('entity_id', 'emag_voucher_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->addForeignKey(
            $installer->getFkName(
                'innobyte_emag_marketplace/sales_quote_voucher',
                'entity_id',
                'sales/quote',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('sales/quote'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('eMAG Quote Vouchers');
    $installer->getConnection()->createTable($table);
}

// Table 'innobyte_emag_marketplace/sales_order_voucher'
if (!$installer->getConnection()->isTableExists($emagSalesOrderVoucherTable)) {
    $table = $installer->getConnection()
        ->newTable($emagSalesOrderVoucherTable)
        ->addColumn(
            'id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
                'identity' => true,
            ),
            'ID'
        )
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false
        ), 'Entity Id')
        ->addColumn('emag_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ), 'eMAG ID')
        ->addColumn('emag_voucher_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ), 'eMAG Voucher ID')
        ->addColumn(
            'emag_voucher_name',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            255,
            array(
                'nullable'  => false,
            ),
            'eMAG Voucher Name'
        )
        ->addColumn(
            'emag_sale_price',
            Varien_Db_Ddl_Table::TYPE_DECIMAL,
            '12,4',
            array(
                'nullable'  => false,
                'default' => 0
            ),
            'eMAG Sale Price'
        )
        ->addColumn(
            'base_emag_sale_price',
            Varien_Db_Ddl_Table::TYPE_DECIMAL,
            '12,4',
            array(
                'nullable'  => false,
                'default' => 0
            ),
            'Base eMAG Sale Price'
        )
        ->addColumn(
            'emag_sale_price_vat',
            Varien_Db_Ddl_Table::TYPE_DECIMAL,
            '12,4',
            array(
                'nullable'  => false,
                'default' => 0
            ),
            'eMAG Sale Price VAT'
        )
        ->addColumn(
            'base_emag_sale_price_vat',
            Varien_Db_Ddl_Table::TYPE_DECIMAL,
            '12,4',
            array(
                'nullable'  => false,
                'default' => 0
            ),
            'Base eMAG Sale Price VAT'
        )
        ->addColumn('emag_status', Varien_Db_Ddl_Table::TYPE_INTEGER, 5, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ), 'eMAG Status')
        ->addColumn(
            'emag_vat',
            Varien_Db_Ddl_Table::TYPE_DECIMAL,
            '4,2',
            array(
                'nullable'  => false,
                'default' => 0
            ),
            'eMAG VAT'
        )
        ->addColumn('emag_created', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'eMAG Creation Time')
        ->addColumn('emag_modified', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'eMAG Modification Time')
        ->addIndex(
            $installer->getIdxName(
                'innobyte_emag_marketplace/sales_order_voucher',
                array('entity_id', 'emag_voucher_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('entity_id', 'emag_voucher_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->addForeignKey(
            $installer->getFkName(
                'innobyte_emag_marketplace/sales_order_voucher',
                'entity_id',
                'sales/order',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('sales/order'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('eMAG Order Vouchers');
    $installer->getConnection()->createTable($table);
}

$emagSalesOrderItemTable = $installer->getTable('innobyte_emag_marketplace/sales_order_item');

// Table 'innobyte_emag_marketplace/sales_order_item'
if (!$installer->getConnection()->isTableExists($emagSalesOrderItemTable)) {
    $table = $installer->getConnection()
        ->newTable($emagSalesOrderItemTable)
        ->addColumn(
            'item_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
                'default' => '0',
            )
            , 'Item Id'
        )
        ->addColumn('emag_details', Varien_Db_Ddl_Table::TYPE_TEXT, '64K', array(), 'eMAG Details')
        ->addColumn('emag_created', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'eMAG Creation Time')
        ->addColumn('emag_modified', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'eMAG Modification Time')
        ->addForeignKey($installer->getFkName(
            'innobyte_emag_marketplace/sales_order_item',
            'item_id',
            'sales/order_item',
            'item_id'
        ),
            'item_id',
            $installer->getTable('sales/order_item'),
            'item_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('eMAG Sales Flat Order Item');

    // Create Table
    $installer->getConnection()->createTable($table);
}

###############################################################################

// table 'innobyte_emag_marketplace_locality'
$tableNameLocality = $installer->getTable('innobyte_emag_marketplace/locality');
$installer->getConnection()->dropTable($tableNameLocality);
$tableLocality = $installer->getConnection()
    ->newTable($tableNameLocality)
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
            'identity' => true,
        ),
        'The table \'s PK'
    )
    ->addColumn(
        'name',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        60,
        array(
            'nullable' => true,
        ),
        'eMAG locality name'
    )
    ->addColumn(
        'name_latin',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        60,
        array(
            'nullable' => true,
        ),
        'eMAG locality name latin'
    )
    ->addColumn(
        'region1',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        60,
        array(
            'nullable' => true,
        ),
        'eMAG locality region 1'
    )
    ->addColumn(
        'region2',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        60,
        array(
            'nullable' => true,
        ),
        'eMAG locality region 2'
    )
    ->addColumn(
        'region3',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        60,
        array(
            'nullable' => true,
        ),
        'eMAG locality region 3'
    )
    ->addColumn(
        'region4',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        60,
        array(
            'nullable' => true,
        ),
        'eMAG locality region 4'
    )
    ->addColumn(
        'region1_latin',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        60,
        array(
            'nullable' => true,
        ),
        'eMAG locality region 1 latin'
    )
    ->addColumn(
        'region2_latin',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        60,
        array(
            'nullable' => true,
        ),
        'eMAG locality region 2 latin'
    )
    ->addColumn(
        'region3_latin',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        60,
        array(
            'nullable' => true,
        ),
        'eMAG locality region 3 latin'
    )
    ->addColumn(
        'region4_latin',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        60,
        array(
            'nullable' => true,
        ),
        'eMAG locality region 4 latin'
    )
    ->addColumn(
        'emag_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable' => false,
        ),
        'eMAG vat ID'
    )
    ->addColumn(
        'geoid',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable' => true,
        ),
        'eMAG locality geoid'
    )
    ->addColumn(
        'emag_modified',
        Varien_Db_Ddl_Table::TYPE_DATETIME,
        null,
        array(
            'nullable' => true,
        ),
        'eMAG locality modification'
    )
    ->addColumn(
        'store_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ),
        'Store ID'
    )
    ->addColumn(
        'updated_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(
            'nullable' => false,
            'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
        ),
        'Row update time'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(
            'nullable' => false,
        ),
        'Row creation time'
    )
    ->addIndex(
        $installer->getIdxName(
            'innobyte_emag_marketplace/locality',
            array('emag_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('emag_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey(
        $installer->getFkName(
            'innobyte_emag_marketplace/locality',
            'store_id',
            'core/store',
            'store_id'
        ),
        'store_id',
        $installer->getTable('core/store'),
        'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('eMAG localities');
$installer->getConnection()->createTable($tableLocality);


// table 'innobyte_emag_marketplace_vat'
$tableNameVats = $installer->getTable('innobyte_emag_marketplace/vat');
$installer->getConnection()->dropTable($tableNameVats);
$tableVats = $installer->getConnection()
    ->newTable($tableNameVats)
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
            'identity' => true,
        ),
        'The table \'s PK'
    )
    ->addColumn(
        'rate',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        array(
            'nullable' => false,
        ),
        'eMAG vat value'
    )
    ->addColumn(
        'emag_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable' => false,
        ),
        'eMAG vat ID'
    )
    ->addColumn(
        'store_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ),
        'Store ID'
    )
    ->addColumn(
        'updated_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(
            'nullable' => false,
            'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
        ),
        'Row update time'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(
            'nullable' => false,
        ),
        'Row creation time'
    )
    ->addIndex(
        $installer->getIdxName(
            'innobyte_emag_marketplace/vat',
            array('emag_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('emag_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey(
        $installer->getFkName(
            'innobyte_emag_marketplace/vat',
            'store_id',
            'core/store',
            'store_id'
        ),
        'store_id',
        $installer->getTable('core/store'),
        'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('eMAG vat');
$installer->getConnection()->createTable($tableVats);

// table 'innobyte_emag_marketplace_category'
$tableNameCategories = $installer->getTable('innobyte_emag_marketplace/category');
$installer->getConnection()->dropTable($tableNameCategories);
$tableCategories = $installer->getConnection()
    ->newTable($tableNameCategories)
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
            'identity' => true,
        ),
        'The table \'s PK'
    )
    ->addColumn(
        'name',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        array(
            'nullable' => false,
        ),
        'eMAG category name'
    )
    ->addColumn(
        'emag_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable' => false,
        ),
        'eMAG category ID'
    )
    ->addColumn(
        'store_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ),
        'Store ID'
    )
    ->addColumn(
        'updated_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(
            'nullable' => false,
            'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
        ),
        'Row update time'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(
            'nullable' => false,
        ),
        'Row creation time'
    )
    ->addIndex(
        $installer->getIdxName(
            'innobyte_emag_marketplace/category',
            array('emag_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('emag_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey(
        $installer->getFkName(
            'innobyte_emag_marketplace/category',
            'store_id',
            'core/store',
            'store_id'
        ),
        'store_id',
        $installer->getTable('core/store'),
        'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('eMAG categories');
$installer->getConnection()->createTable($tableCategories);

// table 'innobyte_emag_marketplace_category_characteristic'
$tableNameCategoryCharacteristic = $installer->getTable(
    'innobyte_emag_marketplace/category_characteristic'
);
$installer->getConnection()->dropTable($tableNameCategoryCharacteristic);
$tableCategoryCharacteristic = $installer->getConnection()
    ->newTable($tableNameCategoryCharacteristic)
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
            'identity' => true,
        ),
        'The table \'s PK'
    )
    ->addColumn(
        'emag_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable' => false,
        ),
        'eMAG characteristic ID'
    )
    ->addColumn(
        'category_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ),
        'Magento eMAG category id'
    )
    ->addColumn(
        'name',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        array(
            'nullable' => false,
        ),
        'eMAG characteristic name'
    )
    ->addColumn(
        'display_order',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable' => true,
        ),
        'eMAG characteristic display order'
    )
    ->addIndex(
        $installer->getIdxName(
            'innobyte_emag_marketplace/category_characteristic',
            array('emag_id', 'category_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('emag_id', 'category_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey(
        $installer->getFkName(
            'innobyte_emag_marketplace/category_characteristic',
            'category_id',
            'innobyte_emag_marketplace/category',
            'id'
        ),
        'category_id',
        $installer->getTable('innobyte_emag_marketplace/category'),
        'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('eMAG category characteristics');
$installer->getConnection()->createTable($tableCategoryCharacteristic);

// table 'innobyte_emag_marketplace_category_familytype'
$tableNameCategoryFamilytype = $installer->getTable(
    'innobyte_emag_marketplace/category_familytype'
);
$installer->getConnection()->dropTable($tableNameCategoryFamilytype);
$tableCategoryFamilytype = $installer->getConnection()
    ->newTable($tableNameCategoryFamilytype)
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
            'identity' => true,
        ),
        'The table \'s PK'
    )
    ->addColumn(
        'emag_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable' => false,
        ),
        'eMAG characteristic ID'
    )
    ->addColumn(
        'category_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ),
        'Magento eMAG category id'
    )
    ->addColumn(
        'name',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        array(
            'nullable' => false,
        ),
        'eMAG family type name'
    )
    ->addIndex(
        $installer->getIdxName(
            'innobyte_emag_marketplace/category_familytype',
            array('emag_id', 'category_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('emag_id', 'category_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey(
        $installer->getFkName(
            'innobyte_emag_marketplace/category_familytype',
            'category_id',
            'innobyte_emag_marketplace/category',
            'id'
        ),
        'category_id',
        $installer->getTable('innobyte_emag_marketplace/category'),
        'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('eMAG category family types');
$installer->getConnection()->createTable($tableCategoryFamilytype);

// create table 'innobyte_emag_marketplace/category_familytype_characteristic'
$tableNameCatFamilyCharacteristic = $installer->getTable(
    'innobyte_emag_marketplace/category_familytype_characteristic'
);
$installer->getConnection()->dropTable($tableNameCatFamilyCharacteristic);
$tableCatFamilyCharacteristic = $installer->getConnection()
    ->newTable($tableNameCatFamilyCharacteristic)
    ->addColumn(
        'characteristic_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ),
        'eMAG characteristic id'
    )
    ->addColumn(
        'family_type_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ),
        'Magento eMAG family type id'
    )
    ->addColumn(
        'characteristic_family_type_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable' => true,
        ),
        'Display type'
    )
    ->addColumn(
        'is_foldable',
        Varien_Db_Ddl_Table::TYPE_TINYINT,
        null,
        array(
            'nullable' => true,
        ),
        'A foldable characteristic wraps all family members as one item'
    )
    ->addColumn(
        'display_order',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable' => true,
        ),
        'Display order'
    )
    ->addForeignKey(
        $installer->getFkName(
            'innobyte_emag_marketplace/category_familytype_characteristic',
            'family_type_id',
            'innobyte_emag_marketplace/category_familytype',
            'id'
        ),
        'family_type_id',
        $installer->getTable('innobyte_emag_marketplace/category_familytype'),
        'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Family types characteristics');
$installer->getConnection()->createTable($tableCatFamilyCharacteristic);

// table 'innobyte_emag_marketplace_product'
$tableNameProducts = $installer->getTable('innobyte_emag_marketplace/product');
$installer->getConnection()->dropTable($tableNameProducts);
$tableProducts = $installer->getConnection()
    ->newTable($tableNameProducts)
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
            'identity' => true,
        ),
        'The table \'s PK'
    )
    ->addColumn(
        'product_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ),
        'Magento product id'
    )
    ->addColumn(
        'part_number_key',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        array(
            'nullable' => true,
        ),
        'eMAG part number key'
    )
    ->addColumn(
        'name',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        array(
            'nullable' => true,
        ),
        'eMAG product name'
    )
    ->addColumn(
        'brand',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        array(
            'nullable' => true,
        ),
        'eMAG product brand'
    )
    // this is altered afterwards to mediumtext
    ->addColumn(
        'description',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        array(
            'nullable' => true,
        ),
        'eMAG product desc'
    )
    ->addColumn(
        'warranty',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'nullable' => true,
            'unsigned' => true,
        ),
        'eMAG product warranty'
    )
    ->addColumn(
        'commission_type',
        Varien_Db_Ddl_Table::TYPE_TINYINT,
        null,
        array(
            'nullable' => false,
            'default'  => '1',
        ),
        'eMAG commission type'
    )
    ->addColumn(
        'commission_value',
        Varien_Db_Ddl_Table::TYPE_DECIMAL,
        '12,4',
        array(
            'nullable' => false,
            'unsigned' => true,
        ),
        'eMAG commission value'
    )
    ->addColumn(
        'category_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable' => true,
            'unsigned' => true,
        ),
        'Magento eMAG category id'
    )
    ->addColumn(
        'family_type_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable' => true,
            'unsigned' => true,
        ),
        'Magento id of eMAG family type'
    )
    ->addColumn(
        'handling_time',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable' => true,
            'unsigned' => true,
        ),
        'Handling time'
    )
    ->addColumn(
        'status',
        Varien_Db_Ddl_Table::TYPE_TINYINT,
        null,
        array(
            'nullable' => false,
            'default'  => 0,
        ),
        'Vendor offer status'
    )
    ->addColumn(
        'start_date',
        Varien_Db_Ddl_Table::TYPE_DATE,
        null,
        array(
            'nullable' => true,
        ),
        'eMAG offer start date'
    )
    ->addColumn(
        'store_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ),
        'Store ID'
    )
    ->addColumn(
        'vat_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => true,
        ),
        'Store ID'
    )
    ->addColumn(
        'is_synced',
        Varien_Db_Ddl_Table::TYPE_TINYINT,
        1,
        array(
            'unsigned' => true,
            'default' => 0,
        ),
        'Whether product has been sent to eMAG'
    )
    ->addColumn(
        'updated_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(
            'nullable' => false,
            'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
        ),
        'Row update time'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(
            'nullable' => false,
        ),
        'Row creation time'
    )
    ->addIndex(
        $installer->getIdxName(
            'innobyte_emag_marketplace/category',
            array('product_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('product_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey(
        $installer->getFkName(
            'innobyte_emag_marketplace/product',
            'store_id',
            'core/store',
            'store_id'
        ),
        'store_id',
        $installer->getTable('core/store'),
        'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'innobyte_emag_marketplace/product',
            'product_id',
            'catalog/product',
            'entity_id'
        ),
        'product_id',
        $installer->getTable('catalog/product'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'innobyte_emag_marketplace/product',
            'vat_id',
            'innobyte_emag_marketplace/vat',
            'id'
        ),
        'vat_id',
        $installer->getTable('innobyte_emag_marketplace/vat'),
        'id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'innobyte_emag_marketplace/product',
            'category_id',
            'innobyte_emag_marketplace/category',
            'id'
        ),
        'category_id',
        $installer->getTable('innobyte_emag_marketplace/category'),
        'id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'innobyte_emag_marketplace/product',
            'family_type_id',
            'innobyte_emag_marketplace/category_familytype',
            'id'
        ),
        'family_type_id',
        $installer->getTable('innobyte_emag_marketplace/category_familytype'),
        'id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('eMAG product data');
$installer->getConnection()->createTable($tableProducts);
// hack since magento does not have mediumtext column type
// in order to create it direcly as mediumtext
$installer->getConnection()
    ->changeColumn(
        $tableNameProducts,
        'description',
        'description',
        'MEDIUMTEXT NULL'
    );

// table 'innobyte_emag_marketplace_product_barcode'
$tableNameProdBarcodes = $installer->getTable(
    'innobyte_emag_marketplace/product_barcode'
);
$installer->getConnection()->dropTable($tableNameProdBarcodes);
$tableProdBarcodes = $installer->getConnection()
    ->newTable($tableNameProdBarcodes)
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
            'identity' => true,
        ),
        'The table \'s PK'
    )
    ->addColumn(
        'product_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ),
        'FK innobyte_emag_marketplace_product table'
    )
    ->addColumn(
        'value',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        20,
        array(
            'nullable' => false,
        ),
        'product barcode'
    )
    ->addForeignKey(
        $installer->getFkName(
            'innobyte_emag_marketplace/product_barcode',
            'product_id',
            'innobyte_emag_marketplace/product',
            'id'
        ),
        'product_id',
        $installer->getTable('innobyte_emag_marketplace/product'),
        'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Product barcodes');
$installer->getConnection()->createTable($tableProdBarcodes);

// innobyte_emag_marketplace_product_cat_characteristic
$tableNameProdCatChar = $installer->getTable(
    'innobyte_emag_marketplace/product_cat_characteristic'
);
$installer->getConnection()->dropTable($tableNameProdCatChar);
$tableProdCatChar = $installer->getConnection()
    ->newTable($tableNameProdCatChar)
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
            'identity' => true,
        ),
        'The table \'s PK'
    )
    ->addColumn(
        'product_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ),
        'FK innobyte_emag_marketplace_product table'
    )
    ->addColumn(
        'characteristic_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable' => false,
            'unsigned' => true,
        ),
        'FK innobyte_emag_marketplace_category_characteristic table'
    )
    ->addColumn(
        'value',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        array(
            'nullable' => false,
        ),
        'Characteristic value'
    )
    ->addForeignKey(
        $installer->getFkName(
            'innobyte_emag_marketplace/product_cat_characteristic',
            'product_id',
            'innobyte_emag_marketplace/product',
            'id'
        ),
        'product_id',
        $installer->getTable('innobyte_emag_marketplace/product'),
        'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'innobyte_emag_marketplace/product_cat_characteristic',
            'characteristic_id',
            'innobyte_emag_marketplace/category_characteristic',
            'id'
        ),
        'product_id',
        $installer->getTable('innobyte_emag_marketplace/category_characteristic'),
        'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Product \'s category characteristics values');
$installer->getConnection()->createTable($tableProdCatChar);

$installer->endSetup();
