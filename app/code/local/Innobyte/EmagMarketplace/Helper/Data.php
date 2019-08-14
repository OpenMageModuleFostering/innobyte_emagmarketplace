<?php

/**
 * Class Innobyte_EmagMarketplace_Helper_Data
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 * @author Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Config paths
     */
    const CONFIG_PATH = 'Innobyte_EmagMarketplace/';
    const CONFIG_PATH_GENERAL_SETTINGS = 'general_settings/';
    const CONFIG_PATH_API_SETTINGS = 'api_settings/';
    const CONFIG_PATH_SHIP_ORIGIN_EMAG_LOCALITY_ID = 'shipping/origin/emag_locality_id';
    const CONFIG_PATH_COURIER_ACCOUNT_ID = 'Innobyte_EmagMarketplace/shipping_settings/courier_account_id';
    const CONFIG_PATH_AWB_PDF_URL = 'Innobyte_EmagMarketplace/shipping_settings/awb_pdf_url';
    const MODULE_NAME = 'innobyte_emag_marketplace';

    /**
     * Get module config
     *
     * @param string $path
     * @param int|Mage_Core_Model_Store $storeId
     * @return string
     */
    public function getConfig($path, $storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_PATH . $path, $storeId);
    }

    /**
     * Check if extension is enabled
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return bool
     */
    public function isExtensionEnabled($storeId = null)
    {
        return (bool)$this->getConfig(self::CONFIG_PATH_GENERAL_SETTINGS . 'active', $storeId);
    }

    /**
     * Check if debug mode
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return bool
     */
    public function isDebug($storeId = null)
    {
        return (bool)$this->getConfig(self::CONFIG_PATH_GENERAL_SETTINGS . 'debug', $storeId);
    }

    /**
     * Get eMAG domain
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return string
     */
    public function getEmagDomain($storeId = null)
    {
        return (string)$this->getConfig(self::CONFIG_PATH_GENERAL_SETTINGS . 'domain', $storeId);
    }

    /**
     * Get email
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return string
     */
    public function getEmail($storeId = null)
    {
        return (string)$this->getConfig(self::CONFIG_PATH_GENERAL_SETTINGS . 'email', $storeId);
    }

    /**
     * Get email template
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return string
     */
    public function getTemplate($storeId = null)
    {
        return (string)$this->getConfig(self::CONFIG_PATH_GENERAL_SETTINGS . 'template', $storeId);
    }

    /**
     * Get allowed ip addresses to access invoice folders
     *
     * @param null $storeId
     * @return array
     */
    public function getAllowedIpAddresses($storeId = null)
    {
        $ipAddresses = explode(',', $this->getConfig(self::CONFIG_PATH_GENERAL_SETTINGS . 'allowed_ip_addresses', $storeId));

        return $ipAddresses;
    }

    /**
     * Get api url
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return string
     */
    public function getApiUrl($storeId = null)
    {
        return rtrim(strval($this->getConfig(self::CONFIG_PATH_API_SETTINGS . 'url', $storeId)), '/');
    }

    /**
     * Get api username
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return string
     */
    public function getApiUsername($storeId = null)
    {
        return (string)$this->getConfig(self::CONFIG_PATH_API_SETTINGS . 'username', $storeId);
    }

    /**
     * Get api password
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return string
     */
    public function getApiPassword($storeId = null)
    {
        return (string) $this->getConfig(self::CONFIG_PATH_API_SETTINGS . 'password', $storeId);
    }

    /**
     * Get api order url
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return string
     */
    public function getClientCode($storeId = null)
    {
        return (string)$this->getConfig(self::CONFIG_PATH_API_SETTINGS . 'code', $storeId);
    }

    /**
     * Get formatted voucher label
     *
     * @param $label
     * @param $voucherCode
     * @return string
     */
    public function getFormattedVoucherLabel($label, $voucherCode = null)
    {
        $label = $this->__($label);
        if ($voucherCode) {
            $label .= ' (' . $voucherCode . ')';
        }

        return $label;
    }

    /**
     * Retrieve adapter for http requests.
     *
     * @return string One of the Zend_Http_Client adapters.
     */
    public function getMakeHttpCallAdapter()
    {
        $returnValue = 'Zend_Http_Client_Adapter_Curl'; // default value
        $configValue = trim(strval(Mage::getConfig()->getNode('stores/default/innobyte_emag_marketplace_http_call_adapter')));
        if (strlen($configValue)
            && class_exists($configValue)
            && $configValue instanceof Zend_Http_Client_Adapter_Interface
        ) {
            $returnValue = $configValue;
        }
        return $returnValue;
    }

    /**
     * Get resource log file
     *
     * @param $resourceName
     * @return string
     */
    public function getResourceLogFile($resourceName)
    {
        return self::MODULE_NAME . '_' . $resourceName . '.log';
    }

    /**
     * Retrieve timeout for http requests.
     *
     * @return int Timeout expressed in seconds.
     */
    public function getMakeHttpCallTimeout()
    {
        $returnValue = 40; // default value
        $configValue = trim(strval(Mage::getConfig()->getNode('stores/default/innobyte_emag_marketplace_http_call_timeout')));
        if (is_numeric($configValue) && $configValue >= 0) {
            $returnValue = intval($configValue);
        }
        return $returnValue;
    }

    
    
    /**
     * Retrieve current store scope.
     *
     * @return int
     */
    public function getCurrStoreId()
    {
        $returnValue = Mage::app()->getStore()->getId();
        $store = Mage::app()->getRequest()->getParam('store');
        if ($store && Mage::app()->getStore($store)->getStoreId()) {
            $returnValue = Mage::app()->getStore($store)->getStoreId();
        }
        return $returnValue;
    }

    
    
    /**
     * Perform different checks to see if product action is eligible to continue.
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isProductActionValid($product)
    {
        $storeId = $this->getCurrStoreId();
        return ($storeId != Mage_Core_Model_App::ADMIN_STORE_ID
            && $this->isExtensionEnabled($storeId)
            && !is_null($product)
            && ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
                || $product->isConfigurable()));
    }
    
    
    
    /**
     * Checks api settings to be configured and extension to be enabled.
     * 
     * @param int|Mage_Core_Model_Store $storeId
     * @return bool
     */
    public function canMakeApiCall($storeId = null)
    {
        return ($this->isExtensionEnabled($storeId)
            && strlen($this->getApiUrl($storeId))
            && strlen($this->getApiUsername($storeId))
            && strlen($this->getApiPassword($storeId))
            && strlen($this->getClientCode($storeId)));
    }
    
    
    
    /**
     * Check if limited stock flag is enabled to be taken into consideration.
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return bool
     */
    public function isLimitedStockEnabled($storeId = null)
    {
        return (bool)$this->getConfig(self::CONFIG_PATH_GENERAL_SETTINGS . 'enable_limited_stock', $storeId);
    }
    
    
    
    /**
     * Retrieve qty limit under which product, if "in stock", should be considered as "limited stock".
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return int
     */
    public function getLimitedStockLimit($storeId = null)
    {
        return (int)$this->getConfig(self::CONFIG_PATH_GENERAL_SETTINGS . 'limited_stock', $storeId);
    }


    /**
     * Retrieve shipping origin emag locality id.
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return int
     */
    public function getShipOriginEmagLocalityId($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::CONFIG_PATH_SHIP_ORIGIN_EMAG_LOCALITY_ID, $storeId);
    }
    
    
    /**
     * Check if real stock qty shoud be sent to eMAG.
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return bool  TRUE if stock qty should be sent to eMAG, FALSE otherwise.
     */
    public function sendStockQty($storeId = null)
    {
        return (bool)$this->getConfig(self::CONFIG_PATH_GENERAL_SETTINGS . 'send_stock_qty', $storeId);
    }
    
    
    
    /**
     * Try to retrieve Magento region id based on eMAG region name.
     * "Try" means that exact match is searched, otherwise the region with a 
     * single letter difference, if only one is found.
     * 
     * @param string $regionName
     * @param string $countryCode
     * @return Mage_Directory_Model_Region|null can be null if no matching was found.
     */
    public function getMagentoRegion($regionName, $countryCode)
    {
        // search for it directly
        $region = Mage::getModel('directory/region')
            ->loadByName($regionName, $countryCode);
        if ($region->getId() > 0) {
            return $region;
        }
        // otherwise search in all reagions for that country for a closest match.
        $collection = Mage::getResourceModel('directory/region_collection')
            ->addCountryFilter($countryCode)
            ->load();
        if (!$collection->getSize()) {
            return null;
        }        
        $shortest = -1; // shortest levenshtein dist found.
        $shortestCnt = 1; // how many had the shortest distance.
        $closest = null; // the region model that has the shortest distance
        
        foreach ($collection as $region) {
            $regName = $this->getAsciiTranslitVal($region->getName());
            if ($regionName == $regName) {
                $closest = $region;
                $shortest = 0;
                break;
            } else { // calculate levenshtein distance
                $lev = levenshtein($regionName, $regName);
                if ($lev <= $shortest || $shortest < 0) {
                    if ($lev == $shortest) {
                        $shortestCnt++;
                    } else {
                        $shortestCnt = 1;
                    }
                    // set the closest match, and shortest distance
                    $closest  = $region;
                    $shortest = $lev;
                }
            }
        }        
        if (0 == $shortest && $closest->getId() > 0) { // found exact match
            return $closest;
        } elseif (1 == $shortest && 1 == $shortestCnt && $closest->getId() > 0) {
            // return single closest match, "closest" meaning that a single letter
            // should be distinct, otherwise is too risky to afirm that 
            // the eMAG region is THAT Magento region
            return $closest;
        }
        return null;
    }


    
    /**
     * Retrieve ASCII/TRANSLIT value of a string.
     * @param string $string
     * @return string
     */
    public function getAsciiTranslitVal($string)
    {
        $currentLocale = setlocale(LC_CTYPE, "0"); // iconv pb when locale set to C or POSIX on UX systems
        if ('C' == $currentLocale || 'POSIX' == $currentLocale) {
            setlocale(LC_CTYPE, 'en_US.utf8');
        }
        $returnValue = iconv('ISO-8859-1', 'ASCII//TRANSLIT', $string);
        if ('C' == $currentLocale || 'POSIX' == $currentLocale) { // set back current locale
            setlocale(LC_CTYPE, $currentLocale);
        }
        return $returnValue;
    }
    
    
    
    /**
     * Retrieve courier account id.
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return string
     */
    public function getCourierAccountId($storeId = null)
    {
        return trim(Mage::getStoreConfig(self::CONFIG_PATH_COURIER_ACCOUNT_ID, $storeId));
    }

    /**
     * Retrieve url where to read AWB PDF from.
     *
     * @param int|Mage_Core_Model_Store $storeId
     * @return string
     */
    public function getAwbPdfUrl($storeId = null)
    {
        return trim(Mage::getStoreConfig(self::CONFIG_PATH_AWB_PDF_URL, $storeId));
    }
}
