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
 * Model to change postfix configuration
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Postfix extends \NETWAYS\Common\ArrayObject
{

    const TEMP_PREFIX = 'tkmon-postfix';

    /**
     * @var \Pimple
     */
    private $container;

    private $configFile = '/etc/postfix/main.cf';

    private $relayHost;

    public function __construct(\Pimple $container) {
        $this->container = $container;
    }

    private static $mapLines = array(
        'relayhost' => 'RelayHost'
    );

    public function setConfigFile($configFile)
    {
        $this->configFile = $configFile;
    }

    public function getConfigFile()
    {
        return $this->configFile;
    }

    public function setRelayHost($relayHost)
    {
        $this->relayHost = $relayHost;
    }

    public function getRelayHost()
    {
        return $this->relayHost;
    }

    public function load()
    {
        if (!file_exists($this->getConfigFile())) {
            throw new \TKMON\Exception\ModelException('Config file does not exist!');
        }

        $fo = new \SplFileObject($this->getConfigFile(), 'r');

        foreach ($fo as $line) {
            $line = trim($line);
            $match = array();

            if (preg_match('/\s*(\w+)\s*=\s*([^$]+)?$/', $line, $match)>0 && isset(self::$mapLines[$match[1]])) {
                if (isset($match[2])) {
                    $method = 'get'. self::$mapLines[$match[1]];
                    $this->$method($match[2]);
                }
            } else {
                $this[] = $line;
            }
        }
    }

    private function writeSetting(\SplFileObject $fo, $setting, $value)
    {
        $fo->fwrite($setting. ' = '. $value);
    }

    public function write()
    {
        if (!count($this)) {
            throw new \TKMON\Exception\ModelException('Nothing to write, abort!');
        }

        $fo = new \NETWAYS\IO\RealTempFileObject(self::TEMP_PREFIX, 'w');
        foreach ($this as $line) {
            $fo->fwrite($line. PHP_EOL);
        }

        /*
         * Write custom object configuration
         */
        foreach (self::$mapLines as $setting=>$methodSuffix) {
            $method = 'get'. $methodSuffix;
            $this->writeSetting($fo, $setting, $this->$method());
        }


        $fo->fflush();

        /** @var $mv \NETWAYS\IO\Process */
        $mv = $this->container['command']->create('mv');
        $mv->addPositionalArgument($fo->getRealPath());
        $mv->addPositionalArgument($this->getConfigFile());
        $mv->execute();

        unset($fo);
    }
}
