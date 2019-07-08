<?php

namespace Cifren\OxPeckerData\ETL\Iteration\Extractor\Doctrine;

use Psr\Log\LoggerInterface;

interface ORMExtractorInterface
{
    public function setLogger(LoggerInterface $logger);

    public function getCount();

    public function extract();
}
