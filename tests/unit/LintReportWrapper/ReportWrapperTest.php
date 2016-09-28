<?php

// @codingStandardsIgnoreStart
use Cheppers\Robo\Phpcs\LintReportWrapper\ReportWrapper;

class ReportWrapperTest extends \Codeception\Test\Unit
{
    // @codingStandardsIgnoreEnd

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @return array
     */
    public function casesReports()
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

    /**
     * @dataProvider casesReports
     */
    public function testAll(array $expected, array $report)
    {
        $rw = new ReportWrapper($report);

        $this->assertEquals($expected['countFiles'], $rw->countFiles());
        $this->assertEquals($expected['numOfErrors'], $rw->numOfErrors());
        $this->assertEquals($expected['numOfWarnings'], $rw->numOfWarnings());
        $this->assertEquals($expected['highestSeverity'], $rw->highestSeverity());

        /**
         * @var string $filePath
         * @var \Cheppers\Robo\Phpcs\LintReportWrapper\FileWrapper $fw
         */
        foreach ($rw->yieldFiles() as $filePath => $fw) {
            $file = array_shift($report['files']);
            $this->assertEquals($file['filePath'], $fw->filePath());
            $this->assertEquals($file['errors'], $fw->numOfErrors());
            $this->assertEquals($file['warnings'], $fw->numOfWarnings());
            $this->assertEquals($file['__highestSeverity'], $fw->highestSeverity());
            $this->assertEquals($file['__stats'], $fw->stats());

            /**
             * @var int $i
             * @var \Cheppers\LintReport\FailureWrapperInterface $failureWrapper
             */
            foreach ($fw->yieldFailures() as $i => $failureWrapper) {
                $message = $file['messages'][$i];
                $this->assertEquals($message['type'], $failureWrapper->severity());
                $this->assertEquals($message['source'], $failureWrapper->source());
                $this->assertEquals($message['line'], $failureWrapper->line());
                $this->assertEquals($message['column'], $failureWrapper->column());
                $this->assertEquals($message['message'], $failureWrapper->message());
            }
        }
    }
}
