<?php
/**
 * Handles AWB api related operations.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_EmagMarketplace_Model_Api_Awb
    extends Innobyte_EmagMarketplace_Model_Api_Abstract
{
    /**
     * Awb resource name
     */
    const AWB_RESOURCE_NAME = 'awb';

    /**
     * Request to shipment model.
     *
     * @var Mage_Shipping_Model_Shipment_Request
     */
    protected $_shipmentRequest;



    /**
     * Read AWB resource
     *
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function read()
    {
        throw new Innobyte_EmagMarketplace_Exception('Not implemented');
    }
    
    
        
    /**
     * Read AWB 's pdf.
     *
     * @param  int $emagAwbId   eMAG AWB id.
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function readPdf($emagAwbId)
    {
        $url = Mage::helper('innobyte_emag_marketplace')
            ->getAwbPdfUrl($this->getStoreId());
        if (!strlen($url)) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Read AWB PDF URL was not set in system config.'
            );
        }
        $code = $this->getHelper()->getClientCode($this->getStoreId());
        $username = $this->getHelper()->getApiUsername($this->getStoreId());
        $pwd = $this->getHelper()->getApiPassword($this->getStoreId());
        $data = array(
            'code' => $code,
            'username' => $username,
            'emag_id' => $emagAwbId,
            'hash' => sha1($pwd),
        );
        
        return $this->_makeHttpCall($url, $data, Zend_Http_Client::GET);
    }



    /**
     * Save AWB resource
     *
     * @return Innobyte_EmagMarketplace_Model_Api_Response
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function save()
    {
        parent::save();
        return $this->setData($this->_computeApiSaveData())
            ->setStoreId($this->getShipmentRequest()->getStoreId())
            ->_makeApiCall();
    }



    /**
     * Count AWB resource
     *
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function count()
    {
        throw new Innobyte_EmagMarketplace_Exception('Not implemented');
    }



    /**
     * Acknowledge AWB resource
     *
     * @throws Innobyte_EmagMarketplace_Exception
     */
    public function acknowledge()
    {
        throw new Innobyte_EmagMarketplace_Exception('Not implemented');
    }



    /**
     * Get resource name
     *
     * @return string
     */
    public function getResourceName()
    {
        return self::AWB_RESOURCE_NAME;
    }



    /**
     * Setter method for shipment request.
     *
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return Innobyte_EmagMarketplace_Model_Api_Awb
     * @throws Innobyte_EmagMarketplace_Exception  If null param is provided.
     */
    public function setShipmentRequest(Mage_Shipping_Model_Shipment_Request $request)
    {
        if (is_null($request)) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Invalid shipment request object.'
            );
        }
        $this->_shipmentRequest = $request;
        return $this;
    }



    /**
     * Getter method for shipment request.
     *
     * @return Mage_Shipping_Model_Shipment_Request
     */
    public function getShipmentRequest()
    {
        if (is_null($this->_shipmentRequest)) {
            $this->setShipmentRequest(
                Mage::getModel('shipping/shipment_request')
            );
        }
        return $this->_shipmentRequest;
    }



    /**
     * Prepare data for #save() method.
     *
     * @return array    Array with data to be sent on save awb.
     * @throws Innobyte_EmagMarketplace_Exception If invalid info is found.
     */
    protected function _computeApiSaveData()
    {
        if (!$this->getShipmentRequest()->getOrderShipment()
            || !$this->getShipmentRequest()->getOrderShipment()->getOrder()) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Invalid shipment request.'
            );
        }
        $returnValueObj = new Varien_Object(
            array_merge(
                $this->_prepareRequestToShipmentBasicData(),
                array('receiver' => $this->_prepareRequestToShipmentReceiverData()),
                array('sender' => $this->_prepareRequestToShipmentSenderData())
            )
        );

        // dispatch event in case customizations needs to be done by clients
        Mage::dispatchEvent(
            'innobyte_emag_marketplace_compute_api_awb_data',
            array(
                'awb_data' => $returnValueObj,
                'request' => $this->getShipmentRequest()
            )
        );

        return $returnValueObj->toArray();
    }



    /**
     * Prepare data other than receiver/sender keys.
     *
     * @return array
     * @throws Innobyte_EmagMarketplace_Exception If invalid info is found.
     */
    protected function _prepareRequestToShipmentBasicData()
    {
        $request = $this->getShipmentRequest();
        // perform some checks
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            throw new Innobyte_EmagMarketplace_Exception(
                'No packages for request.'
            );
        }
        $emagShippingExtra = $request->getEmagShippingExtra();
        if (!is_array($emagShippingExtra)) {
            throw new Innobyte_EmagMarketplace_Exception(
                'No eMAG shipping extra fields.'
            );
        }
        if (!array_key_exists('cod', $emagShippingExtra)
            || !is_numeric($emagShippingExtra['cod'])
            || $emagShippingExtra['cod'] < 0
            || $emagShippingExtra['cod'] > 999999999) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Invalid cash on delivery value.'
            );
        }
        if (!$request->getOrderShipment()->getOrder()->getEmagOrderId()) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Invalid eMAG order id.'
            );
        }

        // find out how many envelopes & parcels are
        $envelopesNo = $parcelsNo = 0;
        foreach ($request->getPackages() as $packageId => $package) {
            if (Innobyte_EmagMarketplace_Model_Shipping_Carrier_Emag::CONTAINER_ENVELOPE == $package['params']['container']) {
                $envelopesNo++;
            } else {
                $parcelsNo++;
            }
        }
        // compute api required data
        $data = array(
            'order_id' => intval(
                $request->getOrderShipment()->getOrder()->getEmagOrderId()
            ),
            'envelope_number' => $envelopesNo,
            'parcel_number' => $parcelsNo,
            'cod' => floatval($emagShippingExtra['cod']),
        );
        // compute api optional data
        if (is_numeric($request->getPackageWeight())
            && $request->getPackageWeight() >= 0
            && $request->getPackageWeight() <= 99999) {
           $data['weight'] = floatval($request->getPackageWeight());
        }
        if (array_key_exists('insured_value', $emagShippingExtra)
            && is_numeric($emagShippingExtra['insured_value'])
            && $emagShippingExtra['insured_value'] >= 0
            && $emagShippingExtra['insured_value'] <= 999999999) {
            $data['insured_value'] = floatval($emagShippingExtra['insured_value']);
        }
        if (array_key_exists('observation', $emagShippingExtra)
            && strlen(trim($emagShippingExtra['observation']))) {
            $data['observation'] = trim($emagShippingExtra['observation']);
        }
        $yesNoValues = array(0, 1);
        if (array_key_exists('pickup_and_return', $emagShippingExtra)
            && is_numeric($emagShippingExtra['pickup_and_return'])
            && in_array($emagShippingExtra['pickup_and_return'], $yesNoValues)) {
            $data['pickup_and_return'] = intval($emagShippingExtra['pickup_and_return']);
        }
        if (array_key_exists('saturday_delivery', $emagShippingExtra)
            && is_numeric($emagShippingExtra['saturday_delivery'])
            && in_array($emagShippingExtra['saturday_delivery'], $yesNoValues)) {
            $data['saturday_delivery'] = intval($emagShippingExtra['saturday_delivery']);
        }
        if (array_key_exists('sameday_delivery', $emagShippingExtra)
            && is_numeric($emagShippingExtra['sameday_delivery'])
            && in_array($emagShippingExtra['sameday_delivery'], $yesNoValues)) {
            $data['sameday_delivery'] = intval($emagShippingExtra['sameday_delivery']);
        }
        if (array_key_exists('open_on_receipt', $emagShippingExtra)
            && is_numeric($emagShippingExtra['open_on_receipt'])
            && in_array($emagShippingExtra['open_on_receipt'], $yesNoValues)) {
            $data['open_on_receipt'] = intval($emagShippingExtra['open_on_receipt']);
        }        
        $courierAccountId = Mage::helper('innobyte_emag_marketplace')
            ->getCourierAccountId($request->getStoreId());
        if (strlen($courierAccountId)) {
            $data['courier_account_id'] = $courierAccountId;
        }
        return $data;
    }



    /**
     * Prepare data for receiver key.
     *
     * @return array
     * @throws Innobyte_EmagMarketplace_Exception If invalid info is found.
     */
    protected function _prepareRequestToShipmentReceiverData()
    {
        $request = $this->getShipmentRequest();
        // perform some checks
        $name = strlen($request->getRecipientContactCompanyName()) < 3 ?
            $request->getRecipientContactPersonName() :
            $request->getRecipientContactCompanyName();
        if (strlen($name)< 3) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Receiver \'s name must have at least 3 letters.'
            );
        }
        if (!strlen($request->getRecipientContactPersonName())) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Receiver \'s contact person name is empty.'
            );
        }
        if (!preg_match('/^\+?[0-9]{8,11}$/', $request->getRecipientContactPhoneNumber())) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Receiver \'s contact phone must have between 8 and 11 digits and may contain a + sign at the beginning.'
            );
        }
        $shippingAddr = $request->getOrderShipment()->getOrder()
            ->getShippingAddress();
        if ($shippingAddr->getEmagLocalityId() < 1) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Receiver \'s city id is empty.'
            );
        }
        $street = str_replace("\n", '', $request->getRecipientAddressStreet());
        if (strlen($street) < 3) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Receiver \'s street must have at least 3 letters.'
            );
        }
        $legalEntityId = $request->getOrderShipment()->getOrder()
            ->getEmagLegalEntity();
        $data = array(
            'name' => $name,
            'contact' => $request->getRecipientContactPersonName(),
            'phone1' => $request->getRecipientContactPhoneNumber(),
            'legal_entity' => intval((bool)($legalEntityId)),
            'locality_id' => intval($shippingAddr->getEmagLocalityId()),
            'street' => $street,
        );
        if (preg_match('/^\+?[0-9]{8,11}$/', $shippingAddr->getEmagTelephone2())) {
            $data['phone2'] = $shippingAddr->getEmagTelephone2();
        }
        if (strlen($request->getRecipientAddressPostalCode())) {
            $data['zipcode'] = $request->getRecipientAddressPostalCode();
        }

        return $data;
    }



    /**
     * Prepare data for receiver key.
     *
     * @return array
     * @throws Innobyte_EmagMarketplace_Exception If invalid info is found.
     */
    protected function _prepareRequestToShipmentSenderData()
    {
        $request = $this->getShipmentRequest();
        // perform some checks
        $name = strlen($request->getShipperContactCompanyName()) < 3 ?
            $request->getShipperContactPersonName() :
            $request->getShipperContactCompanyName();
        if (strlen($name)< 3) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Sender \'s name must have at least 3 letters.'
            );
        }
        if (!strlen($request->getShipperContactPersonName())) {
            throw new Innobyte_EmagMarketplace_Exception(
                'Sender \'s contact person name is empty.'
            );
        }
        $helper = Mage::helper('innobyte_emag_marketplace');
        if (!preg_match('/^\+?[0-9]{8,11}$/', $request->getShipperContactPhoneNumber())) {
            $errMsg = 'Sender \'s contact phone must have between 8 and 11 digits and may contain a + sign at the beginning.';
            if (Mage::getSingleton('admin/session')->isAllowed('system/config')) {
                $errMsg .= ' <a href="%s" target="_blank">Edit</a> store phone info.';
                $errMsg = $helper->__(
                    $errMsg,
                    Mage::helper('adminhtml')->getUrl(
                        'adminhtml/system_config/edit',
                        array('section' => 'general')
                    )
                );
            }
            throw new Innobyte_EmagMarketplace_Exception($errMsg);
        }
        $localityId = $helper->getShipOriginEmagLocalityId(
            $request->getStoreId()
        );
        if ($localityId < 1) {
            $errMsg = 'Sender \'s city id is empty.';
            if (Mage::getSingleton('admin/session')->isAllowed('system/config')) {
                $errMsg .= ' Please <a href="%s" target="_blank">choose</a> an eMAG city for origin shipping settings.';
                $errMsg = $helper->__(
                    $errMsg,
                    Mage::helper('adminhtml')->getUrl(
                        'adminhtml/system_config/edit',
                        array('section' => 'shipping')
                    )
                );
            }
            throw new Innobyte_EmagMarketplace_Exception($errMsg);
        }
        $street = str_replace("\n", '', $request->getShipperAddressStreet());
        if (strlen($street) < 3) {
            $errMsg = 'Sender \'s street must have at least 3 letters.';
            if (Mage::getSingleton('admin/session')->isAllowed('system/config')) {
                $errMsg .= ' <a href="%s" target="_blank">Edit</a> street origin shipping settings.';
                $errMsg = $helper->__(
                    $errMsg,
                    Mage::helper('adminhtml')->getUrl(
                        'adminhtml/system_config/edit',
                        array('section' => 'shipping')
                    )
                );
            }
            throw new Innobyte_EmagMarketplace_Exception($errMsg);
        }
        $data = array(
            'name' => $name,
            'contact' => $request->getShipperContactPersonName(),
            'phone1' => $request->getShipperContactPhoneNumber(),
            'locality_id' => $localityId,
            'street' => $street,
        );
        if (strlen($request->getShipperAddressPostalCode())) {
            $data['zipcode'] = $request->getShipperAddressPostalCode();
        }

        return $data;
    }
}
