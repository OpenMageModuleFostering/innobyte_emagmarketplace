<?php

/**
 * Class Innobyte_EmagMarketplace_Block_Adminhtml_Sales_Order_Create_Shipping_Method_Form
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Block_Adminhtml_Sales_Order_Create_Shipping_Method_Form
    extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
{

    /**
     * Retrieve array of shipping rates groups
     *  - remove eMAG shipping methods if emag_order_id is not available
     *  - remove Magento default shipping methods if emag_order_id is available
     *
     * @return array
     */
    public function getShippingRates()
    {
        parent::getShippingRates();

        if (!empty($this->_rates)) {
            foreach ($this->_rates as $code => $_rates) {
                if (!$this->getQuote()->getEmagOrderId() && strpos($code, 'emag') !== false) {
                    unset($this->_rates[$code]);
                } elseif ($this->getQuote()->getEmagOrderId() && strpos($code, 'emag') === false) {
                    unset($this->_rates[$code]);
                }
            }
        }

        return $this->_rates;
    }

}
