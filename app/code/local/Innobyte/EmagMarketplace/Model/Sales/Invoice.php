<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Sales_Invoice
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Sales_Invoice extends Mage_Core_Model_Abstract
{

    /**
     * Invoice entity type code
     */
    const ENTITY_TYPE_CODE_INVOICE = 'emag_invoice';

    /**
     * Creditmemo entity type code
     */
    const ENTITY_TYPE_CODE_CREDITMEMO = 'emag_creditmemo';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_sales_invoice';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getInvoice() in this case
     *
     * @var string
     */
    protected $_eventObject = 'invoice';

    /**
     * Emag normal invoice type
     */
    const EMAG_INVOICE_NORMAL = 0;

    /**
     * Emag storno invoice type
     */
    const EMAG_INVOICE_STORNO = 1;

    /**
     * Code
     */
    const THIRD_PARTY_INVOICE = 'third_party_invoice';

    /**
     * Magento invoice directory
     */
    const MAGENTO_INVOICE_DIRECTORY = 'innobyte/emag_marketplace/magento_invoice/';

    /**
     * Magento creditmemo directory
     */
    const MAGENTO_CREDITMEMO_DIRECTORY = 'innobyte/emag_marketplace/magento_creditmemo/';

    /**
     * Third party invoice directory
     */
    const THIRD_PARTY_INVOICE_DIRECTORY = 'innobyte/emag_marketplace/third_party_invoice/';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('innobyte_emag_marketplace/sales_invoice');
    }

    /**
     * Attach third party invoices to order
     *
     * @param $sales Mage_Sales_Model_Order
     * @return Innobyte_EmagMarketplace_Model_Sales_Invoice
     */
    public function attachInvoiceData($sales)
    {
        $invoices = $this->getEmagThirdPartyInvoices($sales->getId());
        if (!empty($invoices)) {
            $sales->addData(array('emag_third_party_invoice' => $invoices));
        }

        return $this;
    }

    /**
     * Get third party invoices collection
     *
     * @param $orderId
     * @return mixed
     */
    public function getEmagThirdPartyInvoicesCollection($orderId)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('order_id', $orderId);

        return $collection;
    }

    /**
     * Get third party invoices
     *
     * @param $orderId
     * @return array
     */
    public function getEmagThirdPartyInvoices($orderId)
    {
        $invoices = array();

        $collection = $this->getEmagThirdPartyInvoicesCollection($orderId);
        if (!$collection->getSize()) {
            return $invoices;
        }

        foreach ($collection as $invoice) {
            $invoices[$invoice->getId()] = $invoice->getEmagInvoiceName();
        }

        return $invoices;
    }

    /**
     * Media invoice directory name
     *
     * @return string
     */
    public function getInvoiceDirectory()
    {
        return self::THIRD_PARTY_INVOICE_DIRECTORY;
    }

    /**
     * Base disk directory where invoices are stored
     *
     * @return string
     */
    public function getInvoiceBaseDirectory()
    {
        return Mage::getBaseDir('media') . DS . $this->getInvoiceDirectory();
    }

    /**
     * Get invoice url
     *
     * @return string
     */
    public function getInvoiceUrl()
    {
        return Mage::getBaseUrl('media') . $this->getInvoiceDirectory() . $this->getEmagInvoiceName();
    }

    /**
     * Get invoice path
     *
     * @return string
     */
    public function getInvoicePath()
    {
        return $this->getInvoiceDirectory() . $this->getEmagInvoiceName();
    }

    /**
     * Get invoice base path
     *
     * @return string
     */
    public function getInvoiceBasePath()
    {
        return $this->getInvoiceBaseDirectory() . $this->getEmagInvoiceName();
    }

}
