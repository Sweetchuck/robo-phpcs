<?php

namespace Cheppers\Robo\Phpcs\Task;

/**
 * Class TaskPhpcsLint.
 *
 * @package Cheppers\Robo\Phpcs\Task
 */
class PhpcsLintFiles extends PhpcsLint
{
    //region Option - ignore
    /**
     * @var string[]
     */
    protected $ignored = [];

    /**
     * @return array|null
     */
    public function getIgnore()
    {
        return $this->ignored;
    }

    /**
     * Set patterns to ignore files.
     *
     * @param string[] $value
     *   File patterns.
     *
     * @return $this
     *   The called object.
     */
    public function setIgnore(array $value)
    {
        $this->ignored = $value;

        return $this;
    }
    //endregion

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        foreach ($options as $name => $value) {
            switch ($name) {
                case 'ignore':
                case 'ignored':
                    $this->setIgnore($value);
                    break;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildOptions()
    {
        return [
            'ignored' => $this->getIgnore(),
        ] + parent::buildOptions();
    }
}
