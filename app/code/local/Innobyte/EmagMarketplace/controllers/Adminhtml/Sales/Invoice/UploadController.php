<?php

/**
 * Class Innobyte_EmagMarketplace_Adminhtml_Sales_Invoice_UploadController
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 *
 */
class Innobyte_EmagMarketplace_Adminhtml_Sales_Invoice_UploadController extends Mage_Adminhtml_Controller_Action
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
     * Get third party invoice model
     *
     * @return Innobyte_EmagMarketplace_Model_Sales_Invoice
     */
    protected function _getInvoiceModel()
    {
        return Mage::getModel('innobyte_emag_marketplace/sales_invoice');
    }

    /**
     * Invoice popup
     */
    public function popupAction()
    {
        $this->loadLayout('popup');
        $this->renderLayout();
    }

    /**
     * Save invoice
     */
    public function saveAction()
    {
        if (!is_uploaded_file($_FILES['invoice']['tmp_name'])) {
            $this->_getSession()->addError(
                $this->_getHelper()->__(
                    'There was an error while uploading file. Please try again!'
                )
            );
            $this->_redirect('*/sales_invoice_upload/popup', array('_current' => true));
            return;
        }

        try {
            $emagInvoice = $this->_getInvoiceModel();

            $uploader = new Varien_File_Uploader($_FILES['invoice']);
            $uploader->setAllowedExtensions(array('pdf', 'doc', 'docx'));
            $uploader->addValidateCallback('validate_image', Mage::helper('catalog/image'), 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->removeValidateCallback('validate_image');
            $result = $uploader->save($emagInvoice->getInvoiceBaseDirectory());

            // set invoice type
            $type = Innobyte_EmagMarketplace_Model_Sales_Invoice::EMAG_INVOICE_NORMAL;
            if ($this->getRequest()->getParam('is_storno')) {
                $type = Innobyte_EmagMarketplace_Model_Sales_Invoice::EMAG_INVOICE_STORNO;
            }

            // save third party invoice
            $emagInvoice->setOrderId($this->getRequest()->getParam('order_id'));
            $emagInvoice->setEmagInvoiceName($result['file']);
            $emagInvoice->setEmagInvoiceType($type);
            $emagInvoice->save();
        } catch (Exception $e) {
            $this->_getSession()->addError(
                $this->_getHelper()->__(
                    $e->getMessage()
                )
            );
        }

        $this->_redirect('*/sales_invoice_upload/popup', array('_current' => true));
    }

    /**
     * Preview/Download invoice
     */
    public function downloadAction()
    {
        $file = null;
        $plain = false;
        if ($this->getRequest()->getParam('file')) {
            // download file
            $file = Mage::helper('core')->urlDecode($this->getRequest()->getParam('file'));
        } else if ($this->getRequest()->getParam('image')) {
            // show plain image
            $file = Mage::helper('core')->urlDecode($this->getRequest()->getParam('image'));
            $plain = true;
        } else {
            return $this->norouteAction();
        }

        $path = $this->_getInvoiceModel()->getInvoiceBaseDirectory();

        $ioFile = new Varien_Io_File();
        $ioFile->open(array('path' => $path));
        $fileName = $ioFile->getCleanPath($path . $file);
        $path = $ioFile->getCleanPath($path);

        if ((!$ioFile->fileExists($fileName) || strpos($fileName, $path) !== 0)
            && !Mage::helper('core/file_storage')->processStorageFile(str_replace('/', DS, $fileName))
        ) {
            return $this->norouteAction();
        }

        if ($plain) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            switch (strtolower($extension)) {
                case 'gif':
                    $contentType = 'image/gif';
                    break;
                case 'jpg':
                    $contentType = 'image/jpeg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    break;
            }

            $ioFile->streamOpen($fileName, 'r');
            $contentLength = $ioFile->streamStat('size');
            $contentModify = $ioFile->streamStat('mtime');

            $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', $contentLength)
                ->setHeader('Last-Modified', date('r', $contentModify))
                ->clearBody();
            $this->getResponse()->sendHeaders();

            while (false !== ($buffer = $ioFile->streamRead())) {
                echo $buffer;
            }
        } else {
            $name = pathinfo($fileName, PATHINFO_BASENAME);
            $this->_prepareDownloadResponse($name, array(
                'type' => 'filename',
                'value' => $fileName
            ));
        }

        return $this;
    }

    /**
     * Delete invoice
     */
    public function deleteAction()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if (!$invoiceId) {
            $this->_getSession()->addError(
                $this->_getHelper()->__(
                    'Invoice not found!'
                )
            );
            $this->_redirect('*/sales_invoice_upload/popup', array('_current' => true));
            return;
        }

        /** @var $invoice Innobyte_EmagMarketplace_Model_Sales_Invoice */
        $invoice = $this->_getInvoiceModel()
            ->load($invoiceId);
        $filePath = $invoice->getInvoiceBasePath();

        $invoice->delete();

        unlink($filePath);

        $this->_getSession()->addSuccess(
            $this->_getHelper()->__(
                'Invoice successfully deleted.'
            )
        );

        $this->_redirect('*/sales_invoice_upload/popup', array('_current' => true));
    }

    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/emag_upload_invoice');
    }

}
