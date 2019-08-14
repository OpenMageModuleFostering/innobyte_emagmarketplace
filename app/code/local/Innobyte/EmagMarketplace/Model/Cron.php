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

    public function testOrder()
    {
        $data = array(
            'status' => 1,
            'date' => '2015-02-02 17:50:18',
            'observation' => '',
            'id' => '12493975',
            'payment_mode' => 'RAMBURS',
            'payment_mode_id' => '1',
            'payment_status' => '0',
            'vendor_name' => 'Ishtar',
            'cancellation_request' => '',
            'has_editable_products' => '1',
            'parent_id' => '',
            'customer' => array(
                'id' => '2853241',
                'name' => 'eMAG User',
                'company' => 'eMAG Company',
                'gender' => 'M',
                'code' => 'code',
                'email' => '',
                'created' => '2015-02-02 17:50:33',
                'modified' => '2015-02-04 13:58:37',
                'bank' => 'eMAG Bank',
                'iban' => 'IBAN',
                'fax' => '021000000000',
                'mkt_id' => '2853241',
                'phone_1' => '0720000001',
                'phone_2' => '0720000002',
                'phone_3' => '0720000003',
                'registration_number' => '',
                'billing_country' => 'RO',
                'billing_suburb' => 'Timis',
                'billing_city' => 'Timisoara',
                'billing_locality_id' => '13763',
                'billing_street' => 'Calea Buziasului, nr 59B',
                'billing_postal_code' => '112233',
                'shipping_country' => 'RO',
                'shipping_suburb' => 'Timis',
                'shipping_city' => 'Timisoara',
                'shipping_locality_id' => '13763',
                'shipping_street' => 'Calea Buziasului, nr 59B',
                'shipping_postal_code' => '112233',
                'is_vat_payer' => '1',
                'legal_entity' => '0',
            ),
            'details' => array(),
            'attachments' => array(),
            'products' => array(
                0 =>
                    array(
                        'id' => '1576182',
                        'product_id' => '153',
                        'part_number' => 'intelcore2extreme',
                        'quantity' => '1',
                        'sale_price' => '9122.4555',
                        'currency' => 'RON',
                        'created' => '2015-02-02 17:50:33',
                        'modified' => '2015-02-04 13:58:37',
                        'status' => '1',
                        'attachments' => array(),
                        'details' => array(),
                        'vat' => '0.2400',
                    ),
            ),
            'shipping_tax' => '0.0000',
            'vouchers' => array(
                0 =>
                    array(
                        'id' => '280934',
                        'modified' => '2015-02-04 13:58:40',
                        'created' => '2015-02-04 13:58:40',
                        'status' => '1',
                        'voucher_id' => '3628521',
                        'sale_price_vat' => '-1.2',
                        'sale_price' => '-5',
                        'voucher_name' => 'eMAG Gift Card 1',
                        'vat' => '0.24',
                    ),
                1 =>
                    array(
                        'id' => '280935',
                        'modified' => '2015-02-04 13:58:40',
                        'created' => '2015-02-04 13:58:40',
                        'status' => '1',
                        'voucher_id' => '3628529',
                        'sale_price_vat' => '0.72',
                        'sale_price' => '-3',
                        'voucher_name' => 'eMAG Gift Card 2',
                        'vat' => '0.24',
                    )
            ),
            'proforms' => array()

        );

        $store = Mage::getModel('core/store')->load(3);

        /** @var $order Innobyte_EmagMarketplace_Model_Order_Convert_Emag */
        $order = Mage::getModel('innobyte_emag_marketplace/order_convert_emag');
        $order->setStore($store);
        $order->setEmagOrder($data);
        $order->convert();
    }

}
