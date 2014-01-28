(function () {
    "use strict";
    define(['jquery', 'bootstrap'], function ($) {

        var providerUrl;

        var getDocumentation = function(identifier, callback) {
            $.ajax(providerUrl, {
                data: {
                    identifier: identifier
                },
                type: 'POST'
            }).done(callback);
        };

        $.fn.documentationWidget = function(options) {
            if (options.url === undefined) {
                throw('options.url is missing');
            } else {
                providerUrl = options.url;
            }

            var element = $(this);

            getDocumentation(options.identifier, function(data) {
                if (data && data.available === true) {
                    $(element).removeClass('hidden');

                    $(element).popover({
                        html: true,
                        title: (options.title) ? options.title : 'Help',
                        content: data.html,
                        placement: 'bottom',
                        delay: {
                            hide: 3000
                        }
                    });

                    $('html').click(function(e) {
                        if ($(e.target).is(element) === false) {
                            $(element).popover('hide');
                        }
                    })
                } else {
                    $(element).addClass('hidden');
                }
            });
        }
    });
})();