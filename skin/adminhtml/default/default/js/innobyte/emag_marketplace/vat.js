/**
 * Js script for eMAG vats.
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
 * Synchronize VATs.
 *
 * @param {string} strUrl URL to syncronize VATs.
 */
inno.emagMarketplace.syncVats = function(strUrl) {
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
