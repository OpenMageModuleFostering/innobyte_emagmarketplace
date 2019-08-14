<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Api_Abstract
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 * @author Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */
abstract class Innobyte_EmagMarketplace_Model_Api_Abstract
{

    /**
     * Available resource actions
     */
    const ACTION_READ = 'read';
    const ACTION_SAVE = 'save';
    const ACTION_COUNT = 'count';
    const ACTION_ACKNOWLEDGE = 'acknowledge';

    /**
     * Default no of items per page.
     */
    const DEFAULT_PAGE_SIZE = 100;
    
    /**
     * Api call action name
     *
     * @var null|string
     */
    protected $_actionName = null;

    /**
     * eMAG order id
     *
     * @var null
     */
    protected $_emagOrderId = null;

    /**
     * Store id
     *
     * @var null|int
     */
    protected $_storeId = null;

    /**
     * Data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * File
     *
     * @var array
     */
    protected $_file = null;

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = array(
        //TODO: populate this if needed
    );

    /**
     * No of items
     *
     * @var int
     */
    protected $_noOfItems = 0;

    /**
     * Pages no.
     * @var int
     */
    protected $_noOfPages = 0;

    /**
     * Items per page.
     * @var type
     */
    protected $_itemsPerPage = self::DEFAULT_PAGE_SIZE;

    /**
     * If set, pagination data will be added when calling #read()
     * @var int
     */
    protected $_currentPage = 0;

    /**
     * Read
     *
     * @return mixed
     */
    public function read()
    {
        $this->setActionName(self::ACTION_READ);
        if ($this->_currentPage > 0 && $this->getItemsPerPage() > 0) {
            $pagination = array(
                'currentPage' => $this->_currentPage,
                'itemsPerPage' => $this->getItemsPerPage(),
            );
            $this->setData(array_merge($this->getData(), $pagination));
        }
    }

    /**
     * Save
     *
     * @return mixed
     */
    public function save()
    {
        $this->setActionName(self::ACTION_SAVE);
    }

    /**
     * Count
     *
     * @return mixed
     */
    public function count()
    {
        $this->setActionName(self::ACTION_COUNT);
    }

    /**
     * Acknowledge
     *
     * @return mixed
     */
    public function acknowledge()
    {
        $this->setActionName(self::ACTION_ACKNOWLEDGE);
    }

    /**
     * Get resource name
     *
     * @return string
     */
    abstract public function getResourceName();

    /**
     * Set api call action name
     *
     * @param $action
     * @return Innobyte_EmagMarketplace_Model_Api_Abstract
     */
    public function setActionName($action)
    {
        $this->_actionName = $action;

        return $this;
    }

    /**
     * Get api call action name
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->_actionName;
    }

    /**
     * Set eMAG order id required for acknowledge
     *
     * @param $orderId
     * @return Innobyte_EmagMarketplace_Model_Api_Abstract
     */
    public function setEmagOrderId($orderId)
    {
        $this->_emagOrderId = $orderId;

        return $this;
    }

    /**
     * Get eMAG order id required for acknowledge
     *
     * @return int
     */
    public function getEmagOrderId()
    {
        return $this->_emagOrderId;
    }

    /**
     * Setter method for store id property
     *
     * @param int $storeId
     * @return Innobyte_EmagMarketplace_Model_Api_Abstract
     */
    public function setStoreId($storeId)
    {
        if (is_numeric($storeId)) {
            $this->_storeId = (int)$storeId;
        }
        return $this;
    }

