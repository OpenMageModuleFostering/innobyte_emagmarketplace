<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Customer_Form
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Customer_Form extends Mage_Customer_Model_Form
{

    /**
     * Check if eMAG attributes can be used
     *  - check if order_address edit mode and emag_order_id is available
     *  - check if quote and emag_order_id is available
     *  - make sure attributes are removed from Customer->Edit page
     *
     * @return bool
     */
    protected function _canUseEmagAttributes()
    {
        if (
            (
                ($this->_getOrderAddress() && $this->_getOrderAddress()->getOrder()->getEmagOrderId())
                || $this->_getQuoteSession()->getQuote()->getEmagOrderId()
            )
            && !$this->_getCurrentCustomer()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get quote session
     *
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    private function _getQuoteSession()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }

    /**
     * Get current order address
     *
     * @return Mage_Sales_Model_Order_Address
     */
    private function _getOrderAddress()
    {
        return Mage::registry('order_address');
    }

    /**
     * Get current customer
     *
     * @return Mage_Customer_Model_Customer
     */
    private function _getCurrentCustomer()
    {
        return Mage::registry('current_customer');
    }

    /**
     * Return array of form attributes
     *  - skip eMAG attributes if emag_order_id is not available
     *  - for customer account edit these attributes should be removed all the time
     *
     * @return array
     */
    public function getAttributes()
    {
        if($this->_canUseEmagAttributes()) {
            return parent::getAttributes();
        }

        if (is_null($this->_attributes)) {
            /* @var $collection Mage_Eav_Model_Resource_Form_Attribute_Collection */
            $collection = $this->_getFormAttributeCollection();

            $collection->setStore($this->getStore())
                ->setEntityType($this->getEntityType())
                ->addFormCodeFilter($this->getFormCode())
                ->setSortOrder();

            $this->_attributes = array();
            $this->_userAttributes = array();

            /** @var $attribute Mage_Customer_Model_Attribute */
            foreach ($collection as $attribute) {
                if (strpos($attribute->getAttributeCode(), 'emag') !== false) {
                    continue;
                }

                /* @var $attribute Mage_Eav_Model_Entity_Attribute */
                $this->_attributes[$attribute->getAttributeCode()] = $attribute;
                if ($attribute->getIsUserDefined()) {
                    $this->_userAttributes[$attribute->getAttributeCode()] = $attribute;
                } else {
                    $this->_systemAttributes[$attribute->getAttributeCode()] = $attribute;
                }
            }
        }
        return $this->_attributes;
    }

}
