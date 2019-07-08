<?php

namespace Cifren\OxPeckerData\ETL\Iteration\Transformer;

use Knp\ETL\ContextInterface;

class ArrayAlterationTransformer extends AlterationTransformer
{
    protected $transformerFunction;

    public function transform($array, ContextInterface $context)
    {
        if (!is_array($array)) {
            throw new \Exception('array needed');
        }
        $args = array_merge([$array], $this->args);
        $arrayTransformed = call_user_func_array($this->transformerFunction, $args);

        return $arrayTransformed;
    }
}
