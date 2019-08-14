<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Invoice_Pdf_Total_Voucher
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Invoice_Pdf_Total_Voucher extends Mage_Sales_Model_Order_Pdf_Total_Default
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
     * Add eMAG voucher to pdf totals
     *
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $vouchers = $this->getVouchers();
        if (empty($vouchers)) {
            return array();
        }

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        $total = array();
        foreach ($vouchers as $voucher) {
            $total[] = array(
                'label' => $voucher['emag_voucher_name'] . ':',
                'amount' => $this->getOrder()->formatPriceTxt($voucher['emag_sale_price']),
                'font_size' => $fontSize,
            );
        }

        return $total;
    }

    /**
     * @return array
     */
    public function getVouchers()
    {
        return $this->getSource()->getDataUsingMethod($this->getSourceField());
    }

}
