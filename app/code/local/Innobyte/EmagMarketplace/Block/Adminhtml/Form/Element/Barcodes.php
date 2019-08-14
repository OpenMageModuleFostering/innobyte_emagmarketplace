<?php
/**
 * eMAG custom form element renderer for barcodes.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Block_Adminhtml_Form_Element_Barcodes
    extends Varien_Data_Form_Element_Text
{
    /**
     * @Override
     * @return string
     */
    public function getElementHtml()
    {
        $returnValue = '';
        $helper = Mage::helper('innobyte_emag_marketplace');
        if (is_array($this->getValue()) && count($this->getValue())) {
            $i = 0;
            foreach ($this->getValue() as $key => $value) {
                $returnValue .= '<input id="' . $this->getHtmlId()
                    .'" name="' . $this->getName() . '[]'
                    .'" value="' . $this->getEscapedValue($key) . '" '
                    . $this->serialize($this->getHtmlAttributes()) . '/>'
                    . "\n";
                if (!$i) {
                    $returnValue .= '<span class="innoemag-addremove-item" '
                        . 'title="' . $helper->__('Add New')
                        . '" onclick="inno.emagMarketplace.addNewBarcode()">'
                        . ' + '
                        . '</span>';
                    $i = 1;
                } else {
                    $returnValue .= '<span class="innoemag-addremove-item" '
                        . 'title="' . $helper->__('Remove')
                        . '" onclick="inno.emagMarketplace.removeBarcode(this)">'
                        . ' - '
                        . '</span>';
                }
                $returnValue .= '<br style="clear: both;"/>' . "\n";
            }
        } else {
            $returnValue .= '<span class="innoemag-addremove-item" '
                . 'title="' . $helper->__('Add New')
                . '" onclick="inno.emagMarketplace.addNewBarcode()">'
                . ' + '
                . '</span>'
                . '<input id="' . $this->getHtmlId()
                . '" name="' . $this->getName() . '[]'
                . '" value="" '
                . $this->serialize($this->getHtmlAttributes()) . '/>'
                . "<br style='clear: both; '/>\n";
        }
        
        $returnValue .= $this->getAfterElementHtml();
        
        return $returnValue;
    }
}
