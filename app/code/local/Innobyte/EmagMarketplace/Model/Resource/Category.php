<?php
/**
 * eMAG category resource model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Resource_Category
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * @Override
     */
    public function _construct()
    {
        $this->_init('innobyte_emag_marketplace/category', 'id');
    }
    
    
    
    /**
     * @Override
     */
    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        $currentTime = Varien_Date::now();
        if ((!$object->getId() || $object->isObjectNew())
            && !$object->getCreatedAt()) {
            $object->setCreatedAt($currentTime);
        }
        $object->setUpdatedAt($currentTime);
        return parent::_prepareDataForSave($object);
    }
    
    
    
    /**
     * @Override
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $write = $this->_getWriteAdapter();
        $read  = $this->_getReadAdapter();
        
        if (is_array($object->getImportedCharacteristics())) {
            $tableNameC = $this->getTable(
                'innobyte_emag_marketplace/category_characteristic'
            );
            
            $select = $read->select()
                ->from($tableNameC, array('emag_id'))
                ->where('category_id = ?', $object->getId());

            $oldCharacteristicsEmagIds = $read->fetchCol($select);
            $newCharacteristicsEmagIds = array();
            foreach ($object->getImportedCharacteristics() as $cModel) {
                $newCharacteristicsEmagIds[] = $cModel->getEmagId();
            }
                        
            $toBeDeleted = array_diff(
                $oldCharacteristicsEmagIds,
                $newCharacteristicsEmagIds
            );
            if (!empty($toBeDeleted)) {
                $where[] = $write->quoteInto('category_id = ?', $object->getId());
                $where[] = $write->quoteInto('emag_id IN (?)', $toBeDeleted);
                $write->delete($tableNameC, $where);
            }
            
            foreach ($object->getImportedCharacteristics() as $cModel) {
                $cModel->setCategoryId($object->getId())
                    ->save();
            }
        }
        
        if (is_array($object->getImportedFamilyTypes())) {
            $tableNameFt = $this->getTable(
                'innobyte_emag_marketplace/category_familytype'
            );
            
            $select = $read->select()
                ->from($tableNameFt, array('emag_id'))
                ->where('category_id = ?', $object->getId());

            $oldFamilyTypeEmagIds = $read->fetchCol($select);
            $newFamilyTypeEmagIds = array();
            foreach ($object->getImportedFamilyTypes() as $ftModel) {
                $newFamilyTypeEmagIds[] = $ftModel->getEmagId();
            }
                        
            $toBeDeleted = array_diff(
                $oldFamilyTypeEmagIds,
                $newFamilyTypeEmagIds
            );
            if (!empty($toBeDeleted)) {
                $where[] = $write->quoteInto('category_id = ?', $object->getId());
                $where[] = $write->quoteInto('emag_id IN (?)', $toBeDeleted);
                $write->delete($tableNameFt, $where);
            }
            
            foreach ($object->getImportedFamilyTypes() as $ftModel) {
                $ftModel->setCategoryId($object->getId())
                    ->save();
            }
        }
        
        return parent::_afterSave($object);
    }
}
