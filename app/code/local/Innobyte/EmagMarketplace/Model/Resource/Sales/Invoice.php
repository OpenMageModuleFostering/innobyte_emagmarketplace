<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Resource_Sales_Invoice
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Resource_Sales_Invoice
    extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('innobyte_emag_marketplace/sales_invoice', 'id');
    }

}
