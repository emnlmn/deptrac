<?php

declare(strict_types=1);

namespace Tests\SensioLabs\Deptrac\OutputFormatter;

use PHPUnit\Framework\TestCase;
use SensioLabs\Deptrac\AstRunner\AstMap\ClassLikeName;
use SensioLabs\Deptrac\AstRunner\AstMap\FileOccurrence;
use SensioLabs\Deptrac\Dependency\Dependency;
use SensioLabs\Deptrac\OutputFormatter\GraphVizOutputFormatter;
use SensioLabs\Deptrac\OutputFormatter\OutputFormatterInput;
use SensioLabs\Deptrac\RulesetEngine\Allowed;
use SensioLabs\Deptrac\RulesetEngine\Context;
use SensioLabs\Deptrac\RulesetEngine\Uncovered;
use SensioLabs\Deptrac\RulesetEngine\Violation;
use Symfony\Component\Console\Output\BufferedOutput;

class GraphVizOutputFormatterTest extends TestCase
{
    public function testGetName(): void
    {
        static::assertEquals('graphviz', (new GraphVizOutputFormatter())->getName());
    }

    public function testFinish(): void
    {
        $dotFile = __DIR__.'/data/graphviz.dot';

        $fileOccurrenceA = FileOccurrence::fromFilepath('classA.php', 0);
        $classA = ClassLikeName::fromFQCN('ClassA');

        $context = new Context([
            new Violation(new Dependency($classA, ClassLikeName::fromFQCN('ClassB'), $fileOccurrenceA), 'LayerA', 'LayerB'),
            new Violation(new Dependency(ClassLikeName::fromFQCN('ClassAB'), ClassLikeName::fromFQCN('ClassBA'), FileOccurrence::fromFilepath('classAB.php', 1)), 'LayerA', 'LayerB'),
            new Allowed(new Dependency($classA, ClassLikeName::fromFQCN('ClassC'), $fileOccurrenceA), 'LayerA', 'LayerC'),
            new Uncovered(new Dependency($classA, ClassLikeName::fromFQCN('ClassD'), $fileOccurrenceA), 'LayerC'),
        ]);

        $output = new BufferedOutput();
        $input = new OutputFormatterInput([
            'display' => false,
            'dump-image' => false,
            'dump-dot' => $dotFile,
            'dump-html' => false,
        ]);

        (new GraphVizOutputFormatter())->finish($context, $output, $input);

        static::assertSame(sprintf("Script dumped to %s\n", $dotFile), $output->fetch());
        static::assertFileEquals(__DIR__.'/data/graphviz-expected.dot', $dotFile);

        unlink($dotFile);
    }
}
