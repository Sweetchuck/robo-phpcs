<?php

namespace Helper\Module;

use Codeception\Module as CodeceptionModule;
use Symfony\Component\Process\Process;

/**
 * Wrapper for basic shell commands and shell output.
 */
class Cli extends CodeceptionModule
{
    /**
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    /**
     * @var int|null
     */
    protected $exitCode = null;

    /**
     * @var string
     */
    protected $stdOutput = null;

    /**
     * @var string
     */
    protected $stdError = null;

    // @codingStandardsIgnoreStart
    public function _cleanup()
    {
        // @codingStandardsIgnoreEnd
        $this->process = null;
    }

    /**
     * Executes a shell command.
     *
     * @param string $command
     *
     * @return $this
     */
    public function runShellCommand($command)
    {
        $this->process = new Process($command);
        $this->exitCode = $this->process->run();
        $this->stdOutput = $this->process->getOutput();
        $this->stdError = $this->process->getErrorOutput();

        return $this;
    }

    /**
     * @return int|null
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }

    /**
     * @return string
     */
    public function getStdOutput()
    {
        return $this->stdOutput;
    }

    /**
     * @return string
     */
    public function getStdError()
    {
        return $this->stdError;
    }
}
