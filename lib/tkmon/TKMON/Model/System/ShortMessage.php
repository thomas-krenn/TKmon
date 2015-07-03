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

namespace TKMON\Model\System;

use NETWAYS\Common\Config;
use TKMON\Model\ApplicationModel;

/**
 * Model to fetch or control sms alert status
 */
class ShortMessage extends ApplicationModel
{
    /**
     * Flag name in configuration stack
     *
     * @var string
     */
    const ENABLED_FLAG = 'sms.enabled';

    /**
     * Return true if alert feature is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        /** @var Config $config */
        $config = $this->container['config'];
        $status = (boolean)$config->get(self::ENABLED_FLAG, false);
        return $status;
    }

    /**
     * Enable or disable alert feature
     * @param bool $flag
     */
    public function enable($flag = true)
    {
        /** @var Config $config */
        $config = $this->container['config'];
        $config->set(self::ENABLED_FLAG, (boolean) $flag);
    }
}