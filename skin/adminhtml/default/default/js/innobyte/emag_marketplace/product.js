/**
 * Js script for eMAG products.
 *
 * @category Innobyte
 * @package  Innobyte_EmagMarketplace
 * @author   Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */
"use strict";

var inno = inno || {};
inno.emagMarketplace = inno.emagMarketplace || {};

/**
 * Triggered on "onchange" for commission type select.
 * Adds / removes percents validation class for commission valuedepending on
 * commission type.
 */
inno.emagMarketplace.changeCommissionType = function() {
    var intCommissionType = parseInt($('innoemag_commission_type').value);
    if (1 === intCommissionType) {
        $('innoemag_commission_value').addClassName('validate-percents');
    } else {
        $('innoemag_commission_value').removeClassName('validate-percents');
    }
};

/**
 * Add new barcode input.
 *
 * @returns {Boolean} TRUE if all code was successfully executed, FALSE otherwise
 */
inno.emagMarketplace.addNewBarcode = function() {
    if (!$('innoemag_barcodes') || !$('note_barcodes')) {
        return false;
    }
    $('note_barcodes').insert({
        before: '<input type="text" class="input-text validate-length minimum-length-1 maximum-length-20 f-left" name="product[inno_emag_mktp][barcodes][]" value="" maxlength="20" />'
            + '<span class="innoemag-addremove-item" '
            + ("undefined" !== typeof(Translator) ? ('title="' + Translator.translate('Remove') + '"') : 'Remove')
            + '" onclick="inno.emagMarketplace.removeBarcode(this)"> - </span>'
            + '<br style="clear: both;"/>'
    });
    return true;
};

/**
 * Remove a barcode input.
 */
inno.emagMarketplace.removeBarcode = function(obj) {
    if ($(obj).previous('input')) {
        $(obj).previous('input').remove();
    }
    if ($(obj).next('br')) {
        $(obj).next('br').remove();
    }
    $(obj).remove();
};

/**
 * Triggered on "onchange" for category select.
 *
 * @returns {Boolean} TRUE if all code was successfully executed, FALSE otherwise
 */
