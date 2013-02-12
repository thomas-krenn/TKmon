/**
 * This file is part of TKMON
 *
 * TKMON is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TKMON is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TKMON.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Marius Hein <marius.hein@netways.de>
 * @copyright 2012-2013 NETWAYS GmbH <info@netways.de>
 */
/*global define:true*/

(function () {
    "use strict";

    define([
        'jquery',
        'bootstrap',
        'serializeJSON',
        'TKMON/service/TypeAhead',
        'TKMON/host/TypeAhead',
        'TKMON/jquery/UpdateHandler'
    ], function () {

        var currentHost;
        var hostSearchUrl;
        var serviceSearchUrl;
        var listUrl;
        var createFormUrl;
        var editFormUrl;
        var addServiceUrl;
        var removeUrl;

        /**
         * Setter for current host_name
         * @param {String} hostName
         */
        var setCurrentHost = function (hostName) {
            currentHost = hostName;
        };

        /**
         * Get current host_name
         * @return {String}
         */
        var getCurrentHost = function () {
            return currentHost;
        };

        /**
         * Toggle the create window
         * @param {String} mode You can use show or hide
         * @param {Object} data
         */
        var toggleCreateWindow = function (mode) {
            if (!mode) {
                mode = $('#services-create').hasClass('hide') ? 'show' : 'hide';
            }

            // DO SOMETHING USEFUL

            if (mode === 'show') {
                $('#services-data *').remove();
                $('#serviceCatalogueId').val("");
                $('div#service-search-container').removeClass('hide');
                $('#services-grid').addClass('hide');
                $('#services-create').removeClass('hide');

                $('#serviceCatalogueId').focus();
            } else {
                $('#services-grid').removeClass('hide');
                $('#services-create').addClass('hide');
            }
        };

        var contentUpdateHandler = function (hostName) {
            setCurrentHost(hostName);

            $('#services-grid').update(listUrl, {
                hostName: hostName
            });
        };

        // --------------------------------------------------------------------
        // Install handler
        // --------------------------------------------------------------------

        /**
         * Submit handler
         *
         * Implements buffering to reduce double from fires
         *
         * Buffer is hardcoded to 2 seconds
         *
         * @param {Event} e
         */
        $('#host-select').submit(function (e) {

            e.preventDefault();
            var that = $(this);

            if (!$(this).data('nosubmit')) {
                var data = JSON.parse($(this).serializeJson());
                contentUpdateHandler(data.hostname);

                $(this).data('nosubmit', true);

                window.setTimeout(function () {
                    $(that).data('nosubmit', false);
                }, 2000);
            }
        });

        $('#service-search').submit(function (e) {
            e.preventDefault();
            var data = JSON.parse($(this).serializeJson());
            data.hostName = getCurrentHost();
            $('#services-data').update(createFormUrl, data);
        });

        /**
         * Live handler (dom created later on) for removing action
         * @param {Event} e
         */
        $('#services-grid').on('click', 'a[data-action=remove]', function (e) {
            e.preventDefault();
            var id = $(this).attr('data-value');
            var that = $(this);

            $.ajax(removeUrl, {
                type:'POST',
                data:JSON.stringify({
                    hostName:getCurrentHost(),
                    serviceId:id
                }),
                dataType:'json',
                success:function (data) {
                    if (data && data.success === true) {
                        contentUpdateHandler(getCurrentHost());
                    } else {
                        $(that).parents('div.btn-group').errorPopover(data);
                    }
                }
            });
        });

        /**
         * @param {Event} e
         */
        $('#services-grid').on('click', 'button[data-action=new],a[data-action=new]', function (e) {
            e.preventDefault();
            toggleCreateWindow();
        });

        $('#services-create').on('click', 'button[data-action=create-cancel],a[data-action=create-cancel]', function (e) {
            e.preventDefault();
            toggleCreateWindow();
        });

        $('#services-create').on('click', 'button[data-action=create-write],a[data-action=create-write]', function (e) {
            e.preventDefault();
            $('form#service-embedded-create').submit();
        });


        $('#services-data').on('submit', 'form#service-embedded-create', function(e) {
            e.preventDefault();

            $('div#embedded-create-errors *').remove();
            $('div#embedded-create-success').addClass('hide');

            var values = JSON.stringify($(this).serializeJSON());

            var handleError = function(data) {
                if (data && typeof(data) === "object") {
                    var html = '';
                    $.each(data.errors, function(index, obj) {
                        html += '<div class="alert alert-error">' +
                            '<h4>' + "Error" + '</h4>' +
                            obj.message + '</div>';
                    });
                    $('div#embedded-create-errors').html(html);
                }
            };

            var handleSuccess = function(data) {
                if (data && typeof(data) === "object") {
                    if (data.success === true) {
                        $('div#embedded-create-success').removeClass('hide');
                        window.setTimeout(function() {
                            toggleCreateWindow();
                            contentUpdateHandler(getCurrentHost());
                        }, 1500);
                    } else {
                        handleError(data);
                    }
                }
            };

            $.ajax(addServiceUrl, {
                type: 'POST',
                data: values,
                dataType: 'json',
                success: handleSuccess
            });
        });

        $('#services-grid').on('click', 'button[data-action=edit],a[data-action=edit]', function (e) {
            e.preventDefault();
            var id = $(this).attr('data-value');
            toggleCreateWindow();
            $('div#service-search-container').addClass('hide');

            var data = {
                hostName: getCurrentHost(),
                serviceDescription: id
            };

            $('#services-data').update(editFormUrl, data);
        });

        // --------------------------------------------------------------------
        // Initialize
        // --------------------------------------------------------------------

        var doInitialize = function() {

            // Install typeahead on textfield
            $('#hostname').hostTypeAhead({
                url: hostSearchUrl
            });

            // Install typeahead on textfield
            $('#serviceCatalogueId').serviceTypeAhead({
                url: serviceSearchUrl
            });
        };

        /**
         * jQuery plugin serviceEditor
         * @param {Object} options
         */
        $.fn.serviceEditor = function(options) {
            hostSearchUrl = options.hostSearchUrl;
            serviceSearchUrl = options.serviceSearchUrl;
            removeUrl = options.removeUrl;
            listUrl = options.listUrl;
            createFormUrl = options.createFormUrl;
            addServiceUrl = options.addServiceUrl;
            editFormUrl = options.editFormUrl;

            doInitialize();

            if (options.hostName) {
                contentUpdateHandler(options.hostName);
                $('#hostname').val(options.hostName);
            }
        };
    });
})();