    /**
     * Getter method for store id property.
     *
     * @return int|null Store 's id.
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * Set data
     *
     * @param $data
     * @return Innobyte_EmagMarketplace_Model_Api_Abstract
     */
    public function setData($data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * File
     *
     * @param $file
     * @return Innobyte_EmagMarketplace_Model_Api_Abstract
     */
    public function setFile($file)
    {
        $this->_file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return array
     */
    public function getFile()
    {
        return $this->_file;
    }

    /**
     * Get eMAG marketplace data helper
     *
     * @return Innobyte_EmagMarketplace_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('innobyte_emag_marketplace');
    }

    /**
     * Get api url for resource and action
     *
     * @return string
     */
    protected function getResourceActionApiUrl()
    {
        $url = $this->getHelper()->getApiUrl($this->getStoreId())
            . DS . $this->getResourceName() . DS . $this->getActionName();

        $orderId = $this->getEmagOrderId();
        if ($orderId) {
            $url .= DS . $orderId;
        }

        return $url;
    }

    /**
     * Make an API call.
     *
     * @internal array  $postData Only the 'data' key of the final post
     *                            (no vendor code, pwd, username, hash)
     * @internal array $file An array with key the formname for that file
     *                    field, and value the path to file to upload.(optional)
     * @return Innobyte_EmagMarketplace_Model_Api_Response  Api response object.
     * @throws Innobyte_EmagMarketplace_Exception if some error occurred.
     */
    protected function _makeApiCall()
    {
        $returnValue = null;

        $file = $this->getFile();
        $postData = $this->getData();
        $url = $this->getResourceActionApiUrl();

        $requestData = array(
            'code' => $this->getHelper()->getClientCode($this->getStoreId()),
            'username' => $this->getHelper()->getApiUsername($this->getStoreId()),
            'data' => $postData,
            'hash' => sha1(
                http_build_query($postData) .
                sha1($this->getHelper()->getApiPassword($this->getStoreId()))
            ),
            'debug_info' => array(
                'site' => Mage::app()->getStore($this->getStoreId())
                    ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),
                'platform' => 'Magento',
                'version' => Mage::getVersion(),
                'extension_version' =>  (string) Mage::getConfig()
                    ->getModuleConfig('Innobyte_EmagMarketplace')->version,
            ),
        );

        $requestDebugData = $postData;
        if (is_array($file)) { // TODO check if we need this
            $requestDebugData = array_merge(
                $requestData,
                array(key($file) => file_get_contents(current($file)))
            );
        } else {
            $requestDebugData = $requestData;
        }
        $debugData = array('url' => $url, 'request' => $requestDebugData);
        
        try {
            $response = $this->_makeHttpCall(
                $url,
                $requestData,
                Zend_Http_Client::POST,
                $file
            );
            $apiResponse = Zend_Json::decode(
                $response->getBody(),
                Zend_Json::TYPE_ARRAY
            );
            $returnValue = Mage::getModel(
                'innobyte_emag_marketplace/api_response',
                $apiResponse
            );
            
            $debugData['response'] = $apiResponse;
            $this->debugData($debugData, $this->getResourceName()); // debug
        } catch (Zend_Json_Exception $zjEx) {
            $debugData['response [ERR]'] = 'Code: ' . $zjEx->getCode()
                . ' Msg: ' . $zjEx->getMessage();
            $this->debugData($debugData, $this->getResourceName()); // debug
            throw new Innobyte_EmagMarketplace_Exception(
                'Json decode error: ' . $zjEx->getMessage(), $zjEx->getCode()
            );
        } catch (Innobyte_EmagMarketplace_Exception $iemEx) {
            $debugData['response [ERR]'] = 'Code: ' . $iemEx->getCode()
                . ' Msg: ' . $iemEx->getMessage();
            $this->debugData($debugData, $this->getResourceName()); // debug
            throw $iemEx;
        } catch (Exception $e) {
            $debugData['response [ERR]'] = 'Code: ' . $e->getCode()
                . ' Msg: ' . $e->getMessage();
            $this->debugData($debugData, $this->getResourceName()); // debug
            throw new Innobyte_EmagMarketplace_Exception(
                'An error occurred. Please try again later.'
            );
        }

        return $returnValue;
    }

    
    
    /**
     * Make a HTTP call.
     *
     * @param  string   $url     Url to make request to.
     * @param  array    $data    Data to send / parameters
     * @param  array    $method  Http method (GET | POST | ...)
     *                           (optional, default is POST)
     * @param  array    $file    An array with key the file field name,
     *                           and value the path to file to upload.(optional)
     * @return Zend_Http_Response The http response object.
     * @throws Innobyte_EmagMarketplace_Exception if an error occurred.
     */
    protected function _makeHttpCall(
        $url,
        array $data = array(),
        $method = Zend_Http_Client::POST,
        $file = null
    )
    {
        $config = array(
            'adapter' => $this->getHelper()->getMakeHttpCallAdapter(),
            'timeout' => $this->getHelper()->getMakeHttpCallTimeout(),
        );
        
        $webClient = new Zend_Http_Client();
        $webClient->setUri($url)
                  ->setConfig($config)
                  ->setMethod($method);
        
        if (Zend_Http_Client::GET == $method) {
            foreach ($data as $key => $value) {
                $webClient->setParameterGet($key, $value);
            }
        } else {
            foreach ($data as $key => $value) {
                $webClient->setParameterPost($key, $value);
            }
        }
        if (is_array($file)) { // TODO: check if we need file / files or not.
            $webClient->setFileUpload(current($file), key($file));
        }
        
        $returnValue = $webClient->request();
        if ($returnValue->isError()) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Http error response: ' . $returnValue->getMessage(),
                $returnValue->getStatus()
            );
        }
        
