<?php
class Innobyte_Core_Model_Source_Feed_Type 
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    const TYPE_INFO     = 'info';       // Innobyte info
    const TYPE_PROMO    = 'promo';      // Promotions/Discounts
    const TYPE_RELEASE  = 'release';    // New Releases
    const TYPE_UPDATE   = 'update';     // Extensions updates


    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::TYPE_UPDATE, 
                'label' => Mage::helper('innobyte_core')->__('Extensions updates')),
            array(
                'value' => self::TYPE_RELEASE, 
                'label' => Mage::helper('innobyte_core')->__('New Releases')),
            array(
                'value' => self::TYPE_PROMO, 
                'label' => Mage::helper('innobyte_core')->__('Promotions/Discounts')),
            array(
                'value' => self::TYPE_INFO, 
                'label' => Mage::helper('innobyte_core')->__('Other information'))
        );
    }

    /**
     * Retrive all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }


    /**
     * Returns label for value
     * 
     * @param string $value
     * @return string
     */
    public function getLabel($value)
    {
        $options = $this->toOptionArray();
        foreach ($options as $v) {
            if ($v['value'] == $value) {
                return $v['label'];
            }
        }
        return '';
    }

    /**
     * Returns array ready for use by grid
     * 
     * @return array
     */
    public function getGridOptions()
    {
        $items = $this->getAllOptions();
        $out = array();
        foreach ($items as $item) {
            $out[$item['value']] = $item['label'];
        }
        return $out;
    }
}
