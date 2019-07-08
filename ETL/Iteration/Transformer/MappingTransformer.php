<?php

namespace Cifren\OxPeckerData\ETL\Iteration\Transformer;

use Knp\ETL\TransformerInterface;
use Knp\ETL\Transformer\DataMap;
use Knp\ETL\ContextInterface;

class MappingTransformer implements TransformerInterface
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
}
