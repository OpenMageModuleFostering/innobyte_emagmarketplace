<?php

/**
 * Class Innobyte_EmagMarketplace_Block_Adminhtml_Sales_Order_View_Invoice_Upload
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Block_Adminhtml_Sales_Order_View_Invoice_Upload
    extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * Configure form container
     */
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'innobyte_emag_marketplace';
        $this->_controller = 'adminhtml_sales_order';

        $this->_removeButton('back');
        $this->_removeButton('reset');
        $this->_updateButton('save', 'label', Mage::helper('innobyte_emag_marketplace')->__('Upload'));
    }

    /**
     * Get form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('innobyte_emag_marketplace')->__('eMAG Upload Invoice');
    }

}
