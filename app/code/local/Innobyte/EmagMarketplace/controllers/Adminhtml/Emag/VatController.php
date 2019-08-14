<?php
/**
 * eMAG VAT controller.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Adminhtml_Emag_VatController
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
            ->isAllowed('innobyte_emag_marketplace/vat');
    }
    
    
    
    /**
     * Init different stuffs.
     *
     * @return Innobyte_EmagMarketplace_Adminhtml_Emag_VatController
     */
    protected function _init()
    {
        $this->loadLayout()
             ->_title($this->__('eMAG'))
             ->_title($this->__('VATs'))
             ->_setActiveMenu('innobyte_emag_marketplace/vat');
        
        return $this;
    }
    
    
    
    /**
     * Display VATs.
     */
    public function indexAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setBody(
                $this->getLayout()
                    ->createBlock(
                        'innobyte_emag_marketplace/adminhtml_vat_grid'
                    )->toHtml()
            );
        } else {
            $this->_init()
                 ->renderLayout();
        }
    }
    
    
    
    /**
     * Read eMAG VATs.
     */
    public function syncVatsAction()
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
            $api = Mage::getSingleton('innobyte_emag_marketplace/api_vat')
                ->setStoreId($storeId);
            $apiResponse = $api->read();
            if ($apiResponse->isError()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    implode(', ', $apiResponse->getMessages())
                );
            }
            
            $hasVat = false;
            foreach ($apiResponse->getResults() as $vat) {
                $hasVat = true;
                $api->importVat($vat)->save();
            }
            
            if ($hasVat) {
                $response = array(
                    'status' => 'success',
                    'message' => $helper->__('Successfully synced VATs.'),
                );
            } else {
                $response = array(
                    'status' => 'success',
                    'message' => $helper->__('No VATs found.'),
                );
            }
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
