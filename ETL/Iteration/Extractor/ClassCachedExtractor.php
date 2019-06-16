<?php

namespace Cifren\OxPeckerData\ETL\Iteration\Extractor;

use Knp\ETL\ExtractorInterface;
use Knp\ETL\ContextInterface;
use Cifren\OxPeckerData\ETL\Iteration\LoggableInterface;
use Psr\Log\LoggerInterface;

/**
 * Allow the user to add a class function loading in order to get an array of data.
 */
class ClassCachedExtractor implements ExtractorInterface, \Iterator, \Countable, LoggableInterface
{
    protected $data;
    protected $aryFunction;
    protected $args;
    protected $count;

    public function __construct($sourceObject, $functionName, $args = null)
    {
        if (!is_object($sourceObject)) {
            throw new \Exception('The argument needs to be an object');
        }

        $this->aryFunction = [$sourceObject, $functionName];
        $this->args = $args;
    }

    public function extract(ContextInterface $context)
    {
        $current = $this->current();
        $this->next();

        return $current;
    }

    private function getIterator()
    {
        if (null === $this->data) {
            $data = call_user_func_array($this->aryFunction, $this->args);

            $this->data = new \ArrayIterator($data);
        }

        return $this->data;
    }

    public function valid()
    {
        return $this->getIterator()->valid();
    }

    public function count()
    {
        return $this->getIterator()->count();
    }

    public function seek($position)
    {
        return $this->getIterator()->seek($position);
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

        return $next;
    }

    public function getCount()
    {
        if (!$this->count) {
            $this->count = $this->count();
        }

        return $this->count;
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
     * @return ClassCachedExtractor
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
