<?php

namespace Helper\Dummy;

/**
 * Class Output.
 *
 * @package Helper\Dummy
 */
class Output extends \Symfony\Component\Console\Output\Output
{

    /**
     * @var string
     */
    public $output = '';

    /**
     * {@inheritdoc}
     */
    protected function doWrite($message, $newline)
    {
        $this->output .= $message . ($newline ? "\n" : '');
    }
}
