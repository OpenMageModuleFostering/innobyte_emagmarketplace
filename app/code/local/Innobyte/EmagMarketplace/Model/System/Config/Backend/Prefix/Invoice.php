<?php

/**
 * Class Innobyte_EmagMarketplace_Model_System_Config_Backend_Prefix_Invoice
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_System_Config_Backend_Prefix_Invoice
    extends Innobyte_EmagMarketplace_Model_System_Config_Backend_Prefix_Abstract
{

    /**
     * Entity types
     *
     * @var array
     */
    protected $_entityType = 'invoice';

    /**
     * Set prefix value
     */
    protected function _afterSave()
    {
        $this->_prefix = $this->getFieldsetDataValue('invoice_prefix');

        return parent::_afterSave();
    }

}
