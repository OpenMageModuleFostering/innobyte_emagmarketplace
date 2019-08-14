<?php

/**
 * eMAG product model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Product extends Mage_Core_Model_Abstract
{
    /**
     * @Override
     * @var string  Event prefix.
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_product';

    /**
     * @Override
     * @var string  Event object key.
     */
    protected $_eventObject = 'emag_product';

    /**
     * Different eMAG image displaying types.
     */
    const IMAGE_DISPLAY_TYPE_MAIN = 1;
    const IMAGE_DISPLAY_TYPE_SECONDARY = 2;
    const IMAGE_DISPLAY_TYPE_OTHER = 0;

    /**
     * Different eMAG stock availabilities.
     */
    const AVAILABILITY_LIMITED_STOCK = 2;
    const AVAILABILITY_IN_STOCK = 3;
    const AVAILABILITY_OUT_OF_STOCK = 5;

    /**
     * Flags that indicates wheter product has been synced (sent to eMAG).
     */
    const IS_SYNCED = 1;
    const IS_NOT_SYNCED = 0;

    /**
     * Product status in order
     */
    const PRODUCT_STATUS_CANCELED = 0;
    const PRODUCT_STATUS_AVAILABLE = 1;

    /**
     * Related objects to save
     * @var array
     */
    protected $_relatedObjects;
    
    
    
    /**
     * @Override
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('innobyte_emag_marketplace/product');
        $this->_relatedObjects = array();
    }


    
    /**
     * Load by product id and store id.
     *
     * @param int $intProductId Mage catalog product id.
     * @param int $intStoreId Store scope id.
     * @return Innobyte_EmagMarketplace_Model_Product
     */
    public function loadByProdIdAndStore($intProductId, $intStoreId)
    {
        $this->_beforeLoad(
            array('product_id' => $intProductId, 'store_id' => $intStoreId),
            array('product_id', 'store_id')
        );
        $this->_getResource()->loadByProdIdAndStore(
            $this,
            $intProductId,
            $intStoreId
        );
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
    }

    

    /**
     * Retrieve commission value.
     *
     * @return mixed
     */
    public function getCommissionValue()
    {
        try {
            $commissionType = Innobyte_EmagMarketplace_Model_Source_CommissionTypes::getEmagCommissionType(
                $this->getCommissionType()
            );
            switch ($commissionType) {
                case Innobyte_EmagMarketplace_Model_Source_CommissionTypes::TYPE_PERCENTAGE:
                    return intval($this->getData('commission_value'));
                case Innobyte_EmagMarketplace_Model_Source_CommissionTypes::TYPE_ABSOLUTE:
                    return floatval($this->getData('commission_value'));
                default:
            }
        } catch (Innobyte_EmagMarketplace_Exception $ex) {
        }
        return $this->getData('commission_value');
    }


    
    /**
     * Setter method for category id.
     *
     * @param  int $categoryId
     * @return Innobyte_EmagMarketplace_Model_Product
     */
    public function setCategoryId($categoryId)
    {
        if ($this->getCategoryId() != $categoryId) {
            // reset magento category object too, make sure to set category id
            // through this method not using #setData()
            $this->unsetData('category');
        }
        $this->setData('category_id', intval($categoryId));
        return $this;
    }

    

    /**
     * Retrieve emag category model based on product 's category id.
     *
     * @return Innobyte_EmagMarketplace_Model_Category|null
     */
    public function getCategory()
    {
        if (!$this->hasCategory() && $this->getCategoryId() > 0) {
            $this->setCategory(
                Mage::getModel('innobyte_emag_marketplace/category')
                    ->load($this->getCategoryId())
            );
        }
        return $this->getData('category');
    }

    

    /**
     * Setter method for product id.
     *
     * @param  int $mageProductId Magento product id.
     * @return Innobyte_EmagMarketplace_Model_Product
     */
    public function setProductId($mageProductId)
    {
        if ($this->getProductId() != $mageProductId) {
            // reset magento product object too, make sure to set product id
            // through this method not using #setData()
            $this->unsetData('magento_product');
        }
        $this->setData('product_id', intval($mageProductId));
        return $this;
    }


    
    /**
     * Retrieve magento product model.
     *
     * @return Mage_Catalog_Model_Product|null
     */
    public function getMagentoProduct()
    {
        if (!$this->hasMagentoProduct() && $this->getProductId() > 0) {
            $this->setMagentoProduct(
                Mage::getModel('catalog/product')
                    ->setStoreId($this->getStoreId())
                    ->load($this->getProductId())
            );
        }
        return $this->getData('magento_product');
    }


    
    /**
     * Setter method for vat id.
     *
     * @param  int $vatId
     * @return Innobyte_EmagMarketplace_Model_Product
     */
    public function setVatId($vatId)
    {
        if ($this->getVatId() != $vatId) {
            // reset emat vat object too, make sure to set vat id
            // through this method not using #setData()
            $this->unsetData('vat');
        }
        $this->setData('vat_id', intval($vatId));
        return $this;
    }


    
    /**
     * Retrieve emag vat model.
     *
     * @return Innobyte_EmagMarketplace_Model_Category|null
     */
    public function getVat()
    {
        if (!$this->hasVat() && $this->getVatId() > 0) {
            $this->setVat(
                Mage::getModel('innobyte_emag_marketplace/vat')
                    ->load($this->getVatId())
            );
        }
        return $this->getData('vat');
    }


    /**
     * Setter method for family type id.
     *
     * @param  int $familyTypeId  Magento id for family type.
     * @return Innobyte_EmagMarketplace_Model_Product
     */
    public function setFamilyTypeId($familyTypeId)
    {
        if ($this->getFamilyTypeId() != $familyTypeId) {
            // reset magento family type object too, make sure to set
            // family id through this method not using #setData()
            $this->unsetData('category');
        }
        $this->setData('family_type_id', intval($familyTypeId));
        return $this;
    }

    

    /**
     * Retrieve emag family type model based on product 's family type id.
     *
     * @return Innobyte_EmagMarketplace_Model_Category_Familytype|null
     */
    public function getFamilyType()
    {
        if (!$this->hasFamilyType() && $this->getFamilyTypeId() > 0) {
            $this->setFamilyType(
                Mage::getModel('innobyte_emag_marketplace/category_familytype')
                    ->load($this->getFamilyTypeId())
            );
        }
        return $this->getData('family_type');
    }
    
    
    
    /**
     * Check if product has been synced
     * (sent whole documentation at least once).
     *
     * @return bool
     */
    public function isSynced()
    {
        return (bool) $this->getIsSynced();
    }
    
    
    
    /**
     * Setter method for synced.
     *
     * @param int     One of the constants
     *                Innobyte_EmagMarketplace_Model_Product::IS_SYNCED,
     *                Innobyte_EmagMarketplace_Model_Product::IS_NOT_SYNCED
     * @return Innobyte_EmagMarketplace_Model_Product
     */
    public function setIsSynced($synced)
    {
        // set synced flag for configurable child products also
        if (!is_null($this->getMagentoProduct())
            && $this->getMagentoProduct()->isConfigurable()) {
            $chidIds = $this->getMagentoProduct()->getTypeInstance()
                ->getUsedProductIds();
            foreach ($chidIds as $childId) {
                $childEmagProduct = Mage::getModel('innobyte_emag_marketplace/product')
                    ->loadByProdIdAndStore($childId, $this->getStoreId());
                if ($childEmagProduct->getId()) {
                    $childEmagProduct->setIsSynced($synced);
                    $this->addRelatedObject($childEmagProduct);
                }
            }
        }
        
        switch ($synced) {
            case Innobyte_EmagMarketplace_Model_Product::IS_SYNCED: // intentionally ommitted break
            case Innobyte_EmagMarketplace_Model_Product::IS_NOT_SYNCED;
                $this->setData('is_synced', $synced);
                break;
            default:
        }
        
        return $this;
    }
    
    
    
    /**
     * Setter method for status.
     *
     * @param int $status  One of the constants
     *              Innobyte_EmagMarketplace_Model_Source_OfferStatus::STATUS_*
     * @return Innobyte_EmagMarketplace_Model_Product
     */
    public function setStatus($status)
    {
        // set status for configurable child products also
        if (!is_null($this->getMagentoProduct())
            && $this->getMagentoProduct()->isConfigurable()) {
            $chidIds = $this->getMagentoProduct()->getTypeInstance()
                ->getUsedProductIds();
            foreach ($chidIds as $childId) {
                $childEmagProduct = Mage::getModel('innobyte_emag_marketplace/product')
                    ->loadByProdIdAndStore($childId, $this->getStoreId());
                if ($childEmagProduct->getId()) {
                    $childEmagProduct->setStatus($status);
                    $this->addRelatedObject($childEmagProduct);
                }
            }
        }
        
        switch ($status) {
            case Innobyte_EmagMarketplace_Model_Source_OfferStatus::STATUS_ACTIVE: // intentionally ommitted break
            case Innobyte_EmagMarketplace_Model_Source_OfferStatus::STATUS_INACTIVE;
                $this->setData('status', $status);
                break;
            default:
        }
        
        return $this;
    }
    
    
    
    /**
     * Retrieve array of related objects used for also saving them.
     *
     * @return array
     */
    public function getRelatedObjects()
    {
        return $this->_relatedObjects;
    }

    

    /**
     * Add new object to related array
     *
     * @param   Mage_Core_Model_Abstract $object
     * @return  Innobyte_EmagMarketplace_Model_Product
     */
    public function addRelatedObject(Mage_Core_Model_Abstract $object)
    {
        if (!is_null($object)) {
            $this->_relatedObjects[] = $object;
        }
        return $this;
    }
    
    
    
    /**
     * @Override
     */
    protected function _afterSave()
    {
        foreach ($this->getRelatedObjects() as $object) {
            $object->save();
        }
        return parent::_afterSave();
    }
}
