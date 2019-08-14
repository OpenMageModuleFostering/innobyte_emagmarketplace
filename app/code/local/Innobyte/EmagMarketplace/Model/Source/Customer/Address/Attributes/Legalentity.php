<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Source_Customer_Address_Attributes_Legalentity
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Source_Customer_Address_Attributes_Legalentity
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $options = array(
            array(
                'label' => Mage::helper('innobyte_emag_marketplace')->__('Private Entity'),
                'value' => 0
            ),
            array(
                'label' => Mage::helper('innobyte_emag_marketplace')->__('Legal Entity'),
                'value' => 1
            )
        );

        return $options;
    }

}
