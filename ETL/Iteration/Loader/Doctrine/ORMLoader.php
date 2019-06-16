<?php

namespace Cifren\OxPeckerData\ETL\Iteration\Loader\Doctrine;

use Psr\Log\LoggerAwareTrait;
use Doctrine\ORM\EntityManager;
use Knp\ETL\ContextInterface;
use Knp\ETL\LoaderInterface;
use Cifren\OxPeckerData\ETL\Iteration\LoggableInterface;
use Psr\Log\LoggerInterface;

class ORMLoader implements LoaderInterface, LoggableInterface
{
    use LoggerAwareTrait;

    private $counter = 0;
    private $flushEvery;
    private $entityManager;
    private $entityClass;

    public function __construct(EntityManager $entityManager, $flushEvery = 100)
    {
        $this->entityManager = $entityManager;
        $this->flushEvery = $flushEvery;
    }

    public function load($entity, ContextInterface $context)
    {
        if (null === $this->entityClass) {
            $this->entityClass = get_class($entity);
        }

        $this->entityManager->persist($entity);
        ++$this->counter;

        if (0 === $this->counter % $this->flushEvery) {
            $this->flush($context);
        }

        if (0 === $this->counter % $this->flushEvery) {
            $this->clear($context);
        }
    }

    public function flush(ContextInterface $context)
    {
        $this->entityManager->flush();
        if (null !== $this->logger) {
            $this->logger->debug(sprintf('flush after %d persist hits', $this->counter));
        }
    }

    public function clear(ContextInterface $context)
    {
        $this->entityManager->clear($this->entityClass);
        if (null !== $this->logger) {
            $this->logger->debug(sprintf('clear after %d persist hits', $this->counter));
        }
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return ORMLoader
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
