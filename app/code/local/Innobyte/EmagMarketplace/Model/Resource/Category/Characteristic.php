<?php
/**
 * eMAG category characteristic resource model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Resource_Category_Characteristic
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * @Override
     */
    public function _construct()
    {
        $this->_init('innobyte_emag_marketplace/category_characteristic', 'id');
    }
}
