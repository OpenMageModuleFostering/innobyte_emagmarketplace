<?php

/**
 * Class Innobyte_EmagMarketplace_Model_System_Config_Backend_Prefix_Abstract
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_System_Config_Backend_Prefix_Abstract extends Mage_Core_Model_Config_Data
{

    /**
     * Prefix
     *
     * @var null
     */
    protected $_prefix = null;

    /**
     * Entity type
     *
     * @var array
     */
    protected $_entityType = null;

    /**
     * Get prefix
     */
    public function getPrefix()
    {
        return $this->_prefix;
    }

    /**
     * Get entity type
     *
     * @return array
     */
    public function getEntityType()
    {
        return $this->_entityType;
    }

    /**
     * Get entity model
     *
     * @return Mage_Eav_Model_Entity_Store
     */
    public function getEntityModel()
    {
        return Mage::getModel('eav/entity_store');
    }

    /**
     * Get entity type model
     *
     * @param $entityType
     * @return Mage_Eav_Model_Entity_Type
     */
    public function getEntityTypeModel($entityType)
    {
        return Mage::getModel('eav/config')->getEntityType($entityType);
    }

    /**
     * Update eav_entity_store table
     *  - set increment_prefix for invoice/creditmemo in current store
     *  - insert new record if not found
     *
     * @return Innobyte_EmagMarketplace_Model_System_Config_Backend_Prefix_Abstract
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        $prefix = $this->getPrefix();
        $storeCode = Mage::app()->getRequest()->getParam('store');

        // exit if invoice prefix is not defined
        if (!$prefix) {
            return $this;
        }

        // throw exception if store code is not present; invoice increment_prefix can only be edited at store level
        if ($prefix && !$storeCode) {
            Mage::throwException('Invoice/Creditmemo prefix can only be edited at store level!');
        }

        // exit if store code is missing
        if (!$storeCode) {
            return $this;
        }

        $store = Mage::getModel('core/store')->load($storeCode, 'code');
        if (!$store->getId()) {
            return $this;
        }

        try {
            $entityType = $this->getEntityType();
            $entityTypeModel = $this->getEntityTypeModel($entityType);
            $entityModel = $this->getEntityModel()
                ->loadByEntityStore($this->getEntityTypeModel($entityType)->getEntityTypeId(), $store->getId());

            if (!$entityModel->getEntityStoreId()) {
                $this->_getResource()->beginTransaction();

                // insert entity model with magento default prefix
                $entityModel
                    ->setEntityTypeId($entityTypeModel->getId())
                    ->setStoreId($store->getId())
                    ->setIncrementPrefix($store->getId())
                    ->save();

                $incrementInstance = Mage::getModel($entityTypeModel->getIncrementModel())
                    ->setPrefix($entityModel->getIncrementPrefix())
                    ->setPadLength($entityTypeModel->getIncrementPadLength())
                    ->setPadChar($entityTypeModel->getIncrementPadChar())
                    ->setLastId($entityModel->getIncrementLastId())
                    ->setEntityTypeId($entityModel->getEntityTypeId())
                    ->setStoreId($entityModel->getStoreId());

                /**
                 * do read lock on eav/entity_store to solve potential timing issues
                 * (most probably already done by beginTransaction of entity save)
                 */
                $incrementId = (string)$incrementInstance->getNextId();
                $incrementId = substr_replace($incrementId, '0', -1);

                $entityModel->setIncrementLastId($incrementId);
                $entityModel->save();

                $this->_getResource()->commit();

                // update entity model with custom prefix
                $entityModel = $this->getEntityModel()
                    ->loadByEntityStore($this->getEntityTypeModel($entityType)->getEntityTypeId(), $store->getId());
                $entityModel
                    ->setIncrementPrefix($prefix)
                    ->save();

            } else {
                $entityModel->setIncrementPrefix($prefix);
                $entityModel->save();
            }
        } catch (Exception $e) {
            $this->_getResource()->rollBack();
            Mage::throwException($e->getMessage());
        }

        return $this;
    }

}
