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

namespace NETWAYS\IO;

/**
 * Working with processes
 * @package NETWAYS\IO
 * @author Marius Hein <marius.hein@netways.de>
 */
class Process
{
    const STDIN                 = 0;
    const STDOUT                = 1;
    const STDERR                = 2;
    const READ                  = 'r';
    const WRITE                 = 'w';
    const PIPE                  = 'pipe';
    const FILE                  = 'file';

    const STATUS_COMMAND        = 'command';
    const STATUS_PID            = 'pid';
    const STATUS_RUNNING        = 'running';
    const STATUS_SIGNALED       = 'signaled';
    const STATUS_STOPPED        = 'stopped';
    const STATUS_EXITCODE       = 'exitcode';
    const STATUS_TERMSIG        = 'termsig';
    const STATUS_STOPSIG        = 'termsig';

    const PATH_SUDO             = '/usr/bin/sudo';

    /**
     * Array of input/output descriptors
     * @var array
     */
    private $descriptors = array();

    /**
     * Array of input / output streams
     * @var array
     */
    private $pipes = array();

    /**
     * Environment settings
     * @var array
     */
    private $environment = array();

    /**
     * Named arguments
     * @var array
     */
    private $namedArguments = array();

    /**
     * Positional arguments
     * @var array
     */
    private $positionalArguments = array();

    /**
     * Command to execute
     * @var
     */
    private $command;

    /**
     * Path where the command is executed
     * @var string
     */
    private $workDirectory = '/';

    /**
     * Internal proc source
     * @var resource
     */
    private $resource;

    /**
     * Microseconds on start
     * @var float
     */
    private $runtime;

    /**
     * Status array
     * @var array
     */
    private $processStatus;

    /**
     * Return flag
     * @var int
     */
    private $processReturn;

    /**
     * Output of stdout
     * @var mixed
     */
    private $processOutput;

    /**
     * Data write to STDIN
     * @var string
     */
    private $processInput;

    /**
     * Error stream
     * @var mixed
     */
    private $processError;

    /**
     * Wrap as sudo call
     * @var bool
     */
    private $wrapSudo=false;

    /**
     * Flag to ignore STDERR output
     * @var bool
     */
    private $ignoreStdErr=false;

    /**
     * Flag to ignore return code
     * @var bool
     */
    private $ignoreProcessReturn=false;

    /**
     * Create a new object
     * @param string $command
     */
    public function __construct($command)
    {
        $this->setCommand($command);
        $this->descriptors = $this->getDefaultDescriptor();
    }

    /**
     * Creates a default descriptor
     *
     * @return array
     */
    private function getDefaultDescriptor()
    {
        return array(
            self::STDIN => array(self::PIPE, self::READ),
            self::STDOUT => array(self::PIPE, self::WRITE),
            self::STDERR => array(self::PIPE, self::WRITE),
        );
    }

    /**
     * Allow to change descriptor interface
     * @param int $output Output array constants
     * @param string $type pipe or file
     * @param string $rw flag if you want to read or write
     * @param string $arg for file, add a file name
     */
    public function changeDescriptor($output, $type, $rw, $arg = null)
    {
        $type_arr = array($type, $rw);
        if ($arg) {
            array_splice($type_arr, 1, 0, $arg);
        }

        $this->descriptors[$output] = $type_arr;
    }

    /**
     * Return the current descriptor array
     * @return array
     */
    public function getDescriptors()
    {
        return $this->descriptors;
    }

    /**
     * Setter for command
     * @param string $command
     * @throws Exception\ProcessException
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * Getter for command
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Flag to ignore exceptions if STDERR is written
     *
     * @param bool $flag
     */
    public function ignoreStdErr($flag = true)
    {
        $this->ignoreStdErr = (bool)$flag;
    }

    /**
     * Setter to set flag that ignore process return value
     *
     * @param bool $flag
     */
    public function ignoreProcessReturn($flag = true)
    {
        $this->ignoreProcessReturn = (bool)$flag;
    }

    /**
     * Set execution directory
     * '/' by default
     * @param string $workDirectory
     */
    public function setWorkDirectory($workDirectory)
    {
        $this->workDirectory = $workDirectory;
    }

    /**
     * Getter for work directory
     * @return string
     */
    public function getWorkDirectory()
    {
        return $this->workDirectory;
    }

    /**
     * Adding a named argument
     * @param string $name
     * @param mixed|null $value
     */
    public function addNamedArgument($name, $value = null)
    {
        $this->namedArguments[$name] = $value;
    }

    /**
     * Add a positional argument to list
     * @param mixed $value
     */
    public function addPositionalArgument($value)
    {
        $this->positionalArguments[] = $value;
    }

    /**
     * Resets positional arguments
     */
    public function resetPositionalArguments()
    {
        $this->positionalArguments = array();
    }

    /**
     * Resets named arguments
     */
    public function resetNamedArguments()
    {
        $this->namedArguments = array();
    }

    /**
     * Resets all arguments
     */
    public function resetArguments()
    {
        $this->resetNamedArguments();
        $this->resetPositionalArguments();
    }

    /**
     * Wrap execution call with sudo
     * @param bool $flag
     */
    public function setSudoersFlag($flag = true)
    {
        $this->wrapSudo = $flag;
    }

    /**
     * Return a ready to use environment var
     * @return array|null
     */
    public function getEnvironment()
    {
        if (count($this->environment)) {
            return $this->environment;
        }

        return null;
    }

