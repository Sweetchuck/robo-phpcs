<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Phpcs\Task;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\TaskInfo;
use Symfony\Component\Filesystem\Filesystem;

class PhpcsParseXml extends BaseTask
{

    protected Filesystem $fs;

    protected string $taskName = 'PHP_CodeSniffer - parse XML';

    protected array $assets = [
        'files' => [],
        'exclude-patterns' => [],
    ];

    protected int $actionExitCode = 0;

    protected string $actionStdError = '';

    // region Option
    // region assetNamePrefix
    protected string $assetNamePrefix = '';

    public function getAssetNamePrefix(): string
    {
        return $this->assetNamePrefix;
    }

    public function setAssetNamePrefix(string $value): static
    {
        $this->assetNamePrefix = $value;

        return $this;
    }
    // endregion

    // region workingDirectory
    protected string $workingDirectory = '';

    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    public function setWorkingDirectory(string $value): static
    {
        $this->workingDirectory = $value;

        return $this;
    }
    // endregion

    // region failOnXmlFileNotExists
    protected bool $failOnXmlFileNotExists = true;

    public function getFailOnXmlFileNotExists(): bool
    {
        return $this->failOnXmlFileNotExists;
    }

    public function setFailOnXmlFileNotExists(bool $value): static
    {
        $this->failOnXmlFileNotExists = $value;

        return $this;
    }
    // endregion

    public function setOptions(array $options): static
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

    protected ?string $xmlFileName = null;

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

    protected function runHeader(): static
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

    protected function runAction(): static
    {
        $wd = $this->getWorkingDirectory() ?: '.';
        $xmlFileName = $this->getXmlFileName();
        if (!$xmlFileName) {
            if ($this->getFailOnXmlFileNotExists()) {
                $this->actionExitCode = 1;
                $this->actionStdError = "XML file not found in directory: \"<info>$wd</info>\"";
            }

            return $this;
        }

        $assets = $this->getFilePathsFromXml(file_get_contents("$wd/$xmlFileName"));
        if ($assets === null) {
            $this->actionExitCode = 2;
            $this->actionStdError = "Invalid XML file: \"<info>$wd/$xmlFileName</info>\"";

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
                $data["$assetNamePrefix$key"] = $value;
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
