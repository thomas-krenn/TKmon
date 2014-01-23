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
 * @copyright 2012-2014 NETWAYS GmbH <info@netways.de>
 */
/*global require:true*/
(function() {
    "use strict";

    require(['jquery', 'bootstrap'], function ($) {

        /**
         * Create the html stub with the controls
         * @param {Object} options
         * @returns {string}
         */
        var createHtml = function(options) {
            var out = "";

            out += '<span class="timer-info">' + options.initialLabel + '</span>';
            out += '<span class="timer-pane badge badge-success" data-target="timer"></span>';
            out += '<span class="timer-action" data-target="action">' +
                '<i class="icon-off"></i> ' + options.stopLabel + '</span>';

            return out;
        };

        /**
         * Current count value
         * @type {number}
         */
        var timerValue = 0;

        /**
         * Flag indicates if the timer is running
         * @type {boolean}
         */
        var timerRun = false;

        /**
         * Label for seconds, e.g. 'seconds' ;-)
         * @type {string}
         */
        var secondsLabel = '';

        /**
         * The timer itself
         *
         * Runs always and is controlled by flag. Count
         * down the timerValue if 0 triggers reload
         */
        var timer = function() {
            if (timerRun) {
                if (timerValue>0) {
                    updateBadge(--timerValue);
                } else {
                    refreshCurrentURL();
                    suspendTimer();
                }
            }

            window.setTimeout(timer, 1000); // RECALL
        };

        /**
         * Current jquery element
         * @type {Element}
         */
        var currentInstance = null;

        /**
         * Updates the counter pane how many seconds are left
         * @param {Number} value
         */
        var updateBadge = function(value) {
            $(currentInstance).find('span[data-target=timer]').html(value + ' ' + secondsLabel);
        };

        /**
         * Magic magic, refresh the current browser URL
         */
        var refreshCurrentURL = function() {
            window.location.href = window.location.href;
        };

        /**
         * Control method
         *
         * Start the timer and sets initial count value if given
         *
         * @param {Number} initialValue
         */
        var startTimer = function(initialValue) {
            timerRun = true;
            if (initialValue && timerValue < 1) {
                timerValue = initialValue;
            }
        };

        /**
         * Suspends the timer
         *
         * Reactivate with startTimer();
         */
        var suspendTimer = function() {
            timerRun = false;
        };

        /**
         * Toggle control
         * Installed on the control element. Changes the icons and
         * starts and stops the timer
         */
        var toggleHandler = function() {
            if (timerRun === true) {
                $(currentInstance).find('span[data-target=action] i').removeClass('icon-off').addClass('icon-ok');
                suspendTimer();
            } else {
                $(currentInstance).find('span[data-target=action] i').addClass('icon-off').removeClass('icon-ok');
                startTimer();
            }
        };

        /**
         * jQuery Plugin
         *
         * For all this stuff above
         *
         * @param {Object} options
         */
        $.fn.siteRefreshTimer = function(options) {
            options = typeof options === "object" ? options : {};
            options = $.extend({
                timeout: 20,
                secondsLabel: 'seconds',
                stopLabel: 'Stop',
                initialLabel: 'Reload page in'
            }, options);

            currentInstance = $(this);
            secondsLabel = options.secondsLabel;

            $(this).addClass('jquery-refresh-timer');
            $(this).html(createHtml(options));

            $(this).find('span[data-target=action]').click(toggleHandler);

            /**
             * Explicit run of the timer
             */
            timer();

            updateBadge(options.timeout);
            startTimer(options.timeout);
        };
    });

})();