    /**
     * Setter for environment
     * @param array $environment
     */
    public function setEnvironment(array $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Add environment var
     *
     * @param string $name Name
     * @param mixed $value Value
     */
    public function addEnvironment($name, $value)
    {
        $this->environment[$name] = $value;
    }

    /**
     * Remove environment var
     * @param string $name
     */
    public function removeEnvironment($name)
    {
        if (array_key_exists($name, $this->environment)) {
            unset($this->environment[$name]);
        }
    }

    /**
     * Create language env vars
     *
     * Based on input locale e.g. en_EN.UTF-8
     *
     * @param string $locale
     */
    public function createLangEnvironment($locale)
    {
        $this->addEnvironment('LANGUAGE', $locale);
        $this->addEnvironment('LC_ALL', $locale);
        $this->addEnvironment('LANG', $locale);
    }

    /**
     * Drop all environments
     */
    public function purgeEnvironment()
    {
        unset($this->environment);
        $this->environment = array();
    }

    /**
     * Object global escape method
     * @param string $arg
     * @return string
     */
    public function escapeShellArg($arg)
    {
        $val = escapeshellarg($arg);

        if (!$val) {
            return "''";
        }

        return $val;
    }

    /**
     * Return a string of all named arguments
     * @return string
     */
    public function getNamedArguments()
    {
        $arguments = array();

        foreach ($this->namedArguments as $name => $value) {

            if (!isset($value)) {
                $arguments[] = $name;
            } else {

                $sep = ' ';
                if (strpos($name, '--') === 0) {
                    $sep = '=';
                }

                $arguments[] = $name
                    . $sep
                    . $this->escapeShellArg($value);
            }
        }

        return implode(' ', $arguments);
    }

    /**
     * Clean up method, close all
     * open pipes
     */
    private function closeAllPipes()
    {
        foreach ($this->pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }
        $this->pipes = array();
    }

    /**
     * Resets the command object to call again
     */
    private function resetPreviousCall()
    {
        $this->processStatus = null;
        $this->processReturn = null;
        $this->processOutput = null;
        $this->processError = null;

        if ($this->resource) {
            proc_close($this->resource);
            $this->resource = null;
        }
    }

    /**
     * Return all positional arguments as string
     * @return string
     */
    public function getPositionalArguments()
    {
        $arguments = array();

        foreach ($this->positionalArguments as $value) {
            $arguments[] = $this->escapeShellArg($value);
        }

        return implode(' ', $arguments);
    }

    /**
     * Return the whole command
     * @return string
     */
    public function getExecutionCall()
    {
        $cmd = array();

        $cmd[] = $this->getCommand();
        $cmd[] = $this->getNamedArguments();
        $cmd[] = $this->getPositionalArguments();



        $call = implode(' ', $cmd);

        if ($this->wrapSudo === true) {
            // proc_open does not export environment vars, let
            // sudo do this for you.
            $environmentString = '';
            if (count($this->environment)) {
                foreach ($this->environment as $key => $val) {
                    $environmentString .= $key. '=\''. escapeshellarg($val). '\'';
                }
                $environmentString .= ' ';
            }

            return self::PATH_SUDO. ' '. $environmentString. $call;
        }

        return $call;
    }

    /**
     * Execute the command
     * @return bool
     * @throws Exception\ProcessException
     */
    public function execute()
    {
        $this->resetPreviousCall();

        $this->resource = proc_open(
            $this->getExecutionCall(),
            $this->getDescriptors(),
            $this->pipes,
            $this->getWorkDirectory(),
            $this->getEnvironment()
        );

        $start = microtime(true);

        if ($this->processInput) {
            fwrite($this->pipes[self::STDIN], $this->processInput);
            fclose($this->pipes[self::STDIN]);
        }

        $this->processOutput = stream_get_contents($this->pipes[self::STDOUT]);

        $this->processError = stream_get_contents($this->pipes[self::STDERR]);

        $this->closeAllPipes();

        $this->processStatus = proc_get_status($this->resource);

        $this->processReturn = $this->getStatusItem(self::STATUS_EXITCODE);

        $this->runtime = microtime(true) - $start;

        // Data is ready, we can throw exceptions

        if ($this->processError && $this->ignoreStdErr === false) {
            throw new Exception\ProcessException('STDERR: '. $this->processError);
        }

        if ($this->processReturn > 0 && $this->ignoreProcessReturn === false) {
            throw new Exception\ProcessException(
                'Process (' . $this->getExecutionCall(). ') exited with '. $this->processReturn .
                (($this->processError) ? '. STDERR: ' . $this->processError :
                    '(NOSTDERR)')
            );
        }

        return true;
    }

    /**
     * Return output of STDOUT
     * @return mixed
     */
    public function getOutput()
    {
        return $this->processOutput;
    }

    /**
     * Return STDERR if not thrown
     * @return mixed
     */
    public function getProcessError()
    {
        return $this->processError;
    }

    /**
     * Return exit status
     * @return int
     */
    public function getExitStatus()
    {
        return $this->processReturn;
    }

    /**
     * Set stdin
     * @param string $input
     */
    public function setInput($input)
    {
        $this->processInput = $input;
    }

    /**
     * Return the current process status
     *
     * See http://de2.php.net/manual/en/function.proc-get-status.php
     *
     * @return array
     */
    public function getStatus()
    {
        return $this->processStatus;
    }

    /**
     * Return specific status item
     *
     * See http://de2.php.net/manual/en/function.proc-get-status.php
     *
     * @param string $type
     * @return mixed
     */
    public function getStatusItem($type)
    {
        return $this->processStatus[$type];
    }

    /**
     * Runtime
     * @return float
     */
    public function getRuntime()
    {
        return $this->runtime;
    }
}
