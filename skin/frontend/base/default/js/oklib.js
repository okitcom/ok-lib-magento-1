var oklibpresenter = (function() {
    var getLibType = function(type) {
        switch (type) {
            case "open":
                return "a";
            case "cash":
                return "t";
        }
        return null;
    };

    return {
        showExisting: function (type) {
            /**
             * Either cash or open
             */
            const current = window.okLibType;
            if (current === type) {
                // just show the lib
                window.oklib.show();
                return true;
            }
            return false;
        },
        showNew: function (type, data) {
            /**
             * Either cash or open
             */
            const current = window.okLibType;
            if (typeof current !== 'undefined' && current != null) {
                window.oklib.remove();
            }
            window.okLibType = type;
            window.oklib.init(getLibType(type), data.guid, {
                color: "dark",
                culture: data.culture,
                loaded: oklib.start,
                initiation: data.initiation
            }, data.environment);
        },
        remove: function () {
            const current = window.okLibType;
            if (typeof current !== 'undefined') {
                window.oklib.remove();
            }
            window.okLibType = null;
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
    console.log($element);
    $element.append(window.jQuery(messageHtml));
}

var loadingOkRequest = false;

$(document).on('click', '#ok-checkout-button', function () {
    if (loadingOkRequest) {
        e.preventDefault();
        return;
    }
    const type = 'cash';
    if (!oklibpresenter.showExisting(type)) {

        var button = window.jQuery("#ok-checkout-button");
        button.addClass("ok-button-progress");
        loadingOkRequest = true;
        window.jQuery.ajax({
            showLoader: true,
            url: '/oklib/cash/init',
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
    if (loadingOkRequest) {
        e.preventDefault();
        return;
    }
    const okLibType = 'cash';
    const addtocart_form_selector = 'product_addtocart_form';

    var shouldRegenerateTransaction = false;

    var form = $(addtocart_form_selector);
    var formData = form.serialize();
    if (lastSelectedOptions != null && lastSelectedOptions !== formData) {
        shouldRegenerateTransaction = true;
    }
    lastSelectedOptions = formData;

    if (shouldRegenerateTransaction || !oklibpresenter.showExisting(okLibType)) {

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
            url: '/oklib/cash/buynow',
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
    } else {
        e.preventDefault();
    }
});

$(document).on('click', '#ok-open-button', function () {
    if (loadingOkRequest) {
        return;
    }
    const type = 'open';
    if (!oklibpresenter.showExisting(type)) {

        var button = window.jQuery("#ok-open-button");
        button.addClass("ok-button-progress");
        loadingOkRequest = true;
        window.jQuery.ajax({
            showLoader: true,
            url: '/oklib/open/init',
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