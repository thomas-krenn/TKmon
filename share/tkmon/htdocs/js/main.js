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

// --- DEFAULT DOC SNIP FOR JQUERY PLUGINS [BEGIN] --

/**
 * See (http://jquery.com/).
 * @name jQuery
 * @class
 * See the jQuery Library  (http://jquery.com/) for full details.  This just
 * documents the function and classes that are added to jQuery by this plug-in.
 */

/**
 * See (http://jquery.com/)
 * @name fn
 * @class
 * See the jQuery Library  (http://jquery.com/) for full details.  This just
 * documents the function and classes that are added to jQuery by this plug-in.
 * @memberOf jQuery
 */

// --- DEFAULT DOC SNIP FOR JQUERY PLUGINS [END] --

/**
 * Starting JS stack in the right order
 */
require([
    // -------------------------
    // Boilerplate and bootstrap
    // -------------------------
    "modernizr",
    "plugins",

    // -------------------------
    // TKMON scripts
    // -------------------------
    "TKMON/jquery/AjaxForm",
    "TKMON/jquery/AjaxContent",
    "TKMON/jquery/TabUrl",
    "TKMON/jquery/ErrorPopover",
    "TKMON/jquery/ClearField"
]);
