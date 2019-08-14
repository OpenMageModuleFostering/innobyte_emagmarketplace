<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Resource_Sales_Quote_Voucher
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Resource_Sales_Quote_Voucher
    extends Innobyte_EmagMarketplace_Model_Resource_Sales_Voucher_Abstract
{

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('innobyte_emag_marketplace/sales_quote_voucher', 'id');
    }

}
