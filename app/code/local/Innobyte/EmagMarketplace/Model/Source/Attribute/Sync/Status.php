<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Source_Attribute_Sync_Status
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Source_Attribute_Sync_Status
{

    /**
     * Attribute options
     *
     * @var null
     */
    protected $_options = null;

    /**
     * Retrieve data helper
     *
     * @return Innobyte_EmagMarketplace_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('innobyte_emag_marketplace');
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'value' => Innobyte_EmagMarketplace_Model_Product::IS_SYNCED,
                    'label' => $this->_getHelper()->__('Yes')
                ),
                array(
                    'value' => Innobyte_EmagMarketplace_Model_Product::IS_NOT_SYNCED,
                    'label' => $this->_getHelper()->__('No')
                ),
            );
        }

        return $this->_options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $_options = array();
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

}