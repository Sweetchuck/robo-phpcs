<?php

namespace Helper\Dummy;

/**
 * Class Process.
 *
 * @package Helper
 */
class Process extends \Symfony\Component\Process\Process
{

    /**
     * @var int
     */
    public static $exitCode = 0;

    /**
     * @var string
     */
    public static $stdOutput = '';

    /**
     * @var string
     */
    public static $stdError = '';

    /**
     * @var \Helper\Dummy\Process
     */
    public static $instance = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        $commandline,
        $cwd = null,
        array $env = null,
        $input = null,
        $timeout = 60,
        array $options = array()
    ) {
        parent::__construct($commandline, $cwd, $env, $input, $timeout, $options);

        static::$instance = $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run($callback = null)
    {
        return static::$exitCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getExitCode()
    {
        return static::$exitCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return static::$stdOutput;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorOutput()
    {
        return static::$stdError;
    }
}
