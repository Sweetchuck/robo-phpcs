<?php

declare(strict_types=1);

namespace Sweetchuck\Robo\Phpcs\Tests\Unit\Task;

use Codeception\Test\Unit;
use League\Container\Container as LeagueContainer;
use Psr\Container\ContainerInterface;
use Robo\Collection\CollectionBuilder;
use Robo\Config\Config as RoboConfig;
use Robo\Robo;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcessHelper;
use Sweetchuck\Robo\Phpcs\Test\Helper\Dummy\DummyTaskBuilder;
use Sweetchuck\Robo\Phpcs\Test\UnitTester;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ErrorHandler\BufferingLogger;

class TestBase extends Unit
{

    protected UnitTester $tester;

    protected ?ContainerInterface $containerBackup;

    protected LeagueContainer $container;

    protected RoboConfig $config;

    protected CollectionBuilder $builder;

    protected DummyTaskBuilder $taskBuilder;

    /**
     * {@inheritdoc}
     */
    public function _before()
    {
        parent::_before();

        DummyProcess::reset();
        Robo::unsetContainer();

        $this->container = new LeagueContainer();
        $application = new SymfonyApplication('Sweetchuck - Robo Git', '1.0.0');
        $application->getHelperSet()->set(new DummyProcessHelper(), 'process');
        $this->config = (new RoboConfig());
        $input = null;
        $output = new DummyOutput([
            'verbosity' => OutputInterface::VERBOSITY_DEBUG,
        ]);

        $this->container->add('container', $this->container);

        Robo::configureContainer($this->container, $application, $this->config, $input, $output);
        $this
            ->container
            ->addShared('logger', BufferingLogger::class);

        $this->builder = CollectionBuilder::create($this->container, null);
        $this->taskBuilder = new DummyTaskBuilder();
        $this->taskBuilder->setContainer($this->container);
        $this->taskBuilder->setBuilder($this->builder);
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
