<?php

namespace Cifren\OxPeckerData\ETL\Iteration;

use Psr\Log\LoggerInterface;

/**
 * Cifren\OxPeckerData\ETL\Iteration\LoggableInterface.
 */
interface LoggableInterface
{
    public function setLogger(LoggerInterface $logger);

    public function getLogger();
}
