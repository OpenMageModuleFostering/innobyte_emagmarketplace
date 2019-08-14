<?php
/**
 * eMAG categories grid.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Block_Adminhtml_Vat_Grid
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
        $this->setId('innoEmagVatsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    
    
    
    /**
     * Setup the collection to show in the grid.
     *
     * @Override
     * @return Innobyte_EmagMarketplace_Block_Adminhtml_Vat_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel(
            'innobyte_emag_marketplace/vat_collection'
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
     * @return Innobyte_EmagMarketplace_Block_Adminhtml_Vat_Grid
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
            'rate',
            array(
                'header' => $this->__('Rate'),
                'align'  => 'right',
                'type'   => 'text',
                'index'  => 'rate',
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
