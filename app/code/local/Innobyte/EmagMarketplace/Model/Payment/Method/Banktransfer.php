<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Payment_Method_Banktransfer
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Payment_Method_Banktransfer extends Mage_Payment_Model_Method_Abstract
{

    /**
     * Code
     */
    const EMAG_BANKTRANSFER = 'emag_banktransfer';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::EMAG_BANKTRANSFER;

    /**
     * Hide payment in frontend
     *
     * @var bool
     */
    protected $_canUseCheckout = false;
    protected $_canUseForMultishipping = false;

}
