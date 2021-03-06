var oklibpresenter = (function() {
    var oklibCash;
    var oklibOpen;

    return {
        instantiate: function() {
            if (typeof oklibCash === 'undefined') {
                oklibCash = new window.oklib.OKLIB();
            }

            if (typeof oklibOpen === 'undefined') {
                oklibOpen = new window.oklib.OKLIB();
            }
        },
        showExisting: function (type) {
            if (type === 'cash') {
                oklibCash.show();
            } else if (type === 'open') {
                oklibOpen.show();
            }                
        },
        showNew: function (type, data) {
            var config = {
                color: 'dark',
                culture: data.culture,
                initiation: data.initiation
            };

            if (type === 'cash') {
                config.loaded = oklibCash.start;
                oklibCash.init('t', data.guid, config, data.environment);
            } else if (type === 'open') {
                config.loaded = oklibOpen.start;
                oklibOpen.init('a', data.guid, config, data.environment);
            }
        },
        isInitialized: function (type) {
            if (type === 'cash') {
                return oklibCash.isInitialized();
            } else if (type === 'open') {
                return oklibOpen.isInitialized();
            }
        },
        reset: function (type) {
            if (type === 'cash') {
                if (oklibCash.isInitialized()) {
                    oklibCash.hide();
                }
                oklibCash = new window.oklib.OKLIB();
            } else if (type === 'open') {
                if (oklibOpen.isInitialized()) {
                    oklibOpen.hide();
                }
                oklibOpen = new window.oklib.OKLIB();
            }
        }
    };
})();

function showMessage(txt, type) {
    var productPageContainer = window.jQuery(".col-main");

    var messageHtml = '<li class="'+type+'-msg"><ul><li>' + txt + '</li></ul></li>';
    var html = '<ul class="messages"></ul>';
    var $element = window.jQuery('ul.messages');
    if($element == null || !$element.length)
        $element = window.jQuery(html).prependTo(productPageContainer);
    $element.append(window.jQuery(messageHtml));
}

function getOkStoreUrl(path) {
    var storeUrl = window.okStoreInfo.store_url;
    if (storeUrl !== 'undefined') {
        return okStoreInfo.store_url + path;
    }
    return '/' . path;
}

var loadingOkRequest = false;

$(document).on('click', '#ok-checkout-button', function (e) {
    oklibpresenter.instantiate();
    e.preventDefault();
    if (loadingOkRequest) {
        return;
    }
    var type = 'cash';
    if (oklibpresenter.isInitialized(type)) {
        oklibpresenter.showExisting(type);
    } else {
        var button = window.jQuery("#ok-checkout-button");
        button.addClass("ok-button-progress");
        loadingOkRequest = true;
        window.jQuery.ajax({
            showLoader: true,
            url: getOkStoreUrl('oklib/cash/init'),
            data: "",
            type: "GET",
            dataType: 'json'
        }).done(function (data) {
            loadingOkRequest = false;
            button.removeClass("ok-button-progress");
            if (data.error) {
                showMessage(data.error, "error");
            } else {
                oklibpresenter.showNew(type, data);
            }
        }).error(function(req, data) {
            loadingOkRequest = false;
            button.removeClass("ok-button-progress");
            showMessage("Your transaction amount exceeds the maximum that is supported by OK.", "error");
        });
    }
});

var lastSelectedOptions = null;

$(document).on('click', '#ok-buynow-button', function (e) {
    oklibpresenter.instantiate();
    if (loadingOkRequest) {
        e.preventDefault();
        return;
    }
    var okLibType = 'cash';
    var addtocart_form_selector = 'product_addtocart_form';

    var form = $(addtocart_form_selector);
    var formData = form.serialize();
    if (lastSelectedOptions != null && lastSelectedOptions !== formData) {
        oklibpresenter.reset(okLibType);
    }
    lastSelectedOptions = formData;

    if (oklibpresenter.isInitialized(okLibType)) {
        oklibpresenter.showExisting(type);
        e.preventDefault();
    } else {
        var magentoForm = new VarienForm(addtocart_form_selector, true);
        if(!magentoForm.validator.validate()){
            return;
        }
        e.preventDefault();

        var button = window.jQuery("#ok-buynow-button");
        button.addClass("ok-button-progress");
        loadingOkRequest = true;
        window.jQuery.ajax({
            showLoader: true,
            url: getOkStoreUrl('oklib/cash/buynow'),
            data: lastSelectedOptions,
            type: "GET",
            dataType: 'json'
        }).done(function (data) {
            loadingOkRequest = false;
            button.removeClass("ok-button-progress");
            if (data.error) {
                showMessage(data.error, "error");
            } else {
                oklibpresenter.showNew(okLibType, data);
            }
        }).error(function(req, data) {
            loadingOkRequest = false;
            button.removeClass("ok-button-progress");
            showMessage("An unknown error occurred.", "error");
        });
    }
});

$(document).on('click', '#ok-open-button', function () {
    oklibpresenter.instantiate();
    if (loadingOkRequest) {
        return;
    }
    var type = 'open';
    if (oklibpresenter.isInitialized(type)) {
        oklibpresenter.showExisting(type);
    } else {
        var button = window.jQuery("#ok-open-button");
        button.addClass("ok-button-progress");
        loadingOkRequest = true;
        window.jQuery.ajax({
            showLoader: true,
            url: getOkStoreUrl('oklib/open/init'),
            data: "",
            type: "GET",
            dataType: 'json'
        }).done(function (data) {
            loadingOkRequest = false;
            button.removeClass("ok-button-progress");
            if (data.error) {
                showMessage(data.error, "error");
            } else {
                oklibpresenter.showNew(type, data);
            }
        }).error(function(req, data) {
            loadingOkRequest = false;
            button.removeClass("ok-button-progress");
            showMessage("An unknown error occurred.", "error");
        });
    }
});