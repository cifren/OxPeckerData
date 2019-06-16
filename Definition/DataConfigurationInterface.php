<?php

namespace Cifren\OxPeckerData\Definition;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;

interface DataConfigurationInterface
{
    /**
     * @param \Cifren\OxPeckerData\Definition\Context $context
     *
     * @return array
     */
    public function getDataSources(Context $context);

    /**
     * @param \Cifren\OxPeckerData\Definition\Context $context
     */
    public function getETLProcesses(Context $context);

    /**
     * @param \Cifren\OxPeckerData\Definition\Context $context
     */
    public function preProcess(Context $context);

    /**
     * @param \Cifren\OxPeckerData\Definition\Context $context
     */
    public function postProcess(Context $context);

    public function setParamsMapping();

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger);

    public function getOptions();

    public function getEntityManager();

    public function setEntityManager(EntityManager $entityManager);

    public function setQueueGroupName($name, array $args);

    public function setQueueUniqueId($name, array $args);
}
