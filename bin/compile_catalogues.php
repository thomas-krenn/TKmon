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
// Compile all PO catalogues found to binaries (mo)
// ----------------------------------------------------------------------------

$ds = DIRECTORY_SEPARATOR;
$dir = dirname(__dir__);

$localesDir = $dir. $ds. 'share'. $ds. 'tkmon'. $ds. 'locales';

$baseCatalogue = $localesDir. $ds. 'messages.pot';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($localesDir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $file) {
    if (strpos($file, '.po') === strlen($file)-3) {
        $moFile = substr($file, 0, strlen($file)-3). '.mo';

        $short = basename($file);

        if (file_exists($moFile)) {
            echo "Binary exists, unlink\n";
            unlink($moFile);
        }

        echo "Compile $short to binary ... ";

        exec(
            '/usr/bin/msgfmt'
            . ' -o '. $moFile
            . ' '. $file
        );

        echo "done\n";
    }
}
