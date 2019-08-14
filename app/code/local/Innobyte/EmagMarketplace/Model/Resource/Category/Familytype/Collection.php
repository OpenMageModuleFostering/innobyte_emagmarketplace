<?php
/**
 * eMAG category family-type resource collection model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Resource_Category_Familytype_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Name prefix of events that are dispatched by model
     *
     * @Override
     * @var string
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_category_familytype_collection';

    /**
     * Name of event parameter
     *
     * @Override
     * @var string
     */
    protected $_eventObject = 'emag_category_familytype_collection';
    
    
    
    /**
     * @Override
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('innobyte_emag_marketplace/category_familytype');
    }

}
