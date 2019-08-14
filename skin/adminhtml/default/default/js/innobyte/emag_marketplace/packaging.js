/**
 * Extend Packaging class from Magento.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */
"use strict";

var inno = inno || {};
inno.emagMarketplace = inno.emagMarketplace || {};

/**
 * Extend "Packaging" class.
 */
inno.emagMarketplace.packaging = Class.create(Packaging, {
    /**
     * @Override
     */
    sendCreateLabelRequest: function($super) {
        // begin Innobyte customization - add extra inno emag params
        if (!$('emag-marketplace-shipping-extra')) {
            $super();
            return;
        }
        $('emag-marketplace-shipping-extra').select('input,select').each(
            function(item, idx) {
                this.paramsCreateLabelRequest[item.name] = item.value;
            },
            this
        );
        // end Innobyte customization - add extra inno emag params
        $super();
    },
    
    /**
     * @Override
     */
    validate: function($super) {
        var blnResult = $super();
        // begin Innobyte customization - validate extra inno emag inputs
        if (!$('emag-marketplace-shipping-extra')) {
            return blnResult;
        }
        var blnInnoFieldsResult = $('emag-marketplace-shipping-extra')
            .select('input,select')
            .collect(
                function (element) {
                    return this.validateElement(element);
                },
                this
            )
            .all();
        blnResult = blnResult && blnInnoFieldsResult;
        // end Innobyte customization - validate extra inno emag inputs
        return blnResult;
    }
});
