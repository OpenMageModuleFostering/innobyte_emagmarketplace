<?php
/**
 * eMAG vat source model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Source_Vat
{
    /**
     * Array with options.
     *
     * @var array
     */
    protected $_options = array();



    /**
     * Retreive options array.
     *
     * @param int $storeId  Store to retrieve categories for.
     * @param boolean $includeEmpty Whether to return also an empty value.
     * @return array
     */
    public function toOptionArray($storeId, $includeEmpty = true)
    {
        $key = $this->_getKey($storeId, $includeEmpty);
        if (!array_key_exists($key, $this->_options)
            || empty($this->_options[$key])) {
            $this->_options[$key] = array();
            $vats = Mage::getResourceModel(
                'innobyte_emag_marketplace/vat_collection'
            )->addFieldToSelect(array('id', 'rate'))
                ->addStoreFilter(intval($storeId));
            foreach ($vats as $vat) {
                $this->_options[$key][] = array(
                    'value' => $vat->getId(),
                    'label' => $vat->getRate()
                );
            }
            
            // store also the other value in order not to make db select again.
            $otherKey = $this->_getKey($storeId, !$includeEmpty);
            $this->_options[$otherKey] = $this->_options[$key];
            if ($includeEmpty) {
                array_unshift(
                    $this->_options[$key],
                    array('value' => '', 'label' => '')
                );
            } else {
                array_unshift(
                    $this->_options[$otherKey],
                    array('value' => '', 'label' => '')
                );
            }
        }
        
        return $this->_options[$key];
    }
    
    
    
    /**
     * Calculates key to store options.
     *
     * @param int $storeId
     * @param boolean $includeEmpty
     * @return string
     */
    private function _getKey($storeId, $includeEmpty)
    {
        return intval($storeId) . '_' . intval($includeEmpty);
    }
    
    
    
    /**
     * Retrieve vat ids.
     *
     * @param int $storeId
     * @return array  Array with vat ids.
     */
    public function getIdsArray($storeId)
    {
        $returnValue = array();
        $options = $this->toOptionArray($storeId, false);
        foreach ($options as $option) {
            $returnValue[] = $option['value'];
        }
        return $returnValue;
    }
}
