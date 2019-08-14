<?php

/**
 * Class Innobyte_EmagMarketplace_Block_Adminhtml_Sales_Order_Create_Items_Grid
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Block_Adminhtml_Sales_Order_Create_Items_Grid
    extends Mage_Adminhtml_Block_Sales_Order_Create_Items_Grid
{

    /**
     * Get quote's origin order id
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        $orderId = Mage::getSingleton('adminhtml/session_quote')->getOrderId();
        $order = Mage::getModel('sales/order')
            ->load($orderId);

        return $order;
    }

    /**
     * Get original item based on quote order id and quote item product id
     *
     * @param Mage_Sales_Model_Order_Item $quoteItem
     * @return null|Mage_Sales_Model_Order_Item
     */
    public function getOriginalItem($quoteItem)
    {
        $order = $this->getOrder();

        // skip if not eMAG order
        if(!$order->getEmagOrderId()) {
            return null;
        }

        /** @var $collection Mage_Sales_Model_Entity_Order_Item_Collection */
        $collection = Mage::getResourceModel('sales/order_item_collection');
        $collection
            ->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('product_id', $quoteItem->getProductId());

        if($collection->getSize()) {
            return $collection->getFirstItem();
        }

        return null;
    }

}
