<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Sales_Order
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Sales_Order extends Innobyte_EmagMarketplace_Model_Sales_Abstract
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_sales_order';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getOrder() in this case
     *
     * @var string
     */
    protected $_eventObject = 'order';

    /**
     * eMAG order statuses
     *
     * @var array
     */
    protected $_orderStatuses = null;

    /**
     * Allowed magento states for eMAG orders
     *
     * @var array
     */
    protected $_allowedOrderStates = array(
        Mage_Sales_Model_Order::STATE_NEW,
        Mage_Sales_Model_Order::STATE_PROCESSING
    );

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('innobyte_emag_marketplace/sales_order');
    }

    /**
     * Get allowed magento order states
     *
     * @return array
     */
    public function getAllowedOrderStates()
    {
        return $this->_allowedOrderStates;
    }

    /**
     * Get eMAG order statuses
     *
     * @return array
     */
    public function getEmagOrderStatuses()
    {
        if(!$this->_orderStatuses) {
            /** @var $model Innobyte_EmagMarketplace_Model_Order_Convert_Emag */
            $model = Mage::getModel('innobyte_emag_marketplace/order_convert_emag');
            $this->_orderStatuses = $model->getEmagOrderStatuses();
        }

        return $this->_orderStatuses;
    }

    /**
     * Check if order can be acknowledged
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function canAcknowledge(Mage_Sales_Model_Order $order)
    {
        return $order->getStatus() == Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_NEW;
    }

    /**
     * Can prepare order
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function canPrepare(Mage_Sales_Model_Order $order)
    {
        if (
            $order->getStatus() == Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_IN_PROGRESS
            || $order->getStatus() == Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_FINALIZED
            || $order->getStatus() == Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_CANCELED
        ) {
            return true;
        }

        return false;
    }

    /**
     * Can finalize order
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function canFinalize(Mage_Sales_Model_Order $order)
    {
        if (
            $order->getStatus() == Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_IN_PROGRESS
            || $order->getStatus() == Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_PREPARED
        ) {
            return true;
        }

        return false;
    }

    /**
     * Can cancel order
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function canCancel(Mage_Sales_Model_Order $order)
    {
        if (
            $order->getStatus() == Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_IN_PROGRESS
            || $order->getStatus() == Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_PREPARED
        ) {
            return true;
        }

        return false;
    }

}
