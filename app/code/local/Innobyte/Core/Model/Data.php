<?php
/**
 * Data model
 *
 * @category   Innobyte
 * @package    Innobyte_Core
 * @author     Daniel Horobeanu <daniel.horobeanu@innobyte.com>
 */
class  Innobyte_Core_Model_Data extends Mage_Core_Model_Abstract
{
    private $_extensionsCache = array();
    
    public function install($event)
    {
        $s = $event->getEvent()->getSection();
        
        $displayNames = Mage::app()->loadCache('innobytecore_extensions_feed');
        if ($displayNames) {
            $this->_extensionsCache = @unserialize($displayNames);
        } else {
            $model = Mage::getModel('innobyte_core/feed')
                            ->refreshExtensions();
            if ($model) {
                $displayNames = Mage::app()->loadCache('innobytecore_extensions_feed');
                if ($displayNames) {
                    $this->_extensionsCache = @unserialize($displayNames);
                }
            }
        }
        
        if (array_key_exists(strtolower($s), array_change_key_case($this->_extensionsCache))) {
            $j = Mage::getStoreConfig($s.'/license/key');
            $c = 0;
//            $c = Mage::getStoreConfig($s.'/license/count');
            Mage::helper('innobyte_core/versions')->a($j, $c, '', $s);
        }
    }
    
    public function init($event)
    {
        if ($event->getEvent()->getObject()->getData('section') == 'advanced') {
            $g = $event->getEvent()->getObject()->getData('groups');
            $ms = $g['modules_disable_output']['fields'];
            $displayNames = Mage::app()->loadCache('innobytecore_extensions_feed');
            if ($displayNames) {
                $this->_extensionsCache = @unserialize($displayNames);
            } else {
                $model = Mage::getModel('innobyte_core/feed')
                                ->refreshExtensions();
                if ($model) {
                    $displayNames = Mage::app()->loadCache('innobytecore_extensions_feed');
                    if ($displayNames) {
                        $this->_extensionsCache = @unserialize($displayNames);
                    }
                }
            }
            
            $data = $event->getEvent()->getObject()->getData();
            foreach ($ms as $m=>$v) {
                
                
                if ($m == 'Innobyte_Core') {
                    continue;
                }
                if (strstr($m, 'Innobyte_') === false) {
                    continue;
                }
                if (array_key_exists(strtolower($m), array_change_key_case($this->_extensionsCache))) {
                    $j = Mage::getStoreConfig($m.'/license/key');
                    $info = Mage::helper('innobyte_core/versions')->avs($m);
                    $r = Mage::getStoreConfig($m.'/license/enabled');
                    
                    if (isset($info['d']) && isset($info['c']) && intval($info['c']) > 0) {
                        foreach (Mage::app()->getWebsites() as $website) {
                            $id = $website->getId();
                            if ($r !== '1') {
                                $data['groups']['modules_disable_output']['fields'][$m]['value'] = '1';
                                break;
                            }
                        }
                    } else {
                        if ( ! $r ) {
                            $data['groups']['modules_disable_output']['fields'][$m]['value'] = '1';
                        }
                    }
                }
            }
            $event->getEvent()->getObject()->setData($data);
        }
    }
}