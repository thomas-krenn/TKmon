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

namespace TKMON\Model\Mail;

/**
 * Model to get the sender address for mails
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Sender extends \TKMON\Model\ApplicationModel
{

    /**
     * Config namespace for the sender
     */
    const SENDER_NAMESPACE = 'mail.sender';

    /**
     * Mail address prefix if not configured
     */
    const DEFAULT_MAIL_PREFIX = 'icinga';
    /**
     * Mail address suffix if not configured
     */
    const DEFAULT_MAIL_SUFFIX = 'tkmon-unconfigured.local';

    /**
     * Sender address
     * @var string
     */
    private $sender;

    /**
     * Create a new model
     *
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        parent::__construct($container);

        $this->setSender(
            $this->determineSender()
        );
    }

    /**
     * Detect sender address
     *
     * Tries to detect the sender address:
     * - From config
     * - From hostmodel with default prefix
     * - Default address
     *
     * @return mixed|null|string
     */
    private function determineSender()
    {
        $sender = null;

        /*
         * If proper configured
         */

        /** @var $config \NETWAYS\Common\Config */
        $config = $this->container['config'];
        $sender = $config->get(self::SENDER_NAMESPACE);

        /*
         * Try to use the hostname
         */
        if ($sender === null) {
            $hostname = new \TKMON\Model\System\Hostname($this->container);
            $hostname->load();
            if ($hostname->getCombined()) {
                $sender = self::DEFAULT_MAIL_PREFIX. '@'. $hostname->getCombined();
            }
        }

        /*
         * Okay, default
         */
        if ($sender === null) {
            $sender = self::DEFAULT_MAIL_PREFIX. '@'. self::DEFAULT_MAIL_SUFFIX;
        }

        return $sender;
    }

    /**
     * Setter to overwrite the value
     * @param $sender
     */
    public function setSender($sender)
    {
        $this->container['config']->set(self::SENDER_NAMESPACE, $sender);
        $this->sender = $sender;
    }

    /**
     * Getter for the value
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }
}
