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

    public function setWorkDirectory($workDirectory)
    {
        $this->workDirectory = $workDirectory;
    }

    public function getWorkDirectory()
    {
        return $this->workDirectory;
    }

    public function addNamedArgument($name, $value = null)
    {
        $this->namedArguments[$name] = $value;
    }

    public function addPositionalArgument($value)
    {
        $this->positionalArguments[] = $value;
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

    public function escapeShellArg($arg)
    {
        $val = escapeshellarg($arg);

        if (!$val) {
            return "''";
        }

        return $val;
    }

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

    private function closeAllPipes()
    {
        foreach ($this->pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }
        $this->pipes = array();
    }

    private function resetPreviousCall()
    {
        $this->processStatus = null;
        $this->processReturn = null;
        $this->processOutput = null;
        $this->processError = null;

        if (count($this->pipes)) {
            $this->closeAllPipes();
        }

        if ($this->resource) {
            proc_close($this->resource);
            $this->resource = null;
        }
    }

    public function getPositionalArguments()
    {
        $arguments = array();

        foreach ($this->positionalArguments as $value) {
            $arguments[] = $this->escapeShellArg($value);
        }

        return implode(' ', $arguments);
    }

    public function getExecutionCall()
    {
        $cmd = array();
        $cmd[] = $this->getCommand();
        $cmd[] = $this->getNamedArguments();
        $cmd[] = $this->getPositionalArguments();

        return implode(' ', $cmd);
    }

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

        if ($this->processError) {
            throw new Exception\ProcessException('STDERR: '. $this->processError);
        }

        if ($this->processReturn > 0) {
            throw new Exception\ProcessException('Process exited with '. $this->processReturn);
        }

        return true;
    }

    public function getOutput()
    {
        return $this->processOutput;
    }

    public function getExitStatus()
    {
        return $this->processReturn;
    }

    public function setInput($input) {
        $this->processInput = $input;
    }

    public function getStatus()
    {
        return $this->processStatus;
    }

    public function getStatusItem($type)
    {
        return $this->processStatus[$type];
    }
}
