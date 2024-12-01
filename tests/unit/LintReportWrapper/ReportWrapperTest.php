<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Phpcs\Tests\Unit\LintReportWrapper;

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Robo\Phpcs\LintReportWrapper\FailureWrapper;
use Sweetchuck\Robo\Phpcs\LintReportWrapper\FileWrapper;
use Sweetchuck\Robo\Phpcs\LintReportWrapper\ReportWrapper;
use Sweetchuck\Robo\Phpcs\Tests\UnitTester;

#[CoversClass(ReportWrapper::class)]
#[CoversClass(FileWrapper::class)]
#[CoversClass(FailureWrapper::class)]
class ReportWrapperTest extends Unit
{
    protected UnitTester $tester;

    public static function casesReports(): array
    {
        return [
            'ok:no-files' => [
                'expected' => [
                    'countFiles' => 0,
                    'numOfErrors' => 0,
                    'numOfWarnings' => 0,
                    'highestSeverity' => 'ok',
                ],
                'report' => [
                    'totals' => [
                        'errors' => 0,
                        'warnings' => 0,
                        'fixable' => 0,
                    ],
                    'files' => [],
                ],
            ],
            'ok:one-file' => [
                'expected' => [
                    'countFiles' => 1,
                    'numOfErrors' => 0,
                    'numOfWarnings' => 0,
                    'highestSeverity' => 'ok',
                ],
                'report' => [
                    'totals' => [
                        'errors' => 0,
                        'warnings' => 0,
                        'fixable' => 0,
                    ],
                    'files' => [
                        'a.php' => [
                            'filePath' => 'a.php',
                            'errors' => 0,
                            'warnings' => 0,
                            '__highestSeverity' => 'ok',
                            '__stats' => [
                                'severityWeight' => '',
                                'severity' => '',
                                'has' => [
                                    'ok' => false,
                                    'warning' => false,
                                    'error' => false,
                                ],
                                'source' => [],
                            ],
                            'messages' => [],
                        ],
                    ],
                ],
            ],
            'warning' => [
                'expected' => [
                    'countFiles' => 1,
                    'numOfErrors' => 0,
                    'numOfWarnings' => 2,
                    'highestSeverity' => 'warning',
                ],
                'report' => [
                    'totals' => [
                        'errors' => 0,
                        'warnings' => 2,
                        'fixable' => 0,
                    ],
                    'files' => [
                        'a.php' => [
                            'filePath' => 'a.php',
                            'errors' => 0,
                            'warnings' => 2,
                            '__highestSeverity' => 'warning',
                            '__stats' => [
                                'severityWeight' => 1,
                                'severity' => 'warning',
                                'has' => [
                                    'ok' => false,
                                    'warning' => true,
                                    'error' => false,
                                ],
                                'source' => [
                                    's1' => [
                                        'severity' => 'warning',
                                        'count' => 1,
                                    ],
                                    's2' => [
                                        'severity' => 'warning',
                                        'count' => 1,
                                    ],
                                ],
                            ],
                            'messages' => [
                                [
                                    'message' => 'm1',
                                    'source' => 's1',
                                    'severity' => 1,
                                    'type' => 'warning',
                                    'line' => 1,
                                    'column' => 1,
                                    'fixable' => false,
                                ],
                                [
                                    'message' => 'm2',
                                    'source' => 's2',
                                    'severity' => 1,
                                    'type' => 'warning',
                                    'line' => 2,
                                    'column' => 2,
                                    'fixable' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'error' => [
                'expected' => [
                    'countFiles' => 1,
                    'numOfErrors' => 1,
                    'numOfWarnings' => 1,
                    'highestSeverity' => 'error',
                ],
                'report' => [
                    'totals' => [
                        'errors' => 1,
                        'warnings' => 1,
                        'fixable' => 0,
                    ],
                    'files' => [
                        'a.php' => [
                            'filePath' => 'a.php',
                            'errors' => 1,
                            'warnings' => 1,
                            '__highestSeverity' => 'error',
                            '__stats' => [
                                'severityWeight' => 2,
                                'severity' => 'error',
                                'has' => [
                                    'ok' => false,
                                    'warning' => true,
                                    'error' => true,
                                ],
                                'source' => [
                                    's1' => [
                                        'severity' => 'error',
                                        'count' => 1,
                                    ],
                                    's2' => [
                                        'severity' => 'warning',
                                        'count' => 1,
                                    ],
                                ],
                            ],
                            'messages' => [
                                [
                                    'message' => 'm1',
                                    'source' => 's1',
                                    'severity' => 2,
                                    'type' => 'error',
                                    'line' => 1,
                                    'column' => 1,
                                    'fixable' => false,
                                ],
                                [
                                    'message' => 'm2',
                                    'source' => 's2',
                                    'severity' => 1,
                                    'type' => 'warning',
                                    'line' => 2,
                                    'column' => 2,
                                    'fixable' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('casesReports')]
    public function testAll(array $expected, array $report)
    {
        $rw = new ReportWrapper($report);

        $this->tester->assertSame($expected['countFiles'], $rw->countFiles());
        $this->tester->assertSame($expected['numOfErrors'], $rw->numOfErrors());
        $this->tester->assertSame($expected['numOfWarnings'], $rw->numOfWarnings());
        $this->tester->assertSame($expected['highestSeverity'], $rw->highestSeverity());

        /**
         * @var string $filePath
         * @var \Sweetchuck\Robo\Phpcs\LintReportWrapper\FileWrapper $fw
         */
        foreach ($rw->yieldFiles() as $filePath => $fw) {
            $file = array_shift($report['files']);
            $this->tester->assertSame($file['filePath'], $fw->filePath());
            $this->tester->assertSame($file['errors'], $fw->numOfErrors());
            $this->tester->assertSame($file['warnings'], $fw->numOfWarnings());
            $this->tester->assertSame($file['__highestSeverity'], $fw->highestSeverity());
            $this->tester->assertSame($file['__stats'], $fw->stats());

            /**
             * @var int $i
             * @var \Sweetchuck\LintReport\FailureWrapperInterface $failureWrapper
             */
            foreach ($fw->yieldFailures() as $i => $failureWrapper) {
                $message = $file['messages'][$i];
                $this->tester->assertSame($message['type'], $failureWrapper->severity());
                $this->tester->assertSame($message['source'], $failureWrapper->source());
                $this->tester->assertSame($message['line'], $failureWrapper->line());
                $this->tester->assertSame($message['column'], $failureWrapper->column());
                $this->tester->assertSame($message['message'], $failureWrapper->message());
            }
        }
    }
}
