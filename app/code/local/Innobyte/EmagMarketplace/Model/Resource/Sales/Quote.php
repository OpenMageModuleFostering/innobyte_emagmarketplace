<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Resource_Sales_Quote
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Resource_Sales_Quote
    extends Innobyte_EmagMarketplace_Model_Resource_Sales_Abstract
{

    /**
     * Main entity resource model name
     *
     * @var string
     */
    protected $_parentResourceModel = 'sales/quote';

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('innobyte_emag_marketplace/sales_quote', 'entity_id');
    }

}
