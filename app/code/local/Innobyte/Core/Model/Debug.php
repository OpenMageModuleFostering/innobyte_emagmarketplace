<?php
/**
 * Debugger model.
 *
 * @category   Innobyte
 * @package    Innobyte_Core
 * @author     Daniel Horobeanu <daniel.horobeanu@innobyte.com>
 */
class  Innobyte_Core_Model_Debug 
    extends Mage_Core_Model_Abstract
{
    const XML_RECIPIENTS_EMAIL_PATH = 'innobyte_core/debugger/email';
    
    public function sendEmail($message)
    {
        $templateId = Mage::getStoreConfig('innobyte_core/debugger/email_template');
        if (!$templateId) {
            $templateId = 'innobyte_core_debugger_email_template';
        }

        $sender = $this->getSenderInfo();
        $recepientEmail = $this->getRecipients();
        $recepientName = '';
        // Set variables that can be used in email template
        $vars = $this->getEmailVars($message);
        
        // Send Transactional Email
        $translate = Mage::getSingleton('core/translate');
        $mailTemplate = Mage::getModel('core/email_template');
        // add attachments
        $folder = Mage::getBaseDir('var').'/log/';
        $files = glob($folder.'*');
        foreach ($files as $file) {
            $mailTemplate->getMail()->createAttachment(
                file_get_contents($file),
                Zend_Mime::TYPE_OCTETSTREAM,
                Zend_Mime::DISPOSITION_ATTACHMENT,
                Zend_Mime::ENCODING_BASE64,
                basename($file)
            );
        }
        $mailTemplate->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars);
        $translate->setTranslateInline(true);
    }
    
    public function getSenderInfo()
    {
        $senderName = 'Innobyte Debugger';
        $senderEmail = Mage::getStoreConfig('trans_email/ident_general/email');
        $sender = array(
            'name' => $senderName,
            'email' => $senderEmail
        );
        
        return $sender;
    }
    
    /**
    * Get email receivers.
    *
    * @param null $storeId
    * @return string
    */
    public function getRecipients()
    {
        $configInfo = Mage::getStoreConfig(self::XML_RECIPIENTS_EMAIL_PATH);
        $recipients = explode(',', $configInfo);
        if (!empty($recipients[0])) {
            return $recipients;
        }
        return false;
    }
    
    
    public function getEmailVars($message)
    {
        $magento = array (
            'edition' => 'NA / assuming Community',
            'version' => Mage::getVersion(),
            'domains' => Mage::helper('innobyte_core/versions')->getAllStoreDomains(),
            'compilation' => $this->getCompilerStatus(),
            'inno_extensions' => $this->getInnobyteExtensions(),
            'inno_extensions_info' => $this->getConfigInnoData(),
            'disabled_modules' => $this->getDisabledModules(),
            'rewrites' => $this->getRewrites(),
            'crontab' => $this->getCrontabInfo(),
            'crons' => $this->getCrons(),
        );

        if (method_exists('Mage','getEdition')) {
            $magento['edition'] = Mage::getEdition();
        }

        ob_start();
        phpinfo();
        $phpInfo = ob_get_contents();
        ob_get_clean();

        $vars = array(
            'php_info' => $phpInfo,
            'magento' => $magento,
            'explanation_message' => $message,
        );
        
        return $vars;
    }
    
    public function getCompilerStatus()
    {
        if (defined('COMPILER_INCLUDE_PATH')) {
            return '<span style="color:green">Enabled</span>';
        } else {
            return 'Disabled';
        }
    }
    
    public function getInnobyteExtensions()
    {
        $extensions = array();

        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        sort($modules);

        foreach ($modules as $moduleName) {
            if (strstr($moduleName, 'Innobyte_') === false) {
                continue;
            }
            
            // Detect installed version
            $ver = Mage::getConfig()->getModuleConfig($moduleName)->version;
            $extensionData = array(
                'version' => $ver,
                'name' => $moduleName,
                );
            $extensions[] = $extensionData;
        }

        return $extensions;
    }
    
    public function getDisabledModules()
    {
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        $dispatchResult = new Varien_Object($modules);
        $modules = $dispatchResult->toArray();

        sort($modules);
        $disabledModules = array();
        foreach ($modules as $moduleName) {
            $path = 'advanced/modules_disable_output/'.$moduleName;
            if (Mage::getStoreConfig($path)) {
                $disabledModules[] = $moduleName;
            }
        }
        
        return $disabledModules;
    }
    
    public function getRewrites()
    {
        $folders = array('app/code/local/', 'app/code/community/');
        $configFiles = array();
        foreach ($folders as $folder){
            $files = glob($folder.'*/*/etc/config.xml');
            $configFiles = array_merge($configFiles, $files);
        }
        $rewrites = array();

        foreach ($configFiles as $file){
            $dom = new DOMDocument;
            $dom->loadXML(file_get_contents($file));
            $xpath = new DOMXPath($dom);
            $path = '//rewrite/*';
            $text = $xpath->query($path);
            foreach ($text as $rewriteElement){
                //what is overwritten (model, block, helper)
                $type = $rewriteElement->parentNode->parentNode->parentNode->tagName;
                //module identifier that is being rewritten (core, catalog, sales, ...)
                $parent = $rewriteElement->parentNode->parentNode->tagName;
                //element that is rewritten (layout, product, category, order)
                $name = $rewriteElement->tagName;
                foreach ($rewriteElement->childNodes as $element){
                    $rewrites[$type][$parent.'/'.$name][] = $element->textContent;//class that rewrites it
                }
            }
        }
        return $rewrites;
    }
    
    public function getCrons()
    {
        $results = array();
        $jobs = (array)Mage::getConfig()->getNode('crontab/jobs');
        $collection = Mage::getResourceModel('cron/schedule_collection');
        foreach ($collection as $item) {
            $job = $jobs[$item['job_code']];
            $results[$item['job_code']]['info'] = array (
                'cron_expr' => $job->schedule->cron_expr,
                'model' => $job->run->model
            );
            $results[$item['job_code']]['schedule'] = array(
                'messages' => $item['messages'],
                'created_at' => $item['created_at'],
                'scheduled_at' => $item['scheduled_at'],
                'executed_at' => $item['executed_at'],
                'finished_at' => $item['finished_at'],
                'status' => $item['status'],
            );
        }
         
        return $results;
    }
    
    public function getCrontabInfo()
    {
        $result = array();
        if(function_exists('exec')) {
            exec('crontab -l', $result);
            $user = array();
            exec('whoami', $user);
            if (!isset($user[0])) {
                $user[0] = '';
            }
            if(!count($result)) {
                $result[] = 'No cron jobs found in crontab - for web server user "'.$user[0].'"... maybe is added on other user';
            }
        } else {
            $result[] = 'EXEC is disabled so we cannot see if Magento cron is active';
        }
        return $result;
    }
    
    public function getConfigInnoData()
    {
        $collection = Mage::getModel('core/config_data')->getCollection()
                ->addFieldToFilter('path', array('like' => '%innobyte%'))
                ->load();
        $results = array();
        foreach($collection as $item){
            $results[$item->getConfigId()] = array(
                'path' => $item->getPath(),
                'value' => $item->getValue(),
            );
        }
        
        return $results;
    }
}
