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

namespace TKMON\Model\Apache;

/**
 * Model to write password files
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class DirectoryAccess extends \TKMON\Model\ApplicationModel
{

    /**
     * Temp prefix
     */
    const TEMP_PREFIX = 'tkmon-apache-access';

    /**
     * Security setting Order Allow,Deny
     */
    const ORDER_ALLOW_DENY = 'Allow,Deny';

    /**
     * Security setting Order Deny,Allow
     */
    const ORDER_DENY_ALLOW = 'Deny,Allow';

    /**
     * All value for restriction blocks
     */
    const FROM_ALL = 'all';

    /**
     * Restriction for localhost
     */
    const FROM_LOCALHOST = '127.0.0.0/255.0.0.0 ::1/128';

    /**
     * Remove restriction, just blank null
     */
    const FROM_NULL = null;

    /**
     * Access ordering
     * @var string
     */
    private $order;

    /**
     * Access restriction
     * @var string
     */
    private $allowFrom;

    /**
     * Deny block
     * @var string
     */
    private $denyFrom;

    /**
     * File to parse / rewrite
     * @var string
     */
    private $file = '/etc/icinga/apache2.conf';

    /**
     * Array of data lines
     * @var array
     */
    private $lines = array();

    /**
     * Creates a new object
     *
     * Configure to allowAll setting
     *
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        parent::__construct($container);

        // Default config
        $this->allowAll();

        /** @var $config \NETWAYS\Common\Config */
        $config = $this->container['config'];

        if (($file = $config->get('icinga.apacheconfig', null)) !== null) {
            $this->setFile($file);
        }
    }

    /**
     * Setter for from network
     * @param string $from
     */
    public function setAllowFrom($from)
    {
        $this->allowFrom = $from;
    }

    /**
     * Getter for from
     * @return string
     */
    public function getAllowFrom()
    {
        return $this->allowFrom;
    }

    /**
     * Setter deny block
     * @param string $denyFrom
     */
    public function setDenyFrom($denyFrom)
    {
        $this->denyFrom = $denyFrom;
    }

    /**
     * Getter deny block
     * @return string
     */
    public function getDenyFrom()
    {
        return $this->denyFrom;
    }

    /**
     * Setter for access order
     * @param string $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Getter for access order
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Modify access to localhost only
     */
    public function allowLocalhostOnly()
    {
        $this->setAllowFrom(self::FROM_LOCALHOST);
        $this->setDenyFrom(self::FROM_ALL);
        $this->setOrder(self::ORDER_DENY_ALLOW);
    }

    /**
     * Modify access to allow everybody
     */
    public function allowAll()
    {
        $this->setAllowFrom('all');
        $this->setDenyFrom(self::FROM_NULL);
        $this->setOrder(self::ORDER_ALLOW_DENY);
    }

    /**
     * Test if the directory is world readable
     * @return bool
     */
    public function publicAccess()
    {
        $order = strtolower($this->getOrder());
        $from = strtolower($this->getAllowFrom());
        $deny = $this->getDenyFrom();

        if (preg_match('/allow,\s*deny/', $order) && $from === 'all' && $deny === null) {
            return true;
        }

        return false;
    }

    /**
     * Setter for file to rewrite
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Setter for file
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Load
     *
     * Load data into object and change access policy
     */
    public function load()
    {
        if (!file_exists($this->getFile())) {
            throw new \TKMON\Exception\ModelException('Config file does not exist');
        }

        $this->lines = array();

        $fo = new \NETWAYS\IO\FileObject($this->getFile(), 'r');
        $block = false;
        $match = array();
        foreach ($fo as $line) {
            if (strpos(strtolower($line), '<directory') !== false) {
                $block = true;
            } elseif ($block === true && strpos(strtolower($line), '</directory') !== false) {
                $block = false;
            } elseif ($block === true && preg_match('/(Order|Allow from|Deny from)\s([^$]+)$/i', $line, $match)) {

                $type = strtolower($match[1]);
                $values = trim($match[2]);

                if ($type === 'order') {
                    $this->setOrder($values);
                } elseif ($type === 'allow from') {
                    $this->setAllowFrom($values);
                } elseif ($type === 'deny from') {
                    $this->setDenyFrom($values);
                }

                $line = '';
            }

            if ($line) {
                $this->lines[] = $line;
            }
        }

        unset($fo);
    }

    /**
     * Write
     *
     * Write to temp and move to original location
     */
    public function write()
    {

        if (!file_exists($this->getFile())) {
            throw new \TKMON\Exception\ModelException('Config file does not exist');
        }

        if (!count($this->lines)) {
            throw new \TKMON\Exception\ModelException('No data loaded before');
        }

        $fo = new \NETWAYS\IO\RealTempFileObject(self::TEMP_PREFIX, 'w');
        $block = false;

        foreach ($this->lines as $line) {
            if ($block === false && strpos(strtolower($line), '<directory') !== false) {
                $block = true;
            } elseif ($block === true && strpos(strtolower($line), '</directory') !== false) {

                if ($this->getOrder()) {
                    $fo->fwrite(chr(9). 'Order '. $this->getOrder(). PHP_EOL);
                }

                if ($this->getAllowFrom()) {
                    $fo->fwrite(chr(9). 'Allow From '. $this->getAllowFrom(). PHP_EOL);
                }

                if ($this->getDenyFrom()) {
                    $fo->fwrite(chr(9). 'Deny From '. $this->getDenyFrom(). PHP_EOL);
                }

                $block = false;
            }

            $fo->fwrite($line);
        }

        $fo->fflush();

        /** @var $move \NETWAYS\IO\Process */
        $move = $this->container['command']->create('mv');
        $move->addPositionalArgument($fo->getRealPath());
        $move->addPositionalArgument($this->getFile());
        $move->execute();

        unset($fo);
    }
}
