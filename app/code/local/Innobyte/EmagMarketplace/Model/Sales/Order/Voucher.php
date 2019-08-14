<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Sales_Order_Voucher
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Sales_Order_Voucher
    extends Innobyte_EmagMarketplace_Model_Sales_Voucher_Abstract
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_sales_order_voucher';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getOrderVoucher() in this case
     *
     * @var string
     */
    protected $_eventObject = 'order_voucher';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('innobyte_emag_marketplace/sales_order_voucher');
    }

}
