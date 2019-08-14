<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Resource_Sales_Order_Address
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Resource_Sales_Order_Address
    extends Innobyte_EmagMarketplace_Model_Resource_Sales_Address_Abstract
{

    /**
     * Main entity resource model name
     *
     * @var string
     */
    protected $_parentResourceModel = 'sales/order_address';

    /**
     * Initializes resource
     */
    protected function _construct()
    {
        $this->_init('innobyte_emag_marketplace/sales_order_address', 'entity_id');
    }

}
