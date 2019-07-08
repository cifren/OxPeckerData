<?php

namespace Tests\ETL\Core;

use Cifren\OxPeckerData\Definition\Context;
use Cifren\OxPeckerData\ETL\Core\IterationETLProcess;
use Cifren\OxPeckerData\ETL\Iteration\Extractor\Doctrine\ORMExtractor;
use Cifren\OxPeckerData\ETL\Iteration\Loader\Doctrine\ORMLoader;
use Cifren\OxPeckerData\ETL\Iteration\Transformer\ArrayAlterationTransformer;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

class IterationETLProcessTest extends TestCase
{
    public function testProcess()
    {
        $ETLProcess = $this->getIterationETLProcess();
        $ETLProcess->process();
    }

    private function getIterationETLProcess(): IterationETLProcess
    {
        $extractor = $this->createMock(ORMExtractor::class);
        $extractor
            ->expects($this->atLeastOnce())
            ->method('extract')
            ->will($this->onConsecutiveCalls(1, 2, 3, 5, 7));

        $tranformer1 = $this->createMock(ArrayAlterationTransformer::class);
        $tranformer2 = $this->createMock(ArrayAlterationTransformer::class);
        $transformers = [$tranformer1, $tranformer2];

        $loader = $this->createMock(ORMLoader::class);
        $loader
            ->expects($this->atLeastOnce())
            ->method('load');
        $loader
            ->expects($this->atLeastOnce())
            ->method('flush');

        $context = $this->createMock(Context::class);
        $logger = $this->createMock(AbstractLogger::class);

        $ETLProcess = new IterationETLProcess($extractor, $transformers, $loader);
        $ETLProcess->setContext($context);
        $ETLProcess->setLogger($logger);

        return $ETLProcess;
    }
}
