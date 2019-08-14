<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Payment_Method_Cashondelivery
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Payment_Method_Cashondelivery extends Mage_Payment_Model_Method_Abstract
{

    /**
     * Code
     */
    const EMAG_CASHONDELIVERY = 'emag_cashondelivery';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code  = self::EMAG_CASHONDELIVERY;

    /**
     * Hide payment in frontend
     *
     * @var bool
     */
    protected $_canUseCheckout = false;
    protected $_canUseForMultishipping = false;

}
