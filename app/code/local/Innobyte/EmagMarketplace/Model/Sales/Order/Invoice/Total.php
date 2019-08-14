<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Sales_Order_Invoice_Total
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Sales_Order_Invoice_Total
    extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{

    /**
     * Collect totals for invoice
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Innobyte_EmagMarketplace_Model_Sales_Order_Invoice_Total
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        // skip if not eMAG order
        if(!$invoice->getEmagOrderId()) {
            return $this;
        }

        $emagVouchersValue = 0;
        $baseEmagVouchersValue = 0;

        // skip empty
        $vouchers = $invoice->getEmagVouchers();
        if (empty($vouchers) || !is_array($vouchers)) {
            return $this;
        }

        foreach ($vouchers as $voucher) {
            $emagVouchersValue += $voucher['emag_sale_price'];
            $baseEmagVouchersValue += $voucher['base_emag_sale_price'];
        }

        $grandTotal = $invoice->getGrandTotal() + $emagVouchersValue;
        $baseGrandTotal = $invoice->getBaseGrandTotal() + $baseEmagVouchersValue;

        $invoice->setGrandTotal($grandTotal);
        $invoice->setBaseGrandTotal($baseGrandTotal);

        return $this;
    }

}
