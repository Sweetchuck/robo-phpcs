<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Phpcs\Tests\Unit\Task;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Codeception\Stub;
use Sweetchuck\Robo\Phpcs\Task\PhpcsParseXml;
use Symfony\Component\Filesystem\Filesystem;

class PhpcsParseXmlTest extends TestBase
{

    protected ?vfsStreamDirectory $rootDir;

    /**
     * {@inheritdoc}
     */
    public function _before()
    {
        parent::_before();

        $this->rootDir = vfsStream::setup('PhpcsParseXmlTest');
    }

    protected function _after()
    {
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

        $task = Stub::construct(
            PhpcsParseXml::class,
            [],
            [
                'container' => $this->getNewContainer(),
            ],
        );

        $assetNamePrefix = $options['assetNamePrefix'] ?? '';
        $result = $task
            ->setOptions($options)
            ->run();

        foreach ($expected as $expectedKey => $expectedValue) {
            switch ($expectedKey) {
                case 'exitCode':
                    $this->tester->assertEquals($expectedValue, $result->getExitCode());
                    break;

                case 'message':
                    $this->tester->assertEquals($expectedValue, $result->getMessage());
                    break;

                case 'files':
                case 'exclude-patterns':
                    $this->tester->assertEquals($expectedValue, $result["$assetNamePrefix$expectedKey"]);
                    break;
            }
        }
    }
}
