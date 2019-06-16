<?php

namespace Cifren\OxPeckerData\Core;

use Cifren\OxPeckerData\Definition\DataConfigurationInterface;
use Knp\ETL\Context\Context;
use Doctrine\ORM\EntityManager;
use Cifren\OxPeckerData\ETL\SQL\DataSource\DataSourceManager;
use Cifren\OxPeckerData\Definition\Context as DataProcessContext;
use Symfony\Bridge\Monolog\Logger;
use Cifren\OxPeckerData\ETL\Core\SqlETLProcess;

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
     * @var Logger
     */
    protected $logger;

    /**
     * @var DataSourceManager
     */
    protected $dataSourceManager;

    /**
     * @var StopwatchInterface
     */
    protected $stopWatch;

    public function __construct(EntityManager $entityManager, Logger $logger, StopwatchInterface $stopWatch)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->dataSourceManager = new DataSourceManager($entityManager, $logger);
        $this->stopWatch = $stopWatch;
    }

    /**
     * Process the data based on the configuration.
     *
     * @param \Cifren\OxPeckerData\Definition\DataConfigurationInterface $config
     * @param array                                                            $params
     */
    public function process(DataConfigurationInterface $config, array $params)
    {
        $dataProcessContext = $this->createContext($params);

        $this->getLogger()->notice('PreProcess');

        $this->stopWatch->start('preProcess');
        $config->preProcess($dataProcessContext);
        $this->stopWatch->stop('preProcess');
        $this->getLogger()->notice(sprintf('Executed in %s', $this->stopWatch->getFinishTime('preProcess')->format('%hh %im %ss')));
        if ($config->getEntityManager()) {
            $this->setEntityManager($config->getEntityManager());
        }

        $etlProcesses = $config->getETLProcesses($dataProcessContext);
        $dataProcessContext->setEtlProcesses($etlProcesses);

        $this->getLogger()->notice('Execute ETL Processes');

        $this->executeETLProcesses($etlProcesses);

        $this->getLogger()->notice('PostProcess');
        $this->stopWatch->start('postProcess');
        $config->postProcess($dataProcessContext);
        $this->stopWatch->stop('postProcess');
        $this->getLogger()->notice(sprintf('Executed in %s', $this->stopWatch->getFinishTime('postProcess')->format('%hh %im %ss')));
    }

    /**
     * Execute ETL processes.
     *
     * @param array $etlProcesses
     */
    protected function executeETLProcesses(array $etlProcesses)
    {
        foreach ($etlProcesses as $etlProcess) {
            $etlProcess->setLogger($this->getLogger());
            if (!$etlProcess->getContext()) {
                $etlProcess->setContext(new Context());
            }

            if ($etlProcess instanceof SqlETLProcess) {
                $etlProcess->setDatasourceManager($this->getDatasourceManager());
            }
            $this->stopWatch->start('etlProcess');
            $etlProcess->process();
            $this->stopWatch->stop('etlProcess');
            $this->getLogger()->notice(sprintf('Executed in %s', $this->stopWatch->getFinishTime('etlProcess')->format('%hh %im %ss')));
        }
        $this->getDatasourceManager()->clear();
    }

    /**
     * setEntityManager.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager
     *
     * @return \Cifren\OxPeckerData\Core\DataProcess
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * getEntityManager.
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
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
     * @throws \Exception
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            throw new \Exception('did you forget to setLogger ?');
        }

        return $this->logger;
    }

    /**
     * setLogger.
     *
     * @param Logger $logger
     *
     * @return \Cifren\OxPeckerData\Core\DataProcess
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param \Cifren\OxPeckerData\ETL\SQL\DataSource\DataSourceManager $datasourceManager
     *
     * @return \Cifren\OxPeckerData\Core\DataProcess
     */
    public function setDatasourceManager(DataSourceManager $datasourceManager)
    {
        $this->dataSourceManager = $datasourceManager;

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return DataSourceManager
     */
    public function getDatasourceManager()
    {
        if (!$this->dataSourceManager) {
            throw new \Exception('did you forget to setDatasourceManager ?');
        }
        $this->dataSourceManager->setEntityManager($this->getEntityManager());

        return $this->dataSourceManager;
    }
}
