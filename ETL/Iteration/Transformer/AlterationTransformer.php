<?php

namespace Cifren\OxPeckerData\ETL\Iteration\Transformer;

use Knp\ETL\ContextInterface;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**.
 *
 * @author cifren
 */
class AlterationTransformer extends AbstractTransformer
{
    /**
     * @var mixed
     */
    protected $transformerFunction;

    /**
     * @var array
     */
    protected $args;

    /**
     * @param mixed $arg1 can be :
     *                    - a closure
     *                    - a class
     * @param mixed $arg2 can be :
     *                    - an array of arguments
     *                    - a method name
     * @param array $arg3 will be an array of arguments
     *
     * @throws \Exception
     */
    public function __construct($arg1, $arg2 = null, $arg3 = null)
    {
        //if closure
        if ($arg1 instanceof \Closure) {
            $this->transformerFunction = $arg1;
            $this->args = $arg2;
        } elseif (is_object($arg1)) { //if class and methodName
            $this->transformerFunction = [$arg1, $arg2];
            $this->args = $arg3;
        } else {
            throw new \Exception('Arguments must of type closure or object');
        }
    }

    public function transform($data, ContextInterface $context)
    {
        return $data;
    }
}
