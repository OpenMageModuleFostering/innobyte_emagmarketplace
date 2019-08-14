<?php
/**
 * Rewrite catalog product edit block.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Block_Adminhtml_Catalog_Product_Edit
    extends Mage_Adminhtml_Block_Catalog_Product_Edit
{
    /**
     * @Override   Added 3 more buttons, noting else changed.
     */
    protected function _prepareLayout()
    {
        $helper = Mage::helper('innobyte_emag_marketplace');
        if (!$helper->isProductActionValid($this->getProduct())) {
            return parent::_prepareLayout();
        }
        $storeId = $helper->getCurrStoreId();
        $emagProduct = Mage::getModel('innobyte_emag_marketplace/product')
            ->loadByProdIdAndStore($this->getProductId(), $storeId);
        
        $productTabsBlock = $this->getLayout()->getBlock('product_tabs');
        $tabsContainerId = $productTabsBlock ?
            $productTabsBlock->getId() : 'product_info_tabs';
        $canMakeApiCall = $helper->canMakeApiCall($storeId);
        $syncBtnDisabled = $emagProduct->getId() && $canMakeApiCall ? 0 : 1;
        $offerBtnDisabled = 1;
        if (($emagProduct->isSynced() || $emagProduct->getPartNumberKey())
            && $canMakeApiCall) {
            $offerBtnDisabled = 0;
        }
        $title = $helper->__(
            'Please configure extension from System -> Configuration -> eMAG Marketplace'
        );
        $this->setChild(
            'emag_send_product_btn',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label' => $helper->__('Send eMAG product'),
                        'onclick' => 'inno.emagMarketplace.send(\''
                            . $tabsContainerId
                            . '\',\''
                            . Mage::helper('adminhtml')->escapeUrl(
                                Mage::helper('adminhtml')->getUrl(
                                    'adminhtml/emag_product/send',
                                    array('_current' => 1, 'send' => 'product')
                                )
                            )
                            . '\', \'product\')',
                        'class' => 'go',
                        'disabled' => $syncBtnDisabled,
                        'title' => !$canMakeApiCall ? $title : '',
                    )
                )
                ->setDisabled($syncBtnDisabled)
        );
        $this->setChild(
            'emag_send_offer_btn',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label' => $helper->__('Send eMAG offer'),
                        'onclick' => 'inno.emagMarketplace.send(\''
                            . $tabsContainerId
                            . '\',\''
                            . Mage::helper('adminhtml')->escapeUrl(
                                Mage::helper('adminhtml')->getUrl(
                                    'adminhtml/emag_product/send',
                                    array('_current' => 1, 'send' => 'offer')
                                )
                            )
                            . '\', \'offer\')',
                        'class' => 'go',
                        'disabled' => $offerBtnDisabled,
                        'title' => !$canMakeApiCall ? $title : '',
                    )
                )
        );
        $this->setChild(
            'emag_deactivate_offer_btn',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label' => $helper->__('Deactivate eMAG offer'),
                        'onclick' => 'inno.emagMarketplace.send(\''
                            . $tabsContainerId
                            . '\',\''
                            . Mage::helper('adminhtml')->escapeUrl(
                                Mage::helper('adminhtml')->getUrl(
                                    'adminhtml/emag_product/deactivateOffer',
                                    array('_current' => 1)
                                )
                            )
                            . '\', \'deactivate-offer\')',
                        'class' => 'go',
                        'disabled' => $offerBtnDisabled,
                        'title' => !$canMakeApiCall ? $title : '',
                    )
                )
        );
        
        return parent::_prepareLayout();
    }
}
