<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Resource_Sales_Order
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Resource_Sales_Order
    extends Innobyte_EmagMarketplace_Model_Resource_Sales_Abstract
{

    /**
     * Main entity resource model name
     *
     * @var string
     */
    protected $_parentResourceModel = 'sales/order';

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('innobyte_emag_marketplace/sales_order', 'entity_id');
    }

}