<?php

namespace Cifren\OxPeckerData\Definition;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;

class DataConfiguration implements DataConfigurationInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    /**
     * Define all your Etl process here.
     *
     * @param Context $context
     *
     * @return array
     */
    public function getETLProcesses(Context $context): array
    {
        return [];
    }

    /**
     * Define all actions you want to execute before loading.
     *
     * @param Context $context
     */
    public function preProcess(Context $context): void
    {
    }

    /**
     * Define all actions you want to execute after the process done.
     *
     * @param Context $context
     */
    public function postProcess(Context $context): void
    {
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
