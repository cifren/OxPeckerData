<?php

namespace Cifren\OxPeckerData\Definition;

use Psr\Log\LoggerInterface;

interface DataConfigurationInterface
{
    /**
     * @param \Cifren\OxPeckerData\Definition\Context $context
     */
    public function getETLProcesses(Context $context): array;

    /**
     * @param \Cifren\OxPeckerData\Definition\Context $context
     */
    public function preProcess(Context $context): void;

    /**
     * @param \Cifren\OxPeckerData\Definition\Context $context
     */
    public function postProcess(Context $context): void;

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void;
}
