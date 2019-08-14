/**
 * Js script for eMAG localities.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */
"use strict";

var inno = inno || {};
inno.emagMarketplace = inno.emagMarketplace || {};

/**
 * Show message on top of the page.
 *
 * @param {string} strMessage  The message to show
 * @param {string} strType     The type of the message (success|error|notice...)
 */
inno.emagMarketplace.showMsg = function(strMessage, strType) {
    if ($('messages')) {
        var strHtml = '<ul class="messages">\n\
                        <li class="' + strType + '-msg">\n\
                            <ul>\n\
                                <li>' + strMessage + '</li>\n\
                            </ul>\n\
                        </li>\n\
                    </ul>';
        $('messages').update(strHtml);
    }
};

/**
 * Synchronize localities.
 *
 * @param {string} strUrl URL to syncronize localities.
 */
inno.emagMarketplace.syncLocalities = function(strUrl) {
    new Ajax.Request(
        strUrl,
        {
            method: 'post',
            onSuccess: function (transport) {
                var response;
                if (transport && transport.responseText
                    && transport.responseText.isJSON()) {
                    response = transport.responseText.evalJSON();
                }
                if (response) {
                    if ('success' === response.status) {
                        inno.emagMarketplace.showMsg(
                            response.message,
                            response.status
                        );
                        location.reload();
                    } else {
                        inno.emagMarketplace.showMsg(
                            response.message,
                            response.status
                        );
                    }
                }
            },
            onFailure: function() {
                if ("undefined" !== typeof(Translator)) {
                    alert(Translator.translate('An error occurred. Please try again later.'));
                } else {
                    alert('An error occurred. Please try again later.');
                }
            }
        }
    );
};

/**
 * Callback for Ajax.Autocompleter
 *
 * @param {string} text The text of the li.
 * @param {object} li The li from autocomplete response.
 * @returns {Boolean} TRUE if all code was successfully executed, FALSE otherwise
 */
inno.emagMarketplace.updateAddressLocality = function(text, li) {
    // get emag locality id
    var intCityEmagId = parseInt(li.getAttribute('data-emag-id'));
    if (isNaN(intCityEmagId) || intCityEmagId < 1) {
        return false;
    }
    
    var strAddressTypePrefix = '';
    var strAddressTypeSuffix = '';
    // compute suffixes and prefixes by determining if we are on order page
    // or in system config shipping origin page
    if ('undefined' !== typeof(order)) {
        strAddressTypePrefix = 'order-';
        strAddressTypeSuffix = '_address';
    }
    // get address type from <ul> tag
    var objUlElem = li.up();
    var strAddressType = objUlElem.getAttribute('data-address-type');
    if ('billing' !== strAddressType && 'shipping' !== strAddressType
        && 'shipping_origin' !== strAddressType) {
        return false;
    }
    var strHighlightColor = '#c8c8c8';
    
    // check and set region if exists
    var strCounty = li.getAttribute('data-county');
    if (strCounty.length > 0) {
        var strRegionElemId = strAddressTypePrefix + strAddressType
            + strAddressTypeSuffix + '_region';
        var objRegionElem = $(strRegionElemId);
        if (objRegionElem) {
            objRegionElem.value = strCounty;
            new Effect.Highlight(
                objRegionElem,
                { startcolor: strHighlightColor, duration: 3 }
            );
            if ('billing' === strAddressType && order.shippingAsBilling) {
                inno.emagMarketplace.copyDataFromBillingToShipping(strRegionElemId);
            }
        }
    }
    
    // check and set region id select if region id info is available
    var intCountyId = parseInt(li.getAttribute('data-county-id'));
    if (!isNaN(intCountyId) && intCountyId > 0) {
        var strRegionIdElemId = strAddressTypePrefix + strAddressType
            + strAddressTypeSuffix + '_region_id';
        var objRegionIdElem = $(strRegionIdElemId);
        if (objRegionIdElem && objRegionIdElem.visible()) {
            objRegionIdElem.value = intCountyId;
            // make the option selected
            $$('select#' + strRegionIdElemId + ' option').each(function(o) {
                if (o.readAttribute('value') == intCountyId) {
                    o.selected = true;
                    throw $break;
                }
            });
            new Effect.Highlight(
                objRegionIdElem,
                { startcolor: strHighlightColor, duration: 3 }
            );
            if ('billing' === strAddressType && order.shippingAsBilling) {
                inno.emagMarketplace.copyDataFromBillingToShipping(strRegionIdElemId);
            }
        }
    }
    
    // set emag locality id
    var strEmagLocalityIdElemId = strAddressTypePrefix + strAddressType
        + strAddressTypeSuffix + '_emag_locality_id';
    var objEmagLocalityIdElem = $(strEmagLocalityIdElemId);
    if (objEmagLocalityIdElem) {
        objEmagLocalityIdElem.value = intCityEmagId;
        if ('billing' === strAddressType && order.shippingAsBilling) {
            inno.emagMarketplace.copyDataFromBillingToShipping(strEmagLocalityIdElemId);
        }
    }
        
    var objCityElem = $(strAddressTypePrefix + strAddressType + strAddressTypeSuffix + '_city');
    if (objCityElem) {
        // called only once "change", for city, so loadBlock call will be called
        // only here, and made "if ('billing' === strAddressType &&
        // order.shippingAsBilling)" checks for the other fields instead of
        // calling "change" for them too.
        if ('undefined' !== typeof(order)) {
            objCityElem.simulate('change'); // this will trigger loadBlock call
        }
        new Effect.Highlight(
            objCityElem,
            { startcolor: strHighlightColor, duration: 3 }
        );
    }
    return true;
};

