<?php
/**
 * eMAG localities grid container.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Block_Adminhtml_Locality
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * @Override
     */
    public function __construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'innobyte_emag_marketplace';
        $this->_controller = 'adminhtml_locality';
        $this->_headerText = $this->__('eMAG Localities');
        
        parent::__construct();
        
        $helper = Mage::helper('innobyte_emag_marketplace');
        $storeId = $helper->getCurrStoreId();
        $canMakeApiCall = $helper->canMakeApiCall($storeId);
        if ($helper->isExtensionEnabled($storeId)
            && $storeId != Mage_Core_Model_App::ADMIN_STORE_ID) {
            $this->_updateButton(
                'add',
                null,
                array(
                    'label' => $this->__('Synchronize localities'),
                    'onclick' => 'inno.emagMarketplace.syncLocalities(\''
                        . $this->escapeUrl(
                            $this->getUrl(
                                '*/*/syncLocalities',
                                array('_current' => 1)
                            )
                        )
                        . '\')',
                    'sort_order' => 100,
                    'disabled' => !$canMakeApiCall,
                    'title' => !$canMakeApiCall ? $helper->__(
                        'Please configure extension from System -> Configuration -> eMAG Marketplace'
                    ) : '',
                )
            );
        } else {
            $this->_removeButton('add');
        }
    }
}
