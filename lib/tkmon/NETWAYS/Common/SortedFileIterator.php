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

namespace NETWAYS\Common;

/**
 * Sorted heap list of files
 *
 * Idea borrowed from Stackoverflow:
 *
 * - http://stackoverflow.com/questions/2930405/sort-directory-listing-using-recursivedirectoryiterator
 *
 * @package NETWAYS\Common
 * @author Marius Hein <marius.hein@netways.de>
 */
class SortedFileIterator extends \SplHeap
{

    /**
     * Creates a new iterable heap list
     * @param \Iterator $iterator
     */
    public function __construct(\Iterator $iterator)
    {
        foreach ($iterator as $value) {
            $this->insert($value);
        }
    }

    /**
     * Compare of filenames
     *
     * Using string compare method to sort files
     * @param \SplFileInfo $value1
     * @param \SplFileInfo $value2
     * @return int
     */
    public function compare($value1, $value2)
    {
        return strcmp($value2->getRealPath(), $value1->getRealPath());
    }
}
