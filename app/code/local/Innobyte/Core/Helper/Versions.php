<?php


class Innobyte_Core_Helper_Versions extends Mage_Core_Helper_Abstract
{
    const EE_PLATFORM = 2;
    const PE_PLATFORM = 1;
    const CE_PLATFORM = 0;
    const ENTERPRISE = 'Enterprise';
    const ENTERPRISE_DETECT_EXTENSION = 'Enterprise';
    const ENTERPRISE_DESIGN_NAME = "enterprise";
    const PROFESSIONAL_DESIGN_NAME = "pro";
    const INSTALLED = 'advanced/modules_disable_output/';
    
    protected static $_platform = -1;
    protected $_m = 'innobyte_core';
    
    private function _e($m)
    {
        $se = $m;
        if (!Mage::getStoreConfig($se.'/license/installed') 
            || (intval(Mage::getStoreConfig($se.'/license/count')) > 20
        )) {
            return array();
        }

        $timeToUpdate = 60 * 60 * 24 * 15;
            
        $r = Mage::getStoreConfig($se.'/license/ar');
        $t = Mage::getStoreConfig($se.'/license/time');
        $s = Mage::getStoreConfig($se.'/license/websites');

        $lastCheck = str_replace($r, '', Mage::helper('core')->decrypt($t));

        $allsites = explode(',', str_replace($r, '', Mage::helper('core')->decrypt($s)));
        $allsites = array_diff($allsites, array(""));

        if (($lastCheck + $timeToUpdate) < time()) {
            $key = Mage::getStoreConfig($se.'/license/key');
            $this->a($key, intval(Mage::getStoreConfig($m.'/license/count')), $s, $se);
        }

        return $allsites;
    }

    public static function getPlatform()
    {
        if (self::$_platform == -1) {
            $pathToClaim = BP . DS . "app" . DS . "etc" . DS . "modules" 
                    . DS . self::ENTERPRISE . "_" 
                    . self::ENTERPRISE_DETECT_EXTENSION .  ".xml";
            $pathToEEConfig = BP . DS . "app" . DS . "code" . DS . "core" 
                    . DS . self::ENTERPRISE . DS 
                    . self::ENTERPRISE_DETECT_EXTENSION . DS . "etc" 
                    . DS . "config.xml";
            $isCommunity = !file_exists($pathToClaim) || !file_exists($pathToEEConfig);
            if ($isCommunity) {
                 self::$_platform = self::CE_PLATFORM;
            } else {
                $_xml = @simplexml_load_file($pathToEEConfig,'SimpleXMLElement', LIBXML_NOCDATA);
                if(!$_xml===FALSE) {
                    $package = (string)$_xml->default->design->package->name;
                    $theme = (string)$_xml->install->design->theme->default;
                    $skin = (string)$_xml->stores->admin->design->theme->skin;
                    $isProffessional = ($package == self::PROFESSIONAL_DESIGN_NAME) && ($theme == self::PROFESSIONAL_DESIGN_NAME) && ($skin == self::PROFESSIONAL_DESIGN_NAME);
                    if ($isProffessional) {
                        self::$_platform = self::PE_PLATFORM;
                        return self::$_platform;
                    }
                }
                self::$_platform = self::EE_PLATFORM;
            }
        }
        return self::$_platform;
    }

    /**
     * Convert platform from string to int and backwards
     * @static
     * @param $platformCode
     * @return int|string
     */
    public static function convertPlatform($platformCode)
    {
        if (is_numeric($platformCode)) {
            // Convert predefined to letters code
            $platform = ($platformCode == self::EE_PLATFORM ? 'ee' : ($platformCode == self::PE_PLATFORM ? 'pe'
                    : 'ce'));
        } else if (is_string($platformCode)) {
            $platformCode = strtolower($platformCode);
            $platform = ($platformCode == 'ee' ? self::EE_PLATFORM : ($platformCode == 'pe' ? self::PE_PLATFORM
                    : self::CE_PLATFORM));
        }else{$platform = self::CE_PLATFORM;}
        return $platform;
    }
    
    public function getAvailableWebsites($m) 
    {
        return $this->_e($m);
    }
    

    public static function convertVersion($v)
    {
        $digits = @explode(".", $v);
        $version = 0;
        if (is_array($digits)) {
            foreach ($digits as $k => $v) {
                $version += ($v * pow(10, max(0, (3 - $k))));
            }

        }
        return $version;
    }
    
