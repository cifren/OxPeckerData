<?php

namespace Cifren\OxPeckerData\ETL\Core;

use Cifren\OxPeckerData\ETL\Iteration\Extractor\Doctrine\ORMExtractorInterface;
use Cifren\OxPeckerData\ETL\Iteration\Loader\Doctrine\ORMLoaderInterface;
use Knp\ETL\LoaderInterface;
use Knp\ETL\TransformerInterface;

class IterationETLProcess extends AbstractETLProcess
{
    /**
     * @var ORMExtractorInterface
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

    public function __construct(ORMExtractorInterface $extractor, array $transformers, ORMLoaderInterface $loader)
    {
        $this->extractor = $extractor;
        $this->transformers = $transformers;
        $this->loader = $loader;
    }

    public function process()
    {
        $context = $this->getContext();
        $extractor = $this->getExtractor();
        $transformers = $this->getTransformers();
        foreach ($transformers as $tranformer) {
            if ($this->logger) {
                $tranformer->setLogger($this->logger);
            }
        }
        $loader = $this->getLoader();

        if ($this->logger) {
            $loader->setLogger($this->logger);
        }

        $i = 0;
        if (!$extractor) {
            $this->notice('No Extractor');

            return null;
        }

        $this->notice('Start Iteration ETL Process');

        if (null !== $this->logger) {
            $countItems = $extractor->getCount();
            $this->notice(sprintf('Will extract %d items', $countItems));
        }

        while (null !== $input = $extractor->extract()) {
            foreach ($transformers as $transformer) {
                $output = $transformer->transform($input, $context);
                if (!empty($output)) {
                    $input = $output;
                }
            }
            $loader->load($input, $context);
            ++$i;
        }

        $this->notice(sprintf('ETL executed on %d items', $i));

        $loader->flush($context);
    }

    /**
     * @return ORMLoaderInterface
     */
    public function getLoader(): ORMLoaderInterface
    {
        return $this->loader;
    }

    /**
     * @return ORMExtractorInterface
     */
    public function getExtractor(): ORMExtractorInterface
    {
        if ($this->logger) {
            $this->extractor->setLogger($this->logger);
        }

        return $this->extractor;
    }

    /**
     * @return array|TransformerInterface
     */
    public function getTransformers(): array
    {
        return $this->transformers;
    }
}
