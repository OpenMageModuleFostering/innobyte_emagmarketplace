<?php
/**
 * eMAG locality controller.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Adminhtml_Emag_LocalityController
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
        $returnValue = false;
        $session = Mage::getSingleton('admin/session');
        switch ($this->getRequest()->getActionName()) {
            case 'index': // intentionally ommitted break;
            case 'syncLocalities':
                $returnValue = $session->isAllowed(
                    'innobyte_emag_marketplace/locality'
                );
                break;
            case 'getCities':
                $returnValue = $session->isAllowed(
                    'sales/order/actions/edit'
                ) || $session->isAllowed('system/config');
                break;
            default:
        }
        return $returnValue;
    }
    
    
    
    /**
     * Init different stuffs.
     *
     * @return Innobyte_EmagMarketplace_Adminhtml_Emag_LocalityController
     */
    protected function _init()
    {
        $this->loadLayout()
             ->_title($this->__('eMAG'))
             ->_title($this->__('Localities'))
             ->_setActiveMenu('innobyte_emag_marketplace/locality');
        
        return $this;
    }
    
    
    
    /**
     * Display localities.
     */
    public function indexAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setBody(
                $this->getLayout()
                    ->createBlock(
                        'innobyte_emag_marketplace/adminhtml_locality_grid'
                    )
                    ->toHtml()
            );
        } else {
            $this->_init()
                 ->renderLayout();
        }
    }
    
    
    
    /**
     * Read eMAG localities.
     */
    public function syncLocalitiesAction()
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
            $api = Mage::getSingleton('innobyte_emag_marketplace/api_locality')
                ->setStoreId($storeId);
            $flag = Mage::getModel('innobyte_emag_marketplace/locality_flag')
                ->loadSelf();
            
            // get pagination
            $data = array();
            if ($flag->getLastSyncronization($storeId)) {
                $data['modified'] = $flag->getLastSyncronization($storeId);
            }
            $apiResponse = $api->setData($data)->count();
            if ($apiResponse->isError()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    implode(', ', $apiResponse->getMessages())
                );
            }
            
            // get localities
            for ($i = 1; $i <= $api->getNoOfPages(); $i++) {
                $apiResponse = $api->setCurrentPage($i)->read();
                if ($apiResponse->isError()) {
                    throw new Innobyte_EmagMarketplace_Exception(
                        implode(', ', $apiResponse->getMessages())
                    );
                }
                foreach ($apiResponse->getResults() as $locality) {
                    $api->importLocality($locality)->save();
                }
            }
            $date = Mage::getModel('core/date')->date('Y-m-d') . ' 00:00:00';
            $flag->setLastSyncronization($storeId, $date)->save();
            $response['status'] = 'success';
            $response['message'] = $helper->__(
                'Successfully synced localities.'
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
    
    
    
    /**
     * Retrieve cities for autocomplete.
     */
    public function getCitiesAction()
    {
        $items = array();
        $helper = Mage::helper('innobyte_emag_marketplace');
        try {
            $addressType = trim($this->getRequest()->getParam('address_type'));
            $allowedAddresses = array('billing', 'shipping', 'shipping_origin');
            if (!in_array($addressType, $allowedAddresses)) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Address Type (billing|shipping|shipping origin) not set.'
                );
            }
            
            $city = trim($this->getRequest()->getParam('city'));
            if (empty($city)) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Please provide some part of the city name'
                );
            }
            $cityTranslited = $helper->getAsciiTranslitVal($city);
            
            $storeId = intval($this->getRequest()->getParam('store_id', 0));
            $country = trim($this->getRequest()->getParam('country', ''));
            
            $citiesColl = Mage::getResourceModel(
                'innobyte_emag_marketplace/locality_collection'
            )->addStoreFilter($storeId)
                ->addFieldToFilter(
                    array('name_latin', 'name'),
                    array(
                        array('like' => '%' . $cityTranslited . '%'),
                        array('like' => '%' . $city . '%'),
                    )
                );
            
            // process products in chunks
            // so DB does not get busy if too many localities
            $citiesCnt = $citiesColl->getSize();
            $chunkSize = 100;
            $pages = ceil($citiesCnt / $chunkSize);
            $i = 0;
            $results = array();
            while ($i < $pages) {
                $citiesColl->clear();
                $citiesColl->getSelect()
                    ->reset(Zend_Db_Select::LIMIT_COUNT)
                    ->reset(Zend_Db_Select::LIMIT_OFFSET)
                    ->limit($chunkSize, $i * $chunkSize);
                foreach ($citiesColl as $locality) {
                    $itemData = array(
                        'id' => $locality->getEmagId(),
                        'name' => $locality->getName(),
                        'county' => $locality->getRegion2(),
                        'description' => $this->_getCityDescription($locality),
                    );
                    if ($country) {
                        // add auxiliay region id data, based on country sent,
                        // unfortunately eMAG cities do not have country info
                        // so the filter to be made more accurrate.
                        $region = $helper->getMagentoRegion(
                            $locality->getRegion2Latin(),
                            $country
                        );
                        if (!is_null($region) && $region->getId()) {
                            $itemData['county_id'] = $region->getId();
                        }
                    }
                    $results[] = $itemData;
                }
                $i++;
            }
            $items = $results;
        } catch (Innobyte_EmagMarketplace_Exception $iemEx) {
            $items[] = array(
                'id' => 'error',
                'name' => '',
                'county' => '',
                'description' => $helper->__($iemEx->getMessage()),
            );
        } catch (Exception $ex) {
            Mage::logException($ex);
            $items[] = array(
                'id' => 'error',
                'name' => '',
                'county' => '',
                'description' => $helper->__(
                    'An error occurred. Please try again later.'
                ),
            );
        }

        $block = $this->getLayout()->createBlock('adminhtml/template')
            ->setTemplate('innobyte/emag_marketplace/city-autocomplete.phtml')
            ->assign('items', $items)
            ->assign('addressType', $addressType);
        $this->getResponse()->setBody($block->toHtml());
    }


    
    /**
     * Retrieve region 3 and region 2.
     *
     * @param Innobyte_EmagMarketplace_Model_Locality $locality
     * @return string
     */
    protected function _getCityDescription(
        Innobyte_EmagMarketplace_Model_Locality $locality
    )
    {
        $returnValue = '';
        if (is_null($locality)) {
            return $returnValue;
        }
        if ($locality->getRegion3()) {
            $returnValue .= '[' . $locality->getRegion3();
        }
        if ($locality->getRegion2()) {
            if ($locality->getRegion3()) {
                $returnValue .= ', ' . $locality->getRegion2();
            } else {
                $returnValue .= '[' . $locality->getRegion2();
            }
        }
        if ($locality->getRegion3() || $locality->getRegion2()) {
            $returnValue .= ']';
        }
        return $returnValue;
    }
}
