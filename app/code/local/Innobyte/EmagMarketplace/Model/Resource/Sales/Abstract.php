<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Resource_Sales_Abstract
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
abstract class Innobyte_EmagMarketplace_Model_Resource_Sales_Abstract extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Column prefix
     *
     * @var null|string
     */
    protected $_columnPrefix = null;

    /**
     * Primary key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Main entity resource model
     *
     * @var string
     */
    protected $_parentResourceModel = '';

    /**
     * Get main entity resource model
     *
     * @return Mage_Sales_Model_Resource_Order|Mage_Sales_Model_Resource_Quote
     */
    protected function _getParentResourceModel()
    {
        if (!$this->_parentResourceModel) {
            return null;
        }

        return Mage::getResourceSingleton($this->_parentResourceModel);
    }

    /**
     * Get attribute column name
     *
     * @param Mage_Customer_Model_Attribute $attribute
     * @return string
     */
    protected function _getColumnName(Mage_Customer_Model_Attribute $attribute)
    {
        $columnName = $attribute->getAttributeCode();
        if ($this->_columnPrefix) {
            $columnName = sprintf('%s_%s', $this->_columnPrefix, $columnName);
        }

        return $columnName;
    }

    /**
     * Saves attribute
     *
     * @param Mage_Customer_Model_Attribute $attribute
     * @return Innobyte_EmagMarketplace_Model_Resource_Sales_Abstract
     */
    public function saveAttribute(Mage_Customer_Model_Attribute $attribute)
    {
        $backendType = $attribute->getBackendType();
        if ($backendType == Mage_Customer_Model_Attribute::TYPE_STATIC) {
            return $this;
        }

        switch ($backendType) {
            case 'datetime':
                $definition = array(
                    'type' => Varien_Db_Ddl_Table::TYPE_DATE,
                );
                break;
            case 'decimal':
                $definition = array(
                    'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                    'length' => 12, 4,
                );
                break;
            case 'int':
                $definition = array(
                    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                );
                break;
            case 'text':
                $definition = array(
                    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                );
                break;
            case 'varchar':
                $definition = array(
                    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                    'length' => 255,
                );
                break;
            default:
                return $this;
        }

        $columnName = $this->_getColumnName($attribute);
        $definition['comment'] = ucwords(str_replace('_', ' ', $columnName));

        $this->_getWriteAdapter()->addColumn($this->getMainTable(), $columnName, $definition);

        return $this;
    }

    /**
     * Deletes an attribute
     *
     * @param Mage_Customer_Model_Attribute $attribute
     * @return Innobyte_EmagMarketplace_Model_Resource_Sales_Abstract
     */
    public function deleteAttribute(Mage_Customer_Model_Attribute $attribute)
    {
        $this->_getWriteAdapter()->dropColumn(
            $this->getMainTable(),
            $this->_getColumnName($attribute)
        );

        return $this;
    }

    /**
     * Check if main entity exists in main table
     *
     * @param Innobyte_EmagMarketplace_Model_Sales_Abstract $sales
     * @return bool
     */
    public function isEntityExists(Innobyte_EmagMarketplace_Model_Sales_Abstract $sales)
    {
        if (!$sales->getId()) {
            return false;
        }

        $resource = $this->_getParentResourceModel();
        if (!$resource) {
            // skip if resource model is not available
            return true;
        }

        $parentTable = $resource->getMainTable();
        $parentIdField = $resource->getIdFieldName();
        $select = $this->_getWriteAdapter()->select()
            ->from($parentTable, $parentIdField)
            ->forUpdate(true)
            ->where("{$parentIdField} = ?", $sales->getId());

        if ($this->_getWriteAdapter()->fetchOne($select)) {
            return true;
        }

        return false;
    }

}
