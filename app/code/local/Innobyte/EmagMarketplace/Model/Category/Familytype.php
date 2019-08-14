<?php
/**
 * eMAG category family-type model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Category_Familytype
    extends Mage_Core_Model_Abstract
{
    /**
     * @Override
     * @var string  Event prefix.
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_category_familytype';
    
    /**
     * @Override
     * @var string  Event object key.
     */
    protected $_eventObject = 'emag_category_familytype';
    
    /**
     * Imported family type characteristics from api response.
     *
     * @var array|null
     */
    protected $_importedCharacteristics;
    
    
    
    /**
     * @Override
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('innobyte_emag_marketplace/category_familytype');
    }
    
    
    
    /**
     * Getter method for imported ft characteristics property.
     *
     * @return array|null null if property is never set.
     */
    public function getImportedCharacteristics()
    {
        return $this->_importedCharacteristics;
    }
    
    
    
    /**
     * Setter method for imported ft characteristics property.
     *
     * @param array Array with api response characteristics for a family type.
     * @return Innobyte_EmagMarketplace_Model_Category_Familytype
     */
    public function setImportedCharacteristics(array $importedCharacteristics)
    {
        $this->_importedCharacteristics = $importedCharacteristics;
        return $this;
    }
    
    
    
    /**
     * Retrieve family type 's characteristics.
     *
     * @return array  Array of varien objects.
     */
    public function getCharacteristics()
    {
        if (!$this->hasCharacteristics() && $this->getId() > 0) {
            $this->setCharacteristics(
                $this->_getResource()->getCharacteristics($this)
            );
        }
        return $this->getData('characteristics');
    }
}
