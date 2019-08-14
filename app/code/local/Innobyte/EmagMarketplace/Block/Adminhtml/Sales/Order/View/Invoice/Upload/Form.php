<?php

/**
 * Class Innobyte_EmagMarketplace_Block_Adminhtml_Sales_Order_View_Invoice_Upload_Form
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Block_Adminhtml_Sales_Order_View_Invoice_Upload_Form
    extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Get current order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::getModel('sales/order')
            ->load($this->getRequest()->getParam('order_id'));
    }

    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $data = array(
            'invoice' => $this->_prepareInvoiceValues(),
            'is_storno' => '1'
        );

        $form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/sales_invoice_upload/save', array('_current' => true)),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            )
        );

        $form->setHtmlIdPrefix('emag_invoice_');
        $form->setUseContainer(true);

        $fieldset = $form->addFieldset('emag_invoice_form', array(
            'legend' => Mage::helper('innobyte_emag_marketplace')->__('Upload File'),
            'class' => 'fieldset'
        ));

        $fieldset->addType('invoice_file', 'Innobyte_EmagMarketplace_Block_Adminhtml_Form_Element_File');

        $fieldset->addField('invoice', 'invoice_file', array(
            'label' => Mage::helper('innobyte_emag_marketplace')->__('Invoice'),
            'disabled' => false,
            'readonly' => true,
            'required' => true,
            'name' => 'invoice'
        ));

        $fieldset->addField('is_storno', 'checkbox', array(
            'label' => Mage::helper('innobyte_emag_marketplace')->__('Is Storno'),
            'name' => 'is_storno',
            'checked' => false,
            'disabled' => false,
            'value' => '1'
        ));

        $this->setForm($form);
        $form->setValues($data);

        return parent::_prepareForm();
    }

    /**
     * Get order invoices
     *
     * @return array
     */
    protected function _prepareInvoiceValues()
    {
        return Mage::getModel('innobyte_emag_marketplace/sales_invoice')
            ->getEmagThirdPartyInvoices($this->getRequest()->getParam('order_id'));
    }

}
