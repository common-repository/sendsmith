jQuery(document).ready(function ($) {

        var data = {
            'action': 'sendsmith_tag_page_visit',
            'path': window.location.href,
            'security': sendsmithAjax.security
        };
        console.log(sendsmithAjax);
        $.post(sendsmithAjax.ajaxurl, data, function (response) {
            console.log(response);
        });

});
