<?php
/**
 * Custom shipping carrier that represents the "eMAG" shipping.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 *
 * @author   Valentin Sandu <valentin.sandu@innobyte.com>
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Shipping_Carrier_Emag
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * @var const int   Container types.
     */
    const CONTAINER_ENVELOPE = 1;
    const CONTAINER_PARCEL   = 2;

    
    /**
     * eMAG shipping code
     */
    const EMAG_SHIPPING = 'emagshipping';

    /**
     * Shipping method code
     *
     * @var string
     */
    protected $_code = self::EMAG_SHIPPING;

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * Allowed methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            'standard' => $this->getConfigData('name'),
        );
    }

    /**
     * Collect rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool|Mage_Shipping_Model_Rate_Result|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!Mage::app()->getStore()->isAdmin()) {
            return false;
        }

        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');
        $result->append($this->_getStandardShippingRate());

        return $result;
    }

    /**
     * Get standard shipping rate
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getStandardShippingRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');
        $rate->setCarrier($this->getCarrierCode());
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('standard');
        $rate->setMethodTitle($this->getConfigData('name'));
        $rate->setPrice(0);
        $rate->setCost(0);

        return $rate;
    }
    
    
    
    /**
     * Check if carrier has shipping label option available.
     * @Override
     * @return boolean
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }
    
    
    
    /**
     * Retrieve container types of carrier.
     *
     * @Override
     * @param Varien_Object|null $params
     * @return array
     */
    public function getContainerTypes(Varien_Object $params = null)
    {
        $helper = Mage::helper('innobyte_emag_marketplace');
        return array(
            self::CONTAINER_ENVELOPE => $helper->__('Envelope'),
            self::CONTAINER_PARCEL => $helper->__('Parcel'),
        );
    }



    /**
     * Do request to shipment.
     *
     * @Override
     * @param   Mage_Shipping_Model_Shipment_Request    $req
     * @return  Varien_Object
     */
    public function requestToShipment(Mage_Shipping_Model_Shipment_Request $req)
    {
        $returnValue = new Varien_Object();
        $helper = Mage::helper('innobyte_emag_marketplace');
        try {
            $req->setData(
                'emag_shipping_extra',
                Mage::app()->getRequest()->getParam('emag-marketplace-shipping')
            );
            $apiAwb = Mage::getModel('innobyte_emag_marketplace/api_awb');
            $apiResponse = $apiAwb->setShipmentRequest($req)->save();
            if ($apiResponse->isError()) {
                throw new Innobyte_EmagMarketplace_Exception(
                    implode(', ', $apiResponse->getMessages())
                );
            }
            $results = $apiResponse->getResults();
            if (!array_key_exists('awb', $results)
                || !is_array($results['awb'])) {
                throw new Innobyte_EmagMarketplace_Exception(
                    'Invalid API awb response'
                );
            }
            
            /* get shipping label */
            $info = array();
            foreach ($results['awb'] as $awbInfo) {
                $labelContent = '';
                try {
                    $pdfResponse = $apiAwb->readPdf($awbInfo['emag_id']);
                    $validContents = array(
                        'application/pdf',
                        'application/x-pdf',
                    );
                    $content = $pdfResponse->getHeader('Content-Type');
                    if (in_array($content, $validContents)) {
                        $labelContent = $pdfResponse->getBody();
                    } else {
                        throw new Innobyte_EmagMarketplace_Exception(
                            'eMAG AWB #' . $awbInfo['emag_id']
                            . ' PDF could not be retrieved.'
                            . ' Response content type: ' . $content
                            . ' Response body: ' . $pdfResponse->getBody()
                        );
                    }
                } catch (Exception $e) {
                    $apiAwb->debugData(
                        $e->getMessage(),
                        Innobyte_EmagMarketplace_Model_Api_Awb::AWB_RESOURCE_NAME
                    );
                }
                $info[] = array(
                    'tracking_number' => $awbInfo['awb_number'],
                    'label_content' => $labelContent,
                );
            }
            $returnValue->setInfo($info);
        } catch (Innobyte_EmagMarketplace_Exception $iemEx) {
            $returnValue->setErrors($helper->__($iemEx->getMessage()));
        }
        return $returnValue;
    }
}
