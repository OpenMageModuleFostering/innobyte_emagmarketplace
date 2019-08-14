<?php
/**
 * eMAG locality model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Locality extends Mage_Core_Model_Abstract
{
    /**
     * @Override
     * @var string  Event prefix.
     */
    protected $_eventPrefix = 'innobyte_emag_marketplace_locality';
    
    /**
     * @Override
     * @var string  Event object key.
     */
    protected $_eventObject = 'emag_locality';
    
    
    
    /**
     * @Override
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('innobyte_emag_marketplace/locality');
    }
}
