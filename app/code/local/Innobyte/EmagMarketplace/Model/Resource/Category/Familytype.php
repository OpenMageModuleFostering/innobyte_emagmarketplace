<?php
/**
 * eMAG category family-type resource model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Resource_Category_Familytype
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * @Override
     */
    public function _construct()
    {
        $this->_init('innobyte_emag_marketplace/category_familytype', 'id');
    }
    
    
    
    /**
     * @Override
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if (is_array($object->getImportedCharacteristics())) {
            $write = $this->_getWriteAdapter();
            $read  = $this->_getReadAdapter();
        
            $tableName = $this->getTable(
                'innobyte_emag_marketplace/category_familytype_characteristic'
            );
            $select = $read->select()
                ->from(array('main_table' => $tableName), array('characteristic_id'))
                ->where('family_type_id = ?', intval($object->getId()));
            $oldCharEmagIds = $read->fetchCol($select);
            $newCharEmagIds = array();
            foreach ($object->getImportedCharacteristics() as $char) {
                $newCharEmagIds[] = intval($char['characteristic_id']);
            }
                        
            $charToDelete = array_diff($oldCharEmagIds, $newCharEmagIds);
            if (!empty($charToDelete)) {
                $where[] = $write->quoteInto(
                    'family_type_id = ?',
                    intval($object->getId())
                );
                $where[] = $write->quoteInto(
                    'characteristic_id IN (?)',
                    $charToDelete
                );
                $write->delete($tableName, $where);
            }
            
            foreach ($object->getImportedCharacteristics() as $char) {
                $char['family_type_id'] = intval($object->getId());
                $write->insertOnDuplicate(
                    $tableName,
                    $char,
                    array(
                        'characteristic_id',
                        'family_type_id',
                        'display_order',
                        'is_foldable',
                        'characteristic_family_type_id',
                    )
                );
            }
        }
        
        return parent::_afterSave($object);
    }
    
    
    
    /**
     * Retrieve a family type 's characteristics.
     *
     * @return array  Array for varien objects.
     */
    public function getCharacteristics(Mage_Core_Model_Abstract $object)
    {
        $read = $this->_getReadAdapter();
        $tableFtCharacteristics = $this->getTable(
            'innobyte_emag_marketplace/category_familytype_characteristic'
        );
        $tableCharacteristics = $this->getTable(
            'innobyte_emag_marketplace/category_characteristic'
        );
        $select = $read->select()
            ->from(array('ft_characteristics' => $tableFtCharacteristics), '*')
            ->join(
                array('characteristics' => $tableCharacteristics),
                'ft_characteristics.characteristic_id = characteristics.emag_id'
                . ' AND characteristics.category_id = '
                . intval($object->getCategoryId()),
                array('characteristics.id AS mage_id_emag_char')
            )
            ->where(
                'ft_characteristics.family_type_id = ?',
                intval($object->getId())
            );
        $characteristics = $read->fetchAll($select);
        $returnValue = array();
        foreach ($characteristics as $char) {
            $returnValue[] = new Varien_Object($char);
        }
        return $returnValue;
    }
}
