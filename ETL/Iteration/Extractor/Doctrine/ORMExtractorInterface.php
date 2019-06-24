<?php

namespace Cifren\OxPeckerData\ETL\Iteration\Extractor\Doctrine;

interface ORMExtractorInterface
{
    public function setLogger(\Psr\Log\LoggerInterface $logger);

    public function getCount();

    public function extract(\Knp\ETL\ContextInterface $getContext);
}
