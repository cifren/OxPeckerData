<?php

namespace Cifren\OxPeckerDataBundle\ETL\Iteration\Transformer;

use Knp\ETL\TransformerInterface;
use Knp\ETL\Transformer\DataMap;
use Knp\ETL\ContextInterface;
use Cifren\OxPeckerDataBundle\ETL\Iteration\LoggableInterface;
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
     * @return \Cifren\OxPeckerDataBundle\ETL\Iteration\Transformer\ObjectAlterationTransformer
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
