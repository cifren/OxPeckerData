<?php

namespace Cifren\OxPeckerData\Definition;

use Knp\ETL\ContextInterface as baseContextInterface;

interface ContextInterface extends baseContextInterface
{
    /**
     * @return array
     */
    public function getArgs();

    /**
     * @param array $args
     */
    public function setArgs(array $args);

    /**
     * @return array
     */
    public function getEtlProcesses();

    /**
     * @param array $etlProcesses
     */
    public function setEtlProcesses(array $etlProcesses);
}
