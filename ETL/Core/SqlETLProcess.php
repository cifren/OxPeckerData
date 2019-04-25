<?php

namespace Cifren\OxPeckerDataBundle\ETL\Core;

use Cifren\OxPeckerDataBundle\ETL\SQL\DataSource\ORMDataSource;
use Knp\ETL\ContextInterface;
use Cifren\OxPeckerDataBundle\ETL\SQL\DataSource\DataSourceManager;
use Psr\Log\LoggerInterface;

class SqlETLProcess implements ETLProcessInterface
{
    protected $context;
    protected $datasource;
    protected $logger;
    protected $datasourceManager;

    public function __construct($query, $entityName, array $mapping, array $options = null)
    {
        $this->datasource = new ORMDataSource($query, $entityName, $mapping, $options);
    }

    public function process()
    {
        $this->getDatasourceManager()->processDataSource($this->datasource);
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setContext(ContextInterface $context)
    {
        $this->context = $context;

        return $this;
    }

    public function getLogger()
    {
        if (!$this->logger) {
            throw new \Exception('did you forget to setLogger ?');
        }

        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    public function getDatasourceManager()
    {
        if (!$this->datasourceManager) {
            throw new \Exception('Did you setDatasourceManager() ?');
        }

        return $this->datasourceManager;
    }

    public function setDatasourceManager(DataSourceManager $datasourceManager)
    {
        $this->datasourceManager = $datasourceManager;

        return $this;
    }
}
