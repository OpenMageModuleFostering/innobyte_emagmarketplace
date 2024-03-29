<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Resource_Sales_Address_Abstract
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
abstract class Innobyte_EmagMarketplace_Model_Resource_Sales_Address_Abstract
    extends Innobyte_EmagMarketplace_Model_Resource_Sales_Abstract
{

    /**
     * Column prefix
     *
     * @var null|string
     */
    protected $_columnPrefix = null;

    /**
     * Attachs data to collection
     *
     * @param Varien_Data_Collection_Db $collection
     * @return Innobyte_EmagMarketplace_Model_Resource_Sales_Address_Abstract
     */
    public function attachDataToCollection(Varien_Data_Collection_Db $collection)
    {
        $items = array();
        $itemIds = array();
        foreach ($collection->getItems() as $item) {
            $itemIds[] = $item->getId();
            $items[$item->getId()] = $item;
        }

        if ($itemIds) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where("{$this->getIdFieldName()} IN (?)", $itemIds);
            $rowSet = $this->_getReadAdapter()->fetchAll($select);
            foreach ($rowSet as $row) {
                $items[$row[$this->getIdFieldName()]]->addData($row);
            }
        }

        return $this;
    }

}
