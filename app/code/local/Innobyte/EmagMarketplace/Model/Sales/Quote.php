<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Sales_Quote
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Sales_Quote extends Innobyte_EmagMarketplace_Model_Sales_Abstract
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_sales_quote';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getQuote() in this case
     *
     * @var string
     */
    protected $_eventObject = 'quote';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('innobyte_emag_marketplace/sales_quote');
    }

}
