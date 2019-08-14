<?php
/**
 * Handles locality api related operations.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Api_Locality
    extends Innobyte_EmagMarketplace_Model_Api_Abstract
{
    /**
     * Locality resource name
     */
    const LOCALITY_RESOURCE_NAME = 'locality';

    
    /**
     * @var Innobyte_EmagMarketplace_Model_Resource_Locality_Collection
     */
    protected $_localityCollection;
    
    
    
    /**
     * Read LOCALITY resource
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
     * Save LOCALITY resource
     *
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function save()
    {
        throw new Innobyte_EmagMarketplace_Exception('Not implemented');
    }

    
    
    /**
     * Count LOCALITY resource
     *
     * @return Innobyte_EmagMarketplace_Model_Api_Response
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function count()
    {
        parent::count();
        $apiResponse = $this->_makeApiCall();
        $this->_setPaginationInfo($apiResponse);
        return $apiResponse;
    }

    
    
    /**
     * Acknowledge LOCALITY resource
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
        return self::LOCALITY_RESOURCE_NAME;
    }
    
    
    
    /**
     * Map eMAG locality from api to magento model.
     *
     * @param array $locality  Locality from api.
     * @return Innobyte_EmagMarketplace_Model_Locality
     */
    public function importLocality(array $locality)
    {
        $this->_getLocalityCollection()->clear()
            ->getSelect()->reset(Zend_Db_Select::WHERE)
            ->limit(1);
        $emagId = isset($locality['emag_id']) ? intval($locality['emag_id']) : 0;
        $returnValue = $this->_getLocalityCollection()
            ->addStoreFilter($this->getStoreId())
            ->addFieldToFilter('emag_id', $emagId)
            ->getFirstItem();
        
        $returnValue->setStoreId($this->getStoreId())
            ->setEmagId($emagId);
        if (isset($locality['name']) && strlen($locality['name'])) {
            $returnValue->setName(strval($locality['name']));
        }
        if (isset($locality['name_latin']) && strlen($locality['name_latin'])) {
            $returnValue->setNameLatin(strval($locality['name_latin']));
        }
        if (isset($locality['region1']) && strlen($locality['region1'])) {
            $returnValue->setRegion1(strval($locality['region1']));
        }
        if (isset($locality['region2']) && strlen($locality['region2'])) {
            $returnValue->setRegion2(strval($locality['region2']));
        }
        if (isset($locality['region3']) && strlen($locality['region3'])) {
            $returnValue->setRegion3(strval($locality['region3']));
        }
        if (isset($locality['region4']) && strlen($locality['region4'])) {
            $returnValue->setRegion4(strval($locality['region4']));
        }
        if (isset($locality['region1_latin'])
            && strlen($locality['region1_latin'])) {
            $returnValue->setRegion1Latin(strval($locality['region1_latin']));
        }
        if (isset($locality['region2_latin'])
            && strlen($locality['region2_latin'])) {
            $returnValue->setRegion2Latin(strval($locality['region2_latin']));
        }
        if (isset($locality['region3_latin'])
            && strlen($locality['region3_latin'])) {
            $returnValue->setRegion3Latin(strval($locality['region3_latin']));
        }
        if (isset($locality['region4_latin'])
            && strlen($locality['region4_latin'])) {
            $returnValue->setRegion4Latin(strval($locality['region4_latin']));
        }
        if (isset($locality['geoid'])
            && is_numeric($locality['geoid'])) {
            $returnValue->setGeoid(intval($locality['geoid']));
        }
        if (isset($locality['modified'])
            && strlen($locality['modified'])) {
            $returnValue->setEmagModified(strval($locality['modified']));
        }
        return $returnValue;
    }
    
    
    
    /**
     * Getter method for locality collection property.
     *
     * @return Innobyte_EmagMarketplace_Model_Resource_Locality_Collection
     */
    protected function _getLocalityCollection()
    {
        if (is_null($this->_localityCollection)) {
            $this->_localityCollection = Mage::getResourceModel(
                'innobyte_emag_marketplace/locality_collection'
            );
        }
        return $this->_localityCollection;
    }
}
