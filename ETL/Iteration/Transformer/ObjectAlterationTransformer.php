<?php

namespace Cifren\OxPeckerDataBundle\ETL\Iteration\Transformer;

use Knp\ETL\ContextInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class ObjectAlterationTransformer extends AlterationTransformer
{
    protected $transformerFunction;

    public function transform($object, ContextInterface $context)
    {
        if (!is_object($object)) {
            throw new UnexpectedTypeException($object, 'object');
        }

        $args = array_merge(array($object), $this->args);
        $objectFromFunction = call_user_func_array($this->transformerFunction, $args);

        if (!empty($objectFromFunction)) {
            $objectTransformed = $objectFromFunction;
        }

        return $objectTransformed;
    }
}
