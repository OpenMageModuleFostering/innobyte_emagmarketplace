<?php
/**
 * eMAG product resource collection model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Resource_Product_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Name prefix of events that are dispatched by model
     * 
     * @Override
     * @var string
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_product_collection';

    /**
     * Name of event parameter
     *
     * @Override
     * @var string
     */
    protected $_eventObject = 'emag_product_collection';
    
    
    
    /**
     * @Override
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('innobyte_emag_marketplace/product');
    }
    
    
    
    /**
     * @Override
     */
    protected function _afterLoad()
    {        
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableNameBarcodes = $this->getTable(
            'innobyte_emag_marketplace/product_barcode'
        );
        $selectBar = $read->select()->from($tableNameBarcodes, array('value'));
        $tableNameCharacteristics = $this->getTable(
            'innobyte_emag_marketplace/product_cat_characteristic'
        );
        $selectChar = $read->select()->from(
            $tableNameCharacteristics,
            array('characteristic_id', 'value')
        );
                
        foreach ($this->getItems() as $item) {
            $selectBar->reset(Zend_Db_Select::WHERE);
            $selectBar->where('product_id = ?', intval($item->getId()));
            $item->setBarcodes($read->fetchCol($selectBar));
            
            $selectChar->reset(Zend_Db_Select::WHERE);
            $selectChar->where('product_id = ?', intval($item->getId()));
            $currentCharacteristics = $read->fetchPairs($selectChar);
            foreach ($currentCharacteristics as $charId => $charValue) {
                $item->setData('category_characteristic' . $charId, $charValue);
            }
        }
        
        return parent::_afterLoad();
    }
}
