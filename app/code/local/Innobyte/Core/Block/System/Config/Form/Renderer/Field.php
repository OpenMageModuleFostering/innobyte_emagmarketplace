<?php

class Innobyte_Core_Block_System_Config_Form_Renderer_Field 
    extends Mage_Adminhtml_Block_System_Config_Form_Field 
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) 
    {
        $value = 1;
        Mage::register('inno-',$element->getHint());
        $websites = Mage::helper('innobyte_core/versions')
                ->getAvailableWebsites($element->getHint());
        if (!empty($websites)) {
             $scopeWebsiteCode = $this->getRequest()->getParam('website');
             $scopeWebsite = Mage::getModel('core/website')
                     ->load($this->getRequest()->getParam('website'), 'code');
             $url = $scopeWebsite->getConfig('web/unsecure/base_url');                                                                                                                                               
             $domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url));
             if ($domain && $scopeWebsite && in_array($domain, $websites)) {               
                 $html = '<strong class="ok">' . $this->__('Licensed') . '</strong>';
             } elseif (!$scopeWebsiteCode) {
                 $domains = Mage::helper('innobyte_core/versions')->getAllStoreDomains();
                 $html = '<strong class="ok">' . $this->__('Licensed') . '</strong>';
             } elseif ($scopeWebsiteCode && in_array($scopeWebsiteCode, $websites)) {
                 $html = '<strong class="ok">' . $this->__('Licensed') .$scopeWebsiteCode. '</strong>';
             } else {
                 $html = '<strong class="required">' . $this->__('Please buy additional licence for the domain :') .'<br/>'.$domain. '</strong>';
             }
         } else {
            Mage::getModel('core/config')->saveConfig(Innobyte_Core_Helper_Versions::INSTALLED.$element->getHint(), $value ); 
            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();
            $html = '<strong class="required">' . $this->__('Please enter a valid key') . '</strong>';
        }
        return $html;
    }
}