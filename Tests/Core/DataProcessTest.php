<?php

namespace Cifren\OxPeckerData\Tests\Core;

use Cifren\OxPeckerData\Core\DataProcess;
use Cifren\OxPeckerData\Definition\DataConfigurationInterface;
use Cifren\OxPeckerData\Model\Stopwatch;
use PHPUnit\Framework\TestCase;

class DataProcessTest extends TestCase
{
    public function testProcess(): void
    {
        list($dataProcess, $configuration) = $this->getProcessMocks();

        $dataProcess->process($configuration, []);
    }

    public function getProcessMocks()
    {
        $configuration = $this->createMock(DataConfigurationInterface::class);
        $configuration
            ->expects($this->atLeastOnce())
            ->method("preProcess");
        $configuration
            ->expects($this->atLeastOnce())
            ->method("getETLProcesses")
            ->willReturn([]);
        $configuration
            ->expects($this->atLeastOnce())
            ->method("postProcess");
        
        $stopWatch = $this->createMock(Stopwatch::class);
        $stopWatch
            ->expects($this->atLeastOnce())
            ->method("start");
        $stopWatch
            ->expects($this->atLeastOnce())
            ->method("stop");
        
        $dateinterval = $this->createMock(\DateInterval::class);        
        $dateinterval
            ->expects($this->atLeastOnce())
            ->method("format");
        $stopWatch
            ->expects($this->atLeastOnce())
            ->method("getFinishTime")
            ->willReturn($dateinterval);
        
        $dataProcess = new DataProcess($stopWatch);

        return [
            $dataProcess,
            $configuration,
        ];
    }
}
