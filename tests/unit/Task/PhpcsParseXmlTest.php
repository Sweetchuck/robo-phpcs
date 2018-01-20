<?php

namespace Sweetchuck\Robo\Phpcs\Tests\Unit\Task;

use League\Container\ContainerInterface;
use org\bovigo\vfs\vfsStream;
use Robo\Robo;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput;
use Sweetchuck\Robo\Phpcs\Task\PhpcsParseXml;
use Codeception\Util\Stub;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class PhpcsParseXmlTest extends \Codeception\Test\Unit
{
    /**
     * @var \Sweetchuck\Robo\Phpcs\Test\UnitTester
     */
    protected $tester;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $rootDir;

    /**
     * @var \League\Container\ContainerInterface
     */
    protected $containerBackup;

    /**
     * {@inheritdoc}
     */
    public function _before()
    {
        parent::_before();

        $this->rootDir = vfsStream::setup('PhpcsParseXmlTest');

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

        (new Filesystem())->remove($this->rootDir->getName());
        $this->rootDir = null;

        parent::_after();
    }

    public function casesRun(): array
    {
        return [
            'xml file not exists; fail:true' => [
                [
                    'exitCode' => 1,
                    'message' => 'XML file not found in directory: "<info>vfs://PhpcsParseXmlTest</info>"',
                    'files' => [],
                    'exclude-patterns' => [],
                ],
                [],
                [],
            ],
            'xml file not exists; fail:false' => [
                [
                    'exitCode' => 0,
                    'message' => '',
                    'files' => [],
                    'exclude-patterns' => [],
                ],
                [
                    'failOnXmlFileNotExists' => false,
                ],
                [],
            ],
            'invalid xml' => [
                [
                    'exitCode' => 2,
                    'message' => 'Invalid XML file: "<info>vfs://PhpcsParseXmlTest/phpcs.xml</info>"',
                    'files' => [],
                    'exclude-patterns' => [],
                ],
                [],
                [
                    'phpcs.xml' => '<?xml version="1.0"?>',
                ],
            ],
            'empty file' => [
                [
                    'exitCode' => 0,
                    'message' => '',
                    'files' => [],
                    'exclude-patterns' => [],
                ],
                [],
                [
                    'phpcs.xml' => '<?xml version="1.0"?><ruleset />',
                ],
            ],
            'basic phpcs.xml' => [
                [
                    'exitCode' => 0,
                    'message' => '',
                    'files' => [
                        'file_01' => true,
                        'file_02' => true,
                    ],
                    'exclude-patterns' => [
                        'ep_01' => true,
                        'ep_02' => true,
                    ],
                ],
                [],
                [
                    'phpcs.xml' => implode("\n", [
                        '<?xml version="1.0"?><ruleset>',
                        '<file>file_01</file>',
                        '<file>file_02</file>',
                        '<exclude-pattern>ep_01</exclude-pattern>',
                        '<exclude-pattern>ep_02</exclude-pattern>',
                        '</ruleset>',
                    ]),
                    'phpcs.xml.dist' => implode("\n", [
                        '<?xml version="1.0"?><ruleset>',
                        '<file>foo</file>',
                        '<exclude-pattern>bar</exclude-pattern>',
                        '</ruleset>',
                    ]),
                ],
            ],
            'basic phpcs.xml.dist' => [
                [
                    'exitCode' => 0,
                    'message' => '',
                    'myPrefix.files' => [
                        'foo' => true,
                    ],
                    'myPrefix.exclude-patterns' => [
                        'bar' => true,
                    ],
                ],
                [
                    'assetNamePrefix' => 'myPrefix.'
                ],
                [
                    'phpcs.xml.dist' => implode("\n", [
                        '<?xml version="1.0"?><ruleset>',
                        '<file>foo</file>',
                        '<exclude-pattern>bar</exclude-pattern>',
                        '</ruleset>',
                    ]),
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesRun
     */
    public function testRun($expected, array $options, array $files): void
    {
        $baseDir = $this->rootDir->url();
        $options += ['workingDirectory' => $baseDir];
        foreach ($files as $fileName => $fileContent) {
            file_put_contents("$baseDir/$fileName", $fileContent);
        }

        /** @var \Sweetchuck\Robo\Phpcs\Task\PhpcsParseXml $task */
        $task = Stub::construct(
            PhpcsParseXml::class,
            [],
            [
                'container' => $this->getNewContainer(),
            ]
        );

        $assetNamePrefix = $options['assetNamePrefix'] ?? '';
        $result = $task
            ->setOptions($options)
            ->run();

        foreach ($expected as $expectedKey => $expectedValue) {
            switch ($expectedKey) {
                case 'exitCode':
                    $this->tester->assertEquals($expected[$expectedKey], $result->getExitCode());
                    break;

                case 'message':
                    $this->tester->assertEquals($expected[$expectedKey], $result->getMessage());
                    break;

                case 'files':
                case 'exclude-patterns':
                    $this->tester->assertEquals($expected[$expectedKey], $result["{$assetNamePrefix}{$expectedKey}"]);
                    break;
            }
        }
    }

    protected function getNewContainer(): ContainerInterface
    {
        $config = [
            'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            'colors' => false,
        ];
        $output = new DummyOutput($config);

        $container = Robo::createDefaultContainer(null, $output);
        $container->add('output', $output, false);

        return $container;
    }
}
