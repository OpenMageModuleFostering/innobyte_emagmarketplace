<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Customer_Attributes
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Customer_Attributes extends Mage_Core_Model_Abstract
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_customer_attributes';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getCustomerAttributes() in this case
     *
     * @var string
     */
    protected $_eventObject = 'customer_attributes';

    /**
     * Entity type
     *
     * @var null
     */
    protected $_entityType = null;

    /**
     * Customer attributes
     *
     * @var array
     */
    protected $_customerAttributes = array();

    /**
     * Customer address attributes
     *
     * @var array
     */
    protected $_customerAddressAttributes = array();

    /**
     * Set entity type
     *
     * @param $entityType
     */
    public function setEntityType($entityType)
    {
        $this->_entityType = $entityType;
    }

    /**
     * Get entity type
     *
     * @return null
     */
    public function getEntityType()
    {
        return $this->_entityType;
    }

    /**
     * Get customer attributes
     *
     * @return array
     */
    public function getCustomerAttributes()
    {
        if (empty($this->_customerAttributes)) {
            $this->_customerAttributes = $this->_getAttributes();
        }

        return $this->_customerAttributes;
    }

    /**
     * Get customer address attributes
     *
     * @return array
     */
    public function getCustomerAddressAttributes()
    {
        if (empty($this->_customerAddressAttributes)) {
            $this->_customerAddressAttributes = $this->_getAttributes();
        }

        return $this->_customerAddressAttributes;
    }

    /**
     * Get attributes
     *
     * @return array
     */
    protected function _getAttributes()
    {
        $attributes = array();
        /* @var $config Mage_Eav_Model_Config */
        $config = Mage::getSingleton('eav/config');
        foreach ($config->getEntityAttributeCodes($this->_entityType) as $attributeCode) {
            $attribute = $config->getAttribute($this->_entityType, $attributeCode);
            if ($attribute && $attribute->getIsUserDefined()) {
                $attributes[] = $attributeCode;
            }
        }
        return $attributes;
    }

}
