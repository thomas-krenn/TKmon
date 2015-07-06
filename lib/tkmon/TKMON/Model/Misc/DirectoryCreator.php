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
 * @copyright 2012-2015 NETWAYS GmbH <info@netways.de>
 */

namespace TKMON\Model\Misc;

/**
 * Model to create environment
 *
 * Creating missing paths for templates and session storage
 *
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class DirectoryCreator
{
    /**
     * Array of paths to create
     * @var string[]
     */
    private $paths = array();

    /**
     * Add path to stack
     * @param string $path
     */
    public function addPath($path)
    {
        $this->paths[] = $path;
    }

    /**
     * Remove path from stack
     * @param string $path
     * @return bool removed or not
     */
    public function removePath($path)
    {
        if (($index = array_search($path, $this->paths)) !== false) {
            unset($this->paths[$index]);
            return true;
        }

        return false;
    }

    /**
     * Tests if path is on stack
     * @param string $path
     * @return bool
     */
    public function hasPath($path)
    {
        return in_array($path, $this->paths);
    }

    /**
     * Drop the whole stack of paths
     */
    public function purgePaths()
    {
        unset($this->paths);
        $this->paths = array();
    }

    /**
     * Create single path
     * @param string $path
     * @return bool
     */
    public function createPath($path)
    {
        if (is_dir($path)) {
            return true;
        }

        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $depth = '';
        foreach ($parts as $part) {
            if (!$part) {
                continue;
            } else {
                $depth .= DIRECTORY_SEPARATOR. $part;
            }

            if (is_dir($depth)) {
                continue;
            } else {
                if (@mkdir($depth) === false) {
                    throw new \TKMON\Exception\ModelException('Error while creating directory: '. $depth);
                }

            }
        }

        return true;
    }

    /**
     * Create all paths on stack
     * @throws \TKMON\Exception\ModelException
     */
    public function createPaths()
    {
        if (count($this->paths) <= 0) {
            throw new \TKMON\Exception\ModelException('No directories to create');
        }

        foreach ($this->paths as $path) {
            $this->createPath($path);
        }
    }
}
