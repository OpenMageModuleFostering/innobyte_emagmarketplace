<?php
/**
 * eMAG offer statuses source model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Source_OfferStatus
{
    /**
     * eMAG offer status
     */
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;


    /**
     * Array with options.
     *
     * @var array
     */
    protected $_options = array();



    /**
     * Retreive options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (empty($this->_options)) {
            $innoHelper = Mage::helper('innobyte_emag_marketplace');
            $this->_options[] = array(
                'value' => self::STATUS_INACTIVE,
                'label' => $innoHelper->__('Inactive'),
            );
            $this->_options[] = array(
                'value' => self::STATUS_ACTIVE,
                'label' => $innoHelper->__('Active'),
            );
        }

        return $this->_options;
    }
    
    
    
    /**
     * Retreive array with values.
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::STATUS_INACTIVE,
            self::STATUS_ACTIVE,
        );
    }
}
