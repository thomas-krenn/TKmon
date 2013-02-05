
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
/*global require:true*/

(function () {
    "use strict";

    define(['jquery', 'bootstrap'], function () {
        "use strict";

        var currentHost;
        var searchUrl;
        var listUrl;
        var removeUrl;

        var setCurrentHost = function (hostName) {
            currentHost = hostName;
        };

        var getCurrentHost = function () {
            return currentHost;
        };

        /**
         * Toggle the create window
         *
         * @param {Object} data
         */
        var toggleCreateWindow = function (mode, data) {
            if (!mode) {
                mode = $('#services-create').hasClass('hide') ? 'show' : 'hide';
            }

            // DO SOMETHING USEFUL

            if (mode === 'show') {
                $('#services-grid').addClass('hide');
                $('#services-create').removeClass('hide');
            } else {
                $('#services-grid').removeClass('hide');
                $('#services-create').addClass('hide');
            }
        };

        /**
         * Type processor for combo field
         *
         * @param {String} q
         * @param {Function} process
         */
        var typeAheadSource = function (q, process) {
            $.ajax(searchUrl, {
                type:'POST',
                data:JSON.stringify({
                    q:q
                }),
                dataType:'json',
                success:function (data) {
                    if (data && data.success == true) {
                        var out = []
                        $.each(data.data, function (key, value) {
                            out.push(key);
                        });
                        process(out);
                    }
                }
            });
        };

        /**
         * Load an embedded grid into the placeholder
         *
         * @param {String} hostName
         */
        var contentUpdateHandler = function (hostName) {

            setCurrentHost();

            if (typeof(hostName) === "undefined") {
                return;
            }

            $.ajax(listUrl, {
                type:'POST',
                data:JSON.stringify({
                    hostName:hostName
                }),
                dataType:'json',
                success:function (data) {
                    $('#services-grid *').remove();
                    if (data && data.success === true) {

                        setCurrentHost(hostName);

                        $('#services-grid').html(data.data[0]);
                    } else {
                        var content = '<div class="alert alert-error">';
                        content += '<h4>Error</h4>';
                        $.each(data.errors, function (key, o) {
                            content += '<p class="container-spacer-down">' + o.message + '</p>';
                        });
                        content += '</h4>';
                        $('#services-grid').html(content);
                    }
                }
            })
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

        /**
         * Live handler (dom created later on) for removing action
         *
         * @param {Event} e
         */
        $('#services-grid').on('click', 'a[data-action=remove]', function (e) {
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

        // --------------------------------------------------------------------
        // Initialize
        // --------------------------------------------------------------------

        // Install typeahead on textfield
        $('#hostname').typeahead({
            source:typeAheadSource,
            matcher:function () {
                return true;
            }
        });

        $.fn.serviceEditor = function(options) {
            searchUrl = options.searchUrl;
            removeUrl = options.removeUrl;
            listUrl = options.listUrl;

            if (options.hostName) {
                contentUpdateHandler(options.hostName);
                $('#hostname').val(options.hostName);
            }
        };
    });
})();