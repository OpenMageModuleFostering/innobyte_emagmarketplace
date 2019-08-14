<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Sales_Order_Status
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @rewrite: add event prefix and event object to use them in observers
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Sales_Order_Status extends Mage_Sales_Model_Order_Status
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_status';

    /**
     * Parameter name in event
     *
     * In observer method you can use $observer->getEvent()->getStatus()
     *
     * @var string
     */
    protected $_eventObject = 'order_status';

}
