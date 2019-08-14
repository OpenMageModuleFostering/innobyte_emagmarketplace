<?php

/**
 * Class Innobyte_EmagMarketplace_Adminhtml_Sales_OrderController
 *
 * @category Innobyte
 * @package Innobyte_EmagMarketplace
 *
 * @author Valentin Sandu <valentin.sandu@innobyte.com>
 *
 */
class Innobyte_EmagMarketplace_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Get eMAG marketplace data helper
     *
     * @return Innobyte_EmagMarketplace_Helper_Data
     */
    protected function _getHelper()
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
     * Acknowledge eMAG order action
     */
    public function emagacknowledgeAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($orderId);

        if ($order->getStatus() != Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_NEW) {
            $this->_getSession()->addError('Invalid order status!');
            $this->_redirect('*/*/view', array('_current' => true));
            return;
        }

        if (!$order->getEmagOrderId()) {
            $this->_getSession()->addError('Only eMAG orders can be acknowledged!');
            $this->_redirect('*/*/view', array('_current' => true));
            return;
        }

        try {
            /** @var $response Innobyte_EmagMarketplace_Model_Api_Response */
            $orderApiModel = $this->getOrderApiModel();
            $response = $orderApiModel
                ->setStoreId($order->getStoreId())
                ->setEmagOrderId($order->getEmagOrderId())
                ->acknowledge();

            if ($response->isError()) {
                $this->_getSession()->addError(
                    $this->getHelper()->__(
                        'eMAG Api Response => %s', implode(',', $response->getMessages())
                    )
                );

                $this->_redirect('*/*/view', array('_current' => true));
                return;
            }

            /** @var $model Innobyte_EmagMarketplace_Model_Order_Convert_Magento */
            $model = Mage::getModel('innobyte_emag_marketplace/order_convert_magento');
            $model->reReadEmagOrder($order);

            $this->_getSession()->addSuccess(
                $this->_getHelper()->__('Order successfully acknowledged.')
            );
        } catch (Innobyte_EmagMarketplace_Exception $e) {
            $this->_getSession()->addError(
                $this->_getHelper()->__($e->getMessage())
            );
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            $this->_getSession()->addError(
                $this->_getHelper()->__('There was an error while trying to acknowledge eMAG order!')
            );
        }

        $this->_redirect('*/*/view', array('_current' => true));
    }

    /**
     * Change order status to:
     *  - emag_prepared
     */
    public function emagprepareAction()
    {
        $status = Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_PREPARED;
        $this->_updateStatus($status);
    }

    /**
     * Change order status to:
     *  - emag_canceled
     */
    public function emagcancelAction()
    {
        $status = Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_CANCELED;
        $this->_updateStatus($status);
    }

    /**
     * Update eMAG order status
     *
     * @param $status
     */
    public function _updateStatus($status)
    {
        $orderId = $this->getRequest()->getParam('order_id');
        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getEmagOrderId()) {
            $this->_getSession()->addError('Not an eMAG order!');
            $this->_redirect('*/*/view', array('_current' => true));
            return;
        }

        try {
            /** @var $order Innobyte_EmagMarketplace_Model_Order_Convert_Magento */
            $order = Mage::getModel('innobyte_emag_marketplace/order_convert_magento');
            $order->setOrderId($orderId);
            $order->setStatus($status);
            $order->convert();

            $this->_getSession()->addSuccess(
                $this->_getHelper()->__('Order successfully synced with eMAG.')
            );
        } catch (Exception $e) {
            $this->_getSession()->addError(
                $this->_getHelper()->__($e->getMessage())
            );
        }

        $this->_redirect('*/*/view', array('_current' => true));
    }

    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());
        switch ($action) {
            case 'emagacknowledge':
                $aclResource = 'sales/order/actions/emag_acknowledge';
                break;
            case 'emagprepare':
                $aclResource = 'sales/order/actions/emag_prepare';
                break;
            case 'emagcancel':
                $aclResource = 'sales/order/actions/emag_cancel';
                break;
            default:
                $aclResource = 'sales/order';
                break;
        }

        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }

}
