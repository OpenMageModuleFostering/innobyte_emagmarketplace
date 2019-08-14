<?php

class Innobyte_Core_Block_System_Config_Form_Renderer_Website
    extends Mage_Adminhtml_Block_System_Config_Form_Field 
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) 
    {
        $html = '';
        $r = Mage::getStoreConfig($element->getHint().'/license/ar');
        $value = explode(',', str_replace($r, '', Mage::helper('core')->decrypt($element->getValue())));

        $nameprefix = $element->getName();
        $idprefix = $element->getId();

        $element->setName($nameprefix . '[]');
        $info = Mage::helper('innobyte_core/versions')->avs($element->getHint());

        if (isset($info['d']) && isset($info['c']) && intval($info['c']) > 0) {
            foreach (Mage::app()->getWebsites() as $website) {
                $element->setChecked(false);

                $id = $website->getId();
                $name = $website->getName();

                $element->setId($idprefix . '_' . $id);
                $element->setValue($id);
                $element->setClass('innobyte-available-sites');

                if (in_array($id, $value) !== false) {
                    $element->setChecked(true);
                }

                if ($id != 0) {
                    $html .= '<div><label>' . $element->getElementHtml() . ' ' . $name . ' </label></div>';
                }
            }

            $html .= '
        	<input id="' . $idprefix . '_diasbled" type="hidden" disabled="disabled" name="' . $nameprefix . '" />
        	<script type="text/javascript">
        	
        	function updateInnobyteWebsites(){
        		$("' . $idprefix . '_diasbled").disabled = "disabled";
        		if($$(".innobyte-available-sites:checked").length >= ' . intval($info['c']) . '){
    				$$(".innobyte-available-sites").each(function(e){
    					if(!e.checked){
    						e.disabled = "disabled";
    					}
    				});
    			}else {
    				$$(".innobyte-available-sites").each(function(e){
    					if(!e.checked){
    						e.disabled = "";
    					}
    				});
    				if($$(".innobyte-available-sites:checked").length == 0){    				
    					$("' . $idprefix . '_diasbled").disabled = "";
    				}
    			}
        	}
        	$$(".innobyte-available-sites").each(function(e){
        		e.observe("click", function(){
        			updateInnobyteWebsites();
        		});
        	});
        	updateInnobyteWebsites();
        </script>';
        } else {
            $html = sprintf('<strong class="required">%s</strong>', $this->__('Please enter a valid key'));
        }

        return $html;
    }
}