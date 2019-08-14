<?php
/**
 * eMAG categories controller.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Adminhtml_Emag_CategoryController
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
            ->isAllowed('innobyte_emag_marketplace/category');
    }
    
    
    
    /**
     * Init different stuffs.
     *
     * @return Innobyte_EmagMarketplace_Adminhtml_Emag_CategoryController
     */
    protected function _init()
    {
        $this->loadLayout()
             ->_title($this->__('eMAG'))
             ->_title($this->__('Categories'))
             ->_setActiveMenu('innobyte_emag_marketplace/category');
        
        return $this;
    }
    
    
    
    /**
     * Display categories.
     */
    public function indexAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setBody(
                $this->getLayout()
                    ->createBlock(
                        'innobyte_emag_marketplace/adminhtml_category_grid'
                    )
                    ->toHtml()
            );
        } else {
            $this->_init()
                 ->renderLayout();
        }
    }
    
    
    
    /**
     * Read eMAG categories.
     */
    public function syncCategoriesAction()
    {
        $response = array(
            'status' => 'error',
            'message' => '',
        );
        $helper = Mage::helper('innobyte_emag_marketplace');
        $storeId = $helper->getCurrStoreId();
        try {
            if (!$helper->canMakeApiCall($storeId)) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Please configure extension from System -> Configuration -> eMAG Marketplace'
                );
            }
            $api = Mage::getSingleton('innobyte_emag_marketplace/api_category')
                ->setStoreId($storeId);
            
            // get pagination
            $apiResponse = $api->count();
            if ($apiResponse->isError()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    implode(', ', $apiResponse->getMessages())
                );
            }
            
            // get categories
            for ($i = 1; $i <= $api->getNoOfPages(); $i++) {
                $apiResponse = $api->setCurrentPage($i)->read();
                if ($apiResponse->isError()) {
                    throw new Innobyte_EmagMarketplace_Exception(
                        implode(', ', $apiResponse->getMessages())
                    );
                }
                foreach ($apiResponse->getResults() as $category) {
                    $api->importCategory($category)->save();
                }
            }
            $response['status'] = 'success';
            $response['message'] = $helper->__(
                'Successfully synced categories.'
            );
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
}