        return $returnValue;
    }

    /**
     * Log debug data to file.
     *
     * @param mixed $debugData
     * @param string $resource One of the Api classes resource name constatnts
     *            Ex: Innobyte_EmagMarketplace_Model_Api_Awb::AWB_RESOURCE_NAME
     * @param $resource
     */
    public function debugData($debugData, $resource)
    {
        if ($this->getHelper()->isDebug($this->getStoreId())) {
            Mage::getModel(
                'core/log_adapter',
                $this->getHelper()->getResourceLogFile($resource)
            )->setFilterDataKeys($this->_debugReplacePrivateDataKeys)
                ->log($debugData);
        }
    }

    /**
     * Getter method for no of items property.
     *
     * @return int
     */
    public function getNoOfItems()
    {
        return $this->_noOfItems;
    }

    /**
     * Getter method for no of pages property.
     *
     * @return int
     */
    public function getNoOfPages()
    {
        return $this->_noOfPages;
    }


    /**
     * Getter method for items per page property.
     *
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->_itemsPerPage;
    }


    /**
     * Setter method for current page property.
     *
     * @param int $currentPage Current page to be read.
     * @return Innobyte_EmagMarketplace_Model_Api_Abstract
     */
    public function setCurrentPage($currentPage)
    {
        $this->_currentPage = intval($currentPage);

        return $this;
    }

    /**
     * Setter method for items per page property.
     *
     * @param int $pageSize
     * @return Innobyte_EmagMarketplace_Model_Api_Abstract
     */
    public function setPageSize($pageSize)
    {
        $this->_itemsPerPage = intval($pageSize);

        return $this;
    }


    /**
     * Imports pagination info from #count() response.
     *
     * @param Innobyte_EmagMarketplace_Model_Api_Response $countResponse
     *                                          Api call response from #count()
     * @return Innobyte_EmagMarketplace_Model_Api_Abstract
     */
    protected function _setPaginationInfo(
        Innobyte_EmagMarketplace_Model_Api_Response $countResponse
    )
    {
        if (is_null($countResponse) || $countResponse->isError()) {
            return $this;
        }
        $noOfPages = 0;
        $itemsPerPage = self::DEFAULT_PAGE_SIZE;
        $countResult = $countResponse->getResults();
        if (array_key_exists('noOfPages', $countResult)
            && array_key_exists('itemsPerPage', $countResult)
            && $countResult['noOfPages'] > 0
            && $countResult['itemsPerPage'] > 0
        ) {
            $noOfPages = intval($countResult['noOfPages']);
            $itemsPerPage = intval($countResult['itemsPerPage']);
        } elseif (array_key_exists('noOfItems', $countResult)
            && $countResult['noOfItems'] > 0
        ) {
            $noOfPages = ceil($countResult['noOfItems'] / $itemsPerPage);
        }

        $this->_noOfItems = isset($countResult['noOfItems'])
            ? intval($countResult['noOfItems']) : 0;
        $this->_noOfPages = $noOfPages;
        $this->_itemsPerPage = $itemsPerPage;
        return $this;
    }
}
