<?php

namespace Cifren\OxPeckerData\Core;

use Cifren\OxPeckerData\Definition\Context as DataProcessContext;
use Cifren\OxPeckerData\Definition\DataConfigurationInterface;
use Cifren\OxPeckerData\Model\StopwatchInterface;
use Knp\ETL\Context\Context as ETLProcessContext;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;

/**
 * Cifren\OxPeckerData\Core\DataProcess.
 */
class DataProcess
{
    use LoggerAwareTrait;
    use LoggerTrait;

    /**
     * @var DataConfigurationInterface
     */
    protected $dataConfiguration;

    /**
     * @var StopwatchInterface
     */
    protected $stopWatch;

    /**
     * @param StopwatchInterface $stopWatch
     */
    public function __construct(StopwatchInterface $stopWatch)
    {
        $this->stopWatch = $stopWatch;
    }

    /**
     * Process the data based on the configuration.
     *
     * @param DataConfigurationInterface $config
     * @param array                      $params
     */
    public function process(DataConfigurationInterface $config, array $params = [])
    {
        $dataProcessContext = $this->createContext($params);

        $this->notice('PreProcess');

        $this->stopWatch->start('preProcess');
        $config->preProcess($dataProcessContext);
        $this->stopWatch->stop('preProcess');
        $this
            ->notice(sprintf(
                    'Executed in %s',
                    $this->stopWatch
                        ->getFinishTime('preProcess')
                        ->format('%hh %im %ss')
            ));

        $etlProcesses = $config->getETLProcesses($dataProcessContext);
        $dataProcessContext->setEtlProcesses($etlProcesses);

        $this->notice('Execute ETL Processes');

        $this->executeETLProcesses($etlProcesses);

        $this->notice('PostProcess');
        $this->stopWatch->start('postProcess');
        $config->postProcess($dataProcessContext);
        $this->stopWatch->stop('postProcess');
        $this->notice(sprintf(
            'Executed in %s',
            $this->stopWatch
                ->getFinishTime('postProcess')
                ->format('%hh %im %ss')
        ));
    }

    /**
     * Execute ETL processes.
     *
     * @param array $etlProcesses
     */
    protected function executeETLProcesses(array $etlProcesses)
    {
        !count($etlProcesses) ?? $this->notice('No ETL processes found');

        foreach ($etlProcesses as $etlProcess) {
            if($this->logger){
                $etlProcess->setLogger($this->logger);
            }

            if (!$etlProcess->getContext()) {
                $etlProcess->setContext(new ETLProcessContext());
            }

//            if ($etlProcess instanceof SqlETLProcess) {
//                $etlProcess->setDatasourceManager($this->getDatasourceManager());
//            }
            $this->stopWatch->start('etlProcess');
            $etlProcess->process();
            $this->stopWatch->stop('etlProcess');
            $this->notice(sprintf(
                'Executed in %s',
                $this->stopWatch
                    ->getFinishTime('etlProcess')
                    ->format('%hh %im %ss')
            ));
        }
//        $this->getDatasourceManager()->clear();
    }

    /**
     * createContext.
     *
     * @param array $params
     *
     * @return DataProcessContext
     */
    protected function createContext(array $params)
    {
        $context = new DataProcessContext($params);

        return $context->setArgs($params);
    }

    /**
     * @param string $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
