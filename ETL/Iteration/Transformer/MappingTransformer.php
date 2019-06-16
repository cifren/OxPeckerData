<?php

namespace Cifren\OxPeckerData\ETL\Iteration\Transformer;

use Knp\ETL\TransformerInterface;
use Knp\ETL\Transformer\DataMap;
use Knp\ETL\ContextInterface;
use Cifren\OxPeckerData\ETL\Iteration\LoggableInterface;
use Psr\Log\LoggerInterface;

class MappingTransformer implements TransformerInterface, LoggableInterface
{
    private $className;
    private $mapper;

    public function __construct($className, DataMap $mapper)
    {
        $this->className = $className;
        $this->mapper = $mapper;
    }

    public function transform($data, ContextInterface $context)
    {
        $object = new $this->className();

        $this->mapper->set($data, $object);

        return $object;
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
     * @return MappingTransformer
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
