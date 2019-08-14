<?php
/**
 * Custom system config field renderer.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Block_Adminhtml_Form_Field_LocalityId
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Return element html.
     *
     * @Override
     * @param Varien_Data_Form_Element_Abstract $elem
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $elem)
    {
        return $elem->getElementHtml() . $this->getAutocompleteJs();
    }
    
    
    
    /**
     * Return localities url.
     *
     * @return string
     */
    public function getLocalitiesUrl()
    {
        return Mage::helper('adminhtml')->getUrl(
            'adminhtml/emag_locality/getCities',
            array('_secure' => 1)
        );
    }
    
    
    
    /**
     * Retrieve javascript code for button.
     *
     * @return string
     */
    public function getAutocompleteJs()
    {
        return '<script type="text/javascript" src="'
            . $this->escapeUrl(
                $this->getSkinUrl('js/innobyte/emag_marketplace/locality.js')
            )
            . '"></script>'
            . '<script type="text/javascript">
            //<![CDATA[
            
                // load css
                var innoEmagMktpCss = document.createElement("link")
                innoEmagMktpCss.setAttribute("rel", "stylesheet")
                innoEmagMktpCss.setAttribute("type", "text/css")
                innoEmagMktpCss.setAttribute("href", "'
                . $this->escapeUrl(
                    $this->getSkinUrl('css/innobyte/emag_marketplace/style.css')
                )
                . '")
                document.getElementsByTagName("head")[0]
                    .appendChild(innoEmagMktpCss);
                    
                // attach autocomplete for city
                inno.emagMarketplace.attachAutocompleteEmagCity('
                    . '\'' . $this->escapeUrl($this->getLocalitiesUrl()) . '\','
                    . '\'shipping_origin\','
                    .'\'' . $this->escapeUrl(
                        $this->getSkinUrl('images/ajax-loader.gif')
                    ) . '\''
                .');
            
            //]]>
            </script>';
    }
    
    
    
    /**
     * Decorate field row html
     *
     * @Override  Added style display none.
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml($element, $html)
    {
        return '<tr id="row_' . $element->getHtmlId()
            . '" style="display: none;">' . $html . '</tr>';
    }
}
