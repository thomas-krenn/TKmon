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
 * @copyright 2012-2015 NETWAYS GmbH <info@netways.de>
 */
/*global define:true*/

(function () {
    "use strict";

    define(['jquery', 'bootstrap'], function ($) {

        /**
         * @name jquery
         * @param {Object|String} options
         * @param {Object} params
         */
        $.fn.update = function(options, params) {
            var that = $(this);

            if (typeof(options) !== "object") {
                options =  {
                    url: options,
                    params: params || {},
                    before: null,
                    after: null
                };
            }

            var xhrOptions = {
                type: 'POST',
                data: JSON.stringify(params),
                dataType: 'json',
                success: function(data) {

                    $(that).html("");

                    if (typeof options.after === "function") {
                        options.after.call(this, data);
                    }

                    if (data && typeof data === "object" && data.success === true) {
                        $.each(data.data, function(index, content) {
                            $(that).append('<div class="container-spacer-down">' + content + '</div>');
                        });
                    } else {
                        var content = '<div class="alert alert-error">';
                        content += '<h4>Error</h4>';
                        $.each(data.errors, function (key, o) {
                            content += '<p class="container-spacer-down">' + o.message + '</p>';
                        });
                        content += '</h4>';
                        $(that).html(content);
                    }
                }
            };

            if (typeof options.before === "function") {
                options.before.call(this, xhrOptions);
            }

            $.ajax(options.url, xhrOptions);
        };

    });

})();
