<?php

namespace Cifren\OxPeckerData\ETL\Iteration\Extractor\Doctrine;

use Psr\Log\LoggerAwareTrait;

/**
 * Cifren\OxPeckerData\ETL\Extractor\Doctrine\ORMExtractor.
 */
class ORMExtractor implements ORMExtractorInterface
{
    use LoggerAwareTrait;

    /**
     * @var \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected $query;

    /**
     * @var int
     */
    protected $count;

    /**
     * @var
     */
    protected $iterator;

    /**
     * Could be a Query or a QueryBuilder.
     */
    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * Seems to have a bug in Knp Bundle, correctif here.
     *
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    public function extract()
    {
        $current = $this->next();

        return $current[0];
    }

    /**
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    public function getQuery()
    {
        if ($this->query instanceof \Doctrine\ORM\QueryBuilder) {
            return $this->query->getQuery();
        }

        return $this->query;
    }

    public function getCount()
    {
        if (!$this->count) {
            $this->count = count($this->getQuery()->getResult());
        }

        return $this->count;
    }

    public function rewind()
    {
        return $this->getIterator()->rewind();
    }

    public function current()
    {
        return $this->getIterator()->current();
    }

    public function key()
    {
        return $this->getIterator()->key();
    }

    public function next()
    {
        $next = $this->getIterator()->next();
        $this->logger->debug('Next SQL', ['sql' => $this->getQuery()->getSql()]);

        return $next;
    }

    public function valid()
    {
        return $this->getIterator()->valid();
    }

    public function getIterator()
    {
        if (null === $this->iterator) {
            $this->iterator = $this->getQuery()->iterate();
        }

        return $this->iterator;
    }
}