inno.emagMarketplace.changeCategory = function(strUrl, blnRetrieveFamilyTypes) {
    // remove old characteristics
    $$('[id^="innoemag_category_characteristic"]').each(function(item) {
        item.up('tr').remove();
    });
    // remove family type
    if ($('innoemag_family_type_id')) {
        $('innoemag_family_type_id').up('tr').remove();
    }
    // check if we have a category id
    if (!$('innoemag_category_id')) {
        return false;
    }
    var intCatId = parseInt($('innoemag_category_id').value);
    if (isNaN(intCatId) || intCatId < 1) {
        return false;
    }
    new Ajax.Request(
        strUrl,
        {
            method: 'get',
            parameters: $H({category_id: intCatId, get_family_types: blnRetrieveFamilyTypes}),
            onSuccess: function (transport) {
                var response;
                if (transport && transport.responseText && transport.responseText.isJSON()) {
                    response = transport.responseText.evalJSON();
                }
                if (response) {
                    if ('success' === response.status) {
                        var strInputs = '';
                        // show the new characteristics
                        var characteristics = response.results.characteristics;
                        var strKey;
                        for (var i in characteristics) {
                            if (characteristics.hasOwnProperty(i)) {
                                strKey = 'category_characteristic' + parseInt(i);
                                strInputs += '<tr>\n'
                                    + '<td class="label"><label for=innoemag_"' + strKey + '">' + characteristics[i] + '<span class="required">*</span></label></td>\n'
                                    + '<td class="value">\n'
                                    + '    <input type="text" class="input-text required-entry validate-length maximum-length-255" id="innoemag_' + strKey + '" name="product[inno_emag_mktp][' + strKey + ']" value="" maxlength="255" />\n'
                                    + '</td>\n'
                                    + '</tr>\n';
                            }
                        }
                        $('innoemag_category_id').up('tr').insert({after: strInputs});
                        // show family type
                        if (blnRetrieveFamilyTypes && response.results.family_types) {
                            var familyTypes = response.results.family_types.items;
                            var ftOptions = '';
                            for (var i in familyTypes) {
                                if (familyTypes.hasOwnProperty(i)) {
                                    ftOptions += '<option value="' + i + '">' + familyTypes[i] + '</option>\n';
                                }
                            }
                            if (ftOptions.length > 0) {
                                strInputs = '<tr>\n'
                                    + '<td class="label"><label for="innoemag_family_type_id">' + ("undefined" !== typeof(Translator) ? Translator.translate('Family Type') : 'Family Type') + '<span class="required">*</span></label></td>\n'
                                    + '<td class="value">\n'
                                    + '    <select onchange="inno.emagMarketplace.changeFamilyType()" class="required-entry" id="innoemag_family_type_id" name="product[inno_emag_mktp][family_type_id]">'
                                    + '        <option value=""></option>\n'
                                    + ftOptions
                                    + '    </select>\n'
                                    + '</td>\n'
                                    + '</tr>\n';
                                $('innoemag_category_id').up('tr').insert({after: strInputs});
                                
                                inno.emagMarketplace.familyTypesCharacteristics = response.results.family_types.characteristics;
                            }
                        } else if ($('innoemag_family_type_id')) {
                            $('innoemag_family_type_id').up('tr').remove();
                        }
                    } else {
                        alert(response.message);
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
    return true;
};

/**
 * Send new product / offer / deactivation request.
 *
 * @param {string} strTabsContainerId   Left tabs container id.
 * @param {string} strUrl   URL to sent request to.
 * @param {string} strType  One of the product|offer|deactivate-offer strings.
 */
inno.emagMarketplace.send = function(strTabsContainerId, strUrl, strType) {
    var blnFormHasChanges = false;
    // check if form contains unsaved data
    $$('#' + strTabsContainerId + ' li a.tab-item-link').each(function(tab) {
        if (tab.hasClassName('changed')) {
            blnFormHasChanges = true;
            throw $break;
        }
    });
    if (blnFormHasChanges) {
        var strConfirmMsg;
        if ('product' === strType) {
            strConfirmMsg = ("undefined" !== typeof(Translator)
                ? Translator.translate("Please confirm product sending. All data that hasn't been saved will be lost.")
                : "Please confirm product sending. All data that hasn't been saved will be lost.");
        } else if ('offer' === strType) {
            strConfirmMsg = ("undefined" !== typeof(Translator)
                ? Translator.translate("Please confirm offer sending. All data that hasn't been saved will be lost.")
                : "Please confirm offer sending. All data that hasn't been saved will be lost.");
        } else {
            strConfirmMsg = ("undefined" !== typeof(Translator)
                ? Translator.translate("Please confirm offer deactivation. All data that hasn't been saved will be lost.")
                : "Please confirm offer deactivation. All data that hasn't been saved will be lost.");
        }
        if (confirm(strConfirmMsg)) {
            setLocation(strUrl);
        }
    } else {
        setLocation(strUrl);
    }
};

/**
 * Triggered on "onchange" for family type select.
 * Enables/disables selected family type 's characteristics.
 *
 * @returns {Boolean} TRUE if all code was successfully executed, FALSE otherwise
 */
inno.emagMarketplace.changeFamilyType = function() {
    if (!$('innoemag_family_type_id')
        || !inno.emagMarketplace.familyTypesCharacteristics) {
        return false;
    }
    $$('[id^=innoemag_category_characteristic]').each(function(item) {
        if (item.hasAttribute('disabled')) {
            item.removeAttribute('disabled');
            item.removeClassName('disabled');
        }
        if (item.next('p.note')) {
            item.next('p.note').remove();
        }
    });
    var intFamTypeId = parseInt($('innoemag_family_type_id').value);
    if (isNaN(intFamTypeId) || intFamTypeId < 1) {
        return false;
    }
    var arrCharacteristics = inno.emagMarketplace.familyTypesCharacteristics[intFamTypeId];
    if (!arrCharacteristics) {
        return false;
    }
    for (var i = 0; i < arrCharacteristics.length; i++) {
        var objCharInput = $('innoemag_category_characteristic' + arrCharacteristics[i]);
        objCharInput.setAttribute('disabled', 'disabled');
        objCharInput.addClassName('disabled');
        objCharInput.value = '';
        var strNote = '<p class="note">' + ("undefined" !== typeof(Translator) ?
            Translator.translate('This attribute will have to be set on associated products individually') :
            'This attribute will have to be set on associated products individually') + '</p>';
        objCharInput.insert({after: strNote});
    }
};

/**
 * Triggerd on part number key input bluring.
 * Makes some fields required / not required depending if we have a part number key.
 *
 * @returns {Boolean} TRUE if all code was successfully executed, FALSE otherwise
 */
inno.emagMarketplace.changePartNumberKey = function() {
    if (!$('innoemag_part_number_key')) {
        return false;
    }
    if ($('innoemag_part_number_key').value.length > 0) {
        if ($('innoemag_name').hasClassName('required-entry')) {
            $$('label[for="innoemag_name"]')[0].select('span.required')[0].remove();
            $('innoemag_name').removeClassName('required-entry');
            
            $$('label[for="innoemag_brand"]')[0].select('span.required')[0].remove();
            $('innoemag_brand').removeClassName('required-entry');
            
            $$('label[for="innoemag_category_id"]')[0].select('span.required')[0].remove();
            $('innoemag_category_id').removeClassName('required-entry');
        }
    } else {
        if (!$('innoemag_name').hasClassName('required-entry')) {
            $$('label[for="innoemag_name"]')[0].insert('<span class="required">*</span>');
            $('innoemag_name').addClassName('required-entry');
            
            $$('label[for="innoemag_brand"]')[0].insert('<span class="required">*</span>');
            $('innoemag_brand').addClassName('required-entry');
            
            $$('label[for="innoemag_category_id"]')[0].insert('<span class="required">*</span>');
            $('innoemag_category_id').addClassName('required-entry');
        }
    }
};