/**
 * Copy data from billing to shipping address.
 * This function is present in original OrderAdmin - sales.js Magento >= 1.8,
 * but not in 1.7.
 *
 * @param string strFieldId Billing field id to copy data from.
 */
inno.emagMarketplace.copyDataFromBillingToShipping = function(strFieldId) {
    var shippingId = $(strFieldId).identify().replace('-billing_', '-shipping_');
    var inputField = $(shippingId);
    if (inputField) {
        inputField.setValue($(strFieldId).getValue());
        if (inputField.changeUpdater) {
            inputField.changeUpdater();
        }
        $(order.shippingAddressContainer).select('select').each(function(el){
            el.disable();
        });
    }
};

/**
 * Attach autocomplete city to an address.
 *
 * @param {string} strLoadCitiesUrl Ajax url to get cities.
 * @param {string} strAddressType Address type(billing|shipping|shipping_origin)
 * @param {string} strLoaderImgUrl Autocomplete loading image; optional.
 * @returns {Boolean} TRUE if all code was successfully executed, FALSE otherwise
 */
inno.emagMarketplace.attachAutocompleteEmagCity = function(strLoadCitiesUrl, strAddressType, strLoaderImgUrl) {
    var strAddressTypePrefix = '';
    var strAddressTypeSuffix = '';
    // compute suffixes and prefixes by determining if we are on order page
    // or in system config shipping origin page
    if ('undefined' !== typeof(order)) {
        strAddressTypePrefix = 'order-';
        strAddressTypeSuffix = '_address';
    }
    // check that city elements exist
    var strCityElemId = strAddressTypePrefix + strAddressType + strAddressTypeSuffix + '_city';
    var objCityElem = $(strCityElemId);
    var strEmagLocalityIdElemId = strAddressTypePrefix + strAddressType + strAddressTypeSuffix + '_emag_locality_id';
    var objEmagCityElem = $(strEmagLocalityIdElemId);
    if (!objCityElem || !objEmagCityElem) {
        return false;
    }
    
    // insert autocomplete indicator and results container
    if (strLoaderImgUrl) {
        objCityElem.insert({
            before: '<span id="emag-city-autocomplete-indicator-' + strAddressType + '" class="emag-city-autocomplete-indicator" style="display: none">'
                + '<img src="' + strLoaderImgUrl +'" alt="' + Translator.translate('Loading...') + '" class="v-middle"/>'
                + '</span>'
        });
    }
    objCityElem.insert({
        after: '<div id="emag-city-autocomplete-' + strAddressType + '" class="emag-city-autocomplete"></div>'
    });
    
    // configure scriptaculos autocompleter
    var additionalParams = 'address_type=' + encodeURIComponent(strAddressType);
    if ('undefined' !== typeof(order)) {
        additionalParams += '&store_id=' + parseInt(order.storeId);
    }
    new Ajax.Autocompleter(
        strCityElemId,
        'emag-city-autocomplete-' + strAddressType,
        strLoadCitiesUrl,
        {
            paramName: strCityElemId,
            parameters: additionalParams,
            minChars: 2,
            indicator: (strLoaderImgUrl ? 'emag-city-autocomplete-indicator-' + strAddressType : false),
            select: 'emag-city-name-' + strAddressType,
            afterUpdateElement: inno.emagMarketplace.updateAddressLocality,
            callback: function(field, query) {
                query += '&city=' + encodeURIComponent(field.value); // resend city param with unique name
                var strCountryIdElemId = strAddressTypePrefix + strAddressType + strAddressTypeSuffix + '_country_id';
                if ($(strCountryIdElemId)) {
                    query += '&country=' + encodeURIComponent($(strCountryIdElemId).value);
                }
                objEmagCityElem.value = ''; // reset locality id
                return query;
            }
        }
    );
};
