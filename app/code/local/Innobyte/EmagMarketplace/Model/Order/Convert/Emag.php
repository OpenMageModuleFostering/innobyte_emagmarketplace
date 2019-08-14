<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Order_Convert_Emag
 *  - convert eMAG order to Magento order
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Order_Convert_Emag
    extends Innobyte_EmagMarketplace_Model_Order_Convert_Abstract
{

    /**
     * Global currency code
     *
     * @var null
     */
    protected $_globalCurrencyCode = null;

    /**
     * Base currency
     *
     * @var null
     */
    protected $_baseCurrency = null;

    /**
     * Current currency
     *
     * @var null
     */
    protected $_currentCurrency = null;

    /**
     * Get order status
     *
     * @return bool|string
     */
    public function getOrderStatus()
    {
        $status = $this->getEmagOrder('status');
        if (!array_key_exists($status, $this->_orderStatuses)) {
            return false;
        }

        return $this->_orderStatuses[$status];
    }

    /**
     * Get billing address
     *
     * @return Mage_Sales_Model_Order_Address
     */
    public function getBillingAddress()
    {
        return Mage::getModel('sales/order_address')
            ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING);
    }

    /**
     * Get shipping address
     *
     * @return Mage_Sales_Model_Order_Address
     */
    public function getShippingAddress()
    {
        return Mage::getModel('sales/order_address')
            ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING);
    }

    /**
     * Get payment method
     *
     * @param $method
     * @return bool|string
     */
    public function getPaymentMethod($method)
    {
        if (!array_key_exists($method, $this->_paymentMethods)) {
            return false;
        }

        return $this->_paymentMethods[$method];
    }

    /**
     * Get eMAG order
     *
     * @param null|string $key
     * @throws Innobyte_EmagMarketplace_Exception
     * @return array|string
     */
    public function getEmagOrder($key = null)
    {
        if ($key) {
            if (!array_key_exists($key, $this->_emagOrder)) {
                throw new Innobyte_EmagMarketplace_Exception(
                    $this->getHelper()->__(
                        'Order property "%s" not found!', $key
                    )
                );
            }
            return $this->_emagOrder[$key];
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
            $this->_order = Mage::getModel('sales/order');
        }

        return $this->_order;
    }

    /**
     * Get global currency code
     *
     * @return null|string
     */
    public function getGlobalCurrencyCode()
    {
        if (!$this->_globalCurrencyCode) {
            $this->_globalCurrencyCode = Mage::app()->getBaseCurrencyCode();
        }

        return $this->_globalCurrencyCode;
    }

    /**
     * Get base currency
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getBaseCurrency()
    {
        if (!$this->_baseCurrency) {
            $this->_baseCurrency = $this->getStore()->getBaseCurrency();
        }

        return $this->_baseCurrency;
    }

    /**
     * Get current currency
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getCurrentCurrency()
    {
        if (!$this->_currentCurrency) {
            $this->_currentCurrency = $this->getStore()->getCurrentCurrency();
        }

        return $this->_currentCurrency;
    }

    /**
     * Get payment
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    public function getPayment()
    {
        if (!$this->_payment) {
            $this->_payment = Mage::getModel('sales/order_payment');
        }

        return $this->_payment;
    }

    /**
     * Get eav config
     *
     * @return Mage_Eav_Model_Config
     */
    public function getEavConfig()
    {
        return Mage::getSingleton('eav/config');
    }

    /**
     * Convert eMAG order to magento order
     */
    public function convert()
    {
        try {
            /** @var $transaction Mage_Core_Model_Resource_Transaction */
            $transaction = Mage::getModel('core/resource_transaction');

            $this->_prepareOrder();
            $this->_prepareCurrency();
            $this->_prepareCurrencyRates();
            $this->_prepareProducts();
            $this->_prepareVouchers();
            $this->_prepareCustomer();
            $this->_prepareBillingAddress();
            $this->_prepareShippingAddress();
            $this->_prepareShipping();
            $this->_preparePayment();
            $this->_prepareCustomerComment();

            $order = $this->getOrder();

            Mage::dispatchEvent(
                'innobyte_emag_marketplace_emag_order_save_before',
                array(
                    'order' => $order
                )
            );

            $transaction->addObject($order);

            $transaction->addCommitCallback(array($order, 'place'));
            $transaction->addCommitCallback(array($order, 'save'));
            $transaction->save();

            Mage::dispatchEvent(
                'innobyte_emag_marketplace_emag_order_save_after',
                array(
                    'order' => $order
                )
            );

            $this->acknowledgeEmagOrder($this->getOrder());

        } catch (Innobyte_EmagMarketplace_Exception $e) {
            $this->_restoreItemQty();

            throw new Innobyte_EmagMarketplace_Exception($e->getMessage());
        } catch (Exception $e) {
            $this->_restoreItemQty();

            throw new Innobyte_EmagMarketplace_Exception($e->getMessage());
        }
    }

    /**
     * Prepare order data
     *
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Mage_Sales_Model_Order
     */
    protected function _prepareOrder()
    {
        $reservedOrderId = $this->getEavConfig()
            ->getEntityType('order')
            ->fetchNewIncrementId($this->getStoreId());

        $status = $this->getOrderStatus();
        if (!$status) {
            throw new Innobyte_EmagMarketplace_Exception(
                $this->getHelper()->__(
                    'Invalid eMAG order status: %s', $status
                )
            );
        }

        $emagOrderId = $this->getEmagOrder('id');
        $emagOrderDate = $this->getEmagOrder('date');

        $this->getOrder()
            ->setIncrementId($reservedOrderId)
            ->setStoreId($this->getStoreId())
            ->setQuoteId(0)
            ->setCustomerGroupId(0)
            ->setState(Mage_Sales_Model_Order::STATE_NEW)
            ->setStatus($status)
            ->setEmagOrderId($emagOrderId)
            ->setEmagOrderDate($emagOrderDate)
            ->setCreatedAt($this->_getGmtDate($emagOrderDate))
            ->setUpdatedAt($this->_getGmtDate($emagOrderDate));

        return $this;
    }

    /**
     * Prepare order currency
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Emag
     */
    protected function _prepareCurrency()
    {
        $this->getOrder()
            ->setGlobalCurrencyCode($this->getGlobalCurrencyCode())
            ->setBaseCurrencyCode($this->getBaseCurrency()->getCode())
            ->setStoreCurrencyCode($this->getBaseCurrency()->getCode())
            ->setOrderCurrencyCode($this->getCurrentCurrency()->getCode());

        return $this;
    }

    /**
     * Prepare currency rates
     *
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Emag
     */
    protected function  _prepareCurrencyRates()
    {
        try {
            $this->getOrder()
                ->setBaseToGlobalRate($this->getBaseCurrency()->getRate($this->getGlobalCurrencyCode()))
                ->setBaseToOrderRate($this->getBaseCurrency()->getRate($this->getCurrentCurrency()->getCode()));
        } catch (Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception(
                $this->getHelper()->__(
                    'There was an error while processing currency rates: %s', $e->getMessage()
                )
            );
        }

        return $this;
    }

    /**
     * Prepare products
     *
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Emag
     */
    protected function _prepareProducts()
    {
        try {
            $emagProducts = $this->getEmagOrder('products');
            if (!count($emagProducts)) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'No products found in eMAG order!'
                );
            }

            $subTotal = 0;
            $baseSubTotal = 0;
            $subtotalInclTax = 0;
            $baseSubtotalInclTax = 0;
            $totalQtyOrdered = 0;
            $totalTaxAmount = 0;
            $totalBaseTaxAmount = 0;

            foreach ($emagProducts as $emagProduct) {
                $currency = $emagProduct['currency'];
                if ($currency != $this->getCurrentCurrency()->getCode()) {
                    throw new Innobyte_EmagMarketplace_Exception(
                        'Store currency and eMAG currency codes do not match!'
                    );
                }

                $status = (int)$emagProduct['status'];
                // continue to next product if product status is: canceled
                if ($status == self::EMAG_PRODUCT_CANCELED) {
                    continue;
                }

                $sku = $emagProduct['part_number'];
                $productId = (int)$emagProduct['product_id'];
                /** @var $_product Mage_Catalog_Model_Product */
                $_product = Mage::getModel('catalog/product')->load($productId);

                if (!$_product->getId()) {
                    throw new Innobyte_EmagMarketplace_Exception(
                        $this->getHelper()->__(
                            'Product with ID %s not found. eMAG - SKU: %s', $productId, $sku
                        )
                    );
                }

                $quantity = (float)$emagProduct['quantity'];
                $vat = (float)$emagProduct['vat'];

                $price = (float)$emagProduct['sale_price'];
                $basePrice = (float)$this->_convertPriceToBaseCurrency($price);
                $originalPrice = (float)$price;
                $baseOriginalPrice = (float)$this->_convertPriceToBaseCurrency($originalPrice);
                $taxAmount = (float)(($price * $vat) * $quantity);
                $taxAmount = (float)$this->getStore()->roundPrice($taxAmount);
                $baseTaxAmount = (float)$this->_convertPriceToBaseCurrency($taxAmount);
                $rowTotal = (float)($price * $quantity);
                $rowTotal = (float)$this->getStore()->roundPrice($rowTotal);
                $baseRowTotal = (float)$this->_convertPriceToBaseCurrency($rowTotal);
                $priceInclTax = (float)$price + $taxAmount;
                $priceInclTax = (float)$this->getStore()->roundPrice($priceInclTax);
                $basePriceInclTax = (float)$this->_convertPriceToBaseCurrency($priceInclTax);
                $rowTotalInclTax = (float)$rowTotal + $taxAmount;
                $rowTotalInclTax = (float)$this->getStore()->roundPrice($rowTotalInclTax);
                $baseRowTotalInclTax = (float)$this->_convertPriceToBaseCurrency($rowTotalInclTax);

                /** @var $orderItem Mage_Sales_Model_Order_Item */
                $orderItem = Mage::getModel('sales/order_item');
                $orderItem
                    ->setQuoteItemId(0)
                    ->setQuoteParentItemId(null)
                    ->setProductId($productId)
                    ->setProductType($_product->getTypeId())
                    ->setQtyBackordered(null)
                    ->setTotalQtyOrdered($quantity)
                    ->setQtyOrdered($quantity)
                    ->setName($_product->getName())
                    ->setSku($_product->getSku())
                    ->setWeight($_product->getWeight())
                    ->setPrice($price)
                    ->setBasePrice($basePrice)
                    ->setOriginalPrice($originalPrice)
                    ->setBaseOriginalPrice($baseOriginalPrice)
                    ->setTaxPercent($vat * 100)
                    ->setTaxAmount($taxAmount)
                    ->setBaseTaxAmount($baseTaxAmount)
                    ->setRowTotal($rowTotal)
                    ->setBaseRowTotal($baseRowTotal)
                    ->setPriceInclTax($priceInclTax)
                    ->setBasePriceInclTax($basePriceInclTax)
                    ->setRowTotalInclTax($rowTotalInclTax)
                    ->setBaseTotalInclTax($baseRowTotalInclTax)
                    ->setGiftMessageAvailable(0)
                    ->setBaseWeeeTaxAppliedAmount(0)
                    ->setBaseWeeeTaxAppliedRowAmnt(0)
                    ->setWeeeTaxAppliedAmount(0)
                    ->setWeeeTaxAppliedRowAmount(0)
                    ->setWeeeTaxApplied(serialize(array()))
                    ->setWeeeTaxDisposition(0)
                    ->setWeeeTaxRowDisposition(0)
                    ->setBaseWeeeTaxDisposition(0)
                    ->setBaseWeeeTaxRowDisposition(0)
                    ->setEmagDetails(json_encode($emagProduct['details']))
                    ->setEmagCreated($emagProduct['created'])
                    ->setEmagModified($emagProduct['modified'])
                    ->setCreatedAt($this->_getGmtDate($emagProduct['created']))
                    ->setUpdatedAt($this->_getGmtDate($emagProduct['modified']));

                $this->getOrder()
                    ->addItem($orderItem);

                $this->_registerItemSale($orderItem);

                $subTotal += $rowTotal;
                $baseSubTotal += $baseRowTotal;
                $subtotalInclTax += $rowTotalInclTax;
                $baseSubtotalInclTax += $baseRowTotalInclTax;
                $totalQtyOrdered += $quantity;
                $totalTaxAmount += $taxAmount;
                $totalBaseTaxAmount += $baseTaxAmount;
            }

            $grandTotal = $subTotal + $totalTaxAmount;
            $baseGrandTotal = $this->_convertPriceToBaseCurrency($grandTotal);

            $this->getOrder()
                ->setSubtotal($subTotal)
                ->setBaseSubtotal($baseSubTotal)
                ->setSubtotalInclTax($subtotalInclTax)
                ->setBaseSubtotalInclTax($baseSubtotalInclTax)
                ->setTotalQtyOrdered($totalQtyOrdered)
                ->setTaxAmount($totalTaxAmount)
                ->setBaseTaxAmount($totalBaseTaxAmount)
                ->setGrandTotal($grandTotal)
                ->setBaseGrandTotal($baseGrandTotal);

        } catch (Innobyte_EmagMarketplace_Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception(
                $this->getHelper()->__(
                    'There was an error while processing products: %s', $e->getMessage()
                )
            );
        }

        return $this;
    }

    /**
     * Prepare vouchers
     *
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Emag
     */
    protected function _prepareVouchers()
    {
        try {
            $emagVouchers = $this->getEmagOrder('vouchers');

            $vouchers = array();
            $voucherDiscount = 0;
            $priceColumns = array('sale_price', 'sale_price_vat');
            $columnPrefix = Innobyte_EmagMarketplace_Model_Sales_Voucher_Abstract::COLUMN_PREFIX;
            foreach ($emagVouchers as $emagVoucher) {
                $voucher = array();
                foreach ($emagVoucher as $column => $value) {
                    $voucher[$columnPrefix . '_' . $column] = $value;
                    // add values for base currency
                    if (in_array($column, $priceColumns)) {
                        $voucher['base_' . $columnPrefix . '_' . $column] = $this->_convertPriceToBaseCurrency($value, false);
                    }
                }
                $vouchers[] = $voucher;

                $salePriceInclVat = $emagVoucher['sale_price'] + $emagVoucher['sale_price_vat'];
                $voucherDiscount += $salePriceInclVat;
            }

            $grandTotal = $this->getOrder()->getGrandTotal() + $voucherDiscount;
            $grandTotal = (float)$this->getStore()->roundPrice($grandTotal);
            $baseGrandTotal = $this->_convertPriceToBaseCurrency($grandTotal);

            $this->getOrder()
                ->setEmagVouchers($vouchers)
                ->setGrandTotal($grandTotal)
                ->setBaseGrandTotal($baseGrandTotal);
        } catch (Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception(
                $this->getHelper()->__(
                    'There was an error while processing vouchers: %s', $e->getMessage()
                )
            );
        }

        return $this;
    }

    /**
     * Prepare customer
     *
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Emag
     */
    protected function _prepareCustomer()
    {
        $emagCustomer = $this->getEmagOrder('customer');
        if (empty($emagCustomer)) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Customer not found in eMAG order!'
            );
        }

        $this->getOrder()
            ->setEmagCustomerId($emagCustomer['id'])
            ->setCustomerEmail($this->_prepareCustomerEmail())
            ->setCustomerFirstname($emagCustomer['name'])
            ->setEmagCustomerGender($emagCustomer['gender'])
            ->setCustomerIsGuest(1);

        return $this;
    }

    /**
     * Prepare billing address
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Emag
     */
    protected function _prepareBillingAddress()
    {
        $emagCustomer = $this->getEmagOrder('customer');

        $billingAddress = $this->getBillingAddress()
            ->setFirstname($emagCustomer['name'])
            ->setEmagBank($emagCustomer['bank'])
            ->setEmagIban($emagCustomer['iban'])
            ->setFax($emagCustomer['fax'])
            ->setTelephone($emagCustomer['phone_1'])
            ->setEmagTelephone2($emagCustomer['phone_2'])
            ->setEmagTelephone3($emagCustomer['phone_3'])
            ->setEmagCompanyCode($emagCustomer['code'])
            ->setEmagCompanyRegNo($emagCustomer['registration_number'])
            ->setCompany($emagCustomer['company'])
            ->setCountryId($emagCustomer['billing_country'])
            ->setRegion($emagCustomer['billing_suburb'])
            ->setEmagLocalityId($emagCustomer['billing_locality_id'])
            ->setCity($emagCustomer['billing_city'])
            ->setStreet($emagCustomer['billing_street'])
            ->setPostcode($emagCustomer['billing_postal_code'])
            ->setEmagIsVatPayer($emagCustomer['is_vat_payer'])
            ->setEmagLegalEntity($emagCustomer['legal_entity']);
        $matchedRegion = $this->getHelper()->getMagentoRegion(
            $emagCustomer['billing_suburb'],
            $emagCustomer['billing_country']
        );
        if (!is_null($matchedRegion) && $matchedRegion->getId()) {
            $billingAddress->setRegionId($matchedRegion->getId());
            $billingAddress->setRegion($matchedRegion->getName());
        }

        $this->getOrder()
            ->setBillingAddress($billingAddress);

        return $this;
    }

    /**
     * Prepare shipping address
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Emag
     */
    protected function _prepareShippingAddress()
    {
        $emagCustomer = $this->getEmagOrder('customer');

        $shippingAddress = $this->getShippingAddress()
            ->setFirstname($emagCustomer['name'])
            ->setEmagBank($emagCustomer['bank'])
            ->setEmagIban($emagCustomer['iban'])
            ->setFax($emagCustomer['fax'])
            ->setTelephone($emagCustomer['phone_1'])
            ->setEmagTelephone2($emagCustomer['phone_2'])
            ->setEmagTelephone3($emagCustomer['phone_3'])
            ->setEmagCompanyCode($emagCustomer['code'])
            ->setEmagCompanyRegNo($emagCustomer['registration_number'])
            ->setCompany($emagCustomer['company'])
            ->setCountryId($emagCustomer['shipping_country'])
            ->setRegion($emagCustomer['shipping_suburb'])
            ->setEmagLocalityId($emagCustomer['shipping_locality_id'])
            ->setCity($emagCustomer['shipping_city'])
            ->setStreet($emagCustomer['shipping_street'])
            ->setPostcode($emagCustomer['shipping_postal_code'])
            ->setEmagIsVatPayer($emagCustomer['is_vat_payer'])
            ->setEmagLegalEntity($emagCustomer['legal_entity']);
        $matchedRegion = $this->getHelper()->getMagentoRegion(
            $emagCustomer['shipping_suburb'],
            $emagCustomer['shipping_country']
        );
        if (!is_null($matchedRegion) && $matchedRegion->getId()) {
            $shippingAddress->setRegionId($matchedRegion->getId());
            $shippingAddress->setRegion($matchedRegion->getName());
        }

        $this->getOrder()
            ->setShippingAddress($shippingAddress);

        return $this;
    }

    /**
     * Prepare shipping
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Emag
     * @throws Innobyte_EmagMarketplace_Exception
     */
    protected function _prepareShipping()
    {
        try {
            $shippingAmount = $this->getEmagOrder('shipping_tax');
            $baseShippingAmount = $this->_convertPriceToBaseCurrency($shippingAmount);

            $grandTotal = $this->getOrder()->getGrandTotal() + $shippingAmount;
            $baseGrandTotal = $this->getOrder()->getBaseGrandTotal() + $baseShippingAmount;

            /** @var $carrier Innobyte_EmagMarketplace_Model_Shipping_Carrier_Emag */
            $carrier = Mage::getModel('innobyte_emag_marketplace/shipping_carrier_emag');
            /** @var $request Mage_Shipping_Model_Rate_Request */
            $request = Mage::getModel('shipping/rate_request');
            $rates = $carrier->collectRates($request);

            if ($rates) {
                $rate = current($rates->getAllRates());
                $this->getOrder()
                    ->setShippingMethod($rate->getCarrier() . '_' . $rate->getMethod())
                    ->setShippingDescription(trim($rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle(), '-'));
            }

            $this->getOrder()
                ->setShippingAmount($shippingAmount)
                ->setBaseShippingAmount($baseShippingAmount)
                ->setGrandTotal($grandTotal)
                ->setBaseGrandTotal($baseGrandTotal);
        } catch (Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception(
                $this->getHelper()->__(
                    'Unable to apply shipping method. ERROR: %s', $e->getMessage()
                )
            );
        }

        return $this;
    }

    /**
     * Prepare payment
     *
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Emag
     */
    protected function _preparePayment()
    {
        try {
            $paymentStatus = $this->getEmagOrder('payment_status');
            $paymentMethod = $this->getEmagOrder('payment_mode_id');

            $method = $this->getPaymentMethod($paymentMethod);
            if (!$method) {
                throw new Innobyte_EmagMarketplace_Exception(
                    $this->getHelper()->__(
                        'Invalid eMag payment method: %s', $method
                    )
                );
            }

            $payment = $this->getPayment();
            $payment->setStoreId($this->getStoreId())
                ->setCustomerPaymentId(0)
                ->setMethod($method);

            $this->getOrder()
                ->setEmagPaymentStatus($paymentStatus)
                ->setPayment($payment);
        } catch (Innobyte_EmagMarketplace_Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception(
                $this->getHelper()->__(
                    'There was an error while processing payment: %s', $e->getMessage()
                )
            );
        }

        return $this;
    }

    /**
     * Prepare customer comment
     *
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Emag
     */
    protected function _prepareCustomerComment()
    {
        $comment = $this->getEmagOrder('observation');

        $this->getOrder()
            ->setEmagCustomerComment($comment);

        return $this;
    }

    /**
     * Convert price to base currency
     *
     * @param float $price
     * @param bool $round
     * @return float
     * @throws Innobyte_EmagMarketplace_Exception
     */
    protected function _convertPriceToBaseCurrency($price, $round = true)
    {
        try {
            $baseCurrency = $this->getBaseCurrency()->getCode();
            $currentCurrency = $this->getCurrentCurrency()->getCode();

            $rates = $this->_getCurrencyRates($baseCurrency);
            $price = $price / $rates[$currentCurrency];

            if ($round) {
                return $this->getStore()->roundPrice($price);
            }

            return $price;
        } catch (Innobyte_EmagMarketplace_Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception('Invalid currency settings. Please check you currency rates.');
        }
    }

    /**
     * Get currency rates
     *
     * @param $currency
     * @return array
     */
    protected function _getCurrencyRates($currency)
    {
        /** @var $model Mage_Directory_Model_Currency */
        $model = Mage::getModel('directory/currency');
        $allowedCurrencies = $model->getConfigAllowCurrencies();
        $rates = $model->getCurrencyRates($currency, $allowedCurrencies);

        return $rates;
    }

    /**
     * Prepare customer email
     *
     * @return string
     */
    private function _prepareCustomerEmail()
    {
        return 'customer.eMAG.'
        . $this->getOrder()->getEmagOrderId()
        . '@' . $this->getHelper()->getEmagDomain($this->getStore()->getId());
    }

    /**
     * Decrease magento stocks for current item
     *
     * @param $item
     */
    private function _registerItemSale(Mage_Sales_Model_Order_Item $item)
    {
        Mage::getSingleton('cataloginventory/stock')->registerItemSale($item);
    }

    /**
     * Restore order item quantities
     */
    private function _restoreItemQty()
    {
        /** @var $item Mage_Sales_Model_Order_Item */
        foreach ($this->getOrder()->getAllItems() as $item) {
            Mage::getSingleton('cataloginventory/stock')->backItemQty($item->getProductId(), $item->getQtyOrdered());
        }
    }

    /**
     * Acknowledge eMAG order
     *
     * @param Mage_Sales_Model_Order $order
     * @return Innobyte_EmagMarketplace_Model_Sales_Observer
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function acknowledgeEmagOrder(Mage_Sales_Model_Order $order)
    {
        /** @var $response Innobyte_EmagMarketplace_Model_Api_Response */
        $response = $this->getOrderApiModel()
            ->setStoreId($order->getStoreId())
            ->setEmagOrderId($order->getEmagOrderId())
            ->acknowledge();

        if ($response->isError()) {
            throw new Innobyte_EmagMarketplace_Exception(
                $this->getHelper()->__(
                    'There was an error while trying to acknowledge eMAG order: %s',
                    implode(',', $response->getMessages())
                )
            );
        }

        $this->reReadEmagOrder($order);

        return $this;
    }

    /**
     * Get GMT date
     *
     * @param $time
     * @return bool|string
     */
    protected function _getGmtDate($time)
    {
        $timezone = new DateTimeZone(
            Mage::app()->getStore($this->getOrder()->getStore())
                ->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)
        );
        $date = new DateTime($time, $timezone);

        return date('Y-m-d H:i:s', $date->getTimestamp());
    }

}
