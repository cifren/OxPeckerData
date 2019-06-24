<?php

namespace Cifren\OxPeckerData\ETL\Iteration\Loader\Doctrine;

use Knp\ETL\ContextInterface;
use Psr\Log\LoggerInterface;

interface ORMLoaderInterface
{
    public function load($input, ContextInterface $context);

    public function setLogger(LoggerInterface $logger);

    public function flush(ContextInterface $context);
}
