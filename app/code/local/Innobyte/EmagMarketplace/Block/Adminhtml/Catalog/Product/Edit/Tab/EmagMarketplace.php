<?php
/**
 * eMAG tab on product page.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Block_Adminhtml_Catalog_Product_Edit_Tab_EmagMarketplace
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Current store scope.
     *
     * @var int
     */
    protected $_storeId;

    /**
     * eMAG category.
     *
     * @var Innobyte_EmagMarketplace_Model_Category
     */
    protected $_category;
    
    /**
     * eMAG Family Type.
     *
     * @var Innobyte_EmagMarketplace_Model_Category_Familytype
     */
    protected $_familyType;
    
    
    
    /**
     * @Override
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_storeId = Mage::helper('innobyte_emag_marketplace')
            ->getCurrStoreId();
    }
    
    
    
    /**
     * @Override
     */
    public function getTabLabel()
    {
        return $this->__('eMAG Marketplace');
    }



    /**
     * @Override
     */
    public function getTabTitle()
    {
        return $this->__(
            'Click here to set eMAG Marketplace info for this product'
        );
    }



    /**
     * @Override
     */
    public function canShowTab()
    {
        return $this->helper('innobyte_emag_marketplace')
            ->isProductActionValid(Mage::registry('current_product'));
    }
    
    
    
    /**
     * @Override
     */
    public function isHidden()
    {
        return !$this->canShowTab();
    }
    
    
    
    /**
     * Retrieve the class name of the tab
     * Return 'ajax' here if you want the tab to be loaded via Ajax
     *
     * return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }
    
    
    
    /**
     * Determine whether to generate content on load or via AJAX
     * If true, the tab's content won't be loaded until the tab is clicked
     * You will need to setup a controller to handle the tab request
     *
     * @return bool
     */
    public function getSkipGenerateContent()
    {
        return true;
    }
    
    
    
    /**
     * Retrieve the URL used to load the tab content
     * Return the URL here used to load the content by Ajax
     * see self::getSkipGenerateContent & self::getTabClass
     *
     * @return string
     */
    public function getTabUrl()
    {
        $storeId = $this->helper('innobyte_emag_marketplace')->getCurrStoreId();
        return $this->helper('adminhtml')->getUrl(
            'adminhtml/emag_product/index',
            array(
                'store' => $storeId,
                'id' => $this->getRequest()->getParam('id'),
            )
        );
    }


    
    /**
     * @Override
     */
    public function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('product[inno_emag_mktp]');
        $form->setHtmlIdPrefix('innoemag_');
        
        $this->_prepareGeneralInfoFieldset($form)
            ->_prepareCategoryFieldset($form)
            ->_prepareOfferFieldset($form);
        
        // set commision value based on direct call to getCommissionValue()
        $this->getEmagProduct()->setData(
            'commission_value',
            $this->getEmagProduct()->getCommissionValue()
        );
        $form->setValues($this->getEmagProduct()->getData());
        
        // set default values for name & brand & desc if new emag product
        if (!$this->getEmagProduct()->getId()
            && !$this->getEmagProduct()->getName()
            && $this->getMageProduct()->getName()
            && ($nameElem = $form->getElement('name'))) {
            $nameElem->setValue($this->getMageProduct()->getName());
        }
        if (!$this->getEmagProduct()->getId()
            && !$this->getEmagProduct()->getBrand()
            && $this->getMageProduct()->getResource()->getAttribute('manufacturer')
            && ($brandElem = $form->getElement('brand'))) {
            $brandElem->setValue($this->getMageProduct()->getAttributeText('manufacturer'));
        }
        if (!$this->getEmagProduct()->getId()
            && !$this->getEmagProduct()->getDescription()
            && $this->getMageProduct()->getDescription()
            && ($descElem = $form->getElement('description'))) {
            $descElem->setValue($this->getMageProduct()->getDescription());
        }

        $priceElem = $form->getElement('price');
        if (!is_null($this->getEmagProduct()->getPrice())) {
            $priceElem->setValue(number_format($this->getEmagProduct()->getPrice(), 2));
        }

        $specialPriceElem = $form->getElement('special_price');
        if (!is_null($this->getEmagProduct()->getSpecialPrice())) {
            $specialPriceElem->setValue(number_format($this->getEmagProduct()->getSpecialPrice(), 2));
        }

        $this->setForm($form);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('adminhtml/template')
                ->setTemplate('innobyte/emag_marketplace/catalog/product/edit/tab/emag_marketplace_form_after.phtml')
        );
        
        // dispatch event in case customization needs to be made by clients
        Mage::dispatchEvent(
            'innobyte_emag_marketplace_prepare_emag_product_form',
            array('form_block' => $this)
        );
        
        return parent::_prepareForm();
    }

    
    
    /**
     * Prepare "General Information" fieldset.
     *
     * @param Varien_Data_Form $form
     * @return Innobyte_EmagMarketplace_Block_Adminhtml_Catalog_Product_Edit_Tab_EmagMarketplace
     */
    protected function _prepareGeneralInfoFieldset(Varien_Data_Form $form)
    {
        $fieldsetGeneral = $form->addFieldset(
            'inno_emag_data_general',
            array(
                'legend' => $this->__('General Information'),
            )
        );
        $fieldsetGeneral->addField(
            'name',
            'text',
            array(
                'label'     => $this->__('Name') . ':',
                'title'     => $this->__('Name'),
                'name'      => 'name',
                'required'  => !strlen($this->getEmagProduct()->getPartNumberKey()),
                'maxlength' => 255,
                'class'     => 'validate-length minimum-length-1 maximum-length-255',
                'note'      => $this->__(
                    'Should be consistent with eMAG Product Documentation Standard.'
                ),
            )
        );
        $fieldsetGeneral->addField(
            'brand',
            'text',
            array(
                'label'     => $this->__('Brand') . ':',
                'title'     => $this->__('Brand'),
                'name'      => 'brand',
                'required'  => !strlen($this->getEmagProduct()->getPartNumberKey()),
                'maxlength' => 255,
                'class'     => 'validate-length minimum-length-1 maximum-length-255',
                'note'      => $this->__(
                    'Should be consistent with eMAG Product Documentation Standard.'
                ),
            )
        );
        $fieldsetGeneral->addField(
            'description',
            'textarea',
            array(
                'label'    => $this->__('Description') . ':',
                'title'    => $this->__('Description'),
                'name'     => 'description',
                'required' => false,
                'style'    => 'width: 500px; height: 150px;',
                'note'     => $this->__(
                    'Should be consistent with eMAG Product Documentation Standard. Can contain basic HTML tags.'
                ),
                'after_element_html' => $this->_getWysiwygEditorButton(
                    'innoemag_description'
                ),
            )
        );
        $fieldsetGeneral->addType(
            'barcodes',
            Mage::getConfig()->getBlockClassName(
                'innobyte_emag_marketplace/adminhtml_form_element_barcodes'
            )
        );
        $fieldsetGeneral->addField(
            'barcodes',
            'barcodes',
            array(
                'label'     => $this->__('Barcode(s)') . ':',
                'title'     => $this->__('Barcode(s)'),
                'name'      => 'barcodes',
                'required'  => false,
                'maxlength' => 20,
                'class'     => 'validate-length minimum-length-1 maximum-length-20 f-left',
                'note'      => $this->__(
                    'Product barcode identifier (EAN, UPC, ISBN, GTIN). Please use the supplier barcode, not your internal barcodes.'
                ),
            )
        );
        return $this;
    }
    
    
    
    /**
     * Prepare "Category" fieldset.
     *
     * @param Varien_Data_Form $form
     * @return Innobyte_EmagMarketplace_Block_Adminhtml_Catalog_Product_Edit_Tab_EmagMarketplace
     */
    protected function _prepareCategoryFieldset(Varien_Data_Form $form)
    {
        $fieldset = $form->addFieldset(
            'inno_emag_data_category',
            array(
                'legend' => $this->__('Category'),
            )
        );
        
        $optCategory = Mage::getSingleton('innobyte_emag_marketplace/source_category')
            ->toOptionArray($this->_storeId, true, $this->areFamilyTypesRequired());
        $fieldset->addField(
            'category_id',
            'select',
            array(
                'label'    => $this->__('Category') . ':',
                'title'    => $this->__('Category'),
                'name'     => 'category_id',
                'required' => !strlen($this->getEmagProduct()->getPartNumberKey()),
                'values'   => $optCategory,
                'onchange' => 'inno.emagMarketplace.changeCategory(\''
                    . $this->getUrl(
                        'adminhtml/emag_product/getCategoryData',
                        array('store' => $this->_storeId)
                    )
                    . '\', '
                    . ($this->areFamilyTypesRequired() ? 'true' : 'false')
                    . ')"'
            )
        );
        
        if ($this->getCategory()->getId() > 0
            && $this->areFamilyTypesRequired()
            && is_array($this->getCategory()->getFamilyTypes())) {
            $optFamType = Mage::getSingleton('innobyte_emag_marketplace/source_familyType')
                ->toOptionArray($this->getEmagProduct()->getCategoryId());
            $fieldset->addField(
                'family_type_id',
                'select',
                array(
                    'label'    => $this->__('Family Type') . ':',
                    'title'    => $this->__('Family Type'),
                    'name'     => 'family_type_id',
                    'required' => true,
                    'values'   => $optFamType,
                    'onchange' => 'inno.emagMarketplace.changeFamilyType()',
                    'after_element_html' => '<script type="text/javascript">
                        //<![CDATA[
                        inno.emagMarketplace.familyTypesCharacteristics = \''
                        . $this->getFamilyTypesCharacteristicsJs()
                        . '\'.evalJSON();'
                        . '//]]>
                        </script>',
                )
            );
        }
        
        if ($this->getCategory()->getId() > 0
            && is_array($this->getCategory()->getCharacteristics())) {
            $ftCharacteristics = $this->getFamilyTypeCharacteristics();
            foreach ($this->getCategory()->getCharacteristics() as $characteristic) {
                $isCurrFamilyTypeChar = in_array(
                    $characteristic->getId(),
                    $ftCharacteristics
                );
                $fieldset->addField(
                    'category_characteristic' . $characteristic->getId(),
                    'text',
                    array(
                        'label'     => $this->__($characteristic->getName()),
                        'title'     => $this->__($characteristic->getName()),
                        'name'      => 'category_characteristic' . $characteristic->getId(),
                        'required'  => true,
                        'maxlength' => 255,
                        'class'     => 'validate-length minimum-length-1 maximum-length-255'
                            . ($isCurrFamilyTypeChar ? ' disabled' : ''),
                        'disabled'  => $isCurrFamilyTypeChar,
                        'note'      => $isCurrFamilyTypeChar ? $this->__(
                            'This attribute will have to be set on associated products individually'
                        ) : null,
                    )
                );
            }
        }
        
        return $this;
    }
    
    
    
    /**
     * Prepare "Product Offer" fieldset.
     *
     * @param Varien_Data_Form $form
     * @return Innobyte_EmagMarketplace_Block_Adminhtml_Catalog_Product_Edit_Tab_EmagMarketplace
     */
    protected function _prepareOfferFieldset(Varien_Data_Form $form)
    {
        $fieldset = $form->addFieldset(
            'inno_emag_data_offer',
            array(
                'legend' => $this->__('Product Offer'),
            )
        );
        
        $fieldset->addField(
            'part_number_key',
            'text',
            array(
                'label'     => $this->__('Part Number Key') . ':',
                'title'     => $this->__('Part Number Key'),
                'name'      => 'part_number_key',
                'required'  => false,
                'onchange'  => 'inno.emagMarketplace.changePartNumberKey()',
                'note'      => $this->__(
                    'Used for attaching a product offer to an existing product in eMAG platform.<br />If you want to create new product, don\'t set this key.'
                ) . '<br />' . $this->__(
                    'Ex: for product http://www.emag.ro/telefon-mobil-nokia-105-black-105-black/pd/D5DD9BBBM/ the part_number_key is D5DD9BBBM.'
                ),
            )
        );
        
        $optStatus = Mage::getSingleton('innobyte_emag_marketplace/source_offerStatus')
            ->toOptionArray();
        $fieldset->addField(
            'status',
            'select',
            array(
                'label'    => $this->__('Offer Status') . ':',
                'title'    => $this->__('Offer Status'),
                'name'     => 'status',
                'required' => true,
                'values'   => $optStatus,
            )
        );
        
        $fieldset->addField(
            'warranty',
            'text',
            array(
                'label'    => $this->__('Warranty') . ':',
                'title'    => $this->__('Warranty'),
                'name'     => 'warranty',
                'required' => false,
                'class'    => 'validate-not-negative-number validate-digits-range digits-range-0-255',
                'note'     => $this->__('The warranty offered in months.'),
            )
        );
        
        $optCommType = Mage::getSingleton('innobyte_emag_marketplace/source_commissionTypes')
            ->toOptionArray();
        $fieldset->addField(
            'commission_type',
            'select',
            array(
                'label'    => $this->__('Commission Type') . ':',
                'title'    => $this->__('Commission Type'),
                'name'     => 'commission_type',
                'required' => true,
                'values'   => $optCommType,
                'onchange' => 'inno.emagMarketplace.changeCommissionType()',
                'note'     => $this->__(
                    'The type of commission for a finalized order containing the offer.'
                ),
            )
        );
        
        $percentsClass = '';
        try {
            $commissionType = Innobyte_EmagMarketplace_Model_Source_CommissionTypes::getEmagCommissionType(
                $this->getEmagProduct()->getCommissionType()
            );
            if (Innobyte_EmagMarketplace_Model_Source_CommissionTypes::TYPE_PERCENTAGE == $commissionType) {
                $percentsClass .= ' validate-percents';
            }
        } catch (Innobyte_EmagMarketplace_Exception $iemEx) {
            //no need to log, for example for new emag products it will get here
        }
        $fieldset->addField(
            'commission_value',
            'text',
            array(
                'label'     => $this->__('Commission Value') . ':',
                'title'     => $this->__('Commission Value'),
                'name'      => 'commission_value',
                'required'  => true,
                'class'     => 'validate-not-negative-number' . $percentsClass,
                'note'      => $this->__(
                    'If type is percentage the value should be between 0 and 100, otherwise a float with 4 decimals and without VAT.'
                ),
            )
        );
        
        $fieldset->addField(
            'handling_time',
            'text',
            array(
                'label'    => $this->__('Handling Time') . ':',
                'title'    => $this->__('Handling Time'),
                'name'     => 'handling_time',
                'required' => false,
                'class'    => 'validate-not-negative-number validate-digits-range digits-range-0-255',
                'note'     => $this->__(
                    'Handling time, in number of days counted from the day the order was received. If handling_time = 0 the order will be shipped the same day it is received.'
                ),
            )
        );
        
        $startDateElem = $fieldset->addField(
            'start_date',
            'date',
            array(
                'label'        => $this->__('Start Date') . ':',
                'title'        => $this->__('Start Date'),
                'name'         => 'start_date',
                'required'     => false,
                'image'        => $this->getSkinUrl('images/grid-cal.gif'),
                'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                'format'       => Mage::app()->getLocale()->getDateFormat(
                    Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
                ),
                'class'        => 'validate-emag-date',
                'note'         => $this->__(
                    'If it\'s a new offer, it represents the date your offer will be available from.<br />For offer updates, it schedules value updates for the following data: sale_price, recommended_price, stock, handling_time, commission, vat_id, warranty, status, availability.<br />All other data will be updated on the fly.<br />Using Start Date, for example, you can schedule the inactivation of an offer, a price update, etc.'
                ),
            )
        );
        $startDateElem->setAfterElementHtml(
            '<script type="text/javascript">
                //<![CDATA[
                    Validation.add(
                        "validate-emag-date",
                        "'
            . $this->__(
                'Date can be as far as 60 days in the future and cannot be earlier than tomorrow.'
            ) . '",
                    function(date){
                        if (date === "") {
                            return true;
                        }
                        var dateObj = null;
                         // check func defined in js/calendar/calendar.js
                        if (typeof (Date.parseDate) === "function") {
                            dateObj = Date.parseDate(date, "'
            . Varien_Date::convertZendToStrFtime(
                $startDateElem->getFormat(),
                true,
                (bool) $startDateElem->getTime()
            ) . '");
                        }
                        if (null === dateObj) {
                            return true; // validate server side
                        }
                        var minDate = new Date(
                            new Date().getTime()
                            +
                            1 * 24 * 60 * 60 * 1000
                        ); // tomorrow
                        minDate.setHours(0);
                        minDate.setMinutes(0);
                        minDate.setSeconds(0);
                        minDate.setMilliseconds(0);
                        var maxDate = new Date(
                            new Date().getTime()
                            +
                            60 * 24 * 60 * 60 * 1000
                        ); // 60 days from now
                        maxDate.setHours(0);
                        maxDate.setMinutes(0);
                        maxDate.setSeconds(0);
                        maxDate.setMilliseconds(0);
                        return (dateObj >= minDate && dateObj <= maxDate);
                    }
                );
                //]]>
            </script>'
        );


        $fieldset->addField(
            'price',
            'text',
            array(
                'label'    => $this->__('Price') . ':',
                'title'    => $this->__('Price'),
                'name'     => 'price',
                'required' => false,
                'note'     => $this->__(
                    'eMAG Marketplace sell price. Leave blank to use default Magento Price. <br /> <strong>Note</strong>: <span style="color: red">VAT is calculated based on <a href="%s">Tax->Calculation Settings->Catalog Prices</a>.</span>',
                    $this->getUrl('adminhtml/system_config/edit/section/tax')
                ),
            )
        );

        $fieldset->addField(
            'special_price',
            'text',
            array(
                'label'    => $this->__('Special Price') . ':',
                'title'    => $this->__('Special Price'),
                'name'     => 'special_price',
                'required' => false,
                'note'     => $this->__(
                    'eMAG Marketplace special price. Leave blank to use default Magento Special Price. <br /> <strong>Note</strong>: <span style="color: red">VAT is calculated based on <a href="%s">Tax->Calculation Settings->Catalog Prices</a>.</span>',
                    $this->getUrl('adminhtml/system_config/edit/section/tax')
                ),
            )
        );
        
        $optVat = Mage::getSingleton('innobyte_emag_marketplace/source_vat')
            ->toOptionArray($this->_storeId);
        $fieldset->addField(
            'vat_id',
            'select',
            array(
                'label'    => $this->__('VAT') . ':',
                'title'    => $this->__('VAT'),
                'name'     => 'vat_id',
                'required' => false,
                'values'   => $optVat,
                'note'     => $this->__(
                    'If not set, will be automatically calculated based on product \'s class tax and store shipping origin address.'
                ),
            )
        );
        
        return $this;
    }
    
    
    
    /**
     * Retrieve eMAG product.
     *
     * @return Innobyte_EmagMarketplace_Model_Product
     */
    public function getEmagProduct()
    {
        return Mage::registry('current_emag_product');
    }
    
    
    
    /**
     * Retrieve magento product.
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getMageProduct()
    {
        return Mage::registry('current_product');
    }
    
    
    
    /**
     * Retrieve family type object
     *
     * @return Innobyte_EmagMarketplace_Model_Category_Familytype
     */
    public function getFamilyType()
    {
        if (is_null($this->_familyType)) {
            $this->_familyType = Mage::getModel(
                'innobyte_emag_marketplace/category_familytype'
            )->load($this->getEmagProduct()->getFamilyTypeId());
        }
        return $this->_familyType;
    }
    
    
    
    /**
     * Retrieve category model.
     *
     * @return Innobyte_EmagMarketplace_Model_Category
     */
    public function getCategory()
    {
        if (is_null($this->_category)) {
            $this->_category = Mage::getModel('innobyte_emag_marketplace/category')
                ->load($this->getEmagProduct()->getCategoryId());
        }
        return $this->_category;
    }
    
    
    
    /**
     * Retrieve family type 's characteristics magento ids.
     *
     * @return array
     */
    public function getFamilyTypeCharacteristics()
    {
        $returnValue = array();
        if (is_array($this->getFamilyType()->getCharacteristics())) {
            foreach ($this->getFamilyType()->getCharacteristics() as $ftChar) {
                $returnValue[] = $ftChar->getMageIdEmagChar();
            }
        }
        return $returnValue;
    }
    
    
    
    /**
     * Check if family types are required.
     *
     * @return boolean
     */
    public function areFamilyTypesRequired()
    {
        return $this->getMageProduct() instanceof Mage_Catalog_Model_Product
            && $this->getMageProduct()->isConfigurable();
    }
    
    
    
    /**
     * Retrieve characteristics of families js.
     *
     * @return string
     */
    public function getFamilyTypesCharacteristicsJs()
    {
        $returnValue = array();
        if ($this->getCategory()->getId()
            && is_array($this->getCategory()->getFamilyTypes())) {
            foreach ($this->getCategory()->getFamilyTypes() as $ft) {
                $chars = array();
                foreach ($ft->getCharacteristics() as $char) {
                    $chars[] = $char->getMageIdEmagChar();
                }
                $returnValue[$ft->getId()] = $chars;
            }
        }
        return Zend_Json::encode($returnValue);
    }
    
    
    
    
    /**
     * @Override  Load tiny mce js
     */
    protected function _prepareLayout() {
        if ($this->_isWysiwygEnabled()
            && $this->getLayout()->getBlock('head')) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        return parent::_prepareLayout();        
    }
    
    
    
    /**
     * Retrieve wysiwyg button.
     *
     * @return string
     */
    protected function _getWysiwygEditorButton($fieldId)
    {
        if (!$this->_isWysiwygEnabled()) {
            return '';
        }
        return Mage::getSingleton('core/layout')
            ->createBlock(
                'adminhtml/widget_button',
                '',
                array(
                    'label'   => Mage::helper('catalog')->__('WYSIWYG Editor'),
                    'type'    => 'button',
                    'class'   => 'btn-wysiwyg',
                    'onclick' => 'catalogWysiwygEditor.open(\''
                    . Mage::helper('adminhtml')->getUrl(
                        'adminhtml/catalog_product/wysiwyg'
                    ) . '\', \''. $fieldId .'\')',
                )
            )
            ->toHtml();
    }



    /**
     * Check whether wysiwyg enabled or not
     *
     * @return boolean
     */
    protected function _isWysiwygEnabled()
    {
        if ($this->helper('core')->isModuleEnabled('Mage_Cms')) {
            return (bool) Mage::getSingleton('cms/wysiwyg_config')->isEnabled();
        }
        return false;
    }

}
