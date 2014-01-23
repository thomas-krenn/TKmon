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

require(['jquery'], function ($) {
    "use strict";

    /**
     * @name clearField
     * @memberOf jQuery.fn
     * @class
     *
     *
     */
    $.fn.clearField = function() {
        $(this).each(function(i, ele) {
            var target = $(ele).attr('data-clear-field');
            if (target) {
                target = String(target).replace(/^#/, "");
                var targetField = $('#' + target);
                if (targetField) {
                    $(ele).click(function() {
                        $(targetField).val("");
                    });
                }
            }
        });
    };

    $('*[data-clear-field]').clearField();
});