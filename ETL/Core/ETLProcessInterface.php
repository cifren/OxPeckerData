<?php

namespace Cifren\OxPeckerData\ETL\Core;

use Knp\ETL\ContextInterface;
use Psr\Log\LoggerInterface;

interface ETLProcessInterface
{
    public function process();

    public function getContext();

    public function setContext(ContextInterface $context);

    public function setLogger(LoggerInterface $logger);
}
