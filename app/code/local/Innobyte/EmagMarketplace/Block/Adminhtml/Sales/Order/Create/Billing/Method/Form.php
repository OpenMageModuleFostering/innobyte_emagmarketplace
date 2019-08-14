<?php

/**
 * Class Innobyte_EmagMarketplace_Block_Adminhtml_Sales_Order_Create_Billing_Method_Form
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Block_Adminhtml_Sales_Order_Create_Billing_Method_Form
    extends Mage_Adminhtml_Block_Sales_Order_Create_Billing_Method_Form
{

    /**
     * Retrieve availale payment methods
     *  - remove eMAG payment methods if emag_order_id is not available
     *  - remove Magento default payment methods if emag_order_id is available
     *
     * @return array
     */
    public function getMethods()
    {
        parent::getMethods();

        $methods = $this->getData('methods');
        if (!empty($methods)) {
            foreach ($methods as $key => $method) {
                if (
                    (!$this->getQuote()->getEmagOrderId() && strpos($method->getCode(), 'emag') !== false)
                    || $method->getCode() == 'emag_unknown'
                ) {
                    unset($methods[$key]);
                } elseif ($this->getQuote()->getEmagOrderId() && strpos($method->getCode(), 'emag') === false) {
                    unset($methods[$key]);
                }
            }
            $this->setData('methods', $methods);
        }

        return $methods;
    }

}
