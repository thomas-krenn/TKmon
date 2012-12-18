<?php
/*
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
 */

namespace TKMON\Mvc\Output;

/**
 * Class to generate simple json output from action classes
 * @package TKMON/Mvc/Output
 * @author Marius Hein <marius.hein@netways.de>
 */
class Json extends \NETWAYS\Common\ArrayObject implements DataInterface
{

    public function getData()
    {
        return $this->getArrayCopy();
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString()
    {
        return json_encode((array)$this);
    }

}
