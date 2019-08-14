<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Sales_Voucher_Abstract
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
abstract class Innobyte_EmagMarketplace_Model_Sales_Voucher_Abstract extends Mage_Core_Model_Abstract
{

    /**
     * Column prefix
     */
    const COLUMN_PREFIX = 'emag';

    /**
     * Emag vouchers field name on sales objects
     */
    const EMAG_VOUCHERS = 'emag_vouchers';

    /**
     * Check if voucher is already added for current order|quote
     *  - vouchers already added should not be saved again
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if ($this->_getResource()->isVoucherExists($this)) {
            $this->_dataSaveAllowed = false;
        }

        return parent::_beforeSave();
    }

    /**
     * Attach eMAG vouchers to order/quote
     *
     * @param $sales Mage_Sales_Model_Quote|Mage_Sales_Model_Order
     * @return Innobyte_EmagMarketplace_Model_Sales_Voucher_Abstract
     */
    public function attachVoucherData($sales)
    {
        $vouchers = $this->getEmagVouchers($sales->getId());
        if (!empty($vouchers)) {
            $sales->addData(array(self::EMAG_VOUCHERS => $vouchers));
        }

        return $this;
    }

    /**
     * Save eMAG vouchers
     *
     * @param Mage_Core_Model_Abstract $sales
     * @return Innobyte_EmagMarketplace_Model_Sales_Abstract
     */
    public function saveVoucherData(Mage_Core_Model_Abstract $sales)
    {
        if (!$sales->getEmagVouchers()) {
            return $this;
        }

        foreach ($sales->getEmagVouchers() as $voucher) {
            $this->addData($voucher);
            $this->setEntityId($sales->getId());

            if (!empty($voucher['isDeleted'])) {
                $this->isDeleted(true);
            }

            $this->save();
            $this->unsetData();
        }

        return $this;
    }

    /**
     * Get eMAG vouchers
     *
     * @param $orderId
     * @return array
     */
    public function getEmagVouchers($orderId)
    {
        $collection = $this->getCollection()
            ->addFieldToSelect('emag_id')
            ->addFieldToSelect('emag_voucher_id')
            ->addFieldToSelect('emag_voucher_name')
            ->addFieldToSelect('emag_sale_price')
            ->addFieldToSelect('base_emag_sale_price')
            ->addFieldToSelect('emag_sale_price_vat')
            ->addFieldToSelect('base_emag_sale_price_vat')
            ->addFieldToSelect('emag_status')
            ->addFieldToSelect('emag_vat')
            ->addFieldToSelect('emag_created')
            ->addFieldToSelect('emag_modified')
            ->addFieldToFilter('entity_id', $orderId);

        $vouchers = array();
        if (!$collection->getSize()) {
            return $vouchers;
        }

        foreach ($collection as $voucher) {
            $vouchers[] = $voucher->getData();
        }

        return $vouchers;
    }

}
