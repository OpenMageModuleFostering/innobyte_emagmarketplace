<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Resource_Sales_Order_Item
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Resource_Sales_Order_Item
    extends Innobyte_EmagMarketplace_Model_Resource_Sales_Item_Abstract
{

    /**
     * Main entity resource model name
     *
     * @var string
     */
    protected $_parentResourceModel = 'sales/order_item';

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('innobyte_emag_marketplace/sales_order_item', 'item_id');
    }

}
