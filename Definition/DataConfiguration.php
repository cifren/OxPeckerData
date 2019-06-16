<?php

namespace Cifren\OxPeckerData\Definition;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use Cifren\OxPeckerData\ETL\SQL\DataSource\ORMDataSource;

class DataConfiguration implements DataConfigurationInterface
{
    protected $entityManager;
    protected $defaultOptions;
    protected $derivedAlias = ORMDataSource::DERIVED_ALIAS;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * authorized arguments.
     */
    public function setParamsMapping()
    {
        return null;
    }

    /**
     * Define all your Etl process here.
     *
     * @param \Cifren\OxPeckerData\Definition\Context $context
     *
     * @return array
     */
    public function getETLProcesses(Context $context)
    {
        return [];
    }

    /**
     * Define all actions you want to execute before loading.
     *
     * @param \Cifren\OxPeckerData\Definition\Context $context
     */
    public function preProcess(Context $context)
    {
    }

    /**
     * Define all actions you want to execute after the process done.
     *
     * @param \Cifren\OxPeckerData\Definition\Context $context
     */
    public function postProcess(Context $context)
    {
    }

    /**
     * Define array of DataSources executed by DataSourceManager.
     *
     * @param \Cifren\OxPeckerData\Definition\Context $context
     *
     * @return array
     */
    public function getDataSources(Context $context)
    {
        return [];
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
     * @param \Symfony\Bridge\Monolog\Logger $logger
     *
     * @return \Cifren\OxPeckerData\Definition\DataConfiguration
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    protected function setOptions(array $defaultOptions)
    {
        return $defaultOptions;
    }

    public function getOptions()
    {
        return $this->setOptions($this->getDefaultOptions());
    }

    protected function getDefaultOptions()
    {
        return $this->defaultOptions;
    }

    protected function setDefaultOptions(array $defaultOptions)
    {
        $this->defaultOptions = $defaultOptions;

        return $this;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     *
     * @return \Cifren\OxPeckerData\Definition\DataConfiguration
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    public function setQueueGroupName($name, array $args)
    {
        return null;
    }

    public function setQueueUniqueId($name, array $args)
    {
        return null;
    }
}
