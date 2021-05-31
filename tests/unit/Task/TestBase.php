<?php

declare(strict_types=1);

namespace Sweetchuck\Robo\Phpcs\Tests\Unit\Task;

use Codeception\Test\Unit;
use Psr\Container\ContainerInterface;
use Robo\Robo;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput;
use Sweetchuck\Robo\Phpcs\Test\UnitTester;
use Symfony\Component\Console\Output\OutputInterface;

class TestBase extends Unit
{

    protected UnitTester $tester;

    protected ?ContainerInterface $containerBackup;

    /**
     * {@inheritdoc}
     */
    public function _before()
    {
        parent::_before();

        $this->containerBackup = Robo::hasContainer() ? Robo::getContainer() : null;
        if ($this->containerBackup) {
            Robo::unsetContainer();
        }
    }

    protected function _after()
    {
        if ($this->containerBackup) {
            Robo::setContainer($this->containerBackup);
        } else {
            Robo::unsetContainer();
        }

        $this->containerBackup = null;

        parent::_after();
    }

    protected function getNewContainer(): ContainerInterface
    {
        $config = [
            'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            'colors' => false,
        ];
        $output = new DummyOutput($config);

        Robo::createContainer();
        $container = Robo::getContainer();
        $container->add('output', $output, false);

        return $container;
    }
}
