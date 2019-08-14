<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
abstract class Innobyte_EmagMarketplace_Model_Order_Convert_Abstract extends Varien_Object
{

    /**
     * eMAG order statuses
     */
    const EMAG_STATUS_CANCELED = 0;
    const EMAG_STATUS_NEW = 1;
    const EMAG_STATUS_IN_PROGRESS = 2;
    const EMAG_STATUS_PREPARED = 3;
    const EMAG_STATUS_FINALIZED = 4;
    const EMAG_STATUS_RETURNED = 5;

    /**
     * Order statuses
     */
    const STATUS_CANCELED = 'emag_canceled';
    const STATUS_NEW = 'emag_new';
    const STATUS_IN_PROGRESS = 'emag_in_progress';
    const STATUS_PREPARED = 'emag_prepared';
    const STATUS_FINALIZED = 'emag_finalized';
    const STATUS_RETURNED = 'emag_returned';

    /**
     * eMAG payment modes
     */
    const EMAG_PAYMENT_UNKNOWN = '';
    const EMAG_PAYMENT_CASHONDELIVERY = 1;
    const EMAG_PAYMENT_BANKTRANSFER = 2;
    const EMAG_PAYMENT_CREDITCARD = 3;

    /**
     * eMAG payment statuses
     */
    const EMAG_PAYMENT_STATUS_NOT_PAID = 0;
    const EMAG_PAYMENT_STATUS_PAID = 1;

    /**
     * Product status in order
     */
    const EMAG_PRODUCT_CANCELED = 0;
    const EMAG_PRODUCT_ACTIVE = 1;

    /**
     * Order statuses
     *
     * @var array
     */
    protected $_orderStatuses = array(
        self::EMAG_STATUS_CANCELED => self::STATUS_CANCELED,
        self::EMAG_STATUS_NEW => self::STATUS_NEW,
        self::EMAG_STATUS_IN_PROGRESS => self::STATUS_IN_PROGRESS,
        self::EMAG_STATUS_PREPARED => self::STATUS_PREPARED,
        self::EMAG_STATUS_FINALIZED => self::STATUS_FINALIZED,
        self::EMAG_STATUS_RETURNED => self::STATUS_RETURNED
    );

    protected $_statusState = array(
        self::STATUS_NEW => Mage_Sales_Model_Order::STATE_NEW,
        self::STATUS_IN_PROGRESS => Mage_Sales_Model_Order::STATE_PROCESSING,
        self::STATUS_PREPARED => Mage_Sales_Model_Order::STATE_PROCESSING,
        self::STATUS_FINALIZED => Mage_Sales_Model_Order::STATE_PROCESSING,
        self::STATUS_CANCELED => Mage_Sales_Model_Order::STATE_PROCESSING,
        self::STATUS_RETURNED => Mage_Sales_Model_Order::STATE_CLOSED
    );

    /**
     * Payment modes
     *
     * @var array
     */
    protected $_paymentMethods = array(
        self::EMAG_PAYMENT_UNKNOWN => Innobyte_EmagMarketplace_Model_Payment_Method_Unknown::EMAG_UNKNOWN,
        self::EMAG_PAYMENT_CASHONDELIVERY => Innobyte_EmagMarketplace_Model_Payment_Method_Cashondelivery::EMAG_CASHONDELIVERY,
        self::EMAG_PAYMENT_BANKTRANSFER => Innobyte_EmagMarketplace_Model_Payment_Method_Banktransfer::EMAG_BANKTRANSFER,
        self::EMAG_PAYMENT_CREDITCARD => Innobyte_EmagMarketplace_Model_Payment_Method_Cc::EMAG_CREDITCARD
    );

    /**
     * Store
     *
     * @var null|Mage_Core_Model_Store
     */
    protected $_store = null;

    /**
     * Store id
     *
     * @var null|int
     */
    protected $_storeId = null;

    /**
     * Order id
     *
     * @var null
     */
    protected $_orderId = null;

    /**
     * Order
     *
     * @var null|Mage_Sales_Model_Order
     */
    protected $_order = null;

    /**
     * @var null|Mage_Sales_Model_Order_Payment
     */
    protected $_payment = null;

    /**
     * eMAG order
     *
     * @var array
     */
    protected $_emagOrder = array();

    /**
     * Debug mode
     *
     * @var null|bool
     */
    protected $_debug = null;

