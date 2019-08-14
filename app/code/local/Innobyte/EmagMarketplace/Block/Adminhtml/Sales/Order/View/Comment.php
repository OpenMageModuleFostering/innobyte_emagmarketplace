<?php

/**
 * Class Innobyte_EmagMarketplace_Block_Adminhtml_Sales_Order_View_Comment
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Block_Adminhtml_Sales_Order_View_Comment
    extends Mage_Adminhtml_Block_Template
{

    /**
     * Retrieve order model
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('sales_order');
    }

    /**
     * Get eMAG customer comments
     */
    public function getCustomerComment()
    {
        $comment = $this->getOrder()->getEmagCustomerComment();

        return $comment;

    }

}
