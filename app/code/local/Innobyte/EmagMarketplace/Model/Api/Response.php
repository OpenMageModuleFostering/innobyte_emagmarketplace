<?php

/**
 * eMAG api respose.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Api_Response
{
    /**
     * Flag that indicates if response was erratic or not.
     *
     * @var bool
     */
    protected $_isError;

    /**
     * API Response Messages.
     *
     * @var array
     */
    protected $_messages;

    /**
     * API Response Results.
     *
     * @var array|int
     */
    protected $_results;


    /**
     * Constructor. initializes stuffs.
     *
     * @param array $apiResponse Api response.
     *
     * @throws Innobyte_EmagMarketplace_Exception
     *              If invalid api response format is provided.
     */
    public function __construct(array $apiResponse)
    {
        if (!array_key_exists('isError', $apiResponse)) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Invalid api response format. "isError" is missing.'
            );
        }
        if (!array_key_exists('messages', $apiResponse)) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Invalid api response format. "messages" is missing.'
            );
        }
        if (!is_array($apiResponse['messages'])) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Invalid api response format. "messages" has invalid format.'
            );
        }
        if (!array_key_exists('results', $apiResponse)) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Invalid api response format. "results" is missing.'
            );
        }
        if (!is_array($apiResponse['results'])) {
            if (!is_numeric($apiResponse['results']) && !is_bool($apiResponse['results'])) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Invalid api response format. "results" has invalid format.'
                );
            }
        }
        $this->_isError = (bool)$apiResponse['isError'];
        $this->_messages = $apiResponse['messages'];
        $this->_results = $apiResponse['results'];
    }


    /**
     * Getter method for messages property.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }


    /**
     * Getter method for results property.
     * On 'order' related requests this maybe numeric, otherwise array.
     *
     * @return array|int
     */
    public function getResults()
    {
        return $this->_results;
    }


    /**
     * Getter method for isError flag.
     *
     * @return boolean
     */
    public function isError()
    {
        return $this->_isError;
    }
}
