<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Order_Convert_Magento
 *  - convert Magento order to eMAG order
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Order_Convert_Magento
    extends Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
{

    /**
     * Order status
     *
     * @var null
     */
    protected $_status = null;

    /**
     * Customer
     *
     * @var array
     */
    protected $_customer = array();

    /**
     * Products
     *
     * @var array
     */
    protected $_products = array();

    /**
     * Invoices/creditmemos
     *
     * @var array
     */
    protected $_invoices = array();

    /**
     * Currency
     *
     * @var null
     */
    protected $_currency = null;

    /**
     * Current order products
     *
     * @var array
     */
    protected $_currentOrderProducts = array();

    /**
     * Get store id from order
     *
     * @return int
     */
    public function getStoreId()
    {
        if (!$this->_storeId) {
            $this->_storeId = $this->getOrder()->getStoreId();
        }

        return $this->_storeId;
    }

    /**
     * Set order status
     *
     * @param $status
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    public function setStatus($status)
    {
        $this->_status = $status;

        return $this;
    }

    /**
     * Set customer
     *
     * @param $customer
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    public function setCustomer($customer)
    {
        $this->_customer = $customer;

        return $this;
    }

    /**
     * Set products for eMAG order
     *
     * @param $products
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    public function setProducts($products)
    {
        $this->_products = $products;

        return $this;
    }

    /**
     * Set currency
     *
     * @param $currency
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    public function setCurrency($currency)
    {
        $this->_currency = $currency;

        return $this;
    }

    /**
     * Get order id
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->_orderId;
    }

    /**
     * Get order status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Get customer
     *
     * @return array
     */
    public function getCustomer()
    {
        return $this->_customer;
    }

    /**
     * Get products
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->_products;
    }

    /**
     * Add product
     *
     * @param $product
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    protected function _addProduct($product)
    {
        $this->_products[] = $product;

        return $this;
    }

    /**
     * Get invoices/creditmemos
     *
     * @return array
     */
    public function getInvoices()
    {
        return $this->_invoices;
    }

    /**
     * Add invoice/creditmemo
     *
     * @param $invoice
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    protected function _addInvoice($invoice)
    {
        $this->_invoices[] = $invoice;

        return $this;
    }

    /**
     * Get order currency
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getCurrency()
    {
        if (!$this->_currency) {
            $this->_currency = $this->getOrder()->getOrderCurrency();
        }

        return $this->_currency;
    }

    /**
     * Get order status
     *
     * @return bool|int
     */
    public function getOrderStatus()
    {
        $status = $this->getStatus();
        if (is_null($status)) {
            $status = $this->getOrder()->getStatus();
        }

        $statuses = array_flip($this->_orderStatuses);

        if (!array_key_exists($status, $statuses)) {
            return false;
        }

        return $statuses[$status];
    }

    /**
     * Get payment method
     *
     * @param $method
     * @return bool|int
     */
    public function getPaymentMethod($method)
    {
        $paymentMethods = array_flip($this->_paymentMethods);
        if (!array_key_exists($method, $paymentMethods)) {
            return false;
        }

        return $paymentMethods[$method];
    }

    /**
     * Get eMAG order
     *
     * @return Varien_Object
     */
    public function getEmagOrder()
    {
        if (!$this->_emagOrder) {
            $this->_emagOrder = new Varien_Object();
        }

        return $this->_emagOrder;
    }

    /**
     * Get order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
        }

        return $this->_order;
    }

    /**
     * Get payment
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    public function getPayment()
    {
        if (!$this->_payment) {
            $this->_payment = $this->getOrder()->getPayment();
        }

        return $this->_payment;
    }

    /**
     * Get entity model
     *
     * @return Mage_Eav_Model_Entity_Store
     */
    public function getEntityModel()
    {
        return Mage::getModel('eav/entity_store');
    }

    /**
     * Get entity type model
     *
     * @param $entityType
     * @return Mage_Eav_Model_Entity_Type
     */
    public function getEntityTypeModel($entityType)
    {
        return Mage::getModel('eav/config')->getEntityType($entityType);
    }

    /**
     * Invoice/creditmemo prefix
     *
     * @param $entityType
     * @return string
     */
    public function getEntityPrefix($entityType)
    {
        $entityModel = $this->getEntityModel()
            ->loadByEntityStore(
                $this->getEntityTypeModel($entityType)->getEntityTypeId(),
                $this->getStoreId()
            );

        return $entityModel->getIncrementPrefix();
    }

    /**
     * Convert magento order to eMAG order
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function convert()
    {
        try {
            $this->_prepareOrder();
            $this->_prepareCustomer();
            $this->_prepareBillingAddress();
            $this->_prepareShippingAddress();
            $this->_prepareShipping();
            $this->_prepareProducts();
            //TODO: uncomment if magento native invoices need to be sent to eMAG
            //$this->_prepareInvoices();
            $this->_prepareVouchers();
            $this->_preparePayment();
            $this->_prepareCustomerComment();
            $this->_prepareAttachments();

            $data['order'] = $this->getEmagOrder()->toArray();

            $api = $this->getOrderApiModel();
            $response = $api->setStoreId($this->getStoreId())
                ->setData($data)
                ->save();

            if ($response->isError()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    $this->getHelper()->__(
                        'eMAG Api Response => %s', implode(',', $response->getMessages())
                    )
                );
            }

            $this->reReadEmagOrder($this->getOrder());
        } catch (Innobyte_EmagMarketplace_Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception($e->getMessage());
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR, $this->getHelper()->getResourceLogFile('order'), $this->_isDebug());
            throw new Innobyte_EmagMarketplace_Exception(
                $this->getHelper()->__(
                    'There was an error while processing order: #%s', $this->getOrder()->getIncrementId()
                )
            );
        }

        return $this;
    }

    /**
     * Prepare order data
     *
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Mage_Sales_Model_Order
     */
    protected function _prepareOrder()
    {
        try {
            $orderId = $this->getOrder()->getEmagOrderId();
            $orderDate = $this->getOrder()->getEmagOrderDate();
            $orderStatus = $this->getOrderStatus();

            if (!$orderStatus) {
                throw new Innobyte_EmagMarketplace_Exception(
                    $this->getHelper()->__(
                        'Invalid Magento order status: %s', $this->getOrder()->getStatus()
                    )
                );
            }

            $vendorName = $this->getHelper()->getClientCode($this->getStoreId());

            $this->getEmagOrder()
                ->setId($orderId)
                ->setDate($orderDate)
                ->setStatus($orderStatus)
                ->setVendorName($vendorName);
        } catch (Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * Prepare products
     *  - add current products to eMAG order
     *  - on edit check if any products from parent order are not present in current order
     *
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    protected function _prepareProducts()
    {
        try {
            $items = $this->getOrder()->getAllItems();
            foreach ($items as $item) {
                $product = $this->_processOrderItem($item);
                $this->_addProduct($product);
                $this->_currentOrderProducts[] = $item->getProductId();
            }

            if ($this->_isEdited()) {
                $this->_processCanceledProducts();
            }

            $this->getEmagOrder()
                ->setProducts($this->getProducts());
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR, $this->getHelper()->getResourceLogFile('order'), $this->_isDebug());
            throw new Innobyte_EmagMarketplace_Exception(
                'There was an error while processing order products!'
            );
        }

        return $this;
    }

    /**
     * Check if order was edited
     *
     * @return bool
     */
    private function _isEdited()
    {
        if ($this->getOrder()->getRelationParentId()) {
            return true;
        }

        return false;
    }

    /**
     * Check if any products where canceled
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    protected function _processCanceledProducts()
    {
        /** @var $parentOrder Mage_Sales_Model_Order */
        $parentOrder = Mage::getModel('sales/order')
            ->load($this->getOrder()->getRelationParentId());

        /** @var $item Mage_Sales_Model_Order_Item */
        foreach ($parentOrder->getAllVisibleItems() as $item) {
            if (!in_array($item->getProductId(), $this->_currentOrderProducts)) {
                $product = $this->_processOrderItem($item, true);
                $this->_addProduct($product);
            }
        }

        return $this;
    }

    /**
     * Convert order item to eMAG product
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @param bool $canceled
     * @return array
     */
    private function _processOrderItem(Mage_Sales_Model_Order_Item $item, $canceled = false)
    {
        $status = Innobyte_EmagMarketplace_Model_Product::PRODUCT_STATUS_AVAILABLE;
        if ($canceled) {
            $status = Innobyte_EmagMarketplace_Model_Product::PRODUCT_STATUS_CANCELED;
        }

        $product = array(
            'product_id' => $item->getProductId(),
            'part_number' => $item->getSku(),
            'quantity' => $item->getQtyOrdered(),
            'sale_price' => $item->getPrice,
            'currency' => $this->getCurrency()->getCode(),
            'created' => $item->getEmagCreated(),
            'modified' => $item->getEmagModified(),
            'status' => $status,
            'attachments' => array(), // attachment field will be empty at product level; all attachments are attached to order
            'details' => json_decode($item->getEmagDetails()),
            'vat' => number_format($item->getTaxPercent() / 100, 4),
        );

        return $product;
    }

    /**
     * Prepare vouchers
     *
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    protected function _prepareVouchers()
    {
        $vouchers = array();
        $emagVouchers = $this->getOrder()->getEmagVouchers();

        if (!is_array($emagVouchers)) {
            return $this;
        }

        try {
            foreach ($emagVouchers as $emagVoucher) {
                $voucher = array();
                foreach ($emagVoucher as $key => $value) {

                    if (strpos($key, 'base_') !== false) {
                        unset($emagVoucher[$key]);
                        continue;
                    }

                    $column = $this->_removeColumnPrefix($key);
                    $voucher[$column] = $emagVoucher[$key];
                }
                $vouchers[] = $voucher;
            }

            $this->getEmagOrder()
                ->setVouchers($vouchers);

        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR, $this->getHelper()->getResourceLogFile('order'), $this->_isDebug());
            throw new Innobyte_EmagMarketplace_Exception(
                'There was an error while processing vouchers!'
            );
        }

        return $this;
    }

    /**
     * Remove column prefix
     *
     * @param $column
     * @return mixed
     */
    protected function _removeColumnPrefix($column)
    {
        $columnPrefix = Innobyte_EmagMarketplace_Model_Sales_Voucher_Abstract::COLUMN_PREFIX;

        return str_replace($columnPrefix . '_', '', $column);
    }

    /**
     * Prepare customer
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    protected function _prepareCustomer()
    {
        $customer = array(
            'id' => $this->getOrder()->getEmagCustomerId(),
            'name' => $this->getOrder()->getCustomerName(),
            'gender' => $this->getOrder()->getEmagCustomerGender(),
        );

        $this->getEmagOrder()
            ->setCustomer($customer);

        return $this;
    }

    /**
     * Prepare billing address
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    protected function _prepareBillingAddress()
    {
        $customer = $this->getEmagOrder()->getCustomer();
        $billingAddress = $this->getOrder()->getBillingAddress();
        $billing = array(
            'company' => $billingAddress->getCompany(),
            'code' => $billingAddress->getEmagCompanyCode(),
            'email' => '',
            'bank' => $billingAddress->getEmagBank(),
            'iban' => $billingAddress->getEmagIban(),
            'fax' => $billingAddress->getFax(),
            'phone_1' => $billingAddress->getTelephone(),
            'phone_2' => $billingAddress->getEmagTelephone2(),
            'phone_3' => $billingAddress->getEmagTelephone3(),
            'registration_number' => $billingAddress->getEmagCompanyRegNo(),
            'is_vat_payer' => $billingAddress->getEmagIsVatPayer(),
            'legal_entity' => $billingAddress->getEmagLegalEntity(),
            'billing_country' => $billingAddress->getCountryId(),
            'billing_suburb' => $billingAddress->getRegion(),
            'billing_city' => $billingAddress->getCity(),
            'billing_locality_id' => $billingAddress->getEmagLocalityId(),
            'billing_street' => $billingAddress->getStreet1(),
            'billing_postal_code' => $billingAddress->getPostcode()
        );

        $customer = array_merge($customer, $billing);
        $this->getEmagOrder()
            ->setCustomer($customer);

        return $this;
    }

    /**
     * Prepare shipping address
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    protected function _prepareShippingAddress()
    {
        $customer = $this->getEmagOrder()->getCustomer();
        $shippingAddress = $this->getOrder()->getShippingAddress();
        $shipping = array(
            'shipping_country' => $shippingAddress->getCountryId(),
            'shipping_suburb' => $shippingAddress->getRegion(),
            'shipping_city' => $shippingAddress->getCity(),
            'shipping_locality_id' => $shippingAddress->getEmagLocalityId(),
            'shipping_street' => $shippingAddress->getStreet1(),
            'shipping_postal_code' => $shippingAddress->getPostcode()
        );

        $customer = array_merge($customer, $shipping);
        $this->getEmagOrder()
            ->setCustomer($customer);

        return $this;
    }

    /**
     * Prepare shipping
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    protected function _prepareShipping()
    {
        $shippingAmount = $this->getOrder()->getShippingAmount();
        $this->getEmagOrder()
            ->setShippingTax($shippingAmount);

        return $this;
    }

    /**
     * Prepare payment
     *
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    protected function _preparePayment()
    {
        try {
            $payment = $this->getPayment();
            $status = $this->getOrder()->getEmagPaymentStatus();
            $method = $this->getPaymentMethod($payment->getMethodInstance()->getCode());
            if (!$method) {
                throw new Innobyte_EmagMarketplace_Exception(
                    $this->getHelper()->__(
                        'Invalid Magento payment method: %s', $method
                    )
                );
            }

            $this->getEmagOrder()->setPaymentStatus($status);
            $this->getEmagOrder()->setPaymentModeId($method);
        } catch (Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * Prepare invoices and creditmemos
     *
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    protected function _prepareInvoices()
    {
        try {
            $invoices = $this->getOrder()->getInvoiceCollection();
            foreach ($invoices as $invoice) {
                $item = $this->_processInvoice($invoice);
                $this->_addInvoice($item);
            }

            $creditmemos = $this->getOrder()->getCreditmemosCollection();
            foreach ($creditmemos as $creditmemo) {
                $item = $this->_processInvoice($creditmemo, true);
                $this->_addInvoice($item);
            }

            $this->getEmagOrder()
                ->setInvoices($this->getInvoices());
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR, $this->getHelper()->getResourceLogFile('order'), $this->_isDebug());
            throw new Innobyte_EmagMarketplace_Exception(
                'There was an error while processing order invoices!'
            );
        }

        return $this;
    }

    /**
     * Process invoice/creditmemo
     *
     * @param Mage_Sales_Model_Order_Invoice|Mage_Sales_Model_Order_Creditmemo $entity
     * @param bool $storno
     * @return array
     */
    private function _processInvoice($entity, $storno = false)
    {
        $type = Innobyte_EmagMarketplace_Model_Sales_Invoice::EMAG_INVOICE_NORMAL;
        $entityType = Mage_Sales_Model_Order::ACTION_FLAG_INVOICE;
        if ($storno) {
            $type = Innobyte_EmagMarketplace_Model_Sales_Invoice::EMAG_INVOICE_STORNO;
            $entityType = Mage_Sales_Model_Order::ACTION_FLAG_CREDITMEMO;
        }

        // get entity prefix(series)
        $series = $this->getEntityPrefix($entityType);
        // remove entity prefix from increment number(firs occurrence only)
        $number = substr_replace($entity->getIncrementId(), '', 0, strlen($series));
        // prepare products
        $products = $this->_prepareInvoiceProducts($entity);

        $item = array(
            'id' => $entity->getId(),
            'series' => $series,
            'number' => $number,
            'date' => $entity->getCreatedAt(),
            'due_date' => '0000-00-00 00:00:00', //TODO: send correct value only if requested by eMAG; currently is not needed
            'net_value' => $entity->getSubtotal(),
            'gross_value' => $entity->getGrandTotal(),
            'products' => $products,
            'details' => '',
            'type' => $type
        );

        return $item;
    }

    /**
     * Prepare invoice/creditmemo products
     *
     * @param Mage_Sales_Model_Order_Invoice|Mage_Sales_Model_Order_Creditmemo $entity
     * @return array
     */
    private function _prepareInvoiceProducts($entity)
    {
        $products = array();

        /** @var $item Mage_Sales_Model_Order_Invoice_Item|Mage_Sales_Model_Order_Creditmemo_Item */
        foreach ($entity->getAllItems() as $item) {
            /** @var $orderItem Mage_Sales_Model_Order_Item */
            $orderItem = $this->getOrder()->getItemById($item->getOrderItemId());
            $products[] = array(
                'id' => $item->getProductId(),
                'quantity' => $item->getQty(),
                'sale_price' => $item->getPrice(),
                'vat' => number_format($orderItem->getTaxPercent() / 100, 4),
                'details' => ''
            );
        }

        return $products;
    }


    /**
     * Prepare customer comment
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    protected function _prepareCustomerComment()
    {
        $comment = $this->getOrder()->getEmagCustomerComment();
        $this->getEmagOrder()
            ->setObservation($comment);

        return $this;
    }

    /**
     * Prepare attachments
     *
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    protected function _prepareAttachments()
    {
        $attachments = array();
        try {
            $thirdPartyInvoices = $this->_getThirdPartyInvoices();
            foreach ($thirdPartyInvoices as $invoice) {
                $attachments[] = array(
                    'name' => $invoice->getEmagInvoiceName(),
                    'url' => $this->_getFileUrl($invoice->getId(), Innobyte_EmagMarketplace_Model_Sales_Invoice::THIRD_PARTY_INVOICE),
                    'type' => 1
                );
            }

//TODO: uncomment if magento native invoices need to be sent to eMAG
//            $magentoInvoices = $this->_getMagentoInvoices();
//            foreach ($magentoInvoices as $invoice) {
//                $this->_generateInvoice($invoice);
//
//                $attachments[] = array(
//                    'name' => $this->getInvoiceFileName($invoice, Mage_Sales_Model_Order::ACTION_FLAG_INVOICE),
//                    'url' => $this->_getFileUrl($invoice->getId(), Mage_Sales_Model_Order::ACTION_FLAG_INVOICE),
//                    'type' => 1
//                );
//            }
//
//            $magentoCreditmemos = $this->_getMagentoCreditmemos();
//            foreach ($magentoCreditmemos as $creditmemo) {
//                $this->_generateCreditmemo($creditmemo);
//
//                $attachments[] = array(
//                    'name' => $this->getInvoiceFileName($creditmemo, Mage_Sales_Model_Order::ACTION_FLAG_CREDITMEMO),
//                    'url' => $this->_getFileUrl($creditmemo->getId(), Mage_Sales_Model_Order::ACTION_FLAG_CREDITMEMO),
//                    'type' => 1
//                );
//            }

            $this->getEmagOrder()
                ->setAttachments($attachments);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR, $this->getHelper()->getResourceLogFile('order'), $this->_isDebug());
            throw new Innobyte_EmagMarketplace_Exception(
                'There was an error while processing order attachments!'
            );
        }

        return $this;
    }

    /**
     * Get third party invoices
     *
     * @return array
     */
    private function _getThirdPartyInvoices()
    {
        /** @var $model Innobyte_EmagMarketplace_Model_Sales_Invoice */
        $model = Mage::getModel('innobyte_emag_marketplace/sales_invoice');
        $invoices = $model->getEmagThirdPartyInvoicesCollection($this->getOrder()->getId());

        return $invoices;
    }

    /**
     * Get magento invoices
     *
     * @return array
     */
    private function _getMagentoInvoices()
    {
        $invoices = $this->getOrder()->getInvoiceCollection();

        return $invoices;
    }

    /**
     * Get magento creditmemos
     *
     * @return array
     */
    private function _getMagentoCreditmemos()
    {
        $creditmemos = $this->getOrder()->getCreditmemosCollection();

        return $creditmemos;
    }

    /**
     * Generate invoice pdf and save it to disk
     *
     * @param $invoice
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    private function _generateInvoice($invoice)
    {
        try {
            /** @var $model Mage_Sales_Model_Order_Pdf_Invoice */
            $model = Mage::getModel('sales/order_pdf_invoice');
            $pdf = $model->getPdf(array($invoice))->render();

            $invoiceBaseDirectory = $this->getInvoiceBaseDirectory();
            $invoiceBasePath = $invoiceBaseDirectory . $this->getInvoiceFileName($invoice, Mage_Sales_Model_Order::ACTION_FLAG_INVOICE);

            if (is_dir_writeable(Mage::getBaseDir('media'))) {
                if (!is_dir($invoiceBaseDirectory)) {
                    mkdir($invoiceBaseDirectory, 0755, true);
                }
            } else {
                throw new Innobyte_EmagMarketplace_Exception(
                    $this->getHelper()->__(
                        'Unable to write to media folder: %s', $invoiceBaseDirectory
                    )
                );
            }

            try {
                if (file_put_contents($invoiceBasePath, $pdf) === false) {
                    throw new Innobyte_EmagMarketplace_Exception(
                        'Unable to save Magento invoice to disk'
                    );
                }
            } catch (Exception $e) {
                Mage::log($e->getMessage(), Zend_Log::ERR, $this->getHelper()->getResourceLogFile('order'), $this->_isDebug());
                throw new Innobyte_EmagMarketplace_Exception(
                    $this->getHelper()->__(
                        'Magento Invoice Pdf could not be saved. Invoice increment id: #%s',
                        $invoice->getIncrementId()
                    )
                );
            }
        } catch (Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * Generate creditmemo pdf and save it to disk
     *
     * @param $creditmemo
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    private function _generateCreditmemo($creditmemo)
    {
        try {
            /** @var $model Mage_Sales_Model_Order_Pdf_Creditmemo */
            $model = Mage::getModel('sales/order_pdf_creditmemo');
            $pdf = $model->getPdf(array($creditmemo))->render();

            $creditmemoBaseDirectory = $this->getCreditmemoBaseDirectory();
            $creditmemoBasePath = $creditmemoBaseDirectory . $this->getInvoiceFileName(
                    $creditmemo,
                    Mage_Sales_Model_Order::ACTION_FLAG_CREDITMEMO
                );

            if (is_dir_writeable(Mage::getBaseDir('media'))) {
                if (!is_dir($creditmemoBaseDirectory)) {
                    mkdir($creditmemoBaseDirectory, 0755, true);
                }
            } else {
                throw new Innobyte_EmagMarketplace_Exception(
                    $this->getHelper()->__(
                        'Unable to write to media folder: %s', $creditmemoBaseDirectory
                    )
                );
            }

            try {
                if (file_put_contents($creditmemoBasePath, $pdf) === false) {
                    throw new Innobyte_EmagMarketplace_Exception(
                        'Unable to save Magento creditmemo to disk'
                    );
                }

            } catch (Exception $e) {
                Mage::log($e->getMessage(), Zend_Log::ERR, $this->getHelper()->getResourceLogFile('order'), $this->_isDebug());
                throw new Innobyte_EmagMarketplace_Exception(
                    $this->getHelper()->__(
                        'Magento Creditmemo Pdf could not be saved. Creditmemo increment id: #%s',
                        $creditmemo->getIncrementId()
                    )
                );
            }
        } catch (Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * Get invoice|creditmemo file name
     *
     * @param Mage_Sales_Model_Order_Invoice|Mage_Sales_Model_Order_Creditmemo $invoice
     * @param string $type
     * @return string
     */
    public function getInvoiceFileName($invoice, $type)
    {
        $fileName = $type . '_' . $invoice->getIncrementId() . '.pdf';

        return $fileName;
    }

    /**
     * Get invoice base directory
     *
     * @return string
     */
    public function getInvoiceBaseDirectory()
    {
        return Mage::getBaseDir('media') . DS . Innobyte_EmagMarketplace_Model_Sales_Invoice::MAGENTO_INVOICE_DIRECTORY;
    }

    /**
     * Get creditmemo base directory
     *
     * @return string
     */
    public function getCreditmemoBaseDirectory()
    {
        return Mage::getBaseDir('media') . DS . Innobyte_EmagMarketplace_Model_Sales_Invoice::MAGENTO_CREDITMEMO_DIRECTORY;
    }

    /**
     * Get file url
     *
     * @param $invoiceId
     * @param $type
     * @return string
     */
    private function _getFileUrl($invoiceId, $type)
    {
        $url = Mage::getUrl(
            'marketplace/invoice/download',
            array(
                'invoice' => Mage::helper('core')->encrypt($invoiceId),
                'type' => Mage::helper('core')->encrypt($type),
                'store' => $this->getOrder()->getStoreId()
            )
        );

        return $url;
    }

}
