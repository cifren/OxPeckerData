<?php

namespace Cifren\OxPeckerData\ETL\Iteration\Extractor\Doctrine;

use Knp\ETL\Extractor\Doctrine\ORMExtractor as BaseExtractor;
use Knp\ETL\ContextInterface;
use Cifren\OxPeckerData\ETL\Iteration\LoggableInterface;
use Psr\Log\LoggerInterface;

/**
 * Cifren\OxPeckerData\ETL\Extractor\Doctrine\ORMEXtractor.
 */
class ORMExtractor extends BaseExtractor implements LoggableInterface
{
    /**
     * @var \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected $query;

    /**
     * @var int
     */
    protected $count;

    /**
     * Could be a Query or a QueryBuilder.
     */
    public function __construct($query)
    {
        if (empty($query)) {
            throw new \Exception('Query can\'t be empty');
        }
        $this->query = $query;
    }

    /**
     * Seems to have a bug in Knp Bundle, correctif here.
     *
     * @param \Knp\ETL\ContextInterface $context
     *
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    public function extract(ContextInterface $context)
    {
        $current = $this->next();

        return $current[0];
    }

    /**
     * @return type
     */
    public function getQuery()
    {
        if ($this->query instanceof \Doctrine\ORM\QueryBuilder) {
            return $this->query->getQuery();
        }

        return $this->query;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return ORMExtractor
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    public function getCount()
    {
        if (!$this->count) {
            $this->count = count($this->getQuery()->getResult());
        }

        return $this->count;
    }
}
