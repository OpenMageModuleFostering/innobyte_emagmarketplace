<?php
/**
 * eMAG localities grid.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Block_Adminhtml_Locality_Grid
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
        $this->setId('innoEmagLocalitiesGrid');
        $this->setDefaultSort('main_table.emag_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    
    
    
    /**
     * Setup the collection to show in the grid.
     *
     * @Override
     * @return Innobyte_EmagMarketplace_Block_Adminhtml_Locality_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel(
            'innobyte_emag_marketplace/locality_collection'
        )->addStoreFilter(
            Mage::helper('innobyte_emag_marketplace')->getCurrStoreId()
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    
    
    /**
     * Setup the shown columns.
     *
     * @Override
     * @return Innobyte_EmagMarketplace_Block_Adminhtml_Locality_Grid
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
            )
        );
        $this->addColumn(
            'name_latin',
            array(
                'header' => $this->__('Name Latin'),
                'align'  => 'left',
                'type'   => 'text',
                'index'  => 'name',
            )
        );
        $this->addColumn(
            'region1',
            array(
                'header' => $this->__('Region 1'),
                'align'  => 'left',
                'type'   => 'text',
                'index'  => 'region1',
            )
        );
        $this->addColumn(
            'region2',
            array(
                'header' => $this->__('Region 2'),
                'align'  => 'left',
                'type'   => 'text',
                'index'  => 'region2',
            )
        );
        $this->addColumn(
            'region3',
            array(
                'header' => $this->__('Region 3'),
                'align'  => 'left',
                'type'   => 'text',
                'index'  => 'region3',
            )
        );
        $this->addColumn(
            'region4',
            array(
                'header' => $this->__('Region 4'),
                'align'  => 'left',
                'type'   => 'text',
                'index'  => 'region4',
            )
        );
        $this->addColumn(
            'region1_latin',
            array(
                'header' => $this->__('Region 1 Latin'),
                'align'  => 'left',
                'type'   => 'text',
                'index'  => 'region1_latin',
            )
        );
        $this->addColumn(
            'region2_latin',
            array(
                'header' => $this->__('Region 2 Latin'),
                'align'  => 'left',
                'type'   => 'text',
                'index'  => 'region2_latin',
            )
        );
        $this->addColumn(
            'region3_latin',
            array(
                'header' => $this->__('Region 3 Latin'),
                'align'  => 'left',
                'type'   => 'text',
                'index'  => 'region3_latin',
            )
        );
        $this->addColumn(
            'region4_latin',
            array(
                'header' => $this->__('Region 4 Latin'),
                'align'  => 'left',
                'type'   => 'text',
                'index'  => 'region4_latin',
            )
        );
        $this->addColumn(
            'geoid',
            array(
                'header' => $this->__('Geoid'),
                'type'   => 'text',
                'index'  => 'geoid',
            )
        );
        $this->addColumn(
            'emag_modified',
            array(
                'header' => $this->__('eMAG last modified'),
                'align'  => 'center',
                'type'   => 'datetime',
                'index'  => 'emag_modified',
                'width'  => '150px',
            )
        );
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                array(
                    'header'     => Mage::helper('sales')->__('Store'),
                    'index'      => 'store_id',
                    'type'       => 'store',
                    'store_view' => true,
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
                'width'  => '150px',
            )
        );
        $this->addColumn(
            'created_at',
            array(
                'header' => $this->__('Created at'),
                'align'  => 'center',
                'type'   => 'datetime',
                'index'  => 'created_at',
                'width'  => '150px',
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
}
