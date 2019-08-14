<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Order_Convert_Emag_Update
 *  - convert eMAG order to Magento order
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Order_Convert_Emag_Update
    extends Innobyte_EmagMarketplace_Model_Order_Convert_Emag
{

    /**
     * Get order
     *
     * @return Mage_Sales_Model_Order
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Get billing address
     *
     * @return Mage_Sales_Model_Order_Address
     */
    public function getBillingAddress()
    {
        return $this->getOrder()->getBillingAddress();
    }

    /**
     * Get shipping address
     *
     * @return Mage_Sales_Model_Order_Address
     */
    public function getShippingAddress()
    {
        return $this->getOrder()->getShippingAddress();
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
     * Convert eMAG order to magento order
     */
    public function convert()
    {
        try {
            /** @var $transaction Mage_Core_Model_Resource_Transaction */
            $transaction = Mage::getModel('core/resource_transaction');

            $this->_prepareOrder();
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

            $transaction->addObject($order);

            $transaction->addCommitCallback(array($order, 'save'));
            $transaction->save();
        } catch (Innobyte_EmagMarketplace_Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Innobyte_EmagMarketplace_Exception($e->getMessage());
        }
    }

    /**
     * Prepare order
     *
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Mage_Sales_Model_Order
     */
    protected function _prepareOrder()
    {
        // exit if order is invalid
        if (!$this->getOrder()->getId()) {
            throw new Innobyte_EmagMarketplace_Exception(
                'eMAG order update failed. Invalid Magento Order!'
            );
        }

        $status = $this->getOrderStatus();
        if (!$status) {
            throw new Innobyte_EmagMarketplace_Exception(
                $this->getHelper()->__(
                    'Invalid eMAG order status: %s', $status
                )
            );
        }
        $state = $this->getStatusState($status);

        $this->getOrder()->setState($state);
        $this->getOrder()->setStatus($status);

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

                $sku = $emagProduct['part_number'];
                $productId = (int)$emagProduct['product_id'];

                /** @var $orderItem Mage_Sales_Model_Order_Item */
                $orderItem = Mage::getModel('sales/order_item');
                $items = $this->getOrder()->getAllItems();
                foreach ($items as $item) {
                    if ($item->getProductId() == $productId) {
                        $orderItem = $item;
                        break;
                    }
                }

                $status = (int)$emagProduct['status'];
                // continue to next product if product status is: canceled
                if ($status == self::EMAG_PRODUCT_CANCELED) {
                    $orderItem->isDeleted(true);
                    continue;
                }

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
                    ->setEmagDetails(json_encode($emagProduct['details']))
                    ->setEmagCreated($emagProduct['created'])
                    ->setEmagModified($emagProduct['modified']);

                // add item to order only if it's not already added
                if (!$orderItem->getId()) {
                    $this->getOrder()
                        ->addItem($orderItem);
                }

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
     *  - mark old vouchers as deleted
     *
     * @throws Innobyte_EmagMarketplace_Exception
     * @return Innobyte_EmagMarketplace_Model_Order_Convert_Emag
     */
    protected function _prepareVouchers()
    {
        $vouchers = $this->getOrder()->getEmagVouchers();
        if (is_array($vouchers)) {
            foreach ($vouchers as $voucher) {
                $voucher['isDeleted'] = true;
            }
        }

        return parent::_prepareVouchers();
    }

}
