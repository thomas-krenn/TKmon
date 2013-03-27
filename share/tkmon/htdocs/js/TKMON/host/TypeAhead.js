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

    /**
     * @
     */
    define(['jquery', 'bootstrap'], function () {

        /**
         * @type {String}
         */
        var dataUrl;

        /**
         * Setter for url
         * @param {String} url
         */
        var setUrl = function(url) {
            dataUrl = url;
        };

        /**
         * Getter for URL
         * @returns {String}
         */
        var getUrl = function() {
            return dataUrl;
        };

        /**
         * Source processor
         * @param {String} q Query
         * @param {Function} process Bootstrap data processor
         */
        var source = function (q, process) {

            $.ajax(getUrl(), {
                type:'POST',
                data:JSON.stringify({
                    q: q
                }),
                dataType:'json',
                success:function (data) {
                    if (data && data.success === true) {
                        var out = [];
                        $.each(data.data, function (key) {
                            out.push(key);
                        });
                        process(out);
                    }
                }
            });
        };

        /**
         * What to dispaly
         * @returns {boolean}
         */
        var matcher = function() {
            return true;
        };

        /**
         * jQuery typeahead plugin (bootstrap) for hosts
         *
         * @name hostTypeAhead
         * @class
         * @memberOf jQuery.fn
         * @param {Object} options
         */
        $.fn.hostTypeAhead = function(options) {
            setUrl(options.url);
            $(this).typeahead({
                source: source,
                matcher: matcher
            });
        };
    });

})();