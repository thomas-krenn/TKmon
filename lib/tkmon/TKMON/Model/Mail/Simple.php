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

namespace TKMON\Model\Mail;

/**
 * Model to send mails with PHP
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
     * To address
     * @var string
     */
    private $to;

    /**
     * Body content
     * @var string
     */
    private $content;

    /**
     * Sender address
     * @var string
     */
    private $sender;

    /**
     * Mail subject
     * @var string
     */
    private $subject;

    /**
     * Array of additional headers
     * @var array
     */
    private $headers = array();

    /**
     * Options passed to sendmail
     * @var array
     */
    private $options = array();

    /**
     * Create a new object
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        parent::__construct($container);

        $this->addHeader('x-mailer', $container['config']['app.version.release']);
    }

    /**
     * Reset the object to unconfigured state
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
     * Setter for mail content
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Getter for mail content
     *
     * Prepare the content with RFC like wordwrap
     *
     * @return string
     */
    public function getContent()
    {
        return wordwrap($this->content, 70, "\r\n");
    }

    /**
     * Setter for subject
     * @param $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Getter for subject
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Setter for to address
     * @param $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * Getter for to address
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Setter for sender
     *
     * Also sets header and sendmail options for envelope header
     *
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
     * Getter for sender
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Normalizer header
     * @param $name
     * @return string
     */
    private function normalizeHeaderName($name)
    {
        return strtolower($name);
    }

    /**
     * Add header to message
     * @param string $name
     * @param mixed $value
     */
    public function addHeader($name, $value)
    {
        $this->headers[$this->normalizeHeaderName($name)] = $value;
    }

    /**
     * Remove all headers
     */
    public function purgeHeaders()
    {
        unset($this->headers);
        $this->headers = array();
    }

    /**
     * Remove single item from headers
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
     * Getter for all headers
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Fix header name to match RFC
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
     * Return headers as string
     * @return string
     */
    public function getHeaderAsString()
    {
        $out = array();
        foreach ($this->headers as $name => $value) {
            $out[] = $this->sanitizeHeaderName($name) . ': '. $value;
        }
        return implode("\r\n", $out);
    }

    /**
     * Return sendmail options
     * @return string
     */
    public function getOptionsAsString()
    {
        $out = array();
        foreach ($this->options as $switch => $value) {
            $out[] = $switch. ' '. escapeshellarg($value);
        }
        return implode(' ', $out);
    }

    /**
     * Send the mail to the air
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
