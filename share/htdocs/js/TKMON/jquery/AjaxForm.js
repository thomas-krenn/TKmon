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

    require(['jquery'], function ($) {

        var selector = '[data-ajax="form"]';

        /**
         * Creates an object for use within a jquery plugin
         * @param {jquery} el
         * @constructor
         */
        var Html5AjaxForm = function (el) {
            if ($(el).attr("data-ajax") === "form") {
                $(el).on('submit', this.submitHandler);
            }
        };

        Html5AjaxForm.prototype = {
            /**
             * JS handler when submitting a form
             * @param {Event} e
             */
            submitHandler: function (e) {

                // We do not run new requests, instead making
                // ajax requests to the form
                if (e) {
                    e.preventDefault();
                }

                var errorTarget = Html5AjaxForm.prototype.getErrorTarget.call(this);
                var actionUrl = Html5AjaxForm.prototype.getActionUrl.call(this);
                var submitButton = Html5AjaxForm.prototype.getSubmitButton.call(this);
                var data = null;
                var dataType = null;
                var that = $(this);

                submitButton.addClass("disabled");
                $($(this).attr("data-success-frame")).addClass("hidden");
                errorTarget.find('*').remove();

                if ($(this).attr("data-ajax-type") && $(this).attr("data-ajax-type") === "json") {
                    data = $(this).serializeJson();
                    dataType = "json";
                } else {
                    data = $(this).serialize();
                }

                /*
                 * Post the data to server and change
                 *
                 * - data
                 * - contentType header
                 * - dataType
                 *
                 * as needed.
                 */
                $.ajax({
                    type: "POST",
                    url: actionUrl,
                    data: data,
                    contentType: (dataType === "json") ?
                        "application/json; charset=utf-8" :
                        "application/x-www-form-urlencoded; charset=utf-8",
                    dataType: dataType,
                    success: function (struct) {

                        // On try to convert string into an object
                        // Under normal circumstances not needed
                        if (typeof(struct) !== "object") {
                            struct = JSON.parse(struct);
                        }

                        if (typeof (struct) === "object") {
                            if (struct.success === true) {
                                if (that.attr("data-success-frame")) {
                                    $(that.attr("data-success-frame")).removeClass("hidden");
                                }

                                if (that.attr("data-success-callback")) {
                                    var cb = that.attr("data-success-callback");
                                    if (typeof (window[cb]) === 'function') {
                                        window[cb].call(that);
                                    }
                                }

                                if (that.attr("data-form-reset") === 'true') {
                                    Html5AjaxForm.prototype.resetForm(that);
                                }
                            } else {
                                $.each(struct.errors, function (i, e) {
                                    errorTarget.append(Html5AjaxForm.prototype.createErrorPanel(e));
                                });
                            }
                        } else {
                            $.error("Unknown response from " + actionUrl);
                        }

                        submitButton.removeClass("disabled");
                    }
                });
            },

            /**
             * Creates html fragment to place in an error container
             * @param {Object} data
             * @return {String}
             */
            createErrorPanel: function (data) {
                return '<div class="alert alert-error">' +
                    '<h4>Error!</h4>' + data.message +
                    '</div>';
            },

            /**
             * Returns a element based on the data-target attribute
             * @return {*|jQuery|HTMLElement}
             */
            getErrorTarget: function () {
                var errorSelector = $(this).attr("data-error-target");

                if (!errorSelector) {
                    $.error('No error target configured: data-error-target');
                }

                return $(errorSelector);
            },

            /**
             * Returns submit button of the html form
             * @return {*|jQuery}
             */
            getSubmitButton: function () {
                return $(this).find('input[type="submit"]');
            },

            /**
             * Returns action url from form
             * @return {String}
             */
            getActionUrl: function () {
                return $(this).attr('action');
            },

            /**
             * Resets a the whole form
             * TODO: Add complex fields
             * @param {jquery} form
             */
            resetForm: function(form) {
                $(form).find('input[type!="submit"]').val('');
            }
        };

        /*
         * Plugin object if you use the plugin without
         * html5 tags
         */
        $.fn.Html5AjaxForm = function (option) {
            return this.each(function () {
                var ele = $(this);

                var data = ele.data('Html5AjaxForm');
                if (!data) {
                    ele.data('Html5AjaxForm', new Html5AjaxForm(this));
                }
                if (typeof option === 'string') {
                    data[option].call(ele);
                }
            });
        };

        /**
         * Small plugin to serialize forms to json
         * @param option
         * @return {String}
         */
        $.fn.serializeJson = function(option) {
            var $this = $(this);
            var data = $this.serializeArray();
            var out = {};

            // TODO: Nested keys not implemented
            for (var i in data) {
                out[data[i].name] = data[i].value;
            }

            return JSON.stringify(out);
        }

        /*
         * Auto gemeration of ajax forms to write only html in forms
         */
        $(document).on('submit.Html5AjaxForm', selector, Html5AjaxForm.prototype.submitHandler);

        /**
         * Additional focus plugin to focus the first element of a form
         */
        $("form[data-form-focus-first]").each(function (index, element) {
            $($(element).attr("data-form-focus-first")).focus();
        });
    });
})();