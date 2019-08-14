<?php
/**
 * eMAG family type source model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Source_FamilyType
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
     * @param int $categoryId  Category to retrieve families for.
     * @param boolean $includeEmpty Whether to return also an empty value.
     * @return array
     */
    public function toOptionArray($categoryId, $includeEmpty = true)
    {
        $key = $this->_getKey($categoryId, $includeEmpty);
        if (!array_key_exists($key, $this->_options)
            || empty($this->_options[$key])) {
            $this->_options[$key] = array();
            $familyTypesColl = Mage::getResourceModel(
                'innobyte_emag_marketplace/category_familytype_collection'
            )->addFieldToFilter('category_id', intval($categoryId));
            $helper = Mage::helper('innobyte_emag_marketplace');
            
            foreach ($familyTypesColl as $familyType) {
                $this->_options[$key][] = array(
                    'value' => $familyType->getId(),
                    'label' => $helper->__($familyType->getName()),
                );
            }
            
            // store also the other value in order not to make db select again.
            $otherKey = $this->_getKey($categoryId, !$includeEmpty);
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
     * @param int $categoryId
     * @param boolean $includeEmpty
     * @return string
     */
    private function _getKey($categoryId, $includeEmpty)
    {
        return intval($categoryId) . '_' . intval($includeEmpty);
    }
    
    
    
    /**
     * Retrieve categories ids.
     *
     * @param int $storeId
     * @return array  Array with category ids.
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
