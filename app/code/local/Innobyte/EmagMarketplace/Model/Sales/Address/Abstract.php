<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Sales_Address_Abstract
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
abstract class Innobyte_EmagMarketplace_Model_Sales_Address_Abstract
    extends Innobyte_EmagMarketplace_Model_Sales_Abstract
{

    /**
     * Attach data to collection
     *
     * @param Varien_Data_Collection_Db $collection
     * @return Innobyte_EmagMarketplace_Model_Sales_Address_Abstract
     */
    public function attachDataToCollection(Varien_Data_Collection_Db $collection)
    {
        $this->_getResource()->attachDataToCollection($collection);

        return $this;
    }

}
