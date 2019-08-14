<?php
/**
 * Locality flag where last syncronization date is stored.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Locality_Flag extends Mage_Core_Model_Flag
{
    /**
     * Flag code
     *
     * @Override
     * @var string
     */
    protected $_flagCode = 'innobyte_emag_marketplace_locality_sync';

    
    
    /**
     * Retrieve flag data array
     *
     * @Override
     * @return array
     */
    public function getFlagData()
    {
        $flagData = parent::getFlagData();
        if (!is_array($flagData)) {
            $flagData = array();
            $this->setFlagData($flagData);
        }
        return $flagData;
    }
    
    
    
    /**
     * Retrieve last syncronization date of localities for a store.
     *
     * @param int $storeId
     * @return string Date in the format 'Y-m-d H:i:s', or empty string
     *                if no syncronization has been done before.
     */
    public function getLastSyncronization($storeId)
    {
        $flagData = $this->getFlagData();
        if (!array_key_exists($storeId, $flagData)) {
            $flagData[$storeId] = '';
            $this->setFlagData($flagData);
        }
        return $flagData[$storeId];
    }
    
    
    
    /**
     * Set last syncronization date of localities for a store.
     *
     * @param int $storeId
     * @param string $date   Should be in the format 'Y-m-d H:i:s'
     * @return Innobyte_EmagMarketplace_Model_Locality_Flag
     */
    public function setLastSyncronization($storeId, $date)
    {
        $flagData = $this->getFlagData();
        $flagData[$storeId] = $date;
        $this->setFlagData($flagData);
        return $this;
    }
}
