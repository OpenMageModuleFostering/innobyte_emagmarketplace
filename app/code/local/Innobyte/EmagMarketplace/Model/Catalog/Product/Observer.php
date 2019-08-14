<?php
/**
 * Observer that handles product related events.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Catalog_Product_Observer
{
    /**
     * Helper object.
     *
     * @var Innobyte_EmagMarketplace_Helper_Data
     */
    protected $_helper;
    
    
    
    /**
     * Constructor; initializes stuffs.
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('innobyte_emag_marketplace');
    }
    
    
    
    /**
     * Getter method for helper property.
     *
     * @return Innobyte_EmagMarketplace_Helper_Data
     */
    protected function _getHelper()
    {
        return $this->_helper;
    }
    
    
    
    /**
     * Saves product emag data.
     * Triggered on catalog_product_save_after event.
     *
     * @param  Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Catalog_Product_Observer
     * @throws Mage_Core_Exception
     */
    public function saveProductEmagData(Varien_Event_Observer $observer)
    {
        $storeId = $this->_getHelper()->getCurrStoreId();
        $product = Mage::getModel('catalog/product')->setStoreId($storeId)
            ->load($observer->getEvent()->getProduct()->getId());
        if (!$this->_getHelper()->isProductActionValid($product)
            || !Mage::app()->getRequest()->isPost()) {
            return $this;
        }
        
        $data = Mage::app()->getRequest()->getPost('product', array());
        if (!array_key_exists('inno_emag_mktp', $data)
            || !is_array($data['inno_emag_mktp'])) {
            return $this;
        }
        
        $emagData = $data['inno_emag_mktp'];
        $emagProduct = Mage::getModel('innobyte_emag_marketplace/product')
            ->loadByProdIdAndStore($product->getId(), $storeId);
        try {
            $this->_checkFilterEmagProductData($emagData, $product);
            $emagProduct->addData($emagData)
                ->setProductId($product->getId())
                ->setStoreId($storeId);
            // save data also on non-filled associated products if config prod
            if ($product->isConfigurable()) {
                $childIds = $product->getTypeInstance()->getUsedProductIds();
                foreach ($childIds as $childId) {
                    $childEmagProduct = Mage::getModel(
                        'innobyte_emag_marketplace/product'
                    )->loadByProdIdAndStore(
                        $childId,
                        $storeId
                    );
                    if (!$childEmagProduct->getId()) {
                        $childEmagProduct->addData($emagData)
                            ->unsFamilyTypeId()
                            ->setProductId($childId)
                            ->setStoreId($storeId);
                        $emagProduct->addRelatedObject($childEmagProduct);
                    }
                }
            }
            $emagProduct->save();
        } catch (Mage_Core_Exception $ex) {
            // set data in session to repopulate form if error occurred
            Mage::getSingleton('adminhtml/session')->setData(
                'inno_emag_mktp_data',
                $emagData
            );
            throw $ex;
        }
        
        return $this;
    }
    
    
    
    /**
     * Check / filter emag product form data.
     *
     * @param  array $emagData  Product post data.
     * @param  Mage_Catalog_Model_Product $product    Magento product.
     * @throws Mage_Core_Exception
     */
    protected function _checkFilterEmagProductData(
        &$emagData,
        Mage_Catalog_Model_Product $product
    )
    {
        $storeId = $this->_getHelper()->getCurrStoreId();
        
        if (is_null($product) || !$product->getId()) {
            Mage::throwException($this->_getHelper()->__('Invalid product id.'));
        }
        
        if (array_key_exists('part_number_key', $emagData)) {
            $emagData['part_number_key'] = trim($emagData['part_number_key']);
            if (empty($emagData['part_number_key'])) {
                $emagData['part_number_key'] = null;
            }
        } else {
            $emagData['part_number_key'] = null;
        }
        
        if (array_key_exists('name', $emagData)) {
            $emagData['name'] = trim($emagData['name']);
            if (empty($emagData['name'])) {
                $emagData['name'] = null;
            }
        } else {
            $emagData['name'] = null;
        }
        if (empty($emagData['name']) && !$emagData['part_number_key']) {
            Mage::throwException(
                $this->_getHelper()->__('Please fill eMAG product name.')
            );
        }
        
        if (array_key_exists('brand', $emagData)) {
            $emagData['brand'] = trim($emagData['brand']);
            if (empty($emagData['brand'])) {
                $emagData['brand'] = null;
            }
        } else {
            $emagData['brand'] = null;
        }
        if (empty($emagData['brand']) && !$emagData['part_number_key']) {
            Mage::throwException(
                $this->_getHelper()->__('Please fill eMAG product brand.')
            );
        }
        
        if (array_key_exists('description', $emagData)) {
            $emagData['description'] = trim($emagData['description']);
            if (empty($emagData['description'])) {
                $emagData['description'] = null;
            }
        } else {
            $emagData['description'] = null;
        }
        
        if (array_key_exists('barcodes', $emagData)) {
            if (is_array($emagData['barcodes'])) {
                $emagData['barcodes'] = array_filter(
                    array_unique(
                        array_map('trim', $emagData['barcodes'])
                    )
                );
            } else {
                $emagData['barcodes'] = array();
            }
        } else {
            $emagData['barcodes'] = array();
        }
        
        $categoryId = null;
        $emagCategory = Mage::getModel('innobyte_emag_marketplace/category');
        if (array_key_exists('category_id', $emagData)) {
            $emagCategory->load($emagData['category_id']);
            if ($emagCategory->getStoreId() == $storeId) {
                $categoryId = $emagCategory->getId();
            }
        }
        $emagData['category_id'] = $categoryId;
        if (empty($emagData['category_id'])) {
            if (!$emagData['part_number_key']) {
                Mage::throwException(
                    $this->_getHelper()->__('Please choose eMAG category.')
                );
            }
        } elseif (is_array($emagCategory->getCharacteristics())) {
            $skipChars = array();
            if ($product->isConfigurable()) {
                if (empty($emagData['family_type_id'])) {
                    Mage::throwException(
                        $this->_getHelper()->__('Please choose eMAG family type.')
                    );
                }
                $familyType = null;
                foreach ($emagCategory->getFamilyTypes() as $ftModel) {
                    if ($ftModel->getId() == $emagData['family_type_id']) {
                        $familyType = $ftModel;
                        break;
                    }
                }
                if (is_null($familyType)) {
                    Mage::throwException(
                        $this->_getHelper()->__('Invalid family type provided.')
                    );
                }
                foreach ($familyType->getCharacteristics() as $ftChar) {
                    $skipChars[] = $ftChar->getMageIdEmagChar();
                }
            }
            
            foreach ($emagCategory->getCharacteristics() as $characteristic) {
                $charKey = 'category_characteristic' . $characteristic->getId();
                if ((!array_key_exists($charKey, $emagData)
                    || !strlen(trim($emagData[$charKey])))
                    && !in_array($characteristic->getId(), $skipChars)) {
                    Mage::throwException(
                        $this->_getHelper()->__(
                            'No category characteristic "%s" value found.',
                            $this->_getHelper()->__($characteristic->getName())
                        )
                    );
                } elseif (!in_array($characteristic->getId(), $skipChars)) {
                    $emagData[$charKey] = trim($emagData[$charKey]);
                } else {
                    unset($emagData[$charKey]);
                }
            }
        }
        
        $validOfferStatuses = Mage::getSingleton('innobyte_emag_marketplace/source_offerStatus')
            ->toArray();
        if (!array_key_exists('status', $emagData)
            || !in_array($emagData['status'], $validOfferStatuses)) {
            Mage::throwException(
                $this->_getHelper()->__('Please choose eMAG offer status.')
            );
        }
        
        if (array_key_exists('warranty', $emagData)) {
            $emagData['warranty'] = (int) $emagData['warranty'];
            if ($emagData['warranty'] < 0 || $emagData['warranty'] > 255) {
                Mage::throwException(
                    $this->_getHelper()->__(
                        'Invalid eMAG warranty. Should be a real number equal to or greater than 0 and smaller than 255.'
                    )
                );
            }
            if (!$emagData['warranty']) {
                $emagData['warranty'] = null;
            }
        }
        
        if (!array_key_exists('commission_type', $emagData)
            || empty($emagData['commission_type'])) {
            Mage::throwException(
                $this->_getHelper()->__('Please choose eMAG commission type.')
            );
        }
        try {
            $commissionType = Innobyte_EmagMarketplace_Model_Source_CommissionTypes::getEmagCommissionType($emagData['commission_type']);
        } catch (Innobyte_EmagMarketplace_Exception $ex) {
            Mage::throwException($this->_getHelper()->__($ex->getMessage()));
        }
        
        if (!array_key_exists('commission_value', $emagData)) {
            Mage::throwException(
                $this->_getHelper()->__('Please fill eMAG commission value.')
            );
        }
        if (Innobyte_EmagMarketplace_Model_Source_CommissionTypes::TYPE_PERCENTAGE == $commissionType) {
            $emagData['commission_value'] = (int) $emagData['commission_value'];
            if ($emagData['commission_value'] < 0
                || $emagData['commission_value'] > 100) {
                Mage::throwException(
                    $this->_getHelper()->__(
                        'Invalid eMAG commission value. Should be a percentage between 0 and 100.'
                    )
                );
            }
        } else {
            $emagData['commission_value'] = (float) $emagData['commission_value'];
            if ($emagData['commission_value'] < 0) {
                Mage::throwException(
                    $this->_getHelper()->__(
                        'Invalid eMAG commission value. Should be a real number equal to or greater than 0.'
                    )
                );
            }
        }
        
        if (array_key_exists('handling_time', $emagData)) {
            $emagData['handling_time'] = (int) $emagData['handling_time'];
            if ($emagData['handling_time'] < 0
                || $emagData['handling_time'] > 255) {
                Mage::throwException(
                    $this->_getHelper()->__(
                        'Invalid eMAG handling time. Should be a real number equal to or greater than 0 and smaller than 255.'
                    )
                );
            }
            if (!$emagData['handling_time']) {
                $emagData['handling_time'] = null;
            }
        }
        
        $date = null;
        if (array_key_exists('start_date', $emagData)) {
            $filterInput = new Zend_Filter_LocalizedToNormalized(
                array(
                    'date_format' => Mage::app()->getLocale()
                        ->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
                )
            );
            $filterInternal = new Zend_Filter_NormalizedToLocalized(
                array('date_format' => Varien_Date::DATE_INTERNAL_FORMAT)
            );
            $date = $filterInput->filter($emagData['start_date']);
            $date = $filterInternal->filter($date);
            
            if ($date) {
                try {
                    $zendDate = new Zend_Date($date, Varien_Date::DATE_INTERNAL_FORMAT);
                } catch (Zend_Date_Exception $zdex) {
                    Mage::throwException(
                        $this->_getHelper()->__('Invalid eMAG offer start date.')
                    );
                }
                $now = new Zend_Date(null, Varien_Date::DATE_INTERNAL_FORMAT);
                if (!$zendDate->isLater($now)
                    || !$zendDate->isEarlier($now->addDay(61))) {
                    Mage::throwException(
                        $this->_getHelper()->__(
                            'Offer start date can be as far as 60 days in the future and cannot be earlier than tomorrow.'
                        )
                    );
                }
            }
        }
        if ($date) {
            $emagData['start_date'] = $date;
        } else {
            $emagData['start_date'] = null;
        }
        
        $vatId = null;
        if (array_key_exists('vat_id', $emagData)) {
            $vatId = (int) $emagData['vat_id'];
            $vats = Mage::getSingleton('innobyte_emag_marketplace/source_vat')
                ->getIdsArray($storeId);
            if (!in_array($vatId, $vats)) {
                $vatId = null;
            }
        }
        if (null === $vatId) { // calculate vat percent
            $taxClassId = $product->getTaxClassId();
            $request = Mage::getSingleton('tax/calculation')
                ->getRateRequest(false, false, false, Mage::app()->getStore($storeId));
            $percent = Mage::getSingleton('tax/calculation')
                ->getRate($request->setProductClassId($taxClassId));
            $percent = $percent / 100;
            $vats = Mage::getSingleton('innobyte_emag_marketplace/source_vat')
                ->toOptionArray($storeId, false);
            foreach ($vats as $vatRate) {
                if ($vatRate['label'] == $percent && $vatRate['value'] > 0) {
                    $vatId = $vatRate['value'];
                    break;
                }
            }
        }
        $emagData['vat_id'] = $vatId;
        if (empty($emagData['vat_id'])) {
            Mage::throwException(
                $this->_getHelper()->__('Please choose eMAG VAT.')
            );
        }
    }
    
    
    
    /**
     * Add eMAG related mass actions to products grid.
     * Triggered on adminhtml_catalog_product_grid_prepare_massaction event.
     *
     * @param  Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Catalog_Product_Observer
     * @throws Mage_Core_Exception
     */
    public function addEmagProductMassActions(Varien_Event_Observer $observer)
    {
        $gridBlock = $observer->getEvent()->getBlock();
        
        $storeId = $this->_getHelper()->getCurrStoreId();
        if (!($gridBlock instanceof Mage_Adminhtml_Block_Catalog_Product_Grid)
            || $storeId == Mage_Core_Model_App::ADMIN_STORE_ID
            || !$this->_getHelper()->isExtensionEnabled($storeId)) {
            return $this;
        }
        
        $gridBlock->getMassactionBlock()->addItem(
            'emag_send_product_mass',
            array(
                'label'=> $this->_getHelper()->__('Send eMAG product'),
                'url'  => $gridBlock->getUrl(
                    'adminhtml/emag_product/massSendProduct',
                    array('_current' => 1)
                ),
            )
        );
        $gridBlock->getMassactionBlock()->addItem(
            'emag_send_offer_mass',
            array(
                'label'=> $this->_getHelper()->__('Send eMAG offer'),
                'url'  => $gridBlock->getUrl(
                    'adminhtml/emag_product/massSendOffer',
                    array('_current' => 1)
                ),
            )
        );
        $gridBlock->getMassactionBlock()->addItem(
            'emag_deactivate_offer_mass',
            array(
                'label'=> $this->_getHelper()->__('Deactivate eMAG offer'),
                'url'  => $gridBlock->getUrl(
                    'adminhtml/emag_product/massDeactivateOffer',
                    array('_current' => 1)
                ),
            )
        );
        
        return $this;
    }
}
