<?php

namespace Cifren\OxPeckerData\Tests\Core;

use Cifren\OxPeckerData\Core\DataProcess;
use Cifren\OxPeckerData\Definition\DataConfigurationInterface;
use Cifren\OxPeckerData\Model\Stopwatch;
use PHPUnit\Framework\TestCase;
use Cifren\OxPeckerData\ETL\Core\IterationETLProcess;

class DataProcessTest extends TestCase
{
    public function testProcess(): void
    {
        list($dataProcess,
            $configurationWithoutETLProcesses,
            $configurationWithETLProcesses
        ) = $this->getProcessMocks();

        $dataProcess->process($configurationWithoutETLProcesses);

        $dataProcess->process($configurationWithETLProcesses);
    }

    public function getProcessMocks()
    {
        $iterationProcess = $this->createMock(IterationETLProcess::class);
        $iterationProcess
            ->expects($this->atLeastOnce())
            ->method('setContext');
        $iterationProcess
            ->expects($this->atLeastOnce())
            ->method('getContext')
            ->willReturn(false);
        $iterationProcess
            ->expects($this->atLeastOnce())
            ->method('process');

        $configurationWithoutETLProcesses = $this->createMock(DataConfigurationInterface::class);
        $configurationWithoutETLProcesses
            ->expects($this->atLeastOnce())
            ->method('preProcess');
        $configurationWithoutETLProcesses
            ->expects($this->atLeastOnce())
            ->method('getETLProcesses')
            ->willReturn([]);
        $configurationWithoutETLProcesses->expects($this->atLeastOnce())
            ->method('postProcess');

        $configurationWithETLProcesses = $this->createMock(DataConfigurationInterface::class);
        $configurationWithETLProcesses
            ->expects($this->atLeastOnce())
            ->method('preProcess');
        $configurationWithETLProcesses
            ->expects($this->atLeastOnce())
            ->method('getETLProcesses')
            ->willReturn([$iterationProcess]);
        $configurationWithETLProcesses->expects($this->atLeastOnce())
            ->method('postProcess');

        $stopWatch = $this->createMock(Stopwatch::class);
        $stopWatch
            ->expects($this->atLeastOnce())
            ->method('start');
        $stopWatch
            ->expects($this->atLeastOnce())
            ->method('stop');

        $dateinterval = $this->createMock(\DateInterval::class);
        $dateinterval
            ->expects($this->atLeastOnce())
            ->method('format');
        $stopWatch
            ->expects($this->atLeastOnce())
            ->method('getFinishTime')
            ->willReturn($dateinterval);

        $dataProcess = new DataProcess($stopWatch);

        return [
            $dataProcess,
            $configurationWithoutETLProcesses,
            $configurationWithETLProcesses,
        ];
    }
}
