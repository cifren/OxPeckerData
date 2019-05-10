<?php

namespace Cifren\OxPeckerDataBundle\ETL\Iteration\Transformer;

use Knp\ETL\ContextInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class ArrayAlterationTransformer extends AlterationTransformer
{
    protected $transformerFunction;

    public function transform($array, ContextInterface $context)
    {
        if (!is_array($array)) {
            throw new UnexpectedTypeException($array, 'array');
        }
        $args = array_merge([$array], $this->args);
        $arrayTransformed = call_user_func_array($this->transformerFunction, $args);

        return $arrayTransformed;
    }
}
