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

    require(['jquery', 'bootstrap'], function ($) {

        var createErrorsHtml = function(struct) {
            var html = '';

            html = '<div>';

            $.each(struct, function(index, obj) {
                html += '<div>' + obj.message + '</div>';
            });

            html += '</div>';

            return html;
        };

        $.fn.errorPopover = function(data) {

            var that = $(this);

            $(that).popover({
                title: 'Kaboom!',
                html: true,
                content: createErrorsHtml(data.errors),
                trigger: 'manual',
                animation: true
            });

            $(that).popover('show');

            window.setInterval(function() {
                $(that).popover('destroy');
            }, 5000);
        };

    });
})();