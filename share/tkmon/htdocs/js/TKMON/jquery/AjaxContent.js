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

(function () {
    "use strict";

    require(['jquery'], function ($) {

        $.fn.Html5AjaxContent = function(option) {
            if (option === "insert") {
                $(this).each(function(index, element) {
                    $(element).find('*').remove();
                });
            }

            $(this).each(function(index, element) {
                var url = $(element).attr('data-ajax-call');

                if (option === 'initial' && $(element).attr('data-ajax-call-disable-autoload') === 'true') {
                    return;
                }

                if (url) {
                    $.ajax({
                        url: url
                    }).done(function(data) {
                            if ($(element).attr('data-ajax-call-insert')) {
                                $(element).html('');
                            }
                            $(element).append(data);
                        });
                }
            });
        };

        $("*[data-ajax-call]").Html5AjaxContent('initial');

    });
})();