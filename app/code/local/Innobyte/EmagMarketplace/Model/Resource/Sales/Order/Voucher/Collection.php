<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Resource_Sales_Order_Voucher_Collection
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Resource_Sales_Order_Voucher_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_sales_order_voucher_collection';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getOrderVoucherCollection() in this case
     *
     * @var string
     */
    protected $_eventObject = 'order_voucher_collection';

    /**
     * Initialize resource
     */
    public function _construct()
    {
        $this->_init('innobyte_emag_marketplace/sales_order_voucher');
    }

}