    public function a($k, $c = 0, $s = '', $m = '')
    {
        $value = 1;
        if (!$this->_m || $this->_m == 'innobyte_core'){
            $this->_m = $m;
        }
        
        if (!$k) {
            $e = Mage::helper('core');
            $se = $this->_m;
            $value1 = Mage::getStoreConfig($se.'/license/ar');
            $value2 =(string) '';//Mage::getStoreConfig($se.'/license/websites');
            $groups = array(
                'license' => array(
                    'fields' => array(
                        'ar' => array('value' => $value1),
                        'websites' => array('value' => $value2),
                        'enabled' => array('value' => !$value),
                        'time' => array(
                            'value' => (string) $e->encrypt($value1 . (time() - (60 * 60 * 24 * 15 - 1800)) . $value1)
                        ),
                        'count' => array('value' => $value)
                    )
                )
            );

            Mage::getModel('adminhtml/config_data')
                    ->setSection($se)
                    ->setGroups($groups)
                    ->save();
            Mage::getModel('core/config')->saveConfig(self::INSTALLED.$this->_m, $value ); 
            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();
            return;
        }
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, sprintf('http://shop.innobyte.com/feeds/l.php'));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'k='.urlencode($k)
                    . '&m='.$this->_m.'&d='
                    . urlencode(implode(',', $this->getAllStoreDomains()))
                    . '&p='.$this->getPlatform()
                    . '&x='.((Mage::getStoreConfigFlag(self::INSTALLED.$this->_m)==1)?'0':'1')
                    );
            curl_setopt($ch, CURLOPT_TIMEOUT, 7);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $content = curl_exec($ch);
            if (false === $content) {
                Mage::throwException(curl_error($ch));
	    }
        
            $r = Zend_Json::decode($content);
        } catch (Exception $e){
            Mage::throwException($this->__('Please try again later. '.$e->getMessage()));
            return;
        }
        
        $e = Mage::helper('core');
        $se = $this->_m;

        $value = 1;
        if (empty($r)) {
            $value1 = Mage::getStoreConfig($se.'/license/ar');
            $value2 =(string) '';
            $groups = array(
                'license' => array(
                    'fields' => array(
                        'ar' => array('value' => $value1),
                        'websites' => array('value' => $value2),
                        'enabled' => array('value' => !$value),
                        'time' => array(
                            'value' => (string) $e->encrypt($value1 . (time() - (60 * 60 * 24 * 15 - 1800)) . $value1)
                        ),
                        'count' => array('value' => $c + 1)
                    )
                )
            );

            
            Mage::getModel('adminhtml/config_data')
                    ->setSection($se)
                    ->setGroups($groups)
                    ->save();
            Mage::getModel('core/config')->saveConfig('advanced/modules_disable_output/'.$this->_m, $value ); 
            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();

            return;
        }

        $value1 = '';
        $value2 = '';
        if (isset($r['d']) && isset($r['c'])) {
            $value1 = $e->encrypt(base64_encode(Zend_Json::encode($r)));
            if (!$s) {
                $s = Mage::getStoreConfig($se.'/license/websites');
            }
            $s = array_slice(explode(',', $r['d']), 0, $r['c']);
            $value2 = $e->encrypt($value1 . implode(',', $s) . $value1);
            $value = 0;
            Mage::getModel('core/config')->saveConfig('advanced/modules_disable_output/'.$this->_m, 0 ); 
        }
        $groups = array(
            'license' => array(
                'fields' => array(
                    'ar' => array('value' => $value1),
                    'websites' => array('value' => (string) $value2),
                    'time' => array(
                        'value' => (string) $e->encrypt($value1 . time() . $value1)
                    ),
                    'enabled' => array('value' => !$value),
                    'installed' => array('value' => 1),
                    'count' => array('value' => 0)
                )
            )
        );

        Mage::getModel('adminhtml/config_data')
                ->setSection($se)
                ->setGroups($groups)
                ->save();

        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
    }
    
    public function getAllStoreDomains()
    {
        $domains = array();
        foreach (Mage::app()->getWebsites() as $website) {
            $url = $website->getConfig('web/unsecure/base_url');
            if($domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url))){
                $domains[] = $domain;
            }
            $url = $website->getConfig('web/secure/base_url');
            if($domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url))){
                $domains[] = $domain;
            }
        }

        return array_unique($domains);
    }
    
    public function getAvailabelsWebsites($m = null) 
    {
        $this->_m = $m;
        return $this->_e($m);
    }
    
    public function avs($m)
    {
        return Zend_Json::decode(base64_decode(Mage::helper('core')->decrypt(Mage::getStoreConfig($m.'/license/ar'))));
    }
}
