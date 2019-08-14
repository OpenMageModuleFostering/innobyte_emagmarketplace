<?php
/**
 * eMAG commission types source model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Source_CommissionTypes
{
    /**
     * eMAG commission types
     */
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_ABSOLUTE = 'absolute';

    /**
     * Mapping between eMAG commission types and locally magento values
     *
     * @var array
     */
    protected static $_typesMatch = array(
        1 => self::TYPE_PERCENTAGE,
        2 => self::TYPE_ABSOLUTE,
    );

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
            foreach (self::$_typesMatch as $magentoValue => $emagValue) {
                $this->_options[] = array(
                    'value' => $magentoValue,
                    'label' => $innoHelper->__($emagValue),
                );
            }
        }

        return $this->_options;
    }



    /**
     * Retrieve eMAG string commission type based on
     * integer magento defined commission type.
     *
     * @param int $magentoCommissionType
     * @return string  eMAG commission type.
     * @throws Innobyte_EmagMarketplace_Exception
     *                  If invalid magento commission type provided.
     */
    public static function getEmagCommissionType($magentoCommissionType)
    {
        if (array_key_exists($magentoCommissionType, self::$_typesMatch)) {
            return self::$_typesMatch[$magentoCommissionType];
        }
        throw new Innobyte_EmagMarketplace_Exception(
            'Invalid commission type provided'
        );
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
