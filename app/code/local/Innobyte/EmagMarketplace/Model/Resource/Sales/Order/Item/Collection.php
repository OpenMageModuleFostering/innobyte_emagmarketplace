<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Resource_Sales_Quote_Item_Collection
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Resource_Sales_Order_Item_Collection
    extends Mage_Sales_Model_Resource_Order_Item_Collection
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_item_collection';

    /**
     * Parameter name in event
     *
     * In observer method you can use $observer->getEvent()->getOrderItemCollection()
     *
     * @var string
     */
    protected $_eventObject = 'order_item_collection';
}
