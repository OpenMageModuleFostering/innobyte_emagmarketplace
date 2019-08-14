<?php

/**
 * Adminhtml Core IndexController.
 *
 * @category   Innobyte
 * @package    Innobyte_Core
 * @author     Daniel Horobeanu <daniel.horobeanu@innobyte.com>
 *
 */
class Innobyte_Core_Adminhtml_IndexController 
    extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * Send report
     */
    public function innoCoreSendReportAction()
    {
        $result['code'] = 'success';
        $result['message'] = 'Report was successfully sent.';
        try {
            $message = $this->getRequest()->getPost('message', 'No message');
            $model = Mage::getModel('innobyte_core/debug');
            $model->sendEmail($message);
        } catch (Exception $e) {
            $result['code'] = 'error';
            $result['message'] = $e->getMessage();
        }
        
        Mage::app()->getResponse()->setBody(json_encode($result));
    }

}
