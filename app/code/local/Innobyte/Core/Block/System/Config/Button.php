<?php

/**
 * System Configuration Button Block
 *
 * @category   Innobyte
 * @package    Innobyte_Core
 * @author     Daniel Horobeanu <daniel.horobeanu@innobyte.com>
 */
class Innobyte_Core_Block_System_Config_Button
    extends Mage_Adminhtml_Block_System_Config_Form_Field 
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('innobyte/core/button.phtml');
    }

    /**
     * Return element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxSendReportUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_index/innoCoreSendReport', array('_current' => true));
    }

    /**
     * "Send email" button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
            'id' => 'innobyte_core_send_email_button',
            'label' => $this->helper('adminhtml')->__('Send email'),
            'onclick' => 'inno.core.sendReport(); return false;'
        ));
        return $button->toHtml();
    }

}
