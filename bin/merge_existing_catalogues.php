<?php
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

// ----------------------------------------------------------------------------
// Extend existing PO with new data from default catalogue (default.pot)
// ----------------------------------------------------------------------------

$ds = DIRECTORY_SEPARATOR;
$dir = dirname(__dir__);

$localesDir = $dir. $ds. 'share'. $ds. 'tkmon'. $ds. 'locales';

$baseCatalogue = $localesDir. $ds. 'messages.pot';

exec('find '. $localesDir. ' -mindepth 1 -name *.po -exec msgmerge -v -U {} '. $baseCatalogue. ' \\;');
exec('find '. $localesDir. ' -mindepth 1 -name *.po~ -exec rm -v {} \\;');
