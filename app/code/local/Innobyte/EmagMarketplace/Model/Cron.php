<?php

/**
 * Class Innobyte_EmagMarketplace_Model_Cron
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 */
class Innobyte_EmagMarketplace_Model_Cron extends Mage_Core_Model_Abstract
{

    /**
     * Sync errors
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Email send flag
     *
     * @var bool
     */
    protected $_canSendEmail = false;

    /**
     * Add sync error
     *
     * @param string $message
     * @param bool $notice
     * @return Innobyte_EmagMarketplace_Model_Cron
     */
    public function addError($message, $notice = false)
    {
        if (!$notice) {
            $this->_canSendEmail = true;
        }
        $this->_errors[] = $message;

        return $this;
    }

    /**
     * Reset errors array
     *
     * @return Innobyte_EmagMarketplace_Model_Cron
     */
    public function resetErrors()
    {
        $this->_errors = array();

        return $this;
    }

    /**
     * Get sync errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Get email send flag
     *
     * @return bool
     */
    public function canSendEmail()
    {
        return $this->_canSendEmail;
    }

    /**
     * Reset email send flag
     *
     * @return bool
     */
    public function resetEmailFlag()
    {
        $this->_canSendEmail = false;

        return false;
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
     * @return Innobyte_EmagMarketplace_Model_Api_Order
     */
    public function getOrderApiModel()
    {
        return Mage::getModel('innobyte_emag_marketplace/api_order');
    }

    /**
     * Read eMAG orders
     *
     * @return bool
     */
    public function readOrders()
    {
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                /** @var $store Mage_Core_Model_Store */
                foreach ($stores as $store) {
                    if (!$this->getHelper()->canMakeApiCall($store->getId())) {
                        continue;
                    }

                    // reset erros and email flag
                    $this->resetErrors();
                    $this->resetEmailFlag();

                    $this->addError(
                        $this->getHelper()->__(
                            'Sync process started for store: %s', $store->getFrontendName()
                        ),
                        true
                    );

                    $api = $this->getOrderApiModel()->setStoreId($store->getId());

                    /** @var $response Innobyte_EmagMarketplace_Model_Api_Response */
                    $response = $api->count();

                    // continue to next store if api order count returns error
                    if ($response->isError()) {
                        foreach ($response->getMessages() as $message) {
                            $this->addError($message);
                        }
                        continue;
                    }

                    // continue to next store if no orders found
                    if ($api->getNoOfItems() == 0) {
                        continue;
                    }

                    // loop through all pages and get orders
                    for ($currentPage = 1; $currentPage <= $api->getNoOfPages(); $currentPage++) {
                        $response = $this->getOrderApiModel()
                            ->setStoreId($store->getId())
                            ->setCurrentPage($currentPage)
                            ->read();

                        if (!$this->_processEmagOrders($store, $response)) {
                            continue;
                        }
                    }

                    $this->addError(
                        $this->getHelper()->__(
                            'Sync process finished for store: %s', $store->getFrontendName()
                        ),
                        true
                    );

                    if ($this->canSendEmail()) {
                        $this->_sendEmail($store);
                    }
                }
            }
        }
    }

    /**
     * Process eMAG orders
     *
     * @param $store
     * @param Innobyte_EmagMarketplace_Model_Api_Response $response
     * @return bool
     */
    protected function _processEmagOrders($store, $response)
    {
        // continue to next store if api order read returns error
        if ($response->isError()) {
            foreach ($response->getMessages() as $message) {
                $this->addError($message);
            }
            return false;
        }

        $emagOrders = $response->getResults();
        foreach ($emagOrders as $emagOrder) {
            Mage::log(
                $this->getHelper()->__('Sync process started for order: #%s', $emagOrder['id']),
                Zend_Log::NOTICE,
                $this->getHelper()->getResourceLogFile('order'),
                true
            );
            $this->addError($this->getHelper()->__('Sync process started for order: #%s', $emagOrder['id']), true);

            try {
                /** @var $order Innobyte_EmagMarketplace_Model_Order_Convert_Emag */
                $order = Mage::getModel('innobyte_emag_marketplace/order_convert_emag');
                $order->setStore($store);
                $order->setEmagOrder($emagOrder);
                $order->convert();
            } catch (Innobyte_EmagMarketplace_Exception $e) {
                Mage::log(
                    $this->getHelper()->__(
                        'eMAG order: #%s could not be processed. ERROR: %s',
                        $emagOrder['id'],
                        $e->getMessage()
                    ),
                    Zend_Log::ERR,
                    $this->getHelper()->getResourceLogFile('order'),
                    true
                );
                $this->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::log(
                    $e->getMessage(),
                    Zend_Log::ERR,
                    $this->getHelper()->getResourceLogFile('order'),
                    true
                );
                $this->addError($e->getMessage());
            }

            Mage::log(
                $this->getHelper()->__('Sync process finished for order: #%s', $emagOrder['id']),
                Zend_Log::NOTICE,
                $this->getHelper()->getResourceLogFile('order'),
                true
            );
            $this->addError($this->getHelper()->__('Sync process finished for order: #%s', $emagOrder['id']), true);
        }

        return true;
    }

    /**
     * Send email on sync error
     *
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    protected function _sendEmail($store)
    {
        $recipientName = '';
        $recipientEmail = $this->getHelper()->getEmail($store->getId());
        if (!$recipientEmail) {
            return false;
        }

        try {
            $templateId = $this->getHelper()->getTemplate($store->getId());
            if (!$templateId) {
                return false;
            }

            // set sender information
            $sender = array(
                'name' => $this->getHelper()->__('Innobyte eMAG Marketplace'),
                'email' => Mage::getStoreConfig('trans_email/ident_general/email', $store->getId())
            );

            // set variables that can be used in email template
            $vars = array(
                'store' => $store,
                'errors' => $this->_processErrors($this->getErrors()),
            );

            /** @var $translate Mage_Core_Model_Translate */
            $translate = Mage::getSingleton('core/translate');

            /** @var $mailer Mage_Core_Model_Email_Template */
            $mailer = Mage::getModel('core/email_template');
            $mailer->sendTransactional(
                $templateId,
                $sender,
                $recipientEmail,
                $recipientName,
                $vars,
                $store->getId()
            );

            $translate->setTranslateInline(true);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Process sync errors
     *
     * @param $messages
     * @return string
     */
    protected function _processErrors($messages)
    {
        $errors = '';
        foreach ($messages as $message) {
            $errors .= '<p>' . $message . '</p>';
        }

        return $errors;
    }

}
