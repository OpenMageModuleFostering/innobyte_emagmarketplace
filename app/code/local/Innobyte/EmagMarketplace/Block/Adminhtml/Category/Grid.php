<?php
/**
 * eMAG categories grid.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Block_Adminhtml_Category_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor.
     *
     * @Override
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('innoEmagCategoriesGrid');
        $this->setDefaultSort('main_table.emag_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    
    
    
    /**
     * Setup the collection to show in the grid.
     *
     * @Override
     * @return Innobyte_EmagMarketplace_Block_Adminhtml_Category_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel(
            'innobyte_emag_marketplace/category_collection'
        )->addStoreFilter(
            Mage::helper('innobyte_emag_marketplace')->getCurrStoreId()
        );
        
        $charTable = $collection->getTable(
            'innobyte_emag_marketplace/category_characteristic'
        );
        $famTypesTable = $collection->getTable(
            'innobyte_emag_marketplace/category_familytype'
        );
        $collection->getSelect()->joinLeft(
            array('characteristics' => $charTable),
            'characteristics.category_id = main_table.id',
            new Zend_Db_Expr(
                'GROUP_CONCAT('
                . 'DISTINCT characteristics.name '
                . 'ORDER BY characteristics.display_order ASC '
                . "SEPARATOR ', '"
                . ') AS characteristics'
            )
        )->joinLeft(
            array('familytypes' => $famTypesTable),
            'familytypes.category_id = main_table.id',
            new Zend_Db_Expr(
                'GROUP_CONCAT('
                . 'DISTINCT familytypes.name '
                . "SEPARATOR ', '"
                . ') AS family_types'
            )
        )->group('main_table.id');
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    
    
    /**
     * Setup the shown columns.
     *
     * @Override
     * @return Innobyte_EmagMarketplace_Block_Adminhtml_Category_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'emag_id',
            array(
                'header' => $this->__('eMAG ID'),
                'align'  => 'left',
                'type'   => 'number',
                'index'  => 'emag_id',
                'filter_index' => 'main_table.emag_id',
                'width'  => '50px',
            )
        );
        $this->addColumn(
            'name',
            array(
                'header' => $this->__('Name'),
                'align'  => 'left',
                'type'   => 'text',
                'index'  => 'name',
                'filter_index' => 'main_table.name',
            )
        );
        $this->addColumn(
            'characteristics',
            array(
                'header'   => $this->__('Characteristics'),
                'align'    => 'left',
                'type'     => 'text',
                'index'    => 'characteristics',
                'sortable' => false,
                'filter_condition_callback' => array(
                    $this,
                    '_filterCharacteristics'
                ),
            )
        );
        $this->addColumn(
            'family_types',
            array(
                'header'   => $this->__('Family Types'),
                'align'    => 'left',
                'type'     => 'text',
                'index'    => 'family_types',
                'sortable' => false,
                'filter_condition_callback' => array(
                    $this,
                    '_filterFamilytypes'
                ),
            )
        );
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                array(
                    'header'       => Mage::helper('sales')->__('Store'),
                    'index'        => 'store_id',
                    'filter_index' => 'main_table.store_id',
                    'type'         => 'store',                    
                    'store_view'   => true,
                )
            );
        }
        $this->addColumn(
            'updated_at',
            array(
                'header' => $this->__('Updated at'),
                'align'  => 'center',
                'type'   => 'datetime',
                'index'  => 'updated_at',
                'filter_index' => 'main_table.updated_at',
                'width'  => '100px',
            )
        );
        $this->addColumn(
            'created_at',
            array(
                'header' => $this->__('Created at'),
                'align'  => 'center',
                'type'   => 'datetime',
                'index'  => 'created_at',
                'filter_index' => 'main_table.created_at',
                'width'  => '100px',
            )
        );
        
        return parent::_prepareColumns();
    }
    
    
    
    /**
     * Disable edit mode.
     *
     * @Override
     * @param object $row
     * @return boolean
     */
    public function getRowUrl($row)
    {
        return false;
    }
    
    
    
    /**
     * Get grid 's url.
     *
     * @Override
     * @return string
     */
    public function getGridUrl()
    {
        return Mage::helper('adminhtml')->getUrl(
            '*/*/index',
            array('_current' => 1)
        );
    }
    
    
    
    /**
     * Custom filter for characteristics.
     *
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param type $column
     * @return Innobyte_EmagMarketplace_Block_Adminhtml_Category_Grid
     */
    protected function _filterCharacteristics($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $this->getCollection()->getSelect()
            ->where('characteristics.name LIKE ?', "%$value%");
        return $this;
    }
    
    
    
    /**
     * Custom filter for characteristics.
     *
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param type $column
     * @return Innobyte_EmagMarketplace_Block_Adminhtml_Category_Grid
     */
    protected function _filterFamilytypes($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $this->getCollection()->getSelect()
            ->where('familytypes.name LIKE ?', "%$value%");
        return $this;
    }
}
