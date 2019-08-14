<?php
class Innobyte_Core_Block_Shop extends Mage_Adminhtml_Block_Template
{
    public $configPage = '';
    private $_extensionsCache = array();
    private $_shopData = null;


    protected function _prepareLayout()
    {
        $this->configPage = $this->getAction()->getRequest()
                ->getParam('section', false);
        if ($this->configPage == 'innobyte_core') {
            $this->getLayout()
                    ->getBlock('head')
                    ->addJs('innobyte/core/init.js')
                    ;
            $this->setData('extensions', $this->_initExtensions());
        } elseif ($this->configPage == 'innobyte_shop') {
            // Innobyte extensions shop
            $this->getLayout()
                    ->getBlock('head')
                    ->addJs('innobyte/core/init.js')
                    ;
            $this->setData('shop_data', $this->_getShopData());
        }
        parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        if ($this->configPage == 'innobyte_core' || $this->configPage == 'innobyte_shop') {
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    protected function _initExtensions()
    {
        $extensions = array();

        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        sort($modules);

        foreach ($modules as $moduleName) {
            if ($moduleName == 'Innobyte_Core') {
                continue;
            }
            if (strstr($moduleName, 'Innobyte_') === false) {
                continue;
            }

            // Detect extension platform
            try {
                $platform = Mage::getConfig()->getNode('modules/'.$moduleName.'/platform');
                if ($platform) {
                    $platform = strtolower($platform);
                    $ignore_platform = false;
                } else {
                    throw new Exception();
                }
            } catch (Exception $e) {
                $platform = 'ce';
                $ignore_platform = true;
            }
            $platform = Innobyte_Core_Helper_Versions::convertPlatform($platform);

            // Detect installed version
            $ver = Mage::getConfig()->getModuleConfig($moduleName)->version;
            $isPlatformValid = false;
            if ($platform >= $this->getPlatform()) {
                $isPlatformValid = true;
            }
            $feedInfo = $this->getExtensionInfo($moduleName);

            if ( ! $feedInfo->getData() ) {
                $feedInfo->setDisplayName($moduleName);
                $feedInfo->setUrl('http://shop.innobyte.com');
                $feedInfo->setVersion($ver);
            }
            
            $feedExtensionVersion = $this->_convertVersion($feedInfo->getLatestVersion());
            $installedExtVersion = $this->_convertVersion($ver);
            $upgradeAvailable = false;
            if (($feedExtensionVersion - $installedExtVersion) > 0) {
                $upgradeAvailable = true;
            }

            $extensionData = array(
                'version' => $ver,
                'name' => $moduleName,
                'is_platform_valid' => $isPlatformValid,
                'platform' => $platform,
                'feed_info' => $feedInfo,
                'upgrade_available' => $upgradeAvailable
                );
            $extensions[] = new Varien_Object($extensionData);
        }

        return $extensions;
    }

    /**
     * Convert version to comparable integer
     * @param $version
     * @return int
     */
    protected function _convertVersion($version)
    {
        $digits = @explode(".", $version);
        $version = 0;
        if (is_array($digits)) {
            foreach ($digits as $k => $version) {
                $version += ($version * pow(10, max(0, (3 - $k))));
            }
        }
        return $version;
    }


    /**
     * Get extension info from cached feed
     * @param $moduleName
     * @return bool|Varien_Object
     */
    public function getExtensionInfo($moduleName)
    {
        if (!sizeof($this->_extensionsCache)) {
//            Mage::app()->removeCache('innobytecore_extensions_feed');
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
        }
        if (array_key_exists($moduleName, $this->_extensionsCache)) {
            $data = array(
                'url' => @$this->_extensionsCache[$moduleName]['url'],
                'display_name' => @$this->_extensionsCache[$moduleName]['display_name'],
                'latest_version' => @$this->_extensionsCache[$moduleName]['version']
            );
            return new Varien_Object($data);
        }
        return new Varien_Object();
    }

    /**
     * Return icon for installed extension
     * @param $extension
     * @return Varien_Object
     */
    public function getIcon($extension)
    {
        if ($extension->getUpgradeAvailable()) {
            $iconPic = 'images/innobyte/core/error_msg_icon.gif';
            $title = "Update available";
        } elseif (!$extension->getIsPlatformValid()) {
            $iconPic = 'images/innobyte/core/note_msg_icon.gif';
            $title = "Wrong Extension Platform";
        } else {
            $iconPic = 'images/innobyte/core/icon-enabled.png';
            $title = "Installed and up to date";
        }
        $icon = new Varien_Object();
        $data = array(
            'title' => $title, 
            'source' => $this->getSkinUrl($iconPic));
        $icon->setData($data);
        return $icon;
    }

    /**
     * Fetch store data and return as Varien Object
     * @return Varien_Object
     */
    protected function _getShopData()
    {
        if (!is_null($this->_shopData)) {
            return $this->_shopData;
        }
        $connection = $this->_getShopConnection();
        $shopResponse = $connection->read();

        if ($shopResponse !== false) {
            $shopResponse = preg_split('/^\r?$/m', $shopResponse, 2);
            $shopResponse = trim($shopResponse[1]);
            Mage::app()->saveCache($shopResponse, Innobyte_Core_Helper_Config::SHOP_CACHE_KEY);
        }
        else {
            $shopResponse =  Mage::app()->loadCache(Innobyte_Core_Helper_Config::SHOP_CACHE_KEY);
            if (!$shopResponse) {
                Mage::getSingleton('adminhtml/session')
                        ->addError($this->__('Sorry, but Extensions Shop is not available now. Please try again in a few minutes.'));
            }
        }

        $connection->close();
        $this->_shopData = new Varien_Object(array('text_response' => $shopResponse));
        return $this->_shopData;
    }

    /**
     * Returns URL to store
     * @return Varien_Http_Adapter_Curl
     */
    protected function _getShopConnection()
    {
        $params = array();
        $url = array();
        foreach ($params as $k => $v) {
            $url[] = urlencode($k) . "=" . urlencode($v);
        }
        $url = rtrim(Innobyte_Core_Helper_Config::SHOP_URL) 
            . (sizeof($url) ? ("?" . implode("&", $url)) : "");

        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(array('timeout' => 5));
        $curl->write(Zend_Http_Client::GET, $url, '1.0');

        return $curl;
    }


}
 
