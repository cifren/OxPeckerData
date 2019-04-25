<?php

namespace Cifren\OxPeckerDataBundle\ETL\SQL\DataSource;

use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;

class DataSourceManager
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
     * @var type
     */
    protected $uniqueId;

    /**
     * @var type
     */
    protected $derivedAliases = array();

    /**
     * @var type
     */
    protected $temporaryTableNames = array();

    public function __construct(EntityManager $entityManager, LoggerInterface $logger)
    {
        $this->uniqueId = uniqid();
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * createTableFromDataSource.
     *
     * @param \Cifren\OxPeckerDataBundle\DataSource\ORMDataSource $dataSource
     */
    public function processDataSource(ORMDataSource $dataSource)
    {
        $entityName = $dataSource->getEntityName();

        $this->getLogger()->notice(" - $entityName");
        $options = $dataSource->getOptions();
        if ($options['tableType'] == ORMDataSourceType::REGULAR_TABLE && $options['dropOnInit']) {
            $this->dropTable($entityName);
        }

        if ($options['tableType'] != ORMDataSourceType::DERIVED_TABLE) {
            $this->createTable($entityName, $options['tableType'] == ORMDataSourceType::TEMPORARY_TABLE);
            $this->insertTable($entityName, $dataSource->getQuery(), $dataSource->getMapping(), $options['commentMessage']);
        } else {
            $this->createDerivedAliases($entityName, $this->getSql($dataSource->getQuery()));
        }
    }

    /**
     * dropTable.
     *
     * @param string $entityName
     */
    public function dropTable($entityName)
    {
        //very Slow
//        $tool = new SchemaTool($this->getEntityManager());
//        $classes = array(
//            $this->getEntityManager()->getClassMetadata($entityName),
//        );
//        $tool->dropSchema($classes);

        $classMetadata = $this->getEntityManager()->getClassMetadata($entityName);
        $sql = "DROP TABLE {$classMetadata->getTableName()}";

        try {
            $this->getEntityManager()->getConnection()->query($sql);
            $this->getLogger()->notice("Drop Table $entityName");
        } catch (\Exception $e) {
            $this->getLogger()->debug("Catch Exception {$e->getMessage()}");
        }
    }

    /**
     * createTable.
     *
     * @param string $entityName
     */
    public function createTable($entityName, $temporary = false)
    {
        $classMetaData = $this->getEntityManager()->getClassMetadata($entityName);

        if ($temporary) {
            //create temporary and unique name
            $originTableName = $classMetaData->getTableName();
            $newTableName = $originTableName.$this->uniqueId;
            $classMetaData->setTableName($newTableName);

            //store information
            $this->temporaryTableNames[$entityName]['origin'] = $originTableName;
            $this->temporaryTableNames[$entityName]['new'] = $newTableName;
        }
        $tool = new SchemaTool($this->getEntityManager());
        $classes = array(
            $classMetaData,
        );
        $sql = $tool->getCreateSchemaSql($classes)[0];

        //if temporary table
        if ($temporary) {
            $sql = str_replace('CREATE TABLE ', 'CREATE TEMPORARY TABLE ', $sql);
        }
        //try to do it, if not, means table already exist
        try {
            $conn = $this->getEntityManager()->getConnection();
            $conn->executeQuery($sql);
            $this->getLogger()->notice('Create '.(!$temporary ?: 'Temporary')." Table $entityName");
        } catch (\Exception $e) {
            $this->getLogger()->debug("Catch Exception {$e->getMessage()}");
        }
    }

    /**
     * insertTable.
     *
     * @param string                    $entityName
     * @param string|Query|QueryBuilder $query
     * @param array                     $mapping
     */
    public function insertTable($entityName, $query, array $mapping, $commentMessage)
    {
        $this->getLogger()->notice("Insert Table $entityName");
        $classMetadata = $this->getEntityManager()->getClassMetadata($entityName);

        $connection = $this->getEntityManager()->getConnection();

        $fieldsString = null;
        foreach ($mapping as $targetField) {
            $fieldsString .= $fieldsString ? ',' : '';

            $fieldsString .= $this->getColumnName($classMetadata, $targetField);
        }

        $sqlSelect = $this->getSql($query);
        $sql = "INSERT INTO {$classMetadata->getTableName()} ($fieldsString) ({$sqlSelect})";

        $connection->query($sql);
        $sqlRowCount = 'SELECT ROW_COUNT() as count';
        $stmt = $connection->query($sqlRowCount);
        $result = $stmt->fetch();
        $this->getLogger()->notice("{$result['count']} row inserted".($commentMessage ? " -- Comment: {$commentMessage}" : null));
    }

    protected function createDerivedAliases($entityName, $query)
    {
        if (preg_match('/'.ORMDataSource::DERIVED_ALIAS.'/', $entityName) != true) {
            throw new \Exception("The entityName '{$entityName}' should contain '".ORMDataSource::DERIVED_ALIAS."' at the beginning of the name");
        }

        $this->derivedAliases[$entityName] = $query;
    }

    /**
     * Get Column Name from the table giving targetfield from the entity.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $classMetadata
     * @param string                              $targetField
     *
     * @return string
     */
    public function getColumnName(ClassMetadata $classMetadata, $targetField)
    {
        if (isset($classMetadata->columnNames[$targetField])) {
            $columnName = $classMetadata->getFieldMapping($targetField)['columnName'];
        } else {
            $columnName = $classMetadata->getAssociationMapping($targetField)['joinColumns'][0]['name'];
        }

        return $columnName;
    }

    /**
     * getEntityManager.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     *
     * @return \Cifren\OxPeckerDataBundle\DataSource\DataSourceManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * getSql.
     *
     * @param string|Query|QueryBuilder $query
     *
     * @return string
     */
    protected function getSql($query)
    {
        $isSqlQuery = true;
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
            $isSqlQuery = false;
        }

        if ($query instanceof Query) {
            $sql = $query->getSql();
            $isSqlQuery = false;
        } else {
            $sql = $query;
        }

        //apply table name from entity for temporary table
        if ($isSqlQuery && preg_match_all('/%[^%]*%/', $sql, $matches)) {
            foreach ($matches[0] as $match) {
                if (isset($this->temporaryTableNames[substr($match, 1, -1)])) {
                    $sql = str_replace($match, $this->temporaryTableNames[substr($match, 1, -1)]['new'], $sql);
                }
            }
        }

        //replace derived alias by sql statement
        if (preg_match_all('/%'.ORMDataSource::DERIVED_ALIAS.'[^%]*%/', $sql, $matches)) {
            foreach ($matches[0] as $match) {
                if (isset($this->derivedAliases[substr($match, 1, -1)])) {
                    $sql = str_replace($match, '('.$this->derivedAliases[substr($match, 1, -1)].')', $sql);
                }
            }
        }

        return $sql;
    }

    /**
     * getLogger.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     *
     * @throws \Exception
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
     * @param LoggerInterface $logger
     *
     * @return \Cifren\OxPeckerDataBundle\DataSource\DataSourceManager
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    public function getDerivedAliases()
    {
        return $this->derivedAliases;
    }

    public function getTemporaryTableNames()
    {
        return $this->derivedAliases;
    }

    public function clear()
    {
        //put back entities used, like doctrine definition
        foreach ($this->temporaryTableNames as $key => $tableName) {
            $classMetaData = $this->getEntityManager()->getClassMetadata($key);
            $classMetaData->setTableName($tableName['origin']);
        }

        //clear all variables
        $this->temporaryTableNames = array();
        $this->derivedAliases = array();
        $this->uniqueId = uniqid();
    }
}
