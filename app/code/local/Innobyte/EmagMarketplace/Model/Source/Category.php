<?php
/**
 * eMAG category source model.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Source_Category
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
     * @param boolean $onlyWithFamilyTypes Whether to return only categories
     *                                     that have family type(s).
     * @return array
     */
    public function toOptionArray(
        $storeId,
        $includeEmpty = true,
        $onlyWithFamilyTypes = false
    )
    {
        $key = $this->_getKey($storeId, $includeEmpty, $onlyWithFamilyTypes);
        if (!array_key_exists($key, $this->_options)
            || empty($this->_options[$key])) {
            $this->_options[$key] = array();
            $categoriesColl = Mage::getResourceModel(
                'innobyte_emag_marketplace/category_collection'
            )->addFieldToSelect(array('id', 'name'))
                ->addStoreFilter(intval($storeId))
                ->setOnlyWithFamilyTypes($onlyWithFamilyTypes);
            $helper = Mage::helper('innobyte_emag_marketplace');
            
            // process products in chunk so DB
            // does not get busy if too many categories
            $catCnt = $categoriesColl->getSize();
            $chunkSize = 100;
            $pages = ceil($catCnt / $chunkSize);
            $i = 0;
            $categoriesColl->setOrder('name', Varien_Data_Collection::SORT_ORDER_ASC);
            while ($i < $pages) {
                $categoriesColl->clear();
                $categoriesColl->getSelect()
                    ->reset(Zend_Db_Select::LIMIT_COUNT)
                    ->reset(Zend_Db_Select::LIMIT_OFFSET)
                    ->limit($chunkSize, $i * $chunkSize);
                foreach ($categoriesColl as $category) {
                    $this->_options[$key][] = array(
                        'value' => $category->getId(),
                        'label' => $helper->__($category->getName()),
                    );
                }
                $i++;
            }
            
            // store also the other value in order not to make db select again.
            $otherKey = $this->_getKey($storeId, !$includeEmpty, $onlyWithFamilyTypes);
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
     * @param boolean $onlyWithFamilyTypes
     * @return string
     */
    private function _getKey($storeId, $includeEmpty, $onlyWithFamilyTypes)
    {
        return intval($storeId) . '_' . intval($includeEmpty)
            . '_' . intval($onlyWithFamilyTypes);
    }
}
