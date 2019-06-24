<?php

namespace Cifren\OxPeckerData\ETL\Core;

use Cifren\OxPeckerData\ETL\Iteration\Extractor\Doctrine\ORMExtractorInterface;
use Cifren\OxPeckerData\ETL\Iteration\Loader\Doctrine\ORMLoaderInterface;
use Knp\ETL\ExtractorInterface;
use Knp\ETL\TransformerInterface;
use Knp\ETL\LoaderInterface;
use Knp\ETL\ContextInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class IterationETLProcess implements ETLProcessInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var ExtractorInterface
     */
    protected $extractor;

    /**
     * @var TransformerInterface
     */
    protected $transformers;

    /**
     * @var LoaderInterface
     */
    protected $loader;

    public function __construct(ORMExtractorInterface $extractor, array $transformers, ORMLoaderInterface $loader, LoggerInterface $logger = null)
    {
        $this->extractor = $extractor;
        $this->transformers = $transformers;
        $this->loader = $loader;
        $this->setLogger($logger);
    }

    public function process()
    {
        $context = $this->getContext();
        $extractor = $this->getExtractor();
        $extractor->setLogger($this->logger);
        $transformers = $this->getTransformers();
        foreach ($transformers as $tranformer) {
            $tranformer->setLogger($this->logger);
        }
        $loader = $this->getLoader();
        $loader->setLogger($this->logger);

        $i = 0;
        if (!$extractor) {
            $this->logger->notice('No Extractor');

            return null;
        }

        if (null !== $this->logger) {
            $this->logger->notice('Start Iteration ETL Process');
        }

        if (null !== $this->logger) {
            $countItems = $extractor->getCount();
            $this->logger->notice(sprintf('Will extract %d items', $countItems));
        }

        while (null !== $input = $extractor->extract($this->getContext())) {
            foreach ($transformers as $transformer) {
                $output = $transformer->transform($input, $context);
                if (!empty($output)) {
                    $input = $output;
                }
            }
            $loader->load($input, $context);
            ++$i;
        }

        if (null !== $this->logger) {
            $this->logger->notice(sprintf('ETL executed on %d items', $i));
        }

        $loader->flush($context);
    }

    /**
     * @return ORMLoaderInterface
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @return ORMExtractorInterface
     */
    public function getExtractor()
    {
        return $this->extractor;
    }

    public function getTransformers()
    {
        return $this->transformers;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setLoader(LoaderInterface $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    public function setExtractor(ExtractorInterface $extractor)
    {
        $this->extractor = $extractor;

        return $this;
    }

    public function setTransformers(TransformerInterface $transformers)
    {
        $this->transformers = $transformers;

        return $this;
    }

    public function setContext(ContextInterface $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
