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

/**
 * Gettext initialisation class to start the gettext translator
 *
 * @package NETWAYS\Intl
 * @author Marius Hein <marius.hein@netways.de>
 */
class Gettext
{
    /**
     * Language domains
     * @var array
     */
    private $domains = array();

    /**
     * Encoding
     * @var string
     */
    private $encoding = 'UTF-8';

    /**
     * Current locale
     * @var string
     */
    private $locale;

    /**
     * Setter for encoding
     * @param string $encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * Getter for encoding
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Add a translation domain
     * @param string $domain
     * @param string $path
     * @throws \NETWAYS\Common\Exception
     */
    public function addDomain($domain, $path)
    {

        if (!is_dir($path)) {
            throw new \NETWAYS\Common\Exception('Locale directory does not exist: '. $path);
        }

        if (array_key_exists($domain, $this->domains)) {
            throw new \NETWAYS\Common\Exception('Domain already exist: '. $domain);
        }

        $this->domains[$domain] = $path;

        bindtextdomain($domain, $path);
        bind_textdomain_codeset($domain, $this->getEncoding());
    }

    /**
     * Set default domain
     * @param string $domain
     * @throws \NETWAYS\Common\Exception
     */
    public function setDefaultDomain($domain)
    {
        if (!array_key_exists($domain, $this->domains)) {
            throw new \NETWAYS\Common\Exception('Domain not configured: '. $domain);
        }

        textdomain($domain);
    }

    /**
     * Set current locale
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        $setTag = $locale. '.'. $this->getEncoding();

        putenv('LANG='. $setTag);
        putenv('LANGUAGE='. $setTag);

        setlocale(LC_MESSAGES, $setTag);
    }

    /**
     * Get current locale
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
