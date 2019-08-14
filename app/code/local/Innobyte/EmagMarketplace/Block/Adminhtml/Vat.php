<?php
/**
 * eMAG VATs grid container.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Block_Adminhtml_Vat
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * @Override
     */
    public function __construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'innobyte_emag_marketplace';
        $this->_controller = 'adminhtml_vat';
        $this->_headerText = $this->__('eMAG VATs');
        
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
                    'label' => $this->__('Synchronize VATs'),
                    'onclick' => 'inno.emagMarketplace.syncVats(\''
                        . $this->escapeUrl(
                            $this->getUrl(
                                '*/*/syncVats',
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
