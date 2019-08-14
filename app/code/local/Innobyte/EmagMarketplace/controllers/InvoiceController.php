<?php

/**
 * Class Innobyte_EmagMarketplace_InvoiceController
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_InvoiceController extends Mage_Core_Controller_Front_Action
{

    /**
     * Download invoice
     */
    public function downloadAction()
    {
        $storeId = $this->getRequest()->getParam('store');
        if (!$this->_isAllowed($storeId)) {
            die('Access Denied');
        }

        $invoiceId = Mage::helper('core')->decrypt($this->getRequest()->getParam('invoice'));
        $type = Mage::helper('core')->decrypt($this->getRequest()->getParam('type'));

        $data = array();
        if ($type == Innobyte_EmagMarketplace_Model_Sales_Invoice::THIRD_PARTY_INVOICE) {
            $data = $this->_getThirdPartyInvoiceData($invoiceId);
        } elseif ($type == Mage_Sales_Model_Order::ACTION_FLAG_INVOICE) {
            $data = $this->_getMagentoInvoiceData($invoiceId);
        } elseif ($type == Mage_Sales_Model_Order::ACTION_FLAG_CREDITMEMO) {
            $data = $this->_getMagentoCreditmemoData($invoiceId);
        }

        if (empty($data)) {
            die('Could not read invoice');
        }

        header('Content-Transfer-Encoding: binary');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($data['path'])) . ' GMT');
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . filesize($data['path']));
        header('Content-Encoding: none');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=' . $data['filename']);
        readfile($data['path']);
    }

    /**
     * Check if url is allowed from current IP Address
     *
     * @param int $storeId
     * @return bool
     */
    protected function _isAllowed($storeId)
    {
        $ipAddress = Mage::helper('core/http')->getRemoteAddr();
        $allowedIpAddresses = Mage::helper('innobyte_emag_marketplace')->getAllowedIpAddresses($storeId);
        if (in_array($ipAddress, $allowedIpAddresses)) {
            return true;
        }

        return false;
    }

    /**
     * Get third party invoice data
     *
     * @param $invoiceId
     * @return array
     */
    protected function _getThirdPartyInvoiceData($invoiceId)
    {
        /** @var $invoice Innobyte_EmagMarketplace_Model_Sales_Invoice */
        $invoice = Mage::getModel('innobyte_emag_marketplace/sales_invoice')->load($invoiceId);

        $data = array();
        if ($invoice->getId()) {
            $data = array(
                'path' => $invoice->getInvoiceBasePath(),
                'filename' => $invoice->getEmagInvoiceName()
            );
        }

        return $data;
    }

    /**
     * Get magento invoice data
     *
     * @param $invoiceId
     * @return array
     */
    protected function _getMagentoInvoiceData($invoiceId)
    {
        /** @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
        /** @var $model Innobyte_EmagMarketplace_Model_Order_Convert_Magento */
        $model = Mage::getModel('innobyte_emag_marketplace/order_convert_magento');

        $data = array();
        if ($invoice->getId()) {
            $data = array(
                'path' => $model->getInvoiceBaseDirectory(),
                'filename' => $model->getInvoiceFileName($invoice, Mage_Sales_Model_Order::ACTION_FLAG_CREDITMEMO)
            );
        }

        return $data;
    }

    /**
     * Get credit memo invoice data
     *
     * @param $creditmemoId
     * @return array
     */
    protected function _getMagentoCreditmemoData($creditmemoId)
    {
        /** @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
        /** @var $model Innobyte_EmagMarketplace_Model_Order_Convert_Magento */
        $model = Mage::getModel('innobyte_emag_marketplace/order_convert_magento');

        $data = array();
        if ($creditmemo->getId()) {
            $data = array(
                'path' => $model->getCreditmemoBaseDirectory(),
                'filename' => $model->getInvoiceFileName($creditmemo, Mage_Sales_Model_Order::ACTION_FLAG_CREDITMEMO)
            );
        }
        return $data;
    }

}
