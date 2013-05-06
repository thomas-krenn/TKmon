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

namespace NETWAYS\Intl;

use NETWAYS\Intl\Exception\SimpleTranslatorException;

/**
 * Class SimpleTranslator
 * @package NETWAYS\Intl
 * @author Marius Hein <marius.hein@netways.de>
 */
class SimpleTranslator
{
    /**
     * Default class locale
     * @var string
     */
    const DEFAULT_LOCALE = 'en_US';

    /**
     * @var string
     */
    private $defaultLocale = null;

    /**
     * Translate locale
     * @var string
     */
    private $locale = null;

    /**
     * Create new translator instance
     * @param string $locale
     * @param string $defaultLocale
     */
    public function __construct($locale = null, $defaultLocale = null)
    {
        if ($locale) {
            $this->setLocale($locale);
        }

        if ($defaultLocale !== null) {
            $this->setDefaultLocale($defaultLocale);
        } else {
            $this->setDefaultLocale(self::DEFAULT_LOCALE);
        }
    }

    /**
     * Setter for default locale
     * @param string $defaultLocale
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Getter for locale
     * @return null|string
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Try to translate struct into locale
     * @param \stdClass $textStruct
     * @return string
     * @throws Exception\SimpleTranslatorException
     */
    public function translate(\stdClass $textStruct)
    {
        if (!$this->getDefaultLocale()) {
            throw new SimpleTranslatorException('Default locale not defined');
        }

        if ($this->getLocale() !== null && isset($textStruct->{$this->getLocale()})) {
            return $textStruct->{$this->getLocale()};
        } elseif (isset($textStruct->{$this->getDefaultLocale()})) {
            return $textStruct->{$this->getDefaultLocale()};
        } else {
            throw new SimpleTranslatorException(
                'Locale not found: '
                . $this->getLocale()
                . ', '
                . $this->getDefaultLocale()
            );
        }
    }
}
