/*
 * The commons javascript
 */

// Document ready
jQuery(function () {
    var $ = jQuery;
    window.amzCommons = window.amzCommons || {};

    amzCommons.notice = function (state, message) {
        $('#noticeDialog').html('');

        var closeBtn = $('<button>', {
                'class': 'notice-dismiss',
                'click': function () {
                    $(divNotice).remove();
                }
            }),
            text = $('<p>', {
                'html': message
            }),
            divNotice = $('<div>', {
                'class': 'notice is-dismissible ' + state
            });

        $(divNotice).append(text);
        $(divNotice).append(closeBtn);

        $('#noticeDialog').append(divNotice);

        setTimeout(function () {
            $(divNotice).remove();
        }, 4000);

    };

    amzCommons.callApi = function (data, successCb, errorCb) {

        $('#noticeDialog').html('');
        $('#productResult').hide();

        $.post(ajaxurl, data)
            .done(function (response) {
                successCb(response);
            })
            .fail(function (xhr, status, error) {
                console.log('xhr: ', xhr);
                console.log('status: ', status);
                console.log('error: ', error);
                amzCommons.notice('notice-error', xhr.responseText);
                if (errorCb) {
                    errorCb();
                }
            });

    };
});
