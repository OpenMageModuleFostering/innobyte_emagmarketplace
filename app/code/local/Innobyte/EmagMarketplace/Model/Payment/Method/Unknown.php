<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Payment_Method_Unknown
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Payment_Method_Unknown extends Mage_Payment_Model_Method_Abstract
{

    /**
     * Code
     */
    const EMAG_UNKNOWN = 'emag_unknown';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::EMAG_UNKNOWN;

    /**
     * Hide payment in frontend
     *
     * @var bool
     */
    protected $_canUseCheckout = false;
    protected $_canUseForMultishipping = false;

}
