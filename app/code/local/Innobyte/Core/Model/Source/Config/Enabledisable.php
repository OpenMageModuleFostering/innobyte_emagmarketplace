<?php

class Innobyte_Core_Model_Source_Config_Enabledisable {

    /**
     * Options getter
     * 
     * @return int
     */
    public function toOptionArray() 
    {
        $options = array(
            array(
                'value' => 0,
                'label' => Mage::helper('innobyte_core')->__('No')),
        );

        $ext = Mage::registry('inno-');
        if ($ext) {
            $websites = Mage::helper('innobyte_core/versions')->getAvailabelsWebsites($ext);
        }
        if (empty($websites)) {
            $options[] = array(
                'value' => 1, 
                'label' => Mage::helper('innobyte_core')->__('Yes')
            );
        }

        return $options;
    }
}