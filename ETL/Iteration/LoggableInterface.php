<?php

namespace Cifren\OxPeckerDataBundle\ETL\Iteration;

use Psr\Log\LoggerInterface;

/**
 * Cifren\OxPeckerDataBundle\ETL\Iteration\LoggableInterface.
 */
interface LoggableInterface
{
    public function setLogger(LoggerInterface $logger);

    public function getLogger();
}
