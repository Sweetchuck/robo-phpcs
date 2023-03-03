<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Phpcs\Test;

use Codeception\Actor;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Assert;
use Sweetchuck\Robo\Phpcs\Test\_generated\AcceptanceTesterActions;
use Symfony\Component\Finder\Finder;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends Actor
{
    use AcceptanceTesterActions;

    /**
     * @return $this
     */
    public function clearTheReportsDir()
    {
        $reportsDir = codecept_data_dir('actual');
        if (is_dir($reportsDir)) {
            $finder = (new Finder())
                ->in($reportsDir)
                ->files();
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            foreach ($finder as $file) {
                unlink($file->getPathname());
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function haveAFileLikeThis(string $fileName)
    {
        $expectedDir = codecept_data_dir('expected');
        $actualDir = codecept_data_dir('actual');

        Assert::assertStringContainsString(
            file_get_contents("$expectedDir/$fileName"),
            file_get_contents("$actualDir/$fileName"),
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function haveAValidCheckstyleReport(string $fileName)
    {
        $fileName = codecept_data_dir($fileName);
        $doc = new DOMDocument();
        $doc->loadXML(file_get_contents($fileName));
        $xpath = new DOMXPath($doc);
        $rootElement = $xpath->query('/checkstyle');
        Assert::assertEquals(1, $rootElement->length, 'Root element of the Checkstyle XML is exists.');

        return $this;
    }
}
