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
                    $this->_getHelper()->__(
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
    public function emagPrepareAction()
    {
        try {
            $orderId = $this->getRequest()->getParam('order_id');
            $status = Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_PREPARED;
            $this->_updateStatus($status, $orderId);

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
     * Mass change order status to:
     *  - emag_prepared
     */
    public function massEmagPrepareAction()
    {
        $status = Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_PREPARED;
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countPrepareOrder = 0;
        $countNonPrepareOrder = 0;
        foreach ($orderIds as $orderId) {
            try{
                $this->_updateStatus($status, $orderId);
                $countPrepareOrder++;
            }
            catch(Exception $e) {
                $countNonPrepareOrder++;
            }
        }
        if ($countNonPrepareOrder) {
            if ($countPrepareOrder) {
                $this->_getSession()->addError($this->__('%s order(s) cannot be prepared.', $countNonPrepareOrder));
            } else {
                $this->_getSession()->addError($this->__('The order(s) cannot be prepared.'));
            }
        }
        if ($countPrepareOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been prepared.', $countPrepareOrder));
        }

        $this->_redirect('*/*/');
    }

    /**
     * Change order status to:
     *  - emag_finalized
     */
    public function emagFinalizeAction()
    {
        try {
            $orderId = $this->getRequest()->getParam('order_id');
            $status = Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_FINALIZED;
            $this->_updateStatus($status, $orderId);

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
     * Mass change order status to:
     *  - emag_finalized
     */
    public function massEmagFinalizeAction()
    {
        $status = Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_FINALIZED;
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countFinalizeOrder = 0;
        $countNonFinalizeOrder = 0;
        foreach ($orderIds as $orderId) {
            try{
                $this->_updateStatus($status, $orderId);
                $countFinalizeOrder++;
            }
            catch(Exception $e) {
                $countNonFinalizeOrder++;
            }
        }
        if ($countNonFinalizeOrder) {
            if ($countFinalizeOrder) {
                $this->_getSession()->addError($this->__('%s order(s) cannot be finalized.', $countNonFinalizeOrder));
            } else {
                $this->_getSession()->addError($this->__('The order(s) cannot be finalized.'));
            }
        }
        if ($countFinalizeOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been finalized.', $countFinalizeOrder));
        }

        $this->_redirect('*/*/');
    }

    /**
     * Change order status to:
     *  - emag_canceled
     */
    public function emagCancelAction()
    {
        try {
            $orderId = $this->getRequest()->getParam('order_id');
            $status = Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_CANCELED;
            $this->_updateStatus($status, $orderId);

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
     * Mass change order status to:
     *  - emag_canceled
     */
    public function massEmagCancelAction()
    {
        $status = Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_CANCELED;
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countCancelOrder = 0;
        $countNonCancelOrder = 0;
        foreach ($orderIds as $orderId) {
            try{
                $this->_updateStatus($status, $orderId);
                $countCancelOrder++;
            }
            catch(Exception $e) {
                $countNonCancelOrder++;
            }
        }
        if ($countNonCancelOrder) {
            if ($countCancelOrder) {
                $this->_getSession()->addError($this->__('%s order(s) cannot be canceled.', $countNonCancelOrder));
            } else {
                $this->_getSession()->addError($this->__('The order(s) cannot be canceled.'));
            }
        }
        if ($countCancelOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been canceled.', $countCancelOrder));
        }

        $this->_redirect('*/*/');
    }

    /**
     * Update eMAG order status
     *
     * @param string $status
     * @param int $orderId
     */
    public function _updateStatus($status, $orderId)
    {
        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getEmagOrderId()) {
            Mage::throwException('Not an eMAG order!');
        }

        /** @var $model Innobyte_EmagMarketplace_Model_Sales_Order */
        $model = Mage::getModel('innobyte_emag_marketplace/sales_order');
        switch ($status) {
            case Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_PREPARED:
                if (!$model->canPrepare($order)) {
                    Mage::throwException('Order #%s can not be prepared!', $order->getIncrementId());
                }
                break;
            case Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_FINALIZED:
                if (!$model->canFinalize($order)) {
                    Mage::throwException('Order #%s can not be finalized!', $order->getIncrementId());
                }
                break;
            case Innobyte_EmagMarketplace_Model_Order_Convert_Abstract::STATUS_CANCELED:
                if (!$model->canCancel($order)) {
                    Mage::throwException('Order #%s can not be canceled!', $order->getIncrementId());
                }
                break;
        }

        try {
            /** @var $order Innobyte_EmagMarketplace_Model_Order_Convert_Magento */
            $order = Mage::getModel('innobyte_emag_marketplace/order_convert_magento');
            $order->setOrderId($orderId);
            $order->setStatus($status);
            $order->convert();
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }
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
            case 'emagAcknowledge':
                $aclResource = 'sales/order/actions/emag_acknowledge';
                break;
            case 'emagPrepare':
                $aclResource = 'sales/order/actions/emag_prepare';
                break;
            case 'emagCancel':
                $aclResource = 'sales/order/actions/emag_cancel';
                break;
            default:
                $aclResource = 'sales/order';
                break;
        }

        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }

}
