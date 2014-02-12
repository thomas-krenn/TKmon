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
 * @copyright 2012-2014 NETWAYS GmbH <info@netways.de>
 */

namespace TKMON\Web\Scoped;

/**
 * Scoped runner for index.php
 *
 * This is needed to run PHP code always within a scoped environment
 */
final class ASimpleDummyScopedRunnerForTKMon
{
    /**
     * Filename of composer autoload.php file
     *
     * @var string
     */
    const AUTOLOAD_FILENAME = 'autoload.php';

    /**
     * Directory name of composer library code
     */
    const VENDOR_DIRECTORY = 'vendor';

    /**
     * Create a filename maybe we can load
     *
     * You can use multiple arguments as directory parts
     *
     * @param string $arg1 directory part
     * @return string
     */
    private static function alFile($arg1)
    {
        return implode(DIRECTORY_SEPARATOR, func_get_args()). DIRECTORY_SEPARATOR. self::AUTOLOAD_FILENAME;
    }

    /**
     * Dispatches web request
     *
     * Tries to load autoload.php from composer from different locations
     */
    public static function doTheWebDance()
    {

        $path = __DIR__;
        $paths = array();

        /*
         * Add thigs like /usr/lib/tkmon/vendor/autoload.php to stack
         * Default packaged path environment
         */
        $paths[] = DIRECTORY_SEPARATOR. self::alFile('usr', 'lib', 'tkmon', self::VENDOR_DIRECTORY);
        $paths[] = DIRECTORY_SEPARATOR. self::alFile('usr', 'lib', 'tkmon');

        while ($path) {
            $path = dirname($path);

            if ($path === DIRECTORY_SEPARATOR) {
                $path = '';
            }

            $paths[] = self::alFile($path, 'lib', 'tkmon', self::VENDOR_DIRECTORY);
            $paths[] = self::alFile($path, 'lib', self::VENDOR_DIRECTORY);
            $paths[] = self::alFile($path, self::VENDOR_DIRECTORY);
        }

        $found = false;

        foreach ($paths as $alFile) {
            if (file_exists($alFile)) {
                require $alFile;
                $found = true;
                break;
            }
        }

        // Load or die
        if ($found === true) {
            \TKMON\Binary\Web::run();
        } else {
            die ('Could not load autoloader, tried: '. implode(', ', $paths));
        }

        return 0;
    }
}

exit(ASimpleDummyScopedRunnerForTKMon::doTheWebDance()); // Call dummy class
