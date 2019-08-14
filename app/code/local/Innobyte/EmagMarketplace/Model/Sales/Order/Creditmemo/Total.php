<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Sales_Order_Creditmemo_Total
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Sales_Order_Creditmemo_Total
    extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{

    /**
     * Collect totals for credit memo
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return Innobyte_EmagMarketplace_Model_Sales_Order_Creditmemo_Total
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        // skip if not eMAG order
        if(!$creditmemo->getEmagOrderId()) {
            return $this;
        }

        $emagVouchersValue = 0;
        $baseEmagVouchersValue = 0;

        // skip empty
        $vouchers = $creditmemo->getEmagVouchers();
        if (empty($vouchers) || !is_array($vouchers)) {
            return $this;
        }

        foreach ($vouchers as $voucher) {
            $salePriceInclVat = $voucher['emag_sale_price'] + $voucher['emag_sale_price_vat'];
            $baseSalePriceInclVat = $voucher['base_emag_sale_price'] + $voucher['base_emag_sale_price_vat'];

            $emagVouchersValue += $salePriceInclVat;
            $baseEmagVouchersValue += $baseSalePriceInclVat;
        }

        $grandTotal = $creditmemo->getGrandTotal() + $emagVouchersValue;
        $baseGrandTotal = $creditmemo->getBaseGrandTotal() + $baseEmagVouchersValue;

        $creditmemo->setGrandTotal($grandTotal);
        $creditmemo->setBaseGrandTotal($baseGrandTotal);

        return $this;
    }

}
