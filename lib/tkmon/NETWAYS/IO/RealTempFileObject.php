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

namespace NETWAYS\IO;

/**
 * Temp file in filesystem.
 * @package NETWAYS\IO
 * @author Marius Hein <marius.hein@netways.de>
 */
class RealTempFileObject extends FileObject
{
    /**
     * Creates a new object
     *
     * @link http://php.net/manual/en/splfileobject.construct.php
     * @param string $prefix Prefix of the temp file
     * @param string $open_mode [optional]
     * @param bool $use_include_path [optional]
     * @param resource $context [optional]
     */
    public function __construct($prefix, $open_mode = 'r', $use_include_path = false, $context = null)
    {

        $fileName = tempnam(sys_get_temp_dir(), $prefix);

        /*
         * Hack we have to decide what we want to do, $content
         * must not be null
         */
        if ($context === null) {
            parent::__construct(
                $fileName,
                $open_mode,
                $use_include_path
            );
        } else {
            parent::__construct(
                $fileName,
                $open_mode,
                $use_include_path,
                $context
            );
        }
    }

    /**
     * Delete the file before destruct object
     */
    public function __destruct()
    {
        $fname = $this->getRealPath();
        /*
         * Switched from is_file to file_exists, output from
         * is_file is cached and not the real situation on disk
         */
        if (file_exists($fname)) {
            $this->fflush();
            unlink($fname);
        }
    }
}
