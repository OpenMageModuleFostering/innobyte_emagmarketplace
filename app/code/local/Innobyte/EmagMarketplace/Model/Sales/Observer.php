<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Sales_Observer
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Sales_Observer
{

    /**
     * Conversion types
     */
    const CUSTOMER = 'customer';
    const CUSTOMER_ADDRESS = 'customer_address';

    /**
     * Get voucher attribute name
     *
     * @return string
     */
    protected function _getVouchersAttributeName()
    {
        return Innobyte_EmagMarketplace_Model_Sales_Voucher_Abstract::EMAG_VOUCHERS;
    }

    /**
     * @return Innobyte_EmagMarketplace_Model_Api_Order
     */
    public function getOrderApiModel()
    {
        return Mage::getModel('innobyte_emag_marketplace/api_order');
    }

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
     * Change shipping method rate price
     *
     * Apply eMAG shipping price on:
     *  - adminhtml_sales_order_create_process_data_before
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function applyEmagShippingMethod(Varien_Event_Observer $observer)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getSession()->getQuote();
        $data = Mage::app()->getRequest()->getPost('order');

        if (
            isset($data['carrier'])
            && $data['carrier'] == Innobyte_EmagMarketplace_Model_Shipping_Carrier_Emag::EMAG_SHIPPING
        ) {
            $shippingMethod = $data['shipping_method'];
            $quote->getShippingAddress()->setShippingMethod($shippingMethod);

            $shippingAmount = $this->_parseShippingAmount($data['shipping_amount']);
            $rates = $quote->getShippingAddress()->collectShippingRates()
                ->getGroupedAllShippingRates();

            foreach ($rates as $carrier) {
                /** @var $rate Mage_Sales_Model_Quote_Address_Rate */
                foreach ($carrier as $rate) {
                    // set new price for eMAG shipping
                    if ($rate->getCode() == $shippingMethod) {
                        $rate->setPrice($shippingAmount);
                        $rate->save();
                        break;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Parse shipping amount
     *
     * @param $price
     * @return float|int|null
     */
    protected function _parseShippingAmount($price)
    {
        $price = Mage::app()->getLocale()->getNumber($price);
        if ($price > 0) {
            return $price;
        }

        return 0;
    }

    /**
     * Convert shipping amount to base currency
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param $shippingAmount
     * @param bool $round
     * @return float
     */
    protected function _getBaseShippingAmount(Mage_Sales_Model_Quote $quote, $shippingAmount, $round = false)
    {
        $baseCurrency = $quote->getBaseCurrencyCode();
        $currentCurrency = $quote->getQuoteCurrencyCode();

        /** @var $model Mage_Directory_Model_Currency */
        $model = Mage::getModel('directory/currency');
        $allowedCurrencies = $model->getConfigAllowCurrencies();
        $rates = $model->getCurrencyRates($baseCurrency, $allowedCurrencies);

        $baseShippingAmount = $shippingAmount / $rates[$currentCurrency];

        if ($round) {
            return $quote->getStore()->roundPrice($baseShippingAmount);
        }

        return $baseShippingAmount;
    }

    /**
     * Save attribute data for quote on:
     *  - sales_quote_save_after
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function quoteAfterSave(Varien_Event_Observer $observer)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getQuote();

        // skip if not eMAG order
        if (!$quote->getEmagOrderId()) {
            return $this;
        }

        if ($quote instanceof Mage_Core_Model_Abstract) {
            Mage::getModel('innobyte_emag_marketplace/sales_quote')
                ->saveAttributeData($quote);

            Mage::getModel('innobyte_emag_marketplace/sales_quote_voucher')
                ->saveVoucherData($quote);
        }

        return $this;
    }

    /**
     * Change order status to "emag_in_progress" on:
     *  - sales_order_invoice_register
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function invoiceRegister(Varien_Event_Observer $observer)
    {
        /** @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();
        if (!$order->getEmagOrderId()) {
            return $this;
        }
        $order->setState(
            Mage_Sales_Model_Order::STATE_PROCESSING,
            Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_IN_PROGRESS
        );

        return $this;
    }

    /**
     * Generate invoice for eMAG orders paid with credit card and payment status "paid" on:
     *  - sales_order_place_after
     *
     * Notes:
     *  - order must be in state processing to generate invoice
     *  - order must have acknowledge() request sent to eMAG before generating invoice
     *  - order payment must be credit card with status "paid"
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     * @throws Exception
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function orderAfterPlace(Varien_Event_Observer $observer)
    {
        /** @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();
        if (!$order->getEmagOrderId()) {
            return $this;
        }

        if ($order->getState() != Mage_Sales_Model_Order::STATE_PROCESSING) {
            return $this;
        }

        if ($order->getEmagPaymentStatus() != Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::EMAG_PAYMENT_STATUS_PAID) {
            return $this;
        }

        try {
            $invoice = $this->_prepareInvoice($order);
            $order->addStatusHistoryComment(
                $this->_getHelper()->__(
                    'Amount of %s captured by eMAG Marketplace.', $order->formatPriceTxt($order->getGrandTotal())
                ),
                false
            );

            $transaction = Mage::getModel('core/resource_transaction');
            $transaction->addObject($invoice);
            $transaction->addObject($invoice->getOrder());
            $transaction->save();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * Prepare correct statuses for eMAG order on:
     *  - sales_order_save_before
     *
     * Notes:
     *  - eMAG orders allowed states are only: new, processing,
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function orderBeforeSave(Varien_Event_Observer $observer)
    {
        /** @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();

        if (!$order->getEmagOrderId()) {
            return $this;
        }

        // exit if order state is canceled, complete or closed
        if (
            $order->isStateProtected($order->getState())
            || $order->getState() == Mage_Sales_Model_Order::STATE_CANCELED
        ) {
            return $this;
        }

        // for edit mode order state and status is not available; apply it
        if (!$order->getState()) {
            $order->setState(
                Mage_Sales_Model_Order::STATE_PROCESSING,
                Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_IN_PROGRESS
            );
        }

        /** @var $model Innobyte_EmagMarketplace_Model_Sales_Order */
        $model = Mage::getModel('innobyte_emag_marketplace/sales_order');
        $allowedOrderStates = $model->getAllowedOrderStates();
        if (!in_array($order->getState(), $allowedOrderStates)) {
            Mage::throwException(
                $this->_getHelper()->__(
                    'Order state "%s" is not valid for eMAG orders!', $order->getState()
                )
            );
        }

        //TODO: check if status is set correctly after performing a magento default action; if not set is manually
        //if (!in_array($order->getStatus(), $model->getEmagOrderStatuses())) {
        //    $order->setStatus($order->getOrigData('status'));
        //}

        if (
            !$order->isCanceled()
            && !$order->canUnhold()
            && !$order->canInvoice()
            && !$order->canShip()
        ) {
            if (
                0 == $order->getBaseGrandTotal()
                || $order->canCreditmemo()
            ) {
                $emagOrderStatus = $this->getConfig()
                    ->getStateDefaultStatus(Mage_Sales_Model_Order::STATE_COMPLETE);
            } /**
             * Order can be closed just in case when we have refunded amount.
             * In case of "0" grand total order checking ForcedCanCreditmemo flag
             */
            elseif (
                floatval($order->getTotalRefunded())
                || (!$order->getTotalRefunded()
                    && $order->hasForcedCanCreditmemo())
            ) {
                $emagOrderStatus = Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_RETURNED;
            }

            Mage::register('emag_order_status', $emagOrderStatus);
        }

        return $this;
    }

    /**
     * Set eMAG order status as "emag_returned" for full refund on:
     * - sales_order_status_load_after
     *
     * @see method _checkState() from Mage_Sales_Model_Order for more info
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function updateOrderStatus(Varien_Event_Observer $observer)
    {
        /** @var $status Mage_Sales_Model_Order_Status */
        $status = $observer->getEvent()->getOrderStatus();

        //set order status to "emag_returned" if credit memo is total refund
        $orderStatus = Mage::registry('emag_order_status');
        if ($orderStatus) {
            $status->setStatus($orderStatus);
        }

        return $this;
    }

    /**
     * Save attribute data for order on:
     *  - sales_order_save_after
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function orderAfterSave(Varien_Event_Observer $observer)
    {
        /** @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();

        // skip if not eMAG order
        if (!$order->getEmagOrderId()) {
            return $this;
        }

        if ($order instanceof Mage_Core_Model_Abstract) {
            Mage::getModel('innobyte_emag_marketplace/sales_order')
                ->saveAttributeData($order);

            Mage::getModel('innobyte_emag_marketplace/sales_order_voucher')
                ->saveVoucherData($order);
        }

        // update eMAG order only if state is processing
        if ($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING) {
            if (!Mage::registry('is_emag_order_updated')) {
                $this->_updateEmagOrder($order);
            }
        }

        return $this;
    }

    /**
     * Save item custom data for order item on:
     *  - sales_order_item_save_after
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function orderItemAfterSave(Varien_Event_Observer $observer)
    {
        /** @var $item Mage_Sales_Model_Order_Item */
        $item = $observer->getEvent()->getItem();

        // skip if not eMAG order
        if (!$item->getOrder()->getEmagOrderId()) {
            return $this;
        }

        if ($item instanceof Mage_Core_Model_Abstract) {
            Mage::getModel('innobyte_emag_marketplace/sales_order_item')
                ->saveAttributeData($item);
        }

        return $this;
    }

    /**
     * Attach attribute data to quote on:
     *  - sales_quote_load_after
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function quoteAfterLoad(Varien_Event_Observer $observer)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getQuote();
        if ($quote instanceof Mage_Core_Model_Abstract) {
            Mage::getModel('innobyte_emag_marketplace/sales_quote')
                ->load($quote->getId())
                ->attachAttributeData($quote);

            // skip if not eMAG order
            if (!$quote->getEmagOrderId()) {
                return $this;
            }

            // attach eMAG vouchers
            Mage::getModel('innobyte_emag_marketplace/sales_quote_voucher')
                ->attachVoucherData($quote);
        }

        return $this;
    }

    /**
     * Attach item custom data to quote item on:
     *  - sales_quote_item_collection_products_after_load
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function orderItemCollectionAfterLoad(Varien_Event_Observer $observer)
    {
        $collection = $observer->getEvent()->getOrderItemCollection();
        if ($collection instanceof Varien_Data_Collection_Db) {
            Mage::getModel('innobyte_emag_marketplace/sales_order_item')
                ->attachDataToCollection($collection);
        }

        return $this;
    }

    /**
     * Attach attribute invoice and voucher data to order on:
     *  - sales_order_load_after
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function orderAfterLoad(Varien_Event_Observer $observer)
    {
        /** @var $quote Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof Mage_Core_Model_Abstract) {
            // attach attribute data
            Mage::getModel('innobyte_emag_marketplace/sales_order')
                ->load($order->getId())
                ->attachAttributeData($order);

            // skip if not eMAG order
            if (!$order->getEmagOrderId()) {
                return $this;
            }

            // attach third party invoice data
            Mage::getModel('innobyte_emag_marketplace/sales_invoice')
                ->attachInvoiceData($order);

            // attach eMAG vouchers
            Mage::getModel('innobyte_emag_marketplace/sales_order_voucher')
                ->attachVoucherData($order);
        }

        return $this;
    }

    /**
     * Attach attribute and voucher data to invoice on:
     *  - sales_order_invoice_load_after
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function invoiceAfterLoad(Varien_Event_Observer $observer)
    {
        /** @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $observer->getEvent()->getInvoice();
        if ($invoice instanceof Mage_Core_Model_Abstract) {
            // attach attribute data
            Mage::getModel('innobyte_emag_marketplace/sales_order')
                ->load($invoice->getId())
                ->attachAttributeData($invoice);

            // attach eMAG vouchers
            $invoice->addData(array($this->_getVouchersAttributeName() => $invoice->getOrder()->getEmagVouchers()));
        }

        return $this;
    }

    /**
     * Attach attribute and voucher data to creditmemo on:
     *  - sales_order_creditmemo_load_after
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function creditmemoAfterLoad(Varien_Event_Observer $observer)
    {
        /** @var $creditmemo Mage_Sales_Model_Order_Invoice */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if ($creditmemo instanceof Mage_Core_Model_Abstract) {
            // attach attribute data
            Mage::getModel('innobyte_emag_marketplace/sales_order')
                ->load($creditmemo->getId())
                ->attachAttributeData($creditmemo);

            // attach eMAG vouchers
            $creditmemo->addData(array($this->_getVouchersAttributeName() => $creditmemo->getOrder()->getEmagVouchers()));
        }

        return $this;
    }

    /**
     * Save attribute data for quote address on:
     *  - sales_quote_address_save_after
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function quoteAddressAfterSave(Varien_Event_Observer $observer)
    {
        /** @var $quoteAddress Mage_Sales_Model_Quote_Address */
        $quoteAddress = $observer->getEvent()->getQuoteAddress();

        // skip if not eMAG order
        if (!$quoteAddress->getQuote()->getEmagOrderId()) {
            return $this;
        }

        if ($quoteAddress instanceof Mage_Core_Model_Abstract) {
            Mage::getModel('innobyte_emag_marketplace/sales_quote_address')
                ->saveAttributeData($quoteAddress);
        }

        return $this;
    }

    /**
     * Save attribute data for order address on:
     *  - sales_order_address_save_after
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function orderAddressAfterSave(Varien_Event_Observer $observer)
    {
        /** @var $orderAddress Mage_Sales_Model_Order_Address */
        $orderAddress = $observer->getEvent()->getAddress();

        // skip if not eMAG order
        if (!$orderAddress->getOrder()->getEmagOrderId()) {
            return $this;
        }

        if ($orderAddress instanceof Mage_Core_Model_Abstract) {
            Mage::getModel('innobyte_emag_marketplace/sales_order_address')
                ->saveAttributeData($orderAddress);
        }

        return $this;
    }

    /**
     * Attach attribute data to quote collection on:
     *  - sales_quote_address_collection_load_after
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function quoteAddressCollectionAfterLoad(Varien_Event_Observer $observer)
    {
        $collection = $observer->getEvent()->getQuoteAddressCollection();
        if ($collection instanceof Varien_Data_Collection_Db) {
            Mage::getModel('innobyte_emag_marketplace/sales_quote_address')
                ->attachDataToCollection($collection);
        }

        return $this;
    }

    /**
     * Attach attribute data to order collection on:
     *  - sales_order_address_collection_load_after
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function orderAddressCollectionAfterLoad(Varien_Event_Observer $observer)
    {
        $collection = $observer->getEvent()->getOrderAddressCollection();
        if ($collection instanceof Varien_Data_Collection_Db) {
            Mage::getModel('innobyte_emag_marketplace/sales_order_address')
                ->attachDataToCollection($collection);
        }

        return $this;
    }

    /**
     * Convert customer to quote on:
     *  - core_copy_fieldset_customer_account_to_quote
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function convertCustomerAccountToQuote(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset($observer, self::CUSTOMER);

        return $this;
    }

    /**
     * Convert quote to order on:
     *  - core_copy_fieldset_sales_convert_quote_to_order
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function convertSalesConvertQuoteToOrder(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset($observer, self::CUSTOMER);

        return $this;
    }

    /**
     * Convert quote to customer on:
     *  - core_copy_fieldset_checkout_onepage_quote_to_customer
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function convertCheckoutOnepageQuoteToCustomer(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset($observer, self::CUSTOMER);

        return $this;
    }

    /**
     * Convert order to quote on:
     *  - core_copy_fieldset_sales_copy_order_to_edit
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function convertSalesCopyOrderToEdit(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset($observer, self::CUSTOMER);

        return $this;
    }

    /**
     * Attach voucher data from order to quote on (order edit):
     *  - core_copy_fieldset_sales_copy_order_to_edit
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function convertSalesCopyOrderVoucherToEdit(Varien_Event_Observer $observer)
    {
        /** @var $source Mage_Sales_Model_Order */
        $source = $observer->getEvent()->getSource();

        // skip if not eMAG order
        if (!$source->getEmagOrderId()) {
            return $this;
        }

        /** @var $target Mage_Sales_Model_Quote */
        $target = $observer->getEvent()->getTarget();
        $attribute = $this->_getVouchersAttributeName();

        $target->setData($attribute, $source->getData($attribute));

        return $this;
    }

    /**
     * Convert customer address to quote address on:
     *  - core_copy_fieldset_customer_address_to_quote_address
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function convertCustomerAddressToQuoteAddress(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset($observer, self::CUSTOMER_ADDRESS);

        return $this;
    }

    /**
     * Convert quote address to order address on:
     *  - core_copy_fieldset_sales_convert_quote_address_to_order_address
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function convertSalesConvertQuoteAddressToOrderAddress(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset($observer, self::CUSTOMER_ADDRESS);

        return $this;
    }

    /**
     * Convert quote address to customer address on:
     *  - core_copy_fieldset_sales_convert_quote_address_to_customer_address
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function convertQuoteAddressToCustomerAddress(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset($observer, self::CUSTOMER_ADDRESS);

        return $this;
    }

    /**
     * Convert order billing address to quote billing address on:
     *  - core_copy_fieldset_sales_copy_order_billing_address_to_order
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function convertSalesCopyOrderBillingAddressToOrder(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset($observer, self::CUSTOMER_ADDRESS);

        return $this;
    }

    /**
     * Convert order shipping address to quote shipping address on:
     *  - core_copy_fieldset_sales_copy_order_shipping_address_to_order
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function convertSalesCopyOrderShippingAddressToOrder(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset($observer, self::CUSTOMER_ADDRESS);

        return $this;
    }

    /**
     * Copy fieldset
     *
     * @param Varien_Event_Observer $observer
     * @param string $type
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    protected function _copyFieldset(Varien_Event_Observer $observer, $type)
    {
        $source = $observer->getEvent()->getSource();
        $target = $observer->getEvent()->getTarget();

        if ($source instanceof Mage_Core_Model_Abstract && $target instanceof Mage_Core_Model_Abstract) {
            if ($type == self::CUSTOMER) {
                /** @var $model Innobyte_EmagMarketplace_Model_Customer_Attributes */
                $model = Mage::getModel('innobyte_emag_marketplace/customer_attributes');
                $model->setEntityType(self::CUSTOMER);
                $attributes = $model->getCustomerAttributes();
            } else if ($type == self::CUSTOMER_ADDRESS) {
                /** @var $model Innobyte_EmagMarketplace_Model_Customer_Attributes */
                $model = Mage::getModel('innobyte_emag_marketplace/customer_attributes');
                $model->setEntityType(self::CUSTOMER_ADDRESS);
                $attributes = $model->getCustomerAddressAttributes();
            } else {
                return $this;
            }

            foreach ($attributes as $attribute) {
                $target->setData($attribute, $source->getData($attribute));
            }
        }

        return $this;
    }

    /**
     * Prepare order invoice
     *
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Invoice
     * @throws Innobyte_EmagMarketplace_Exception
     */
    private function _prepareInvoice($order)
    {
        try {
            if (!$order->canInvoice()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    $this->_getHelper()->__(
                        'Cannot create invoice for eMAG order #%s', $order->getEmagOrderId()
                    )
                );
            }

            /** @var $model Mage_Sales_Model_Service_Order */
            $model = Mage::getModel('sales/service_order', $order);

            /** @var $invoice Mage_Sales_Model_Order_Invoice */
            $invoice = $model->prepareInvoice();

            if (!$invoice->getTotalQty()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Cannot create an invoice without products!'
                );
            }

            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
            $invoice->register();
        } catch (Innobyte_EmagMarketplace_Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception($e->getMessage());
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR, $this->_getHelper()->getResourceLogFile('order'), true);
            throw new Innobyte_EmagMarketplace_Exception(
                $this->_getHelper()->__(
                    'There was an error while creating order invoice: %s', $e->getMessage()
                )
            );
        }

        return $invoice;
    }

    /**
     * Make API call to update eMAG order
     *
     * @param Mage_Sales_Model_Order $sales
     * @return $this
     * @throws Innobyte_EmagMarketplace_Exception
     * @throws Mage_Core_Exception
     */
    protected function _updateEmagOrder(Mage_Sales_Model_Order $sales)
    {
        /** @var $order Innobyte_EmagMarketplace_Model_Order_Convert_Magento */
        $order = Mage::getModel('innobyte_emag_marketplace/order_convert_magento');
        $order->setOrder($sales);
        $order->convert();

        return $this;
    }

    /**
     * Add eMAG buttons to order view page on:
     *  - adminhtml_widget_container_html_before
     *
     * @param Varien_Event_Observer $observer
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     */
    public function addEmagButtonsToOrderView(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
            /** @var $order Mage_Sales_Model_Order */
            $order = Mage::registry('current_order');
            if (!$order->getEmagOrderId()) {
                return $this;
            }

            $block->addButton(
                'emag_upload_invoice',
                array(
                    'label' => $this->_getHelper()->__('eMAG Upload Invoice'),
                    'onclick' => "inno.emag_marketplace.showPopup()",
                    'class' => 'go'
                )
            );

            /** @var $model Innobyte_EmagMarketplace_Model_Sales_Order */
            $model = Mage::getModel('innobyte_emag_marketplace/sales_order');

            $message = $this->_getHelper()->__('You are about to mark this order as acknowledged. Are you sure?');
            if ($model->canAcknowledge($order)) {
                $url = $block->getUrl('*/*/emagacknowledge');
                $block->addButton(
                    'emag_acknowledge',
                    array(
                        'label' => $this->_getHelper()->__('eMAG Acknowledge'),
                        'onclick' => "confirmSetLocation('" . $message . "', '" . $url . "')",
                        'class' => 'go'
                    )
                );
            }

            $message = $this->_getHelper()->__('This action will update order in eMAG Marketplace. Are you sure?');
            if ($model->canPrepare($order)) {
                $url = $block->getUrl('*/*/emagprepare');
                $block->addButton(
                    'emag_prepared',
                    array(
                        'label' => $this->_getHelper()->__('eMAG Prepared'),
                        'onclick' => "confirmSetLocation('" . $message . "', '" . $url . "')",
                        'class' => 'go'
                    )
                );
            }

            if ($model->canCancel($order)) {
                $url = $block->getUrl('*/*/emagcancel');
                $block->addButton(
                    'emag_canceled',
                    array(
                        'label' => $this->_getHelper()->__('eMAG Cancel'),
                        'onclick' => "confirmSetLocation('" . $message . "', '" . $url . "')",
                        'class' => 'go'
                    )
                );
            }
        }

        return $this;
    }

}
