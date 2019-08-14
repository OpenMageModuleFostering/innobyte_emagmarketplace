<?php

/**
 * Class Innobyte_EmagMarketplace_Block_Adminhtml_Form_Element_File
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Block_Adminhtml_Form_Element_File
    extends Mage_Adminhtml_Block_Customer_Form_Element_File
{

    /**
     * Return File preview link HTML
     *
     * @return string
     */
    protected function _getPreviewHtml()
    {
        $values = $this->getValue();

        $html = '';
        foreach ($values as $invoiceId => $value) {
            if ($value && !is_array($value)) {
                $image = array(
                    'alt' => $value,
                    'title' => $value,
                    'src' => Mage::getDesign()->getSkinUrl('images/innobyte/emag_marketplace/pdf_icon.png'),
                    'class' => 'v-middle'
                );
                $url = Mage::helper('adminhtml')->getUrl('adminhtml/sales_invoice_upload/download', array(
                    'file' => Mage::helper('core')->urlEncode($value)
                ));
                $html .= '<div>';
                $html .= '<a href="' . $url . '">' . $this->_drawElementHtml('img', $image) . '</a> ';
                $html .= '<a href="' . $url . '">' . $value . '</a>';
                $html .= $this->_getDeleteHtml($invoiceId);
                $html .= '</div>';
            }
        }

        return $html;
    }

    /**
     * Get delete url
     *
     * @param $invoiceId
     * @return mixed
     */
    protected function _getDeleteHtml($invoiceId)
    {
        $image = array(
            'alt' => Mage::helper('innobyte_emag_marketplace')->__('Delete'),
            'title' => Mage::helper('innobyte_emag_marketplace')->__('Delete'),
            'src' => Mage::getDesign()->getSkinUrl('images/innobyte/emag_marketplace/trash.png'),
            'class' => 'v-middle'
        );
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/sales_invoice_upload/delete', array(
            '_current' => true,
            'invoice_id' => $invoiceId
        ));

        $html = '<a href="' . $url . '">' . $this->_drawElementHtml('img', $image) . '</a>';

        return $html;
    }

}
