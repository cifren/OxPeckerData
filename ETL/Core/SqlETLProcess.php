<?php

namespace Cifren\OxPeckerData\ETL\Core;

use Cifren\OxPeckerData\ETL\SQL\DataSource\DataSourceManager;
use Cifren\OxPeckerData\ETL\SQL\DataSource\ORMDataSource;

class SqlETLProcess extends AbstractETLProcess
{
    /**
     * @var ORMDataSource
     */
    protected $datasource;

    /**
     * @var DataSourceManager
     */
    protected $datasourceManager;

    /**
     * SqlETLProcess constructor.
     * @param $query
     * @param $entityName
     * @param array $mapping
     * @param array|null $options
     */
    public function __construct($query, $entityName, array $mapping, array $options = null)
    {
        $this->datasource = new ORMDataSource($query, $entityName, $mapping, $options);
    }

    /**
     * @throws \Exception
     */
    public function process(): void
    {
        $this->getDatasourceManager()->processDataSource($this->datasource);
    }

    /**
     * @return DataSourceManager
     * @throws \Exception
     */
    public function getDatasourceManager(): DataSourceManager
    {
        if (!$this->datasourceManager) {
            throw new \Exception('Did you setDatasourceManager() ?');
        }

        return $this->datasourceManager;
    }

    /**
     * @param DataSourceManager $datasourceManager
     * @return ETLProcessInterface
     */
    public function setDatasourceManager(DataSourceManager $datasourceManager): ETLProcessInterface
    {
        $this->datasourceManager = $datasourceManager;

        return $this;
    }
}
