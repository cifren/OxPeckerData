<?php

namespace Cifren\OxPeckerData\ETL\Iteration\Loader\Doctrine;

use Knp\ETL\ContextInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

abstract class AbstractLoader implements ORMLoaderInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    abstract public function load($input, ContextInterface $context);

    abstract public function setLogger(LoggerInterface $logger);

    abstract public function flush(ContextInterface $context);

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
