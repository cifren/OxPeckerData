<?php

namespace Cifren\OxPeckerDataBundle\ETL\Core;

use Knp\ETL\ExtractorInterface;
use Knp\ETL\TransformerInterface;
use Knp\ETL\LoaderInterface;
use Knp\ETL\ContextInterface;
use Psr\Log\LoggerInterface;
use Cifren\OxPeckerDataBundle\ETL\Iteration\LoggableInterface;

class IterationETLProcess implements ETLProcessInterface, LoggableInterface
{
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

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(ExtractorInterface $extractor, array $transformers, LoaderInterface $loader, LoggerInterface $logger = null)
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
        $extractor->setLogger($this->getLogger());
        $transformers = $this->getTransformers();
        foreach ($transformers as $tranformer) {
            $tranformer->setLogger($this->getLogger());
        }
        $loader = $this->getLoader();
        $loader->setLogger($this->getLogger());

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

    public function getLoader()
    {
        return $this->loader;
    }

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

    public function getLogger()
    {
        if (!$this->logger) {
            throw new \Exception('did you forget to setLogger ?');
        }

        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
