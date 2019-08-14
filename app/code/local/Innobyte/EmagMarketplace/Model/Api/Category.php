<?php
/**
 * Handles category api related operations.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Api_Category
    extends Innobyte_EmagMarketplace_Model_Api_Abstract
{
    /**
     * Category resource name
     */
    const CATEGORY_RESOURCE_NAME = 'category';
        
    /**
     * @var Innobyte_EmagMarketplace_Model_Resource_Category_Collection
     */
    protected $_categoryCollection;
    
    /**
     * @var Innobyte_EmagMarketplace_Model_Resource_Category_Characteristic_Collection
     */
    protected $_categoryCharCollection;
    
    /**
     * @var Innobyte_EmagMarketplace_Model_Resource_Category_Familytype_Collection
     */
    protected $_categoryFtCollection;
    
    
    
    /**
     * Read CATEGORY resource
     *
     * @return Innobyte_EmagMarketplace_Model_Api_Response
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function read()
    {
        parent::read();
        return $this->_makeApiCall();
    }

    
    
    /**
     * Save CATEGORY resource
     *
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function save()
    {
        throw new Innobyte_EmagMarketplace_Exception('Not implemented');
    }

    
    
    /**
     * Count CATEGORY resource
     *
     * @return Innobyte_EmagMarketplace_Model_Api_Response
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function count()
    {
        parent::count();
        $apiResponse = $this->_makeApiCall();
        $this->_setPaginationInfo($apiResponse);
        return $apiResponse;
    }

    
    
    /**
     * Acknowledge CATEGORY resource
     *
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function acknowledge()
    {
        throw new Innobyte_EmagMarketplace_Exception('Not implemented');
    }

    
    
    /**
     * Get resource name
     *
     * @return string
     */
    public function getResourceName()
    {
        return self::CATEGORY_RESOURCE_NAME;
    }
    
    
    
    /**
     * Map eMAG category from api to magento model.
     *
     * @param array $category  Category from api.
     * @return Innobyte_EmagMarketplace_Model_Category
     */
    public function importCategory(array $category)
    {
        $this->_getCategoryCollection()->clear()
            ->getSelect()->reset(Zend_Db_Select::WHERE)
            ->limit(1);
        $emagCategoryId = isset($category['id']) ? intval($category['id']) : 0;
        $returnValue = $this->_getCategoryCollection()
            ->addStoreFilter($this->getStoreId())
            ->addFieldToFilter('emag_id', $emagCategoryId)
            ->getFirstItem();
        
        $returnValue->setStoreId($this->getStoreId())
            ->setEmagId($emagCategoryId);
        if (isset($category['name']) && strlen($category['name'])) {
            $returnValue->setName(strval($category['name']));
        }
        if (isset($category['characteristics'])
            && is_array($category['characteristics'])) {
            $returnValue->setImportedCharacteristics(
                $this->_importCharacteristics(
                    $category['characteristics'],
                    $returnValue->getId()
                )
            );
        }
        if (isset($category['family_types'])
            && is_array($category['family_types'])) {
            $returnValue->setImportedFamilyTypes(
                $this->_importFamilyTypes(
                    $category['family_types'],
                    $returnValue->getId()
                )
            );
        }
        return $returnValue;
    }
    
    
    
    /**
     * Import characteristics data for a category.
     *
     * @param array $characteristics
     * @param int $catId Magento eMAG category id.
     * @return array With Innobyte_EmagMarketplace_Model_Category_Characteristic
     *               models; can be empty.
     */
    protected function _importCharacteristics(array $characteristics, $catId)
    {
        $returnValue = array();
        if (!count($characteristics)) {
            return $returnValue;
        }
        foreach ($characteristics as $char) {
            $this->_getCategoryCharCollection()->clear()
                ->getSelect()->reset(Zend_Db_Select::WHERE)
                ->limit(1);
            $emagId = isset($char['id']) ? intval($char['id']) : 0;
            $model = $this->_getCategoryCharCollection()
                ->addFieldToFilter('category_id', intval($catId))
                ->addFieldToFilter('emag_id', $emagId)
                ->getFirstItem();
        
            $model->setEmagId($emagId);
            if (isset($char['name'])
                && strlen($char['name'])) {
                $model->setName(strval($char['name']));
            }
            if (isset($char['display_order'])
                && is_numeric($char['display_order'])) {
                $model->setDisplayOrder(intval($char['display_order']));
            }
            $returnValue[] = $model;
        }
        return $returnValue;
    }
    
    
    
    /**
     * Import familytypes data for a category.
     *
     * @param $category Innobyte_EmagMarketplace_Model_Category
     * @param array $familyTypes
     * @param int $catId  Magento eMAG category id.
     * @return array Contains Innobyte_EmagMarketplace_Model_Category_Familytype
     *               models; can be empty.
     */
    protected function _importFamilyTypes(array $familyTypes, $catId)
    {
        $returnValue = array();
        if (!count($familyTypes)) {
            return $returnValue;
        }
        foreach ($familyTypes as $familyType) {
            $this->_getCategoryFtCollection()->clear()
                ->getSelect()->reset(Zend_Db_Select::WHERE)
                ->limit(1);
            $emagId = isset($familyType['id']) ? intval($familyType['id']) : 0;
            $model = $this->_getCategoryFtCollection()
                ->addFieldToFilter('category_id', intval($catId))
                ->addFieldToFilter('emag_id', $emagId)
                ->getFirstItem();
        
            $model->setEmagId($emagId);
            if (isset($familyType['name']) && strlen($familyType['name'])) {
                $model->setName(strval($familyType['name']));
            }
            if (isset($familyType['characteristics'])
                && is_array($familyType['characteristics'])) {
                $model->setImportedCharacteristics(
                    $familyType['characteristics']
                );
            }
            $returnValue[] = $model;
        }
        return $returnValue;
    }
    
    
    
    /**
     * Getter method for category collection property.
     *
     * @return Innobyte_EmagMarketplace_Model_Resource_Category_Collection
     */
    protected function _getCategoryCollection()
    {
        if (is_null($this->_categoryCollection)) {
            $this->_categoryCollection = Mage::getResourceModel(
                'innobyte_emag_marketplace/category_collection'
            );
        }
        return $this->_categoryCollection;
    }
    
    
    
    /**
     * Getter method for category characteristics collection property.
     *
     * @return Innobyte_EmagMarketplace_Model_Resource_Category_Characteristic_Collection
     */
    protected function _getCategoryCharCollection()
    {
        if (is_null($this->_categoryCharCollection)) {
            $this->_categoryCharCollection = Mage::getResourceModel(
                'innobyte_emag_marketplace/category_characteristic_collection'
            );
        }
        return $this->_categoryCharCollection;
    }
    
    
    
    /**
     * Getter method for category familytype collection property.
     *
     * @return Innobyte_EmagMarketplace_Model_Resource_Category_Familytype_Collection
     */
    protected function _getCategoryFtCollection()
    {
        if (is_null($this->_categoryFtCollection)) {
            $this->_categoryFtCollection = Mage::getResourceModel(
                'innobyte_emag_marketplace/category_familytype_collection'
            );
        }
        return $this->_categoryFtCollection;
    }
}
