<?php

namespace Cifren\OxPeckerData\ETL\Core;

use Knp\ETL\ContextInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;

abstract class AbstractETLProcess implements ETLProcessInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    protected $context;

    abstract public function process();

    public function getContext()
    {
        return $this->context;
    }

    public function setContext(ContextInterface $context)
    {
        $this->context = $context;

        return $this;
    }

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
