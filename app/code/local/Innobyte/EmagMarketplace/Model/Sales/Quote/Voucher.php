<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Sales_Quote_Voucher
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Sales_Quote_Voucher
    extends Innobyte_EmagMarketplace_Model_Sales_Voucher_Abstract
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_sales_quote_vouchers';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getQuoteVouchers() in this case
     *
     * @var string
     */
    protected $_eventObject = 'quote_vouchers';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('innobyte_emag_marketplace/sales_quote_voucher');
    }

}
