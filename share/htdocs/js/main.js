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

/*
 * RequireJS configuration.
 *
 * Change some paths to match distribution format
 */
requirejs.config({
    baseUrl: "/js",
    paths: {
        jquery:     "/js/vendor/jquery-1.8.3.min",
        modernizr:  "/js/vendor/modernizr-2.6.2.min",
        bootstrap:  "/js/bootstrap.min"
    }
});

/**
 * Starting JS stack in the right order
 */
require([
    // -------------------------
    // Boilerplate and bootstrap
    // -------------------------
    "modernizr",
    "jquery",
    "plugins",
    "bootstrap",

    // -------------------------
    // TKMON scripts
    // -------------------------
    "TKMON/jquery/AjaxForm"
]);
