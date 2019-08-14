<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Sales_Abstract
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
abstract class Innobyte_EmagMarketplace_Model_Sales_Abstract extends Mage_Core_Model_Abstract
{

    /**
     * Save attribute
     *
     * @param Mage_Customer_Model_Attribute $attribute
     * @return Innobyte_EmagMarketplace_Model_Sales_Abstract
     */
    public function saveAttribute(Mage_Customer_Model_Attribute $attribute)
    {
        $this->_getResource()->saveAttribute($attribute);

        return $this;
    }

    /**
     * Delete attribute
     *
     * @param Mage_Customer_Model_Attribute $attribute
     * @return Innobyte_EmagMarketplace_Model_Sales_Abstract
     */
    public function deleteAttribute(Mage_Customer_Model_Attribute $attribute)
    {
        $this->_getResource()->deleteAttribute($attribute);

        return $this;
    }

    /**
     * Attach attribute data to sales object
     *
     * @param Mage_Core_Model_Abstract $sales
     * @return Innobyte_EmagMarketplace_Model_Sales_Abstract
     */
    public function attachAttributeData(Mage_Core_Model_Abstract $sales)
    {
        $sales->addData($this->getData());

        return $this;
    }

    /**
     * Save attributes data
     *
     * @param Mage_Core_Model_Abstract $sales
     * @return Innobyte_EmagMarketplace_Model_Sales_Abstract
     */
    public function saveAttributeData(Mage_Core_Model_Abstract $sales)
    {
        $this->addData($sales->getData())
            ->setId($sales->getId())
            ->save();

        return $this;
    }

    /**
     * Check if main entity is already deleted from the database:
     *  - delete attributes should not be saved in database
     *
     * @return Innobyte_EmagMarketplace_Model_Sales_Abstract
     */
    protected function _beforeSave()
    {
        if ($this->_dataSaveAllowed && !$this->_getResource()->isEntityExists($this)) {
            $this->_dataSaveAllowed = false;
        }

        return parent::_beforeSave();
    }

}
