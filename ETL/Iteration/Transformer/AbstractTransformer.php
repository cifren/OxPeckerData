<?php

namespace Cifren\OxPeckerData\ETL\Iteration\Transformer;

use Knp\ETL\ContextInterface;
use Knp\ETL\TransformerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;

abstract class AbstractTransformer implements TransformerInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    /**
     * transforms array data into specific representation.
     *
     * @param mixed $data the extracted data to transform
     *
     * @return mixed the transformed data
     */
    abstract public function transform($data, ContextInterface $context);

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
