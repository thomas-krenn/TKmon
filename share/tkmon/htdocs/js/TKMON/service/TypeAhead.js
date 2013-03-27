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

        /**
         * URL
         * @type {String}
         */
        var dataUrl;

        /**
         * Object of labels
         * @type {Object}
         */
        var labels = {};

        /**
         * Data
         * @type {Array}
         */
        var data = [];

        /**
         * Current selected item
         * @type{String}
         */
        var currentItem;

        /**
         * Sets everything to origin
         */
        var resetData = function() {
            data = [];
            labels = {};
        };

        /**
         * Creates data structur
         *
         * - suitable for bootstrap process method
         * - Fill our labels to work with later on
         *
         * @param {Object} result
         */
        var buildData = function(result) {
            var sub;
            $.each(result, function(key, obj) {
                sub = obj._catalogue_attributes;
                data.push(sub.name);
                labels[sub.name] = sub;
            });
        };

        /**
         * Getter for data
         * @returns {Array}
         */
        var getData = function() {
            return data;
        };

        /**
         * Setter for url
         * @param {String} url
         */
        var setUrl = function(url) {
            dataUrl = url;
        };

        /**
         * Getter for url
         * @returns {String}
         */
        var getUrl = function() {
            return dataUrl;
        };

        /**
         * Source processor.
         *
         * Queries ajax endpoint and call data structure method
         *
         * @param {String} q
         * @param {Function} process
         */
        var source = function (q, process) {

            $.ajax(getUrl(), {
                type:'POST',
                data:JSON.stringify({
                    q: q
                }),
                dataType:'json',
                success:function (result) {
                    resetData();
                    if (result && result.success == true) {
                        buildData(result.data);
                        process(getData());
                    }
                }
            });
        };

        /**
         * Bootstrap matcher function
         *
         * Matches all
         *
         * @returns {boolean} Always
         */
        var matcher = function() {
            return true;
        };

        /**
         * Highlighter
         *
         * Formats output row
         *
         * @param {String} item
         * @returns {string} HTML for one row
         */
        var highlighter = function(item) {
            var out = '';
            var o = labels[item];

            out += '<div class="tkmon-catalogue-item">';
            out += '<div class="item-name">';
            out += o.label + ' (' + o.name + ')';
            out += '</div>';
            out += '<div class="item-description">';
            out += o.description;
            out += '</div>';
            out += '</div>';

            return out;
        };

        /**
         * Updater of the field
         *
         * This is
         *
         * @param item
         * @returns {String}
         */
        var updater = function(item) {
            if (item in labels) {
                currentItem = item;
            }
            return currentItem;
        };

        /**
         * jQuery typeahead plugin (bootstrap) for services
         *
         * @name serviceTypeAhead
         * @class
         * @memberOf jQuery.fn
         * @param {Object} options
         */
        $.fn.serviceTypeAhead = function(options) {
            setUrl(options.url);
            $(this).typeahead({
                source: source,
                matcher: matcher,
                highlighter: highlighter,
                updater: updater
            });
        };
    });

})();