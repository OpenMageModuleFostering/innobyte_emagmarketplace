<?php
/**
 * eMAG category model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Category extends Mage_Core_Model_Abstract
{
    /**
     * @Override
     * @var string  Event prefix.
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_category';
    
    /**
     * @Override
     * @var string  Event object key.
     */
    protected $_eventObject = 'emag_category';
    
    /**
     * Imported characteristics from api response.
     *
     * @var array|null
     */
    protected $_importedCharacteristics;
    
    /**
     * Imported family types from api response.
     *
     * @var array|null
     */
    protected $_importedFamilyTypes;
    
    
    
    /**
     * @Override
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('innobyte_emag_marketplace/category');
    }
    
    
    
    /**
     * Getter method for imported characteristics property.
     *
     * @return array|null null if property is never set.
     */
    public function getImportedCharacteristics()
    {
        return $this->_importedCharacteristics;
    }
    
    
    
    /**
     * Getter method for imported family types property.
     *
     * @return array|null null if property is never set.
     */
    public function getImportedFamilyTypes()
    {
        return $this->_importedFamilyTypes;
    }
    
    
    
    /**
     * Setter method for imported characteristics property.
     *
     * @param array With Innobyte_EmagMarketplace_Model_Category_Characteristic
     *              models
     * @return Innobyte_EmagMarketplace_Model_Category
     */
    public function setImportedCharacteristics(array $importedCharacteristics)
    {
        $this->_importedCharacteristics = $importedCharacteristics;
        return $this;
    }
    
    
    
    /**
     * Setter method for imported family types property.
     *
     * @param @param array With
     *                     Innobyte_EmagMarketplace_Model_Category_Familytype
     *                     models
     * @return Innobyte_EmagMarketplace_Model_Category
     */
    public function setImportedFamilyTypes(array $importedFamilyTypes)
    {
        $this->_importedFamilyTypes = $importedFamilyTypes;
        return $this;
    }
    
    
    
    /**
     * Retrieve category 's characteristics.
     *
     * @return array|null null if module not loaded
     */
    public function getCharacteristics()
    {
        if (!$this->hasCharacteristics() && $this->getId() > 0) {
            $collection = Mage::getResourceModel(
                'innobyte_emag_marketplace/category_characteristic_collection'
            )->addFieldToFilter('category_id', array('eq' => $this->getId()))
                ->setOrder('display_order', Varien_Data_Collection::SORT_ORDER_ASC);
            $characteristics = array();
            foreach ($collection as $characteristic) {
                $characteristics[$characteristic->getId()] = $characteristic;
            }
            $this->setCharacteristics($characteristics);
        }
        return $this->getData('characteristics');
    }
    
    
    
    /**
     * Retrieve category 's family types.
     *
     * @return array|null null if module not loaded
     */
    public function getFamilyTypes()
    {
        if (!$this->hasFamilyTypes() && $this->getId() > 0) {
            $collection = Mage::getResourceModel(
                'innobyte_emag_marketplace/category_familytype_collection'
            )->addFieldToFilter('category_id', array('eq' => $this->getId()));
            $familyTypes = array();
            foreach ($collection as $familyType) {
                $familyTypes[$familyType->getId()] = $familyType;
            }
            $this->setFamilyTypes($familyTypes);
        }
        return $this->getData('family_types');
    }
}
