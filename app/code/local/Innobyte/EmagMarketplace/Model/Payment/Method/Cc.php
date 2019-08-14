<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Payment_Method_Cc
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Payment_Method_Cc extends Mage_Payment_Model_Method_Abstract
{

    /**
     * Code
     */
    const EMAG_CREDITCARD = 'emag_cc';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::EMAG_CREDITCARD;

    /**
     * Hide payment in frontend
     *
     * @var bool
     */
    protected $_canUseCheckout = false;
    protected $_canUseForMultishipping = false;

}
