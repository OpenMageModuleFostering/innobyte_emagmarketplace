<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Sales_Quote_Address_Total_Voucher
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Sales_Quote_Address_Total_Voucher
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    /**
     * Collect eMAG vouchers address amount
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Innobyte_EmagMarketplace_Model_Sales_Quote_Address_Total_Voucher
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        // skip if not eMAG order
        if (!$address->getQuote()->getEmagOrderId()) {
            return $this;
        }

        //skip billing address
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $emagVouchersValue = 0;
        $baseEmagVouchersValue = 0;

        // skip empty
        $vouchers = $address->getQuote()->getEmagVouchers();
        if (empty($vouchers) || !is_array($vouchers)) {
            return $this;
        }

        foreach ($vouchers as $voucher) {
            $emagVouchersValue += $voucher['emag_sale_price'];
            $baseEmagVouchersValue += $voucher['base_emag_sale_price'];
        }

        $grandTotal = $address->getGrandTotal() + $emagVouchersValue;
        $baseGrandTotal = $address->getGrandTotal() + $baseEmagVouchersValue;

        $address->setGrandTotal($grandTotal);
        $address->setBaseGrandTotal($baseGrandTotal);

        return $this;
    }

    /**
     * Add eMAG vouchers information to address
     *  - this method only added emag_voucher code in subtotals
     *  - voucher value and title are handled in:
     * app/design/adminhtml/default/default/template/innobyte/emag_marketplace/sales/order/create/totals/voucher.phtml
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Innobyte_EmagMarketplace_Model_Sales_Quote_Address_Total_Voucher
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        // skip if not eMAG order
        if (!$address->getQuote()->getEmagOrderId()) {
            return $this;
        }

        $vouchers = $address->getQuote()->getEmagVouchers();
        if (empty($vouchers) || !is_array($vouchers)) {
            return $this;
        }

        $address->addTotal(array(
            'code' => 'emag_vouchers',
            'title' => '',
            'value' => ''
        ));

        return $this;
    }

}
