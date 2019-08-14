<?php
/**
 * eMAG locality resource model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Resource_Locality
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * @Override
     */
    public function _construct()
    {
        $this->_init('innobyte_emag_marketplace/locality', 'id');
    }
    
    
    
    /**
     * @Override
     */
    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        $currentTime = Varien_Date::now();
        if ((!$object->getId() || $object->isObjectNew())
            && !$object->getCreatedAt()) {
            $object->setCreatedAt($currentTime);
        }
        $object->setUpdatedAt($currentTime);
        return parent::_prepareDataForSave($object);
    }
}
