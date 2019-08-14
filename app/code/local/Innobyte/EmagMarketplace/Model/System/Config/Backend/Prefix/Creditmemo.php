<?php

/**
 * Class Innobyte_EmagMarketplace_Model_System_Config_Backend_Prefix_Creditmemo
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_System_Config_Backend_Prefix_Creditmemo
    extends Innobyte_EmagMarketplace_Model_System_Config_Backend_Prefix_Abstract
{

    /**
     * Entity types
     *
     * @var array
     */
    protected $_entityType = 'creditmemo';

    /**
     * Set prefix value
     */
    protected function _afterSave()
    {
        $this->_prefix = $this->getFieldsetDataValue('creditmemo_prefix');

        return parent::_afterSave();
    }

}
