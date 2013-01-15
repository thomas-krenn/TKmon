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
 * This a collection of common actions
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Simple extends \TKMON\Model\ApplicationModel
{
    /**
     * Maximum length of line
     */
    const MAX_LINE = 70;

    /**
     * @var
     */
    private $to;

    /**
     * @var
     */
    private $content;

    /**
     * @var
     */
    private $sender;

    /**
     * @var
     */
    private $subject;

    /**
     * @var array
     */
    private $headers = array();

    /**
     * @var array
     */
    private $options = array();

    /**
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        parent::__construct($container);

        $this->addHeader('x-mailer', $container['config']['app.version.release']);
    }

    /**
     *
     */
    public function resetState()
    {
        $this->recipients = null;
        $this->to = null;
        $this->content = null;
        $this->sender = null;

        unset($this->headers);
        $this->headers = array();
    }

    /**
     * @param $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return wordwrap($this->content, 70, "\r\n");
    }

    /**
     * @param $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param $sender
     */
    public function setSender($sender)
    {
        $this->options['-f'] = $sender;
        $this->addHeader('from', $sender);
        $this->addHeader('reply-to', $sender);
        $this->sender = $sender;
    }

    /**
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param $name
     * @return string
     */
    private function normalizeHeaderName($name) {
        return strtolower($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function addHeader($name, $value)
    {
        $this->headers[$this->normalizeHeaderName($name)] = $value;
    }

    /**
     *
     */
    public function purgeHeaders()
    {
        unset($this->headers);
        $this->headers = array();
    }

    /**
     * @param $name
     */
    public function removeHeader($name)
    {
        $name = $this->normalizeHeaderName($name);
        if (isset($this->headers[$name])) {
            unset($this->headers[$name]);
        }
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param $name
     * @return string
     */
    private function sanitizeHeaderName($name)
    {
        $parts = explode('-', $name);
        $parts = array_map('ucfirst', $parts);
        return implode('-', $parts);
    }

    /**
     * @return string
     */
    public function getHeaderAsString()
    {
        $out = array();
        foreach ($this->headers as $name=>$value) {
            $out[] = $this->sanitizeHeaderName($name) . ': '. $value;
        }
        return implode("\r\n", $out);
    }

    /**
     * @return string
     */
    public function getOptionsAsString()
    {
        $out = array();
        foreach ($this->options as $switch=>$value) {
            $out[] = $switch. ' '. escapeshellarg($value);
        }
        return implode(' ', $out);
    }

    /**
     *
     */
    public function sendMail()
    {
        mail(
            $this->getTo(),
            $this->getSubject(),
            $this->getContent(),
            $this->getHeaderAsString(),
            $this->getOptionsAsString()
        );
    }
}
