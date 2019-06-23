<?php

namespace Cifren\OxPeckerData\Core;

use Cifren\OxPeckerData\Definition\Context as DataProcessContext;
use Cifren\OxPeckerData\Definition\DataConfigurationInterface;
use Cifren\OxPeckerData\Model\StopwatchInterface;
use Doctrine\ORM\EntityManager;
use Knp\ETL\Context\Context as ETLProcessContext;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Cifren\OxPeckerData\Core\DataProcess.
 */
class DataProcess
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DataConfigurationInterface
     */
    protected $dataConfiguration;

    /**
     * @var StopwatchInterface
     */
    protected $stopWatch;

    /**
     * 
     * @param StopwatchInterface $stopWatch
     */
    public function __construct(
            StopwatchInterface $stopWatch
    ) {
        $this->stopWatch = $stopWatch;
    }

    /**
     * Process the data based on the configuration.
     *
     * @param DataConfigurationInterface $config
     * @param array                      $params
     */
    public function process(DataConfigurationInterface $config, array $params)
    {
        $dataProcessContext = $this->createContext($params);

        $this->logNotice('PreProcess');

        $this->stopWatch->start('preProcess');
        $config->preProcess($dataProcessContext);
        $this->stopWatch->stop('preProcess');
        $this
            ->logNotice(sprintf(
                    'Executed in %s',
                    $this->stopWatch
                        ->getFinishTime('preProcess')
                        ->format('%hh %im %ss')
            ));

        $etlProcesses = $config->getETLProcesses($dataProcessContext);
        $dataProcessContext->setEtlProcesses($etlProcesses);

        $this->logNotice('Execute ETL Processes');

        $this->executeETLProcesses($etlProcesses);

        $this->logNotice('PostProcess');
        $this->stopWatch->start('postProcess');
        $config->postProcess($dataProcessContext);
        $this->stopWatch->stop('postProcess');
        $this->logNotice(sprintf(
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
        !count($etlProcesses) ?? $this->logNotice('No ETL processes found');

        foreach ($etlProcesses as $etlProcess) {
            $etlProcess->setLogger($this->getLogger());
            if (!$etlProcess->getContext()) {
                $etlProcess->setContext(new ETLProcessContext());
            }

//            if ($etlProcess instanceof SqlETLProcess) {
//                $etlProcess->setDatasourceManager($this->getDatasourceManager());
//            }
            $this->stopWatch->start('etlProcess');
            $etlProcess->process();
            $this->stopWatch->stop('etlProcess');
            $this->logNotice(sprintf(
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
     * getLogger.
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * setLogger.
     *
     * @param LoggerInterface $logger
     *
     * @return DataProcess
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param string $message
     */
    public function logNotice($message)
    {
        $this->log(LogLevel::NOTICE, $message);
    }

    /**
     * @param string $level
     * @param string $message
     */
    public function log($level, $message)
    {
        if ($this->getLogger()) {
            $this->getLogger()->log($level, $message);
        }
    }
}