    /**
     * Set order id
     *
     * @param $orderId
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    public function setOrderId($orderId)
    {
        $this->_orderId = $orderId;

        return $this;
    }

    /**
     * Set order
     *
     * @param Mage_Sales_Model_Order $order
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Magento
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        $this->_orderId = $order->getId();

        return $this;
    }

    /**
     * Get order status
     */
    abstract public function getOrderStatus();

    public function getStatusState($status)
    {
        if (!isset($this->_statusState[$status])) {
            return false;
        }

        return $this->_statusState[$status];
    }

    /**
     * Get eMAG order statuses
     *
     * @return array
     */
    public function getEmagOrderStatuses()
    {
        return $this->_orderStatuses;
    }

    /**
     * Get payment method
     *
     * @param $method
     */
    abstract public function getPaymentMethod($method);

    /**
     * Set eMAG order
     *
     * @param $emagOrder
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
     */
    public function setEmagOrder($emagOrder)
    {
        $this->_emagOrder = $emagOrder;

        return $this;
    }

    /**
     * Debug mode
     *
     * @return bool|null
     */
    protected function _isDebug()
    {
        if (!$this->_debug) {
            $this->_debug = $this->getHelper()->isDebug();
        }

        return $this->_debug;
    }

    /**
     * Get eMAG marketplace data helper
     *
     * @return Innobyte_EmagMarketplace_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('innobyte_emag_marketplace');
    }

    /**
     * @return Innobyte_EmagMarketplace_Model_Api_Order
     */
    public function getOrderApiModel()
    {
        return Mage::getModel('innobyte_emag_marketplace/api_order');
    }

    /**
     * Set store
     *
     * @param $store Mage_Core_Model_Store
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
     */
    public function setStore($store)
    {
        $this->_store = $store;
        $this->_storeId = $store->getId();

        return $this;
    }

    /**
     * Get store
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return $this->_store;
    }

    /**
     * Set store id
     *
     * @param $storeId
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;

        return $this;
    }

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * Get order
     *
     * @return Mage_Sales_Model_Order
     */
    abstract public function getOrder();

    /**
     * Get payment
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    abstract public function getPayment();

    /**
     * Convert eMAG order to magento order
     */
    abstract public function convert();

    /**
     * Prepare order data
     *
     * @return Mage_Sales_Model_Order
     */
    abstract protected function _prepareOrder();

    /**
     * Prepare products
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
     */
    abstract protected function _prepareProducts();

    /**
     * Prepare vouchers
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
     */
    abstract protected function _prepareVouchers();

    /**
     * Prepare customer
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
     */
    abstract protected function _prepareCustomer();

    /**
     * Prepare billing address
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
     */
    abstract protected function _prepareBillingAddress();

    /**
     * Prepare shipping address
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
     */
    abstract protected function _prepareShippingAddress();

    /**
     * Prepare shipping
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
     */
    abstract protected function _prepareShipping();

    /**
     * Prepare payment
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
     */
    abstract protected function _preparePayment();

    /**
     * Prepare customer comment
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
     */
    abstract protected function _prepareCustomerComment();

    /**
     * Re-read eMAG order
     *
     * @param Mage_Sales_Model_Order $sales
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function reReadEmagOrder(Mage_Sales_Model_Order $sales)
    {
        Mage::register('is_emag_order_updated', true);

        /** @var $store Mage_Core_Model_Store */
        $store = $sales->getStore();

        $data = array(
            'id' => $sales->getEmagOrderId()
        );

        try {
            /** @var $response Innobyte_EmagMarketplace_Model_Api_Response */
            $response = $this->getOrderApiModel()
                ->setStoreId($sales->getStoreId())
                ->setData($data)
                ->read();

            if ($response->isError()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    $this->getHelper()->__(
                        'eMAG Api Response => %s', implode(',', $response->getMessages())
                    )
                );
            }

            $emagOrders = $response->getResults();
            foreach ($emagOrders as $emagOrder) {
                /** @var $order Innobyte_EmagMarketplace_Model_Order_Convert_Emag_Update */
                $order = Mage::getModel('innobyte_emag_marketplace/order_convert_emag_update');
                $order->setOrder($sales);
                $order->setStore($store);
                $order->setEmagOrder($emagOrder);
                $order->convert();
            }
        } catch (Innobyte_EmagMarketplace_Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception($e->getMessage());
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }

        return $this;
    }

}
