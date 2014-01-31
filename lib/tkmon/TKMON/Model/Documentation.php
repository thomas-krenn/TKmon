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

namespace TKMON\Model;


use TKMON\Exception\ModelException;
use TKMON\Mvc\Output\TwigTemplate;

/**
 * Load documentation out of twig templates
 *
 * @package TKMON\Model
 */
class Documentation extends ApplicationModel
{
    /**
     * Name of the default locale
     *
     * @var string
     */
    const DEFAULT_LOCALE = 'en';

    /**
     * Name of documentation snip to include
     *
     * @var string
     */
    const DEFAULT_FILE_NAME = 'inline.twig';

    /**
     * Base path
     *
     * @var string
     */
    private $basePath;

    /**
     * Locale name
     *
     * @var string
     */
    private $locale;

    /**
     * Identifier of documentation
     *
     * @var string
     */
    private $identifier;

    /**
     * Setter for base path
     *
     * @param string $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Getter for base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Setter for identifier
     *
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $identifier = str_replace($this->container['config']['web.path'], '', $identifier);
        $identifier = trim($identifier, '/');
        $this->identifier = $identifier;
    }

    /**
     * Getter for identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Setter for locale
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        if (strpos($locale, '_') !== false) {
            $locale = substr($locale, 0, strpos($locale, '_'));
        }
        $this->locale = $locale;
    }

    /**
     * Getter for locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Return a html snippet if found
     *
     * @return string
     */
    public function getDocumentation()
    {
        $file = $this->findFile();
        if ($file) {
            $file = str_replace($this->container['config']->get('core.template_dir'), '', $file);
            $twigOutput = new TwigTemplate($this->container['template'], $file);
            $twigOutput['user'] = $this->container['user'];
            return (string)$twigOutput;
        }

        return null;
    }

    /**
     * Return a file name if found
     *
     * @throws  \TKMON\Exception\ModelException
     * @return  string
     *
     */
    public function findFile()
    {
        if ($this->locale === null) {
            throw new ModelException('Parameter locale is missing');
        }

        if ($this->identifier === null) {
            throw new ModelException('Parameter identifier is missing');
        }

        if ($this->basePath === null) {
            throw new ModelException('Parameter basePath is missing');
        }

        $localeArray = array_unique(array($this->getLocale(), self::DEFAULT_LOCALE));

        foreach ($localeArray as $locale) {
            $file = $this->getBasePath()
                . '/' . $this->getIdentifier()
                . '/' . $locale
                . '/' . self::DEFAULT_FILE_NAME;

            if (file_exists($file)) {
                return $file;
            }
        }

        return null;
    }
}
