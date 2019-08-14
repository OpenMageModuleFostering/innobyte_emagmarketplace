<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Api_Product
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Api_Order extends Innobyte_EmagMarketplace_Model_Api_Abstract
{

    /**
     * Order resource name
     */
    const ORDER_RESOURCE_NAME = 'order';

    /**
     * Read ORDER resource
     *
     * @return bool|mixed
     */
    public function read()
    {
        parent::read();

        return $this->_makeApiCall();
    }

    /**
     * Save ORDER resource
     *
     * @return bool|mixed
     */
    public function save()
    {
        parent::save();

        return $this->_makeApiCall();
    }

    /**
     * Count ORDER resource
     *
     * @return bool|mixed
     */
    public function count()
    {
        parent::count();

        $response = $this->_makeApiCall();
        $this->_setPaginationInfo($response);

        return $response;
    }

    /**
     * Acknowledge ORDER resource
     *
     * @return bool|mixed
     */
    public function acknowledge()
    {
        parent::acknowledge();

        return $this->_makeApiCall();
    }

    /**
     * Get resource name
     *
     * @return string
     */
    public function getResourceName()
    {
        return self::ORDER_RESOURCE_NAME;
    }

}
