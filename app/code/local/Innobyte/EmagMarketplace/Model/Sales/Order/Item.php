<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Sales_Order_Item
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Sales_Order_Item
    extends Innobyte_EmagMarketplace_Model_Sales_Item_Abstract
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_sales_order_item';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getOrderItem() in this case
     *
     * @var string
     */
    protected $_eventObject = 'order_item';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('innobyte_emag_marketplace/sales_order_item');
    }

}
