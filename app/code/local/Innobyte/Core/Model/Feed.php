<?php
/**
 * AdminNotification Feed model
 *
 * @category   Innobyte
 * @package    Innobyte_Core
 * @author     Daniel Horobeanu <daniel.horobeanu@innobyte.com>
 */
class  Innobyte_Core_Model_Feed extends Mage_AdminNotification_Model_Feed
{
    const XML_USE_HTTPS_PATH    = 'innobyte_core/adminnotification/use_https';
    const XML_FEED_URL_PATH     = 'innobyte_core/adminnotification/url';
    const XML_FREQUENCY_PATH    = 'innobyte_core/adminnotification/frequency';
    const XML_LAST_UPDATE_PATH  = 'innobyte_core/adminnotification/last_update';

    /**
     * Get news from Innobyte Magento blog posts feed
     *
     * @return Mage_AdminNotification_Model_Feed
     */
    public function news()
    {
        if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
            return $this;
        }
        $feedData = array();
        $feedXml = $this->getFeedData();
        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                $feedData[] = array(
                    'severity'      => (int)$item->severity?(int)$item->severity:Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE,
                    'date_added'    => $this->getDate((string)$item->pubDate),
                    'title'         => (string)$item->title,
                    'description'   => (string)$item->description,
                    'url'           => (string)$item->link,
                );
            }
            if ($feedData) {
                Mage::getModel('adminnotification/inbox')
                        ->parse(array_reverse($feedData));
            }
        }
        $this->setLastUpdate();

        return $this;
    }
    
    
    public function checkExtensions()
    {
        $passedTime = time() - Mage::app()->loadCache('innobytecore_extensions_lastcheck');
        if (!(Mage::app()->loadCache('innobytecore_extensions_feed'))
                || $passedTime > Mage::getStoreConfig(self::XML_FREQUENCY_PATH)) {
            $this->refreshExtensions();
        }
    }

    public function refreshExtensions()
    {
        $exts = array();
        try {
            $node = $this->getFeedData(Innobyte_Core_Helper_Config::EXTENSIONS_FEED_URL);
            if (!$node){
                return false;
            }
            foreach ($node->children() as $ext) {
                $exts[(string)$ext->name] = array(
                    'display_name' => (string)$ext->display_name,
                    'version' => (string)$ext->version,
                    'url' => (string)$ext->url
                );
            }

            Mage::app()->saveCache(serialize($exts), 'innobytecore_extensions_feed');
            Mage::app()->saveCache(time(), 'innobytecore_extensions_lastcheck');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    
    public function checkUpdates()
    {
        $passedTime = time() - Mage::app()->loadCache('innobytecore_updates_lastcheck');
        if ($passedTime > Mage::getStoreConfig(self::XML_FREQUENCY_PATH)) {
            $this->refreshUpdates();
        }
    }

    public function refreshUpdates()
    {
        $feedData = array();
        try {
            $node = $this->getFeedData(Innobyte_Core_Helper_Config::UPDATES_FEED_URL);
            if (!$node) {
                return false;
            }
            foreach ($node->children() as $item) {
                if ($this->isInteresting($item)) {
                    $date = strtotime((string)$item->date);
                    if (!Mage::getStoreConfig('innobyte_core/install/run') 
                        || (Mage::getStoreConfig('innobyte_core/install/run') < $date)
                    ) {
                        $feedData[] = array(    
                            'severity' => (int)$item->severity?(int)$item->severity:Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE,
                            'date_added' => $this->getDate((string)$item->date),
                            'title' => (string)$item->title,
                            'description' => (string)$item->content,
                            'url' => (string)$item->url,
                        );
                    }
                }
            }

            $adminnotificationModel = Mage::getModel('adminnotification/inbox');
            if ($feedData && is_object($adminnotificationModel)) {
                $adminnotificationModel->parse($feedData);
            }

            Mage::app()->saveCache(time(), 'innobytecore_updates_lastcheck');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function isInteresting($item)
    {
        $interests = @explode(',', Mage::getStoreConfig('innobyte_core/adminnotification/feeds'));

        $types = @explode(",", (string)$item->type);
        $exts = @explode(",", (string)$item->extensions);

        foreach($types as $type){
            if(array_search($type, $interests) !== false){
                return true;
            }
            if($type == Innobyte_Core_Model_Source_Feed_Type::TYPE_RELEASE){
                foreach($exts as $ext){
                    if ($this->isExtensionInstalled($ext)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    public function isExtensionInstalled($code)
    {
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());

        foreach ($modules as $moduleName) {
            if ($moduleName == $code) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve DB date from RSS date
     *
     * @param string $rssDate
     * @return string YYYY-MM-DD YY:HH:SS
     */
    public function getDate($rssDate)
    {
        return gmdate('Y-m-d H:i:s', strtotime($rssDate));
    }

    /**
     * Retrieve Update Frequency
     *
     * @return int
     */
    public function getFrequency()
    {
        return Mage::getStoreConfig(self::XML_FREQUENCY_PATH) * 3600;
    }

    public function getLastUpdate()
    {
        return Mage::app()->loadCache('innobytecore_notifications_lastcheck');
    }

    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'innobytecore_notifications_lastcheck');

        return $this;
    }

    /**
     * Retrieve feed data as XML element
     *
     * @return SimpleXMLElement
     */
    public function getFeedData($url = null)
    {
        if (!$url){
            $url = $this->getFeedUrl();
        }
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(array(
            'timeout'   => 2
        ));
        $curl->write(Zend_Http_Client::GET, $url, '1.0');
        $data = $curl->read();

        if (Zend_Http_Response::extractCode($data) != 200){
            $url = 'www.innobyte.com/blog/category/magento/feed/';
            $curl->write(Zend_Http_Client::GET, $url, '1.0');
            $data = $curl->read();
        }
        
        if (Zend_Http_Response::extractCode($data) != 200){
            return false;
        }
        if ($data === false) {
            return false;
        }
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);
        $curl->close();

        try {
            libxml_use_internal_errors(true);
            $xml  = new SimpleXMLElement($data);
        } catch (Exception $e) {
            return false;
        }

        return $xml;
    }
    
    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $prot = 'http://';
            if (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH)) {
                $prot = 'https://';
            }
            $this->_feedUrl = $prot.Mage::getStoreConfig(self::XML_FEED_URL_PATH);
        }
        return $this->_feedUrl;
    }
}
