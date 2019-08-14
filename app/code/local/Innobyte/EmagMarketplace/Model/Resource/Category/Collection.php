<?php
/**
 * eMAG category resource collection model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Resource_Category_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Name prefix of events that are dispatched by model
     *
     * @Override
     * @var string
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_category_collection';

    /**
     * Name of event parameter
     *
     * @Override
     * @var string
     */
    protected $_eventObject = 'emag_category_collection';
    
    
    
    /**
     * @Override
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('innobyte_emag_marketplace/category');
        $this->setFlag('load_only_with_family_types', 0);
        $this->setFlag('load_only_with_family_types_has_been_applied', 0);
    }
    
    
    
    /**
     * Add filter by store
     *
     * @param int|Mage_Core_Model_Store $mxdStore
     *
     * @return Innobyte_EmagMarketplace_Model_Resource_Category_Collection
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
    
    
    
    /**
     * @Override
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        if ($this->getSelect()->getPart(Zend_Db_Select::HAVING)) {
            //No good way to chop this up, so just subquery it
            $subQuery = new Zend_Db_Expr('(' . $countSelect->__toString() . ')');
            $countSelect->reset()
                ->from(array('temp' => $subQuery))
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns('COUNT(*)');
        } else {
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            // Count doesn't work with group by columns keep the group by
            if (count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
                $countSelect->reset(Zend_Db_Select::GROUP);
                $countSelect->distinct(true);
                $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
                $countSelect->columns('COUNT(DISTINCT ' . implode(', ', $group) . ')');
            } else {
                $countSelect->columns('COUNT(*)');
            }
        }
        return $countSelect;
    }
    
    
    
    /**
     * Load collection data
     *
     * @Override
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return Varien_Data_Collection_Db
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->getOnlyWithFamilyTypes()
            && !$this->isLoaded()
            && !$this->getFlag('load_only_with_family_types_has_been_applied')) {
            $this->getSelect()->where(
                new Zend_Db_Expr(
                    ' EXISTS (SELECT 1 FROM '
                    . $this->getTable('innobyte_emag_marketplace/category_familytype')
                    . ' WHERE category_id = main_table.id)'
                )
            );
            $this->setFlag('load_only_with_family_types_has_been_applied', 1);
        }
        
        return parent::load($printQuery, $logQuery);
    }
    
    
    
    /**
     * Setter method for loading only categories that have family type(s).
     *
     * @param boolean $flag
     * @return Innobyte_EmagMarketplace_Model_Resource_Category_Collection
     */
    public function setOnlyWithFamilyTypes($flag)
    {
        $this->setFlag('load_only_with_family_types', (bool) $flag);
        return $this;
    }
    
    
    
    /**
     * Getter method for loading only categories that have family type(s).
     *
     * @return boolean
     */
    public function getOnlyWithFamilyTypes()
    {
        return $this->getFlag('load_only_with_family_types');
    }
}
