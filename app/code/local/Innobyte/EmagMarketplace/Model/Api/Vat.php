<?php
/**
 * Handles vat api related operations.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Api_Vat
    extends Innobyte_EmagMarketplace_Model_Api_Abstract
{
    /**
     * VAT resource name
     */
    const VAT_RESOURCE_NAME = 'vat';
    
    /**
     * @var Innobyte_EmagMarketplace_Model_Resource_Vat_Collection
     */
    protected $_vatCollection;
    
    
    
    /**
     * Read VAT resource
     *
     * @return Innobyte_EmagMarketplace_Model_Api_Response
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function read()
    {
        parent::read();
        return $this->_makeApiCall();
    }
    
    
    
    /**
     * Save VAT resource
     *
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function save()
    {
        throw new Innobyte_EmagMarketplace_Exception('Not implemented');
    }

    
    
    /**
     * Count VAT resource
     *
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function count()
    {
        throw new Innobyte_EmagMarketplace_Exception('Not implemented');
    }

    
    
    /**
     * Acknowledge VAT resource
     *
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function acknowledge()
    {
        throw new Innobyte_EmagMarketplace_Exception('Not implemented');
    }

    
    
    /**
     * Get resource name
     *
     * @return string
     */
    public function getResourceName()
    {
        return self::VAT_RESOURCE_NAME;
    }
    
    
    /**
     * Map eMAG vat from api to magento model.
     *
     * @param array $vat  VAT from api.
     * @return Innobyte_EmagMarketplace_Model_Vat
     */
    public function importEmagData(array $vat)
    {
        if (array_key_exists('vat_id', $vat)
            && is_numeric($vat['vat_id'])) {
            $this->setEmagId(intval($vat['vat_id']));
        }
        if (array_key_exists('vat_rate', $vat)
            && is_numeric($vat['vat_rate'])) {
            $this->setRate(floatval($vat['vat_rate']));
        }
        return $this;
    }
    
    
    
    /**
     * Map eMAG VAT from api to magento model.
     *
     * @param array $vat  VAT from api.
     * @return Innobyte_EmagMarketplace_Model_Vat
     */
    public function importVat(array $vat)
    {
        $this->_getVatCollection()->clear()
            ->getSelect()->reset(Zend_Db_Select::WHERE)
            ->limit(1);
        $emagVatId = isset($vat['vat_id']) ? intval($vat['vat_id']) : 0;
        $returnValue = $this->_getVatCollection()
            ->addStoreFilter($this->getStoreId())
            ->addFieldToFilter('emag_id', $emagVatId)
            ->getFirstItem();
        
        $returnValue->setStoreId($this->getStoreId())
            ->setEmagId($emagVatId);
        if (isset($vat['vat_rate']) && is_numeric($vat['vat_rate'])) {
            $returnValue->setRate(floatval($vat['vat_rate']));
        }
        return $returnValue;
    }
    
    
    
    /**
     * Getter method for vat collection property.
     *
     * @return Innobyte_EmagMarketplace_Model_Resource_Vat_Collection
     */
    protected function _getVatCollection()
    {
        if (is_null($this->_vatCollection)) {
            $this->_vatCollection = Mage::getResourceModel(
                'innobyte_emag_marketplace/vat_collection'
            );
        }
        return $this->_vatCollection;
    }
}
