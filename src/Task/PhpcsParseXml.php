<?php

namespace Sweetchuck\Robo\Phpcs\Task;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\TaskInfo;
use Symfony\Component\Filesystem\Filesystem;

class PhpcsParseXml extends BaseTask
{

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $fs;

    /**
     * @var string
     */
    protected $taskName = 'PHP_CodeSniffer parse XML';

    /**
     * @var array
     */
    protected $assets = [
        'files' => [],
        'exclude-pattern' => [],
    ];

    /**
     * @var int
     */
    protected $actionExitCode = 0;

    /**
     * @var string
     */
    protected $actionStdError = '';

    // region Option
    // region assetNamePrefix
    /**
     * @var string
     */
    protected $assetNamePrefix = '';

    public function getAssetNamePrefix(): string
    {
        return $this->assetNamePrefix;
    }

    /**
     * @return $this
     */
    public function setAssetNamePrefix(string $value)
    {
        $this->assetNamePrefix = $value;

        return $this;
    }
    // endregion

    // region workingDirectory
    /**
     * @var string
     */
    protected $workingDirectory = '';

    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    /**
     * @return $this
     */
    public function setWorkingDirectory(string $value)
    {
        $this->workingDirectory = $value;

        return $this;
    }
    // endregion

    // region failOnXmlFileNotExists
    /**
     * @var bool
     */
    protected $failOnXmlFileNotExists = true;

    public function getFailOnXmlFileNotExists(): bool
    {
        return $this->failOnXmlFileNotExists;
    }

    /**
     * @return $this
     */
    public function setFailOnXmlFileNotExists(bool $value)
    {
        $this->failOnXmlFileNotExists = $value;

        return $this;
    }
    // endregion

    /**
     * @return $this
     */
    public function setOptions(array $options)
    {
        if (array_key_exists('assetNamePrefix', $options)) {
            $this->setAssetNamePrefix($options['assetNamePrefix']);
        }

        if (array_key_exists('workingDirectory', $options)) {
            $this->setWorkingDirectory($options['workingDirectory']);
        }

        if (array_key_exists('failOnXmlFileNotExists', $options)) {
            $this->setFailOnXmlFileNotExists($options['failOnXmlFileNotExists']);
        }

        return $this;
    }
    // endregion

    public function __construct(Filesystem $fs = null)
    {
        $this->fs = $fs ?: new Filesystem();
    }

    public function getTaskName(): string
    {
        return $this->taskName ?: TaskInfo::formatTaskName($this);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTaskContext($context = null)
    {
        $context = parent::getTaskContext($context);
        $context['name'] = $this->getTaskName();

        return $context;
    }

    /**
     * @var string|null
     */
    protected $xmlFileName = null;

    protected function getXmlFileName(): string
    {
        if ($this->xmlFileName === null) {
            $wd = $this->getWorkingDirectory() ?: '.';
            if ($this->fs->exists("$wd/phpcs.xml")) {
                $this->xmlFileName = 'phpcs.xml';
            } elseif ($this->fs->exists("$wd/phpcs.xml.dist")) {
                $this->xmlFileName = 'phpcs.xml.dist';
            } else {
                $this->xmlFileName = '';
            }
        }

        return $this->xmlFileName;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): Result
    {
        return $this
            ->runHeader()
            ->runAction()
            ->runReturn();
    }

    /**
     * @return $this
     */
    protected function runHeader()
    {
        $context = [
            'workingDirectory' => $this->getWorkingDirectory() ?: '.',
            'xmlFileName' => $this->getXmlFileName(),
        ];
        $text = $context['xmlFileName'] ?
            'XML file: "<info>{workingDirectory}/{xmlFileName}"</info>'
            : 'XML file not found in directory: "<info>{workingDirectory}</info>"';

        $this->printTaskInfo($text, $context);

        return $this;
    }

    /**
     * @return $this
     */
    protected function runAction()
    {
        $wd = $this->getWorkingDirectory() ?: '.';
        $xmlFileName = $this->getXmlFileName();
        if (!$xmlFileName) {
            if ($this->getFailOnXmlFileNotExists()) {
                $this->actionExitCode = 1;
                $this->actionStdError = "XML file not found in directory: \"<info>{$wd}</info>\"";
            }

            return $this;
        }

        $assets = $this->getFilePathsFromXml(file_get_contents("$wd/$xmlFileName"));
        if ($assets === null) {
            $this->actionExitCode = 2;
            $this->actionStdError = "Invalid XML file: \"<info>{$wd}/{$xmlFileName}</info>\"";

            return $this;
        }

        $this->assets = $assets;

        return $this;
    }

    protected function runReturn(): Result
    {
        $assetNamePrefix = $this->getAssetNamePrefix();
        if ($assetNamePrefix === '') {
            $data = $this->assets;
        } else {
            $data = [];
            foreach ($this->assets as $key => $value) {
                $data["{$assetNamePrefix}{$key}"] = $value;
            }
        }

        return new Result(
            $this,
            $this->actionExitCode,
            $this->actionStdError,
            $data
        );
    }

    protected function getFilePathsFromXml(string $xmlContent): ?array
    {
        $xml = new \DOMDocument();
        $result = @$xml->loadXML($xmlContent);
        if ($result === false) {
            return null;
        }

        $xpath = new \DOMXPath($xml);

        $xpathQueries = [
            'files' => '/ruleset/file',
            'exclude-patterns' => '/ruleset/exclude-pattern',
        ];

        $paths = array_fill_keys(array_keys($xpathQueries), []);
        foreach ($xpathQueries as $key => $query) {
            $elements = $xpath->query($query);
            /** @var \DOMNode $element */
            foreach ($elements as $element) {
                $paths[$key][$element->textContent] = true;
            }
        }

        return $paths;
    }
}
