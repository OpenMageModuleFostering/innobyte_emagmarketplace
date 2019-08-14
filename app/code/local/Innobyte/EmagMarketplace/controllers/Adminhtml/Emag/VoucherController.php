<?php

/**
 * Class Innobyte_EmagMarketplace_Adminhtml_Emag_VoucherController
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Adminhtml_Emag_VoucherController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Get eMAG marketplace data helper
     *
     * @return Innobyte_EmagMarketplace_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('innobyte_emag_marketplace');
    }

    /**
     * Remove voucher from sales entity
     */
    public function removeAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setBody($this->_getHelper()->__('Invalid request!'));
        }

        try {
            /** @var $voucher Innobyte_EmagMarketplace_Model_Resource_Sales_Quote_Voucher_Collection */
            $collection = Mage::getResourceModel('innobyte_emag_marketplace/sales_quote_voucher_collection');
            $collection
                ->addFieldToFilter('entity_id', $this->getRequest()->getParam('quote_id'))
                ->addFieldToFilter('emag_voucher_id', $this->getRequest()->getParam('voucher_id'));

            /** @var $voucher Innobyte_EmagMarketplace_Model_Sales_Quote_Voucher */
            $voucher = $collection->getFirstItem();

            if ($collection->getSize()) {
                $voucher->delete();
                $this->getResponse()->setBody(
                    $this->_getHelper()->__(
                        'eMAG Voucher successfully deleted!'
                    )
                );
            }
        } catch (Exception $e) {
            $this->getResponse()->setBody(
                $this->_getHelper()->__(
                    'There was an error while removing voucher. Please try again!'
                )
            );
            Mage::log($e->getMessage());
        }
    }

    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('innobyte_emag_marketplace/vouchers');
    }

}
