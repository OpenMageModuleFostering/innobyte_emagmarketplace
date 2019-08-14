<?php
/**
 * Handles product api related operations.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Api_Product
    extends Innobyte_EmagMarketplace_Model_Api_Abstract
{
    /**
     * Product resource name
     */
    const PRODUCT_RESOURCE_NAME = 'product_offer';
    
    /**
     * Default wharehouse id.
     */
    const DEFAULT_WHAREHOUSE_ID = 1;

    /**
     * eMAG product model.
     *
     * @var Innobyte_EmagMarketplace_Model_Product
     */
    protected $_emagProduct;
        
    /**
     * Flag that indicates that whole product data should be sent.
     *
     * @var Innobyte_EmagMarketplace_Model_Product
     */
    protected $_sendProduct = true;
        
    
    
    /**
     * Read PRODUCT resource
     *
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function read()
    {
        throw new Innobyte_EmagMarketplace_Exception('Not implemented');
    }

    
    
    /**
     * Save PRODUCT resource
     * Set eMAG product previous to call this method with #setEmagProduct()
     * Saves either whole product data or just offer data,
     * by default saves whole product data.
     * You can use #saveProduct() or #saveOffer() instead.
     *
     * @return Innobyte_EmagMarketplace_Model_Api_Response
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function save()
    {
        parent::save();
        if ($this->_sendProduct) {
            $this->setData($this->_getProductSavingData());
        } else {
            $this->setData($this->_getOfferSavingData());
        }
        return $this->_makeApiCall();
    }
    
    
    
    /**
     * Count PRODUCT resource
     *
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function count()
    {
        throw new Innobyte_EmagMarketplace_Exception('Not implemented');
    }

    
    
    /**
     * Acknowledge PRODUCT resource
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
        return self::PRODUCT_RESOURCE_NAME;
    }
    
    
    
    /**
     * Save whole product.
     *
     * @return Innobyte_EmagMarketplace_Model_Api_Response
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function saveProduct()
    {
       $this->_sendProduct = true;
       return $this->save();
    }
    
    
    
    /**
     * Save product offer.
     *
     * @return Innobyte_EmagMarketplace_Model_Api_Response
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function saveOffer()
    {
       $this->_sendProduct = false;
       return $this->save();
    }
    
    
    
    /**
     * Handle product saving.
     *
     * @return array
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function _getProductSavingData()
    {
        $returnValue = array();
        
        // for configurable send all child products
        $mageProduct = $this->getEmagProduct()->getMagentoProduct();
        if (!is_null($mageProduct) && $mageProduct->isConfigurable()) {
            $childIds = $mageProduct->getTypeInstance()->getUsedProductIds();
            if (empty($childIds)) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'No associated product(s) found.'
                );
            }
            foreach ($childIds as $childId) {
                $childEmagProduct = Mage::getModel(
                    'innobyte_emag_marketplace/product'
                )->loadByProdIdAndStore(
                    $childId,
                    $this->getEmagProduct()->getStoreId()
                );                
                try {
                    if ($childEmagProduct->getId()
                        && $childEmagProduct->getCategoryId() != $this->getEmagProduct()->getCategoryId()) {
                        throw new Innobyte_EmagMarketplace_Exception(
                            'Associated product \'s category does not match parent configurable product \'s category'
                        );
                    }
                    $returnValue[] = $this->_computeApiProductData(
                        $childEmagProduct,
                        $this->getEmagProduct()
                    );
                } catch (Innobyte_EmagMarketplace_Exception $iemEx) {
                    throw new Innobyte_EmagMarketplace_Exception(
                        Mage::helper('innobyte_emag_marketplace')->__(
                            'Associated product #%d error: ',
                            $childId
                        )
                        . Mage::helper('innobyte_emag_marketplace')->__(
                            $iemEx->getMessage()
                        )
                    );
                }
            }
        } else {
            $returnValue[] = $this->_computeApiProductData(
                $this->getEmagProduct()
            );
        }
        return $returnValue;
    }
    
    
    /**
     * Handle offer saving.
     * 
     * @return array
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function _getOfferSavingData()
    {
        $returnValue = array();
        
        // for configurable send all child products
        $mageProduct = $this->getEmagProduct()->getMagentoProduct();
        if (!is_null($mageProduct) && $mageProduct->isConfigurable()) {
            $childIds = $mageProduct->getTypeInstance()->getUsedProductIds();
            if (empty($childIds)) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'No associated product(s) found.'
                );
            }
            foreach ($childIds as $childId) {
                $childEmagProduct = Mage::getModel(
                    'innobyte_emag_marketplace/product'
                )->loadByProdIdAndStore(
                    $childId,
                    $this->getEmagProduct()->getStoreId()
                );
                try {
                    if ($childEmagProduct->getId()
                        && $childEmagProduct->getCategoryId() != $this->getEmagProduct()->getCategoryId()) {
                        throw new Innobyte_EmagMarketplace_Exception(
                            'Associated product \'s category does not match parent configurable product \'s category'
                        );
                    }
                    
                    $returnValue[] = $this->_computeApiOfferData(
                        $childEmagProduct
                    );
                } catch (Innobyte_EmagMarketplace_Exception $iemEx) {
                    throw new Innobyte_EmagMarketplace_Exception(
                        Mage::helper('innobyte_emag_marketplace')->__(
                            'Associated product #%d error: ',
                            $childId
                        )
                        . Mage::helper('innobyte_emag_marketplace')->__(
                            $iemEx->getMessage()
                        )
                    );
                }
            }
        } else {
            $returnValue[] = $this->_computeApiOfferData(
                $this->getEmagProduct()
            );
        }
        return $returnValue;
    }
    
    
    
    /**
     * Compute api data to be sent to api (product description & offer).
     *
     * @param Innobyte_EmagMarketplace_Model_Product $emagProduct
     * @param Innobyte_EmagMarketplace_Model_Product $parentProduct
     * @return array Array to be sent to eMAG with product desc & offer data.
     * @throws Innobyte_EmagMarketplace_Exception  If invalid values are found.
     */
    protected function _computeApiProductData(
        Innobyte_EmagMarketplace_Model_Product $emagProduct,
        Innobyte_EmagMarketplace_Model_Product $parentProduct = null
    )
    {
        if (!$emagProduct->getId()) {
            if ($emagProduct->getProductId() && $emagProduct->getStoreId()) {
                $emagProduct->loadByProdIdAndStore(
                    $emagProduct->getProductId(),
                    $emagProduct->getStoreId()
                );
            }
            if (!$emagProduct->getId()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'No product eMAG data set.'
                );
            }
        }
        
        $mageProduct = $emagProduct->getMagentoProduct();
        $returnValue = array(
            'id' => (int) $mageProduct->getId(),
        );
        if ($emagProduct->getPartNumberKey()) {
            $returnValue['part_number_key'] = $emagProduct->getPartNumberKey();
        } else {
            if (!$emagProduct->getName()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'No eMAG product name.'
                );
            }
            $returnValue['name'] = $emagProduct->getName();
            if (!$emagProduct->getBrand()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'No eMAG product brand.'
                );
            }
            $returnValue['brand'] = $emagProduct->getBrand();
            if (!$emagProduct->getCategoryId()
                || !$emagProduct->getCategory()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'No eMAG category id.'
                );
            }
            $returnValue['category_id'] = $emagProduct->getCategory()->getEmagId();
            if (!$mageProduct->getSku()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'No product sku.'
                );
            }
            $characteristics = array();
            foreach ($emagProduct->getCategory()->getCharacteristics() as $char) {
                $key = 'category_characteristic' . $char->getId();
                if (!array_key_exists($key, $emagProduct->getData())
                    || !strlen($emagProduct->getData($key))) {
                    throw new Innobyte_EmagMarketplace_Exception(
                        Mage::helper('innobyte_emag_marketplace')->__(
                            'No category characteristic "%s" value found.',
                            $char->getName()
                        )
                    );
                }
                $characteristics[] = array(
                    'id' => $char->getEmagId(),
                    'value' => strval($emagProduct->getData($key)),
                );
            }
            $returnValue['characteristics'] = $characteristics;
                        
            if (!is_null($parentProduct)) {
                if ($parentProduct->getFamilyTypeId() < 1
                    || $parentProduct->getFamilyType()->getEmagId() < 1) {
                    throw new Innobyte_EmagMarketplace_Exception(
                        'No family type found.'
                    );
                }
                $returnValue['family'] = array(
                    'id' => (int) $parentProduct->getId(),
                    'name' => $parentProduct->getName(),
                    'family_type_id' => (int) $parentProduct->getFamilyType()
                        ->getEmagId()
                );
            }
                        
            $returnValue['part_number'] = $mageProduct->getSku();
        }
        if (!$mageProduct->getProductUrl()) {
            throw new Innobyte_EmagMarketplace_Exception(
                'No product url.'
            );
        }
        $returnValue['url'] = $mageProduct->getProductUrl();
        if ($mageProduct->getWeight()) {
            $returnValue['weight'] = (float) $mageProduct->getWeight();
        }
        if ($emagProduct->getDescription()) {
            $returnValue['description'] = $emagProduct->getDescription();
        }
        if (is_array($emagProduct->getBarcodes())
            && count($emagProduct->getBarcodes())) {
            $returnValue['barcode'] = $emagProduct->getBarcodes();
        }
        
        if (isset($_SERVER['INNO_EMAG_MKTP_LOCAL'])) {
            // local / not public server => use hardcoded image
            $images = array(
                array(
                    'display_type' => Innobyte_EmagMarketplace_Model_Product::IMAGE_DISPLAY_TYPE_MAIN,
                    'url' => 'http://s1emagst.akamaized.net/layout/ro/images/logo//19/28252.png',
                )
            );
        } else {
            $images = array();
            $imageHelper = Mage::helper('catalog/image');
            if ($mageProduct->getImage()
                && $mageProduct->getImage() != 'no_selection')
            {
                $images[] = array(
                    'display_type' => Innobyte_EmagMarketplace_Model_Product::IMAGE_DISPLAY_TYPE_MAIN,
                    'url' => (string) $imageHelper->init($mageProduct, 'image'),
                );
            }
            if (count($mageProduct->getMediaGalleryImages())) {
                foreach ($mageProduct->getMediaGalleryImages() as $image) {
                    $images[] = array(
                        'display_type' => Innobyte_EmagMarketplace_Model_Product::IMAGE_DISPLAY_TYPE_SECONDARY,
                        'url' => (string) $imageHelper->init(
                            $mageProduct,
                            'image',
                            $image->getFile()
                        ),
                    );
                }
            }
        }        
        
        if (count($images)) {
            $returnValue['images'] = $images;
        }
        
        $returnValue = array_merge(
            $returnValue,
            $this->_computeApiOfferData($emagProduct)
        );
        
        // dispatch event in case customizations needs to be done by clients
        $returnValueObj = new Varien_Object($returnValue);
        Mage::dispatchEvent(
            'innobyte_emag_marketplace_compute_api_product_data',
            array(
                'emag_product' => $emagProduct,
                'parent_product' => $parentProduct,
                'offer_data' => $returnValueObj,
            )
        );
        
        return $returnValueObj->toArray();
    }
    
    
    
    /**
     * Compute offer api data to be sent to api.
     *
     * @param Innobyte_EmagMarketplace_Model_Product $emagProduct
     * @return array Array to be sent to eMAG with product offer data.
     * @throws Innobyte_EmagMarketplace_Exception  If invalid values are found.
     */
    protected function _computeApiOfferData(
        Innobyte_EmagMarketplace_Model_Product $emagProduct
    )
    {
        if (!$emagProduct->getId()) {
            if ($emagProduct->getProductId() && $emagProduct->getStoreId()) {
                $emagProduct->loadByProdIdAndStore(
                    $emagProduct->getProductId(),
                    $emagProduct->getStoreId()
                );
            }
            if (!$emagProduct->getId()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'No product eMAG data set.'
                );
            }
        }
        $mageProduct = $emagProduct->getMagentoProduct();
        $commissionType = Innobyte_EmagMarketplace_Model_Source_CommissionTypes::getEmagCommissionType(
            $emagProduct->getCommissionType()
        );
        if (!$commissionType) {
            throw new Innobyte_EmagMarketplace_Exception(
                'No eMAG product commission type.'
            );
        }
        if (!$mageProduct->getStockItem()) {
            throw new Innobyte_EmagMarketplace_Exception(
                'No product stock data.'
            );
        }
        $returnValue = array(
            'id' => intval($mageProduct->getId()),
            'status' => intval($emagProduct->getStatus()),
            'vat_id' => intval($emagProduct->getVat()->getEmagId()),
            'handling_time' => array(
                array(
                    'warehouse_id' => self::DEFAULT_WHAREHOUSE_ID,
                    'value' => intval($emagProduct->getHandlingTime()),
                ),
            ),
            'commission' => array(
                array(
                    'type' => $commissionType,
                    'value' => $emagProduct->getCommissionValue(),
                ),
            ),
            'warranty' => intval($emagProduct->getWarranty()),
        );
        
        $helper = Mage::helper('innobyte_emag_marketplace');
        
        // attach availability info
        $returnValue['availability'] = array(
            array('warehouse_id' => self::DEFAULT_WHAREHOUSE_ID)
        );
        $stockQty = $mageProduct->getStockItem()->getQty() * 1;
        if ($mageProduct->isAvailable()) {
            $returnValue['availability'][0]['id'] = Innobyte_EmagMarketplace_Model_Product::AVAILABILITY_IN_STOCK;
            if ($helper->isLimitedStockEnabled($emagProduct->getStoreId())
                && $stockQty <= $helper->getLimitedStockLimit($emagProduct->getStoreId())) {
                $returnValue['availability'][0]['id'] = Innobyte_EmagMarketplace_Model_Product::AVAILABILITY_LIMITED_STOCK;
            }
        } else {
            $returnValue['availability'][0]['id'] = Innobyte_EmagMarketplace_Model_Product::AVAILABILITY_OUT_OF_STOCK;
        }
        
        // attach stock info
        if ($helper->sendStockQty($emagProduct->getStoreId())
            && $stockQty >= 0 && $stockQty < 65535) {
            $returnValue['stock'] = array(
                array(
                    'warehouse_id' => self::DEFAULT_WHAREHOUSE_ID,
                    'value' => $stockQty,
                ),
            );
        }
        
        // attach offer start date
        if ($emagProduct->getStartDate()) {
            try {
                $zendDate = new Zend_Date(
                    $emagProduct->getStartDate(),
                    Varien_Date::DATE_INTERNAL_FORMAT
                );
            } catch (Zend_Date_Exception $zdex) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Invalid eMAG offer start date.'
                );
            }
            $now = new Zend_Date(null, Varien_Date::DATE_INTERNAL_FORMAT);
            if (!$zendDate->isLater($now)
                || !$zendDate->isEarlier($now->addDay(61))) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Offer start date can be as far as 60 days in the future and cannot be earlier than tomorrow.'
                );
            }
            $returnValue['start_date'] = $zendDate->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        }
        
        // attach prices
        $taxHelper = Mage::helper('tax');
        $price = $taxHelper->getPrice(
            $mageProduct,
            $mageProduct->getPrice(),
            false
        );
        $finalPrice = $taxHelper->getPrice(
            $mageProduct,
            $mageProduct->getFinalPrice(),
            false
        );
        $store = $mageProduct->getStore();
        $price = $store->roundPrice($store->convertPrice($price, 0, 0));
        $finalPrice = $store->roundPrice($store->convertPrice($finalPrice, 0, 0));
        if ($finalPrice < $price) {
            $returnValue['sale_price'] = $finalPrice;
            $returnValue['recommended_price'] = $price;
        } else {
            $returnValue['sale_price'] = $finalPrice;
        }
        
        // dispatch event in case customizations needs to be done by clients
        $returnValueObj = new Varien_Object($returnValue);
        Mage::dispatchEvent(
            'innobyte_emag_marketplace_compute_api_offer_data',
            array(
                'emag_product' => $emagProduct,
                'offer_data' => $returnValueObj,
            )
        );
        
        return $returnValueObj->toArray();
    }
    
    
    
    /**
     * Setter method for emag product.
     *
     * @param Innobyte_EmagMarketplace_Model_Product $product
     * @return Innobyte_EmagMarketplace_Model_Api_Product
     * @throws Innobyte_EmagMarketplace_Exception  If null param is provided.
     */
    public function setEmagProduct(Innobyte_EmagMarketplace_Model_Product $product)
    {
        if (is_null($product)) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Invalid product model.'
            );
        }
        $this->_emagProduct = $product;
        return $this;
    }
    
    
    
    /**
     * Getter method for emag product.
     *
     * @return Innobyte_EmagMarketplace_Model_Product
     */
    public function getEmagProduct()
    {
        if (is_null($this->_emagProduct)) {
            $model = Mage::getModel('innobyte_emag_marketplace/product');
            $this->setEmagProduct($model);
        }
        return $this->_emagProduct;
    }
}
