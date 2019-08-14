<?php
/**
 * eMAG locality resource collection model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Resource_Locality_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Name prefix of events that are dispatched by model
     * 
     * @Override
     * @var string
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_locality_collection';

    /**
     * Name of event parameter
     *
     * @Override
     * @var string
     */
    protected $_eventObject = 'emag_locality_collection';
    
    
    
    /**
     * @Override
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('innobyte_emag_marketplace/locality');
    }
    
    
    
    /**
     * Add filter by store
     *
     * @param int|Mage_Core_Model_Store $mxdStore
     *
     * @return Innobyte_EmagMarketplace_Model_Resource_Locality_Collection
     */
    public function addStoreFilter($mxdStore)
    {
        $stores = array();
        $storesCnt = 0;
        if ($mxdStore instanceof Mage_Core_Model_Store) {
            $stores[] = $mxdStore->getId();
            $storesCnt++;
        } elseif (is_numeric($mxdStore)) {
            $stores[] = (int) $mxdStore;
            $storesCnt++;
        }

        if (Mage_Core_Model_App::ADMIN_STORE_ID == $stores[0]) {
            foreach (Mage::app()->getWebsites() as $website) {
                foreach ($website->getGroups() as $group) {
                    $s = $group->getStores();
                    foreach ($s as $storeModel) {
                        $stores[] = $storeModel->getId();
                        $storesCnt++;
                    }
                }
            }
        }
        
        if (1 == $storesCnt) {
            $this->addFieldToFilter('store_id', array('eq' => $stores[0]));
        } elseif ($storesCnt > 1) {
            $this->addFieldToFilter('store_id', array('in' => $stores));
        }
        
        return $this;
    }
}
