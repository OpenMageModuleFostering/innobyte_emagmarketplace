<?php
/**
 * eMAG product controller.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Adminhtml_Emag_ProductController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check ACL.
     *
     * @Override
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('catalog/products');
    }
    
    
    
    /**
     * Display tab 's content.
     */
    public function indexAction()
    {
        $storeId = $this->getRequest()->getParam('store');
        $prodId = $this->getRequest()->getParam('id');
        $mageProduct = Mage::getModel('catalog/product')
            ->setStoreId($storeId)
            ->load($prodId);
        
        $emagProduct = Mage::getModel('innobyte_emag_marketplace/product')
            ->loadByProdIdAndStore($prodId, $storeId);
        $session = Mage::getSingleton('adminhtml/session');
        if (is_array($session->getData('inno_emag_mktp_data'))) {
            $emagProduct->addData($session->getData('inno_emag_mktp_data'));
            $session->unsetData('inno_emag_mktp_data');
        }
        Mage::register('current_emag_product', $emagProduct);
        
        if (!Mage::registry('current_product')) {
            Mage::register('current_product', $mageProduct);
        }
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setBody(
                $this->getLayout()
                    ->createBlock(
                        'innobyte_emag_marketplace/adminhtml_catalog_product_edit_tab_emagMarketplace'
                    )
                    ->toHtml()
            );
        }
    }
    
    
    
    /**
     * Display tab 's content.
     */
    public function getCategoryDataAction()
    {
        $response = array(
            'status' => 'error',
            'message' => '',
            'results' => array(),
        );

        $helper = Mage::helper('innobyte_emag_marketplace');
        try {
            $categoryId = (int) $this->getRequest()->getParam('category_id');
            $getFamilyTypes = (bool) $this->getRequest()->getParam('get_family_types', 0);
            if (!$categoryId) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'No category provided.'
                );
            }
            $category = Mage::getModel('innobyte_emag_marketplace/category')
                ->load($categoryId);
            if ($category->getStoreId() != $helper->getCurrStoreId()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Invalid category provided.'
                );
            }
            $characteristics = is_array($category->getCharacteristics()) ?
                $category->getCharacteristics() :
                array();
            $response['results']['characteristics'] = array();
            foreach ($characteristics as $characteristic) {
                $response['results']['characteristics'][$characteristic->getId()] = $helper->escapeHtml(
                    $helper->__($characteristic->getName())
                );
            }
            if ($getFamilyTypes && is_array($category->getFamilyTypes())) {
                $familyTypes = array();
                $familyTypesCharacteristics = array();
                foreach ($category->getFamilyTypes() as $fType) {
                    $familyTypes[$fType->getId()] = $helper->escapeHtml(
                        $helper->__($fType->getName())
                    );
                    $ftCharacteristics = array();
                    foreach ($fType->getCharacteristics() as $ftChar) {
                        $ftCharacteristics[] = $ftChar->getMageIdEmagChar();
                    }
                    $familyTypesCharacteristics[$fType->getId()] = $ftCharacteristics;
                }
                $response['results']['family_types'] = array(
                    'items' => $familyTypes,
                    'characteristics' => $familyTypesCharacteristics,
                );
            }
            $response['status'] = 'success';
        } catch (Innobyte_EmagMarketplace_Exception $iemEx) {
            $response['message'] = $helper->__($iemEx->getMessage());
        } catch (Exception $ex) {
            Mage::logException($ex);
            $response['message'] = $helper->__(
                'An error occurred. Please try again later.'
            );
        }

        $this->getResponse()
            ->setHeader(
                'Content-Type',
                'application/json; charset=utf-8'
            )
            ->setBody(Mage::helper('core')->jsonEncode($response));
    }
    
    
    
    /**
     * Send whole/offer product.
     */
    public function sendAction()
    {
        $productId = (int) $this->getRequest()->getParam('id', 0);
        $sendType = $this->getRequest()->getParam('send', 'product');
        $helper = Mage::helper('innobyte_emag_marketplace');
        $emagProduct = Mage::getModel('innobyte_emag_marketplace/product')
            ->loadByProdIdAndStore($productId, $helper->getCurrStoreId());
        
        if (!$emagProduct->getId()
            || !$helper->isProductActionValid($emagProduct->getMagentoProduct())
            || !$helper->canMakeApiCall($emagProduct->getStoreId())) {
            return $this->_redirect(
                'adminhtml/catalog_product/edit',
                array('_current' => 1)
            );
        }
        try {
            $apiProd = Mage::getModel('innobyte_emag_marketplace/api_product')
                ->setStoreId($emagProduct->getStoreId())
                ->setEmagProduct($emagProduct);
            if ('product' === $sendType) {
                $apiResp = $apiProd->saveProduct();
            } else {
                $apiResp = $apiProd->saveOffer();
            }
            if ($apiResp->isError()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    implode('<br />', $apiResp->getMessages())
                );
            }
            if ('product' === $sendType && !$emagProduct->isSynced()) {
                $emagProduct
                    ->setIsSynced(Innobyte_EmagMarketplace_Model_Product::IS_SYNCED)
                    ->save();

                Mage::getResourceSingleton('catalog/product_action')->updateAttributes(
                    array($productId),
                    array('emag_is_synced' => Innobyte_EmagMarketplace_Model_Product::IS_SYNCED),
                    $emagProduct->getStoreId()
                );
            }
            if (!count($apiResp->getMessages())) { // check for warnings
                if ('product' === $sendType) {
                    $this->_getSession()->addSuccess(
                        $helper->__('Successfully sent product to eMAG.')
                    );
                } else {
                    $this->_getSession()->addSuccess(
                        $helper->__('Successfully sent offer to eMAG.')
                    );
                }
            } else {
                $this->_getSession()->addNotice(
                    implode('<br />', $apiResp->getMessages())
                );
            }
        } catch (Innobyte_EmagMarketplace_Exception $iemex) {
            $this->_getSession()->addError($helper->__($iemex->getMessage()));
        } catch (Exception $ex) {
            Mage::logException($ex);
            $this->_getSession()->addError(
                $helper->__('An error occurred. Please try again later.')
            );
        }
        return $this->_redirect(
            'adminhtml/catalog_product/edit',
            array('_current' => 1)
        );
    }
    
    
    
    /**
     * Deactivate offer.
     */
    public function deactivateOfferAction()
    {
        $productId = (int) $this->getRequest()->getParam('id', 0);
        $helper = Mage::helper('innobyte_emag_marketplace');
        $emagProduct = Mage::getModel('innobyte_emag_marketplace/product')
            ->loadByProdIdAndStore($productId, $helper->getCurrStoreId());
        
        if (!$emagProduct->getId()
            || !$helper->isProductActionValid($emagProduct->getMagentoProduct())
            || !$helper->canMakeApiCall($emagProduct->getStoreId())) {
            return $this->_redirect(
                'adminhtml/catalog_product/edit',
                array('_current' => 1)
            );
        }
        try {
            $emagProduct
                ->setStatus(Innobyte_EmagMarketplace_Model_Source_OfferStatus::STATUS_INACTIVE);
            $apiResp = Mage::getModel('innobyte_emag_marketplace/api_product')
                ->setStoreId($emagProduct->getStoreId())
                ->setEmagProduct($emagProduct)
                ->saveOffer();
            if ($apiResp->isError()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    implode('<br />', $apiResp->getMessages())
                );
            }
            $emagProduct->save();
            if (!count($apiResp->getMessages())) { // check for warnings
                $this->_getSession()->addSuccess(
                    $helper->__('Successfully deactivated offer.')
                );
            } else {
                $this->_getSession()->addNotice(
                    implode('<br />', $apiResp->getMessages())
                );
            }
        } catch (Innobyte_EmagMarketplace_Exception $iemex) {
            $this->_getSession()->addError($helper->__($iemex->getMessage()));
        } catch (Exception $ex) {
            Mage::logException($ex);
            $this->_getSession()->addError(
                $helper->__('An error occurred. Please try again later.')
            );
        }
        return $this->_redirect(
            'adminhtml/catalog_product/edit',
            array('_current' => 1)
        );
    }
    
    
    
    /**
     * Send whole product (mass).
     */
    public function massSendProductAction()
    {
        $prodIds = $this->getRequest()->getParam('product', array());
        $prodIds = array_filter(array_unique(array_map('intval', $prodIds)));
        $helper = Mage::helper('innobyte_emag_marketplace');
        $storeId = $helper->getCurrStoreId();
        $invalidIds = array();
        $successfullySentIds = array();
        try {
            if (empty($prodIds)) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Please select some products.'
                );
            }
            if (!$helper->canMakeApiCall($storeId)) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Please configure extension from System -> '
                    . 'Configuration -> eMAG Marketplace'
                );
            }
            foreach ($prodIds as $prodId) {
                $emagProd = Mage::getModel('innobyte_emag_marketplace/product')
                    ->loadByProdIdAndStore($prodId, $storeId);
                $isProdValid = $helper->isProductActionValid(
                    $emagProd->getMagentoProduct()
                );
                if (!$emagProd->getId() || !$isProdValid) {
                    $invalidIds[] = $prodId;
                    continue;
                }
                $apiResp = Mage::getModel('innobyte_emag_marketplace/api_product')
                    ->setStoreId($emagProd->getStoreId())
                    ->setEmagProduct($emagProd)
                    ->saveProduct();
                if ($apiResp->isError()) {
                    $this->_getSession()->addError(
                        implode('<br />', $apiResp->getMessages())
                    );
                    continue;
                } elseif (count($apiResp->getMessages())) {
                    $this->_getSession()->addNotice(
                        implode('<br />', $apiResp->getMessages())
                    );
                }
                if (!$emagProd->isSynced()) {
                    $emagProd
                        ->setIsSynced(Innobyte_EmagMarketplace_Model_Product::IS_SYNCED)
                        ->save();

                    Mage::getResourceSingleton('catalog/product_action')->updateAttributes(
                        array($prodId),
                        array('emag_is_synced' => Innobyte_EmagMarketplace_Model_Product::IS_SYNCED),
                        $storeId
                    );
                }
                $successfullySentIds[] = $prodId;
            }
        } catch (Innobyte_EmagMarketplace_Exception $iemex) {
            $this->_getSession()->addError($helper->__($iemex->getMessage()));
        } catch (Exception $ex) {
            Mage::logException($ex);
            $this->_getSession()->addError(
                $helper->__('An error occurred. Please try again later.')
            );
        }
        
        if (!empty($invalidIds)) {
            $this->_getSession()->addNotice(
                $helper->__(
                    'Action cannot be applied for product(s): %s',
                    implode(', ', $invalidIds)
                )
            );
        }
        if (!empty($successfullySentIds)) {
            $this->_getSession()->addSuccess(
                $helper->__(
                    'Successfully sent to eMAG the product(s): %s',
                    implode(', ', $successfullySentIds)
                )
            );
        }
        
        return $this->_redirect(
            'adminhtml/catalog_product/',
            array('store' => $storeId)
        );
    }
    
    
        
    /**
     * Send products offers (mass).
     */
    public function massSendOfferAction()
    {
        $prodIds = $this->getRequest()->getParam('product', array());
        $prodIds = array_filter(array_unique(array_map('intval', $prodIds)));
        $helper = Mage::helper('innobyte_emag_marketplace');
        $storeId = $helper->getCurrStoreId();
        $invalidIds = array();
        $successfullySentIds = array();
        try {
            if (empty($prodIds)) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Please select some products.'
                );
            }
            if (!$helper->canMakeApiCall($storeId)) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Please configure extension from System -> '
                    . 'Configuration -> eMAG Marketplace'
                );
            }
            foreach ($prodIds as $prodId) {
                $emagProd = Mage::getModel('innobyte_emag_marketplace/product')
                    ->loadByProdIdAndStore($prodId, $storeId);
                $isProdValid = $helper->isProductActionValid(
                    $emagProd->getMagentoProduct()
                );
                if (!$emagProd->getId() || !$isProdValid) {
                    $invalidIds[] = $prodId;
                    continue;
                }
                $apiResp = Mage::getModel('innobyte_emag_marketplace/api_product')
                    ->setStoreId($emagProd->getStoreId())
                    ->setEmagProduct($emagProd)
                    ->saveOffer();
                if ($apiResp->isError()) {
                    $this->_getSession()->addError(
                        implode('<br />', $apiResp->getMessages())
                    );
                    continue;
                } elseif (count($apiResp->getMessages())) {
                    $this->_getSession()->addNotice(
                        implode('<br />', $apiResp->getMessages())
                    );
                }
                $successfullySentIds[] = $prodId;
            }
        } catch (Innobyte_EmagMarketplace_Exception $iemex) {
            $this->_getSession()->addError($helper->__($iemex->getMessage()));
        } catch (Exception $ex) {
            Mage::logException($ex);
            $this->_getSession()->addError(
                $helper->__('An error occurred. Please try again later.')
            );
        }
        
        if (!empty($invalidIds)) {
            $this->_getSession()->addNotice(
                $helper->__(
                    'Action cannot be applied for product(s): %s',
                    implode(', ', $invalidIds)
                )
            );
        }
        if (!empty($successfullySentIds)) {
            $this->_getSession()->addSuccess(
                $helper->__(
                    'Successfully sent to eMAG the offer for product(s): %s',
                    implode(', ', $successfullySentIds)
                )
            );
        }
        
        return $this->_redirect(
            'adminhtml/catalog_product/',
            array('store' => $storeId)
        );
    }
    
    
    
    /**
     * Deactivate products offers (mass).
     */
    public function massDeactivateOfferAction()
    {
        $prodIds = $this->getRequest()->getParam('product', array());
        $prodIds = array_filter(array_unique(array_map('intval', $prodIds)));
        $helper = Mage::helper('innobyte_emag_marketplace');
        $storeId = $helper->getCurrStoreId();
        $invalidIds = array();
        $successfullySentIds = array();
        try {
            if (empty($prodIds)) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Please select some products.'
                );
            }
            if (!$helper->canMakeApiCall($storeId)) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Please configure extension from System -> '
                    . 'Configuration -> eMAG Marketplace'
                );
            }
            foreach ($prodIds as $prodId) {
                Mage::log(__METHOD__ . ' ' . $prodId);
                $emagProd = Mage::getModel('innobyte_emag_marketplace/product')
                    ->loadByProdIdAndStore($prodId, $storeId);
                $isProdValid = $helper->isProductActionValid(
                    $emagProd->getMagentoProduct()
                );
                if (!$emagProd->getId() || !$isProdValid) {
                    $invalidIds[] = $prodId;
                    continue;
                }
                $emagProd->setStatus(Innobyte_EmagMarketplace_Model_Source_OfferStatus::STATUS_INACTIVE);
                $apiResp = Mage::getModel('innobyte_emag_marketplace/api_product')
                    ->setStoreId($emagProd->getStoreId())
                    ->setEmagProduct($emagProd)
                    ->saveOffer();
                if ($apiResp->isError()) {
                    $this->_getSession()->addError(
                        implode('<br />', $apiResp->getMessages())
                    );
                    continue;
                } elseif (count($apiResp->getMessages())) {
                    $this->_getSession()->addNotice(
                        implode('<br />', $apiResp->getMessages())
                    );
                }
                $emagProd->save();
                $successfullySentIds[] = $prodId;
            }
        } catch (Innobyte_EmagMarketplace_Exception $iemex) {
            $this->_getSession()->addError($helper->__($iemex->getMessage()));
        } catch (Exception $ex) {
            Mage::logException($ex);
            $this->_getSession()->addError(
                $helper->__('An error occurred. Please try again later.')
            );
        }
        
        if (!empty($invalidIds)) {
            $this->_getSession()->addNotice(
                $helper->__(
                    'Action cannot be applied for product(s): %s',
                    implode(', ', $invalidIds)
                )
            );
        }
        if (!empty($successfullySentIds)) {
            $this->_getSession()->addSuccess(
                $helper->__(
                    'Successfully deactivated offer for product(s): %s',
                    implode(', ', $successfullySentIds)
                )
            );
        }
        
        return $this->_redirect(
            'adminhtml/catalog_product/',
            array('store' => $storeId)
        );
    }
}
