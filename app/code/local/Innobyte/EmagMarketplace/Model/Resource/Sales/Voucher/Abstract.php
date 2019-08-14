<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Resource_Sales_Voucher_Abstract
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
abstract class Innobyte_EmagMarketplace_Model_Resource_Sales_Voucher_Abstract
    extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Check if voucher exists
     *
     * @param $voucher Innobyte_EmagMarketplace_Model_Sales_Voucher_Abstract
     * @return bool
     */
    public function isVoucherExists(Innobyte_EmagMarketplace_Model_Sales_Voucher_Abstract $voucher)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->getMainTable(), 'entity_id')
            ->where("entity_id = ?", $voucher->getEntityId())
            ->where("emag_voucher_id = ?", $voucher->getEmagVoucherId());

        if ($this->_getWriteAdapter()->fetchOne($select)) {
            return true;
        }

        return false;
    }

}
