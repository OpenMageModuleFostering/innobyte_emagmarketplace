<?php
/**
 * eMAG product resource model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Resource_Product
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * @Override
     */
    public function _construct()
    {
        $this->_init('innobyte_emag_marketplace/product', 'id');
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
        
        // save barcodes
        if (!is_array($object->getBarcodes())) {
            $object->setBarcodes(array());
        }
        $tableNameBarcodes = $this->getTable(
            'innobyte_emag_marketplace/product_barcode'
        );
        $select = $read->select()
            ->from($tableNameBarcodes, array('value'))
            ->where('product_id = ?', intval($object->getId()));
        $currentBarcodes = $read->fetchCol($select);
        $newBarcodes = array_diff($object->getBarcodes(), $currentBarcodes);
        $deleteBarcodes = array_diff($currentBarcodes, $object->getBarcodes());
        if (!empty($deleteBarcodes)) {
            $where = array();
            $where[] = $write->quoteInto('product_id = ?', intval($object->getId()));
            $where[] = $write->quoteInto('value IN (?)', $deleteBarcodes);
            $write->delete($tableNameBarcodes, $where);
        }
        if (!empty($newBarcodes)) {
            $dataToInsert = array();
            foreach ($newBarcodes as $newBarcode) {
                $dataToInsert[] = array(
                    'product_id' => intval($object->getId()),
                    'value' => $newBarcode,
                );
            }
            $write->insertMultiple($tableNameBarcodes, $dataToInsert);
        }
        
        // save category 's characteristics
        $tableNameCharacteristics = $this->getTable(
            'innobyte_emag_marketplace/product_cat_characteristic'
        );
        $select = $read->select()
            ->from($tableNameCharacteristics, array('characteristic_id', 'value'))
            ->where('product_id = ?', intval($object->getId()));
        $currentCharacteristics = $read->fetchPairs($select);

        $removeChar = array();
        $categoryCharacteristics = array();
        if (!is_null($object->getCategory())
            && is_array($object->getCategory()->getCharacteristics())) {
            $categoryCharacteristics = $object->getCategory()->getCharacteristics();
        }
        foreach ($currentCharacteristics as $charId => $charValue) {
            if (!array_key_exists($charId, $categoryCharacteristics)) {
                $removeChar[] = $charId;
            }
        }
        $updateChar = array();
        $insertChar = array();
        foreach ($categoryCharacteristics as $characteristic) {
            $key = 'category_characteristic' . $characteristic->getId();
            if ($object->hasData($key)
                && array_key_exists($characteristic->getId(), $currentCharacteristics)
                && $currentCharacteristics[$characteristic->getId()] != $object->getData($key)) {
                $updateChar[$characteristic->getId()] = $object->getData($key);
            } elseif ($object->hasData($key)
                && !array_key_exists($characteristic->getId(), $currentCharacteristics)) {
                $insertChar[$characteristic->getId()] = $object->getData($key);
            }
        }
        if (!empty($removeChar)) {
            $where = array();
            $where[] = $write->quoteInto('product_id = ?', intval($object->getId()));
            $where[] = $write->quoteInto('characteristic_id IN (?)', $removeChar);
            $write->delete($tableNameCharacteristics, $where);
        }
        if (!empty($insertChar)) {
            $dataToInsert = array();
            foreach ($insertChar as $charId => $charValue) {
                $dataToInsert[] = array(
                    'product_id' => intval($object->getId()),
                    'characteristic_id' => $charId,
                    'value' => $charValue,
                );
            }
            $write->insertMultiple($tableNameCharacteristics, $dataToInsert);
        }
        if (!empty($updateChar)) {
            foreach ($updateChar as $charId => $charValue) {
                $where = array();
                $where[] = $write->quoteInto('product_id = ?', intval($object->getId()));
                $where[] = $write->quoteInto('characteristic_id = ?', $charId);
                $write->update($tableNameCharacteristics, array('value' => $charValue), $where);
            }
        }
        
        return parent::_afterSave($object);
    }
    
    
    
    /**
     * @Override
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $read = $this->_getReadAdapter();
        
        // attach barcodes
        $tableNameBarcodes = $this->getTable(
            'innobyte_emag_marketplace/product_barcode'
        );
        $select = $read->select()
            ->from($tableNameBarcodes, array('value'))
            ->where('product_id = ?', intval($object->getId()));
        $object->setBarcodes($read->fetchCol($select));
        
        // attach characteristics
        $tableNameCharacteristics = $this->getTable(
            'innobyte_emag_marketplace/product_cat_characteristic'
        );
        $select = $read->select()
            ->from($tableNameCharacteristics, array('characteristic_id', 'value'))
            ->where('product_id = ?', intval($object->getId()));
        $currentCharacteristics = $read->fetchPairs($select);
        foreach ($currentCharacteristics as $charId => $charValue) {
            $object->setData('category_characteristic' . $charId, $charValue);
        }
        
        return parent::_afterLoad($object);
    }
    
    
    
    /**
     * Custom load method.
     *
     * @param Mage_Core_Model_Abstract $object
     * @param int $intProductId
     * @param int $intStoreId
     * @return Innobyte_EmagMarketplace_Model_Resource_Product
     */
    public function loadByProdIdAndStore(
        Mage_Core_Model_Abstract $object,
        $intProductId,
        $intStoreId
    )
    {
        $read = $this->_getReadAdapter();
        if ($read) {
            $fieldProd  = $read->quoteIdentifier(
                sprintf('%s.%s', $this->getMainTable(), 'product_id')
            );
            $fieldStore  = $read->quoteIdentifier(
                sprintf('%s.%s', $this->getMainTable(), 'store_id')
            );
            $select = $read->select()
                ->from($this->getMainTable())
                ->where($fieldProd . '=?', intval($intProductId))
                ->where($fieldStore . '=?', intval($intStoreId));
            $data = $read->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }
}
